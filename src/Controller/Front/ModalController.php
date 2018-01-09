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
    var $ids = -1;

    /**
     * List media
     */
    public function listAction()
    {
        $draw = $this->params('draw');
        $length = $this->params('length');
        $start = $this->params('start');
        $keyword = $this->params('search');

        if(isset($keyword['value'])){
            $keyword = $keyword['value'];
        } else {
            $keyword = null;
        }

        if(Pi::service()->hasService('log')){
            Pi::service()->getService('log')->mute(true);
        }

        $where = array();

        if(Pi::engine()->section() == 'admin'){
            $roleModel = Pi::model('user_role');
            $select = $roleModel->select();
            $select->where(array('role' => 'admin'));
            $roleRowset = $roleModel->selectWith($select);

            $adminRoles = array();
            foreach($roleRowset as $role){
                $adminRoles[] = $role->uid;
            }

            $where['uid'] = $adminRoles;
        } else {
            $where['uid'] = Pi::user()->getId();
        }

        $where['time_deleted'] = 0;

        $mediaModel = Pi::model('doc', $this->getModule());
        $linkModel = Pi::model('link', $this->getModule());

        $select = $mediaModel->select();
        $select->where($where);
        $select->order('time_created DESC');

        if($keyword && trim($keyword)){

            $keyword = trim($keyword);
            $keywordArray = explode(' ', $keyword);
            $keywordBoolean = '+' . trim(implode(' +', $keywordArray));

            $select->where(
                new \Zend\Db\Sql\Predicate\Expression("MATCH(".$mediaModel->getTable() . ".title, ".$mediaModel->getTable() . ".description) AGAINST (? IN BOOLEAN MODE) OR ".$mediaModel->getTable() . ".title LIKE ? OR ".$mediaModel->getTable() . ".description LIKE ?", $keywordBoolean, '%' . $keyword . '%', '%' . $keyword . '%')
            );
            $select->columns(array_merge($select->getRawState($select::COLUMNS), array(
                new \Zend\Db\Sql\Expression("((MATCH(".$mediaModel->getTable() . ".title) AGAINST (?) * 2) + (MATCH(".$mediaModel->getTable() . ".description) AGAINST (?) * 1)) AS score", array($keyword, $keyword)),
            )));
            $select->order('score DESC, time_created DESC');
        }

        $resultsetFull = $mediaModel->selectWith($select);


        $select = $mediaModel->select();
        $select->where($where);

        $select->limit($length);
        $select->offset($start);
        $select->join(array('link' => $linkModel->getTable()), $mediaModel->getTable() . ".id = link.media_id", array(), \Zend\Db\Sql\Select::JOIN_LEFT);
        $select->group($mediaModel->getTable() . ".id");

        $select->columns(array_merge($select->getRawState($select::COLUMNS), array(
            new \Zend\Db\Sql\Expression('COUNT(DISTINCT link.id) as nb_links'),
        )));

        if($keyword && trim($keyword)){

            $keyword = trim($keyword);
            $keywordArray = explode(' ', $keyword);
            $keywordBoolean = '+' . trim(implode(' +', $keywordArray));

            $select->where(
                new \Zend\Db\Sql\Predicate\Expression("MATCH(".$mediaModel->getTable() . ".title, ".$mediaModel->getTable() . ".description) AGAINST (? IN BOOLEAN MODE) OR ".$mediaModel->getTable() . ".title LIKE ? OR ".$mediaModel->getTable() . ".description LIKE ?", $keywordBoolean, '%' . $keyword . '%', '%' . $keyword . '%')
            );
            $select->columns(array_merge($select->getRawState($select::COLUMNS), array(
                new \Zend\Db\Sql\Expression("((MATCH(".$mediaModel->getTable() . ".title) AGAINST (?) * 2) + (MATCH(".$mediaModel->getTable() . ".description) AGAINST (?) * 1)) AS score", array($keyword, $keyword)),
            )));
            $select->order('score DESC, time_created DESC');
        } else {
            $select->order('time_created DESC');
        }

        $resultset = $mediaModel->selectWith($select);

        $section = Pi::engine()->section() == 'admin' ? 'admin' : 'default';

        $data = array();
        foreach($resultset as $media) {

            $removeBtn = '';

            if (!$media->time_deleted) {
                $removeUrl = $this->url($section, array(
                    'controller'    => 'modal',
                    'action'        => 'delete',
                    'id'            => $media->id,
                ));

                $disabled = '';

                if($media->nb_links > 0){
                    $alertMsg = __("This media can't be deleted as it is already used by current or another item.");
                    $disabled = 'disabled="disabled" data-toggle="tooltip" title="'.$alertMsg.'"';
                }

                $removeBtn = <<<PHP
<a $disabled class="btn btn-danger btn-xs do-ajax remove-media-ajax" data-href="$removeUrl" data-value="delete">
    <span class="glyphicon glyphicon-remove" ></span >
</a>
PHP;

                $mediaEditForm = new MediaEditForm();
                $seasonOptions = $mediaEditForm->get('season')->getOptions();

                $buttons = '';
                foreach ($seasonOptions['value_options'] as $value => $label){
                    $class = $media->season == $value ? 'btn-primary' : 'btn-default';
                    $buttons .= '<button type="button" data-id="'.$value.'" class="btn btn-primary ' . $class . '">'.$label.'</button>';
                }

                $mediaFormActionUrl = Pi::service('url')->assemble(Pi::engine()->section() == 'admin' ? 'admin' : 'default', array(
                    'module' => 'media',
                    'controller' => 'modal',
                    'action' => 'mediaformSeason',
                ));

                $seasonBtn = <<<PHP
<div class="btn-group btn-group-xs season-switch" role="group" aria-label="Extra-small button group" data-url="{$mediaFormActionUrl}" data-id="{$media->id}">
    {$buttons}
</div>
PHP;
            }

            $img = (string) Pi::api('resize','media')->resize($media)->thumbcrop(100, 100);

            $data[] = array(
                'DT_RowAttr' => array(
                    'data-media-id' => $media['id'],
                    'data-media-img' => $img,
                    'data-media-season' => $media['season'],
                ),
                'checked' => '<span class="glyphicon glyphicon-ok"></span>',
                'img' => "<img src='" . $img . "' class='media-modal-thumb' />",
                'title' => $media->title,
                'date' => _date($media->time_created),
                'season' => $seasonBtn,
                'removeBtn' => $removeBtn,
            );
        }

        $output = array(
            "draw" => (int) $draw,
            "recordsTotal" => (int) $resultsetFull->count(),
            "recordsFiltered" => (int) $resultsetFull->count(),
            "data" => $data,
        );

        return $output;
    }

    /**
     * List media
     */
    public function currentSelectedMediaAction()
    {
        $ids = $this->params('ids');

        if(Pi::service()->hasService('log')){
            Pi::service()->getService('log')->mute(true);
        }

        $where = array();
        $where['id'] = explode(',', $ids);

        $mediaModel = Pi::model('doc', $this->getModule());

        $select = $mediaModel->select();
        $select->where($where);
        $select->order('time_created DESC');
        $resultset= $mediaModel->selectWith($select);

        $data = array();
        foreach($resultset as $media) {
            $data[] = array(
                'id' => $media->id,
                'img' => (string) Pi::api('resize','media')->resize($media)->thumbcrop(100, 100),
                'season' => $media->season,
            );
        }

        return $data;
    }

    /**
     * List media
     */
    public function formlistAction()
    {
        if(Pi::service()->hasService('log')){
            Pi::service()->getService('log')->mute(true);
        }

        $ids = (is_numeric($this->params('ids')) || $this->params('ids')) ? $this->params('ids') : $this->ids;

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

        header("X-Robots-Tag: noindex, nofollow", true);

        return Pi::service('view')->render($view->getViewModel());
    }

    /**
     * Media form
     */
    public function mediaformAction()
    {
        header("X-Robots-Tag: noindex, nofollow", true);

        if(Pi::service()->hasService('log')){
            Pi::service()->getService('log')->mute(true);
        }

        $id = $this->params('id');

        if($id){
            // Get media list
            $module = $this->getModule();
            $media = Pi::model('doc', $module)->find($id);

            // Front user can't delete media from others
            if(Pi::engine()->section() != 'admin' && $media->uid != Pi::user()->getId()){
                die('Not allowed');
            }

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

                        $title = preg_replace('#(.*)\.(.*)#', '$1', $file['file']['name']);
                        $title = str_replace(array('-','_','.'), ' ', $title);

                        // Set params
                        $params['filename'] = $file['file']['name'];
                        $params['title'] = $title;
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
                            'season' => $media->season,
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
     * Media form
     */
    public function mediaformSeasonAction()
    {
        if(Pi::service()->hasService('log')){
            Pi::service()->getService('log')->mute(true);
        }

        $id = $this->params('id');
        $season = $this->params('season');

        if($id){
            // Get media list
            $module = $this->getModule();
            $media = Pi::model('doc', $module)->find($id);

            // Front user can't delete media from others
            if(Pi::engine()->section() != 'admin' && $media->uid != Pi::user()->getId()){
                die('Not allowed');
            }

            if ($media && $media->id) {
                $media->season = $season;
                $media->time_updated = time();

                if ($uid = Pi::user()->getId()) {
                    $media->updated_by = $uid;
                }

                $media->save();
            }
        }

        return false;
    }

    /**
     * Upload media
     */
    public function uploadAction(){

        header("X-Robots-Tag: noindex, nofollow", true);

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

            $title = preg_replace('#(.*)\.(.*)#', '$1', $file['file']['name']);
            $title = str_replace(array('-','_','.'), ' ', $title);

            // Set params
            $params['filename'] = $file['file']['name'];
            $params['title'] = $title;
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

                foreach($response['upload_errors'] as &$value){
                    $value = '<li>' . $value . '</li>';
                }
                $response = '<ul>' . implode('', $response['upload_errors']) . '</ul>';
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

        $where = array('id' => $ids);

        // Front user can't delete media from others
        if(Pi::engine()->section() != 'admin'){
            $where['uid'] = Pi::user()->getId();
        }

        // Mark media as deleted
        $this->getModel('doc')->update(
            array('time_deleted' => time()),
            $where
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
