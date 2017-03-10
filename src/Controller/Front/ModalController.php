<?php
/**
 * Pi Engine (http://pialog.org)
 *
 * @link         http://code.pialog.org for the Pi Engine source repository
 * @copyright    Copyright (c) Pi Engine http://pialog.org
 * @license      http://pialog.org/license.txt BSD 3-Clause License
 */

namespace Module\Media\Controller\Front;

use Module\Media\Form\MediaEditFilter;
use Module\Media\Form\MediaEditForm;
use Pi;
use Pi\Mvc\Controller\ActionController;
use Pi\Paginator\Paginator;
use Zend\Db\Sql\Expression;
use Zend\Db\Sql\Predicate\In;

/**
 * Modal controller
 * 
 * @author Frédéric TISSOT <contact@espritdev.fr>
 */
class ModalController extends ActionController
{
    /**
     * List media
     */
    public function listAction()
    {
        if(Pi::service()->hasService('log')){
            Pi::service()->getService('log')->mute(true);
        }

        $where = array();
        $where['uid'] = Pi::user()->getId();
        $where['time_deleted'] = 0;

        // Get media list
        $module = $this->getModule();
        $resultset = Pi::api('doc', $module)->getList(
            $where,
            0,
            0,
            'time_created DESC'
        );

        // Total count
        $totalCount = $this->getModel('doc')->count($where);

        /* @var Pi\Mvc\Controller\Plugin\View $view */
        $view = $this->view();

        $view->setLayout('layout-content');
        $view->setTemplate('../front/modal-list');
        $view->assign(array(
            'title'      => _a('Resource List'),
            'medias'     => $resultset,
            'section'   => Pi::engine()->section() == 'admin' ? 'admin' : 'default',
        ));

        return Pi::service('view')->render($view->getViewModel());
    }

    /**
     * List media
     */
    public function formlistAction()
    {
        if(Pi::service()->hasService('log')){
            Pi::service()->getService('log')->mute(true);
        }

        $ids = $this->params('ids') ?: $this->ids;
        $mediaModel = Pi::model('doc', 'media');

        $where = array(
          new In($mediaModel->getTable().'.id', explode(',', $ids)),
        );


        $order = array(new Expression('FIELD ('.$mediaModel->getTable().'.id, '. $ids .')'));

        // Get media list
        $module = $this->getModule();
        $medias = Pi::api('doc', $module)->getList($where, 0, 0, $order);

        $haveToComplete = false;
        foreach($medias as $media){
            $hasInvalidFields = Pi::api('doc', 'media')->hasInvalidFields($media);

            if($hasInvalidFields){
                $haveToComplete = true;
            }
        }

        /* @var Pi\Mvc\Controller\Plugin\View $view */
        $view = $this->view();

        $view->setLayout('layout-content');
        $view->setTemplate('../front/modal-formlist');
        $view->assign(array(
            'title'      => _a('Resource List'),
            'medias'     => $medias,
            'haveToComplete'   => $haveToComplete,
        ));

        return Pi::service('view')->render($view->getViewModel());
    }

    /**
     * Media form
     */
    public function mediaformAction()
    {
        if(Pi::service()->hasService('log')){
            Pi::service()->getService('log')->mute(true);
        }

        $id = $this->params('id');

        if($id){
            // Get media list
            $module = $this->getModule();
            $media = Pi::model('doc', $module)->find($id);

            $form = new MediaEditForm('media', array('thumbUrl' => Pi::api('doc', 'media')->getUrl($media->id)));
            $form->setAttribute('action', $this->url('', array('action' => 'mediaform')) . '?id=' . $id);

            $form->setData($media->toArray());
            $form->setInputFilter(new MediaEditFilter());
            $form->get('submit')->setAttribute('class', 'hide');

            $view = new \Zend\View\Model\ViewModel;

            if ($this->request->isPost()) {
                $post = $this->request->getPost();
                // Get file type

                $form->setData($post);
                if ($form->isValid()) {

                    $formIsValid = true;
                    // upload image
                    $file = $this->request->getFiles();
                    if (!empty($file['file']['name'])) {
                        $this->currentId = $id;

                        if (extension_loaded('intl') && !normalizer_is_normalized($file['file']['name'])) {
                            $file['file']['name'] = normalizer_normalize($file['file']['name']);
                        }

                        // Set params
                        $params['filename'] = $file['file']['name'];
                        $params['title'] = $file['file']['name'];
                        $params['type'] = 'image';
                        $params['active'] = 1;
                        $params['module'] = 'media';
                        $params['uid'] = Pi::user()->getId();
                        $params['ip'] = Pi::user()->getIp();

                        // Upload media
                        $response = Pi::api('doc', 'media')->upload($params, $id);


                        if(!isset($response['path']) || !$response['path']){
                            $formIsValid = false;

                            $view->setVariable('message', implode('<br />', $response['upload_errors']));
                        } else {
                            $post['path'] = $response['path'];
                            $post['filename'] = $response['filename'];
                        }
                    }

                    if($formIsValid){
                        $media->assign($post);
                        $media->time_updated = time();

                        if ($uid = Pi::user()->getId()) {
                            $media->updated_by = $uid;
                        }

                        $media->save();

                        return array(
                            'status' => 1,
                            'content' => null,
                            'url' => (string) Pi::api('resize','media')->resizeFormList($media),
                        );
                    }
                }
            }


            $view->setTemplate('front/partial/modal-media-form');
            $view->setVariable('form', $form);

            return array(
                'status' => 0,
                'content' => Pi::service('view')->render($view),
            );
        }

        return false;
    }

    /**
     * Upload media
     */
    public function uploadAction(){
        $uid = Pi::user()->getId();

        if($uid){
            // Get file type
            $file = $this->request->getFiles();

            // Get main module
            $from = $this->params('from');
            if(isset($from) && !empty($from)) {
                $params['module'] = $from;
            } else {
                $params['module'] = $this->getModule();
            }

            if (extension_loaded('intl') && !normalizer_is_normalized($file['file']['name'])) {
                $file['file']['name'] = normalizer_normalize($file['file']['name']);
            }

            // Set params
            $params['filename'] = $file['file']['name'];
            $params['title'] = $file['file']['name'];
            $params['type'] = 'image';
            $params['active'] = 1;
            $params['module'] = $this->getModule();
            $params['uid'] = Pi::user()->getId();
            $params['ip'] = Pi::user()->getIp();

            // Upload media
            $response = Pi::api('doc', 'media')->upload($params);


            // Check
            if (!isset($response['id']) || !$response['id']) {
                http_response_code(500);

                $response = implode('<br />', $response['upload_errors']);
            } else {
                $response = __('Media uploaded successfully');
            }
        } else {
            http_response_code(500);
            $response = __('Missing user id');
        }

        echo $response;
        exit;
    }

    /**
     * Delete media resources
     *
     * @return ViewModel
     * @throws \Exception
     */
    public function deleteAction()
    {
        $id     = $this->params('id', 0);
        $ids    = array_filter(explode(',', $id));

        if (empty($ids)) {
            throw new \Exception(_a('Invalid media ID'));
        }

        // Mark media as deleted
        $this->getModel('doc')->update(
            array('time_deleted' => time()),
            array('id' => $ids)
        );

        return $this->redirect()->toRoute(
            '',
            array(
                'controller' => 'modal',
                'action'     => 'list',
            )
        );
    }

    /**
     * Undelete media resources
     *
     * @return ViewModel
     * @throws \Exception
     */
    public function undeleteAction()
    {
        $id     = $this->params('id', 0);
        $ids    = array_filter(explode(',', $id));

        if (empty($ids)) {
            throw new \Exception(_a('Invalid media ID'));
        }

        // Mark media as deleted
        $this->getModel('doc')->update(
            array('time_deleted' => null),
            array('id' => $ids)
        );

        // Go to list page or original page
        return $this->redirect()->toRoute(
            '',
            array(
                'controller' => 'modal',
                'action'     => 'list',
            )
        );
    }
}
