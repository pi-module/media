<?php
/**
 * Pi Engine (http://pialog.org)
 *
 * @link         http://code.pialog.org for the Pi Engine source repository
 * @copyright    Copyright (c) Pi Engine http://pialog.org
 * @license      http://pialog.org/license.txt BSD 3-Clause License
 */

namespace Module\Media\Controller\Front;

use Pi;
use Pi\Mvc\Controller\ActionController;
use Pi\Paginator\Paginator;

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
        $params = array();

        $page   = (int) $this->params('p', 1);
        $limit  = 5;
        $offset = ($page - 1) * $limit;

        $where['uid'] = Pi::user()->getId();
        $where['time_deleted'] = 0;

        // Get media list
        $module = $this->getModule();
        $resultset = Pi::api('doc', $module)->getList(
            $where,
            $limit,
            $offset,
            'time_created DESC'
        );

        // Total count
        $totalCount = $this->getModel('doc')->count($where);

        // Paginator
        $paginator = Paginator::factory($totalCount, array(
            'page' => $page,
            'url_options'   => array(
                'page_param' => 'p',
                'params'     => array_filter(array_merge(array(
                    'module'        => $this->getModule(),
                    'controller'    => 'modal',
                    'action'        => 'list',
                ), $params)),
            ),
        ));

        /* @var Pi\Mvc\Controller\Plugin\View $view */
        $view = $this->view();

        $view->setLayout('layout-content');
        $view->setTemplate('modal-list');
        $view->assign(array(
            'title'      => _a('Resource List'),
            'medias'     => $resultset,
            'paginator'  => $paginator,
            'section'   => Pi::engine()->section() == 'admin' ? 'admin' : 'default',
        ));

        return Pi::service('view')->render($view->getViewModel());
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

            // Set params
            $params['filename'] = $file['file']['name'];
            $params['title'] = $file['file']['name'];
            $params['type'] = 'image';
            $params['active'] = 1;
            $params['module'] = $this->getModule();
            $params['uid'] = Pi::user()->getId();
            $params['ip'] = Pi::user()->getIp();

            // Upload media
            $response = Pi::api('doc', 'media')->upload($params, 'POST');


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
