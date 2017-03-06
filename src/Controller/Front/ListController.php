<?php
/**
 * Pi Engine (http://pialog.org)
 *
 * @link         http://code.pialog.org for the Pi Engine source repository
 * @copyright    Copyright (c) Pi Engine http://pialog.org
 * @license      http://pialog.org/license.txt BSD 3-Clause License
 */

namespace Module\Media\Controller\Front;

use Pi\Mvc\Controller\ActionController;
use Pi\Paginator\Paginator;
use Pi;
use Module\Media\Form\MediaEditFullForm;
use Module\Media\Form\MediaEditFilter;
use Zend\View\Model\ViewModel;

/**
 * List controller
 * 
 * @author Zongshu Lin <lin40553024@163.com>
 */
class ListController extends ActionController
{
    /**
     * Get application title by appkey
     * 
     * @param array $appkeys
     * @return array
     */
    protected function getAppTitle($appkeys)
    {
        $result = array();
        $modelApp = $this->getModel('application');
        $rowApp = $modelApp->select(array('appkey' => $appkeys));
        foreach ($rowApp as $row) {
            $result[$row->appkey] = $row->title ?: $row->name;
        }
        unset($rowApp);
        unset($modelApp);
        
        return $result;
    }
    
    /**
     * Get category title by category ids
     * 
     * @param array $category
     * @return array
     */
    protected function getCategoryTitle($category)
    {
        $result = array();
        $modelCategory = $this->getModel('category');
        $rowCategory = $modelCategory->select(array('id' => $category));
        foreach ($rowCategory as $row) {
            $result[$row->id] = $row->title ?: $row->name;
        }
        unset($rowCategory);
        unset($modelCategory);
        
        return $result;
    }


    /**
     * List all media
     * 
     * @return ViewModel
     */
    public function indexAction()
    {
        $active = $this->params('status', null);
        if ($active !== null) {
            $active = (int) $active;
        }
        $page   = (int) $this->params('p', 1);
        $limit  = (int) $this->config('page_limit') > 0
            ? $this->config('page_limit') : 20;
        $offset = ($page - 1) * $limit;
        
        $where = array();
        $params = array();
        if (1 === $active) {
            $where['active'] = 1;
            $params['status'] = 1;
        } elseif (0 === $active) {
            $where['active'] = 0;
            $params['status'] = 1;
        }
        $delete = $this->params('delete', 0);
        if ($delete) {
            $where['time_deleted > ?'] = 0;
        } else {
            $where['time_deleted'] = 0;
        }
        $params['delete'] = $delete;

        $user = $this->params('user', null);
        if (is_numeric($user)) {
            $userModel = Pi::service('user')->getUser($user);
        } elseif ($user) {
            $userModel = Pi::service('user')->getUser($user, 'identity');
        } else {
            $userModel = '';
        }
        $uid = $userModel ? $userModel->get('id') : 0;

        if($uid){
            $where['uid'] = $uid;
        }
        $params = array(
            'user'  => $user,
        );

        // Get media list
        $module = $this->getModule();
        $resultset = Pi::api('doc', $module)->getList(
            $where,
            $limit,
            $offset,
            'time_created DESC'
        );
        
        $uids = $appkeys = array();
        $apps = $users = $avatars = array();
        foreach ($resultset as $row) {
            $appkeys[] = $row['appkey'];
            $uids[]    = $row['uid'];
        }
        // Get application title
        if (!empty($appkeys)) {
            $apps = $this->getAppTitle($appkeys);
        }
        
        // Get users
        if (!empty($uids)) {
            $users = Pi::user()->get($uids);
            $avatars = Pi::avatar()->get($uids);
        }
        
        // Total count
        $totalCount = $this->getModel('doc')->count($where);

        // Paginator
        $paginator = Paginator::factory($totalCount, array(
            'page' => $page,
            'url_options'   => array(
                'page_param' => 'p',
                'params'     => array_filter(array_merge(array(
                    'module'        => $this->getModule(),
                    'controller'    => 'list',
                    'action'        => 'index',
                ), $params)),
            ),
        ));

        $navTabs = array(
            array(
                'active'    => null === $active && !$delete,
                'label'     => _a('All resources'),
                'href'      => $this->url('', array(
                    'action'    => 'index',
                )),
            ),
            array(
                'active'    => $delete,
                'label'     => _a('Deleted resources'),
                'href'      => $this->url('', array(
                    'action'    => 'index',
                    'delete'    => 1,
                )),
            ),
        );

        $this->view()->setTemplate('../front/list-index');
        
        $this->view()->assign(array(
            'title'      => _a('Resource List'),
            'apps'       => $apps,
            'medias'     => $resultset,
            'paginator'  => $paginator,
            'tabs'       => $navTabs,
            'users'      => $users,
            'avatars'    => $avatars,
            'active'     => $active,
            'delete'     => $delete,
            'user'       => $user,
        ));
    }

    public function attachAction()
    {
        $this->view()->setTemplate('../front/list-attach');
    }

    public function updatecropAction()
    {
        // Get id
        $id = $this->params('id');
        $cropping = $this->params('cropping');

        if (empty($id)) {
            die('ID is missing');
        }

        if (empty($cropping)) {
            die('Cropping data is missing');
        }

        $row = $this->getModel('doc')->find($id);

        if($row){
            $row->cropping = $cropping;
            $row->save();
        } else {
            die('Entity is missing');
        }

        if(Pi::service()->hasService('log')){
            Pi::service()->getService('log')->mute(true);
        }

        return $this->getResponse();
    }

    public function addAction()
    {
        // Set params
        $params = array();

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
        $response = Pi::api('doc', 'media')->upload($params, 'POST');

        // Check
        if (!isset($response['id']) || !$response['id']) {
            $response = array(
                'status'    => 0,
                'message'   => implode(' - ', $response['upload_errors'])
            );
        } else {
            $response = array(
                'status' => 1,
                'message' => __('Media attach '),
                'id' => $response['id'],
                'title' => '',
                'time_create' => '',
                'type' => '',
                'hits' => '',
                'size' => '',
                'preview' => Pi::api('doc', 'media')->getUrl($response['id']),
            );
        }

        return $response;
    }

    /**
     * Delete media resources
     *
     * @return ViewModel|array
     * @throws \Exception
     */
    public function deleteAction()
    {
        $id     = $this->params('id', 0);
        $from   = $this->params('redirect', '');

        // Mark media as deleted
        $this->getModel('doc')->update(
            array('time_deleted' => time()),
            array('id' => $id)
        );

        $request = $this->getRequest();

        if ($request->isXmlHttpRequest()){
            $response = array(
                'status' => 1,
                'message' => __('Media deleted'),
                'id' => $id,
                'title' => '',
                'time_create' => '',
                'type' => '',
                'hits' => '',
                'size' => '',
                'preview' => '',
            );

            return $response;
        } else {
            // Go to list page or original page
            if ($from) {
                $from = urldecode($from);
                return $this->redirect()->toUrl($from);
            } else {
                return $this->redirect()->toRoute(
                    '',
                    array(
                        'controller' => 'list',
                        'action'     => 'index',
                    )
                );
            }
        }
    }

    /**
     * Edit media
     *
     * @return ViewModel
     */
    public function editAction()
    {
        $this->view()->setTemplate('../front/list-edit');

        $id   = $this->params('id', 0);
        $row  = $this->getModel('doc')->find($id);
        if (!$row) {
            $this->view()->assign('id', $row->id);
            return;
        }

        $form = $this->getMediaForm('edit', array('thumbUrl' => Pi::api('doc', 'media')->getUrl($row->id)));
        $form->setData($row->toArray());

        $this->view()->assign(array(
            'form'      => $form,
            'id'        => $row->id,
        ));

        if ($this->request->isPost()) {
            $post = $this->request->getPost();
            $form->setData($post);
            $form->setInputFilter(new MediaEditFilter);
            if (!$form->isValid()) {
                return $this->renderForm(
                    $form,
                    _a('There are some error occur')
                );
            }

            $data = $form->getData();
            $id   = $this->saveMedia($data);
            if (empty($id)) {
                return $this->renderForm(
                    $form,
                    _a('Cannot save media data')
                );
            }

            return $this->redirect()->toRoute(
                '',
                array(
                    'controller' => 'list',
                    'action'     => 'index'
                )
            );
        }
    }

    /**
     * Getting form instance
     *
     * @param string  $action  Action to request when submit
     * @return \Module\Media\Form\MediaEditForm
     */
    protected function getMediaForm($action = 'edit', $options = array())
    {
        $form = new MediaEditFullForm('media', $options);
        $form->setAttribute('action', $this->url('', array('action' => $action)));

        return $form;
    }

    /**e
     * Render form
     *
     * @param Zend\Form\Form $form     Form instance
     * @param string         $message  Message assign to template
     * @param bool           $error    Whether is error message
     */
    public function renderForm($form, $message = null, $error = true)
    {
        $params = compact('form', 'message', 'error');
        $this->view()->assign($params);
    }

    /**
     * Save media data
     *
     * @param array $data
     * @return int
     */
    protected function saveMedia($data)
    {
        $id   = $data['id'];
        unset($data['id']);

        $modelDoc = $this->getModel('doc');
        $rowMedia = $modelDoc->find($id);
        if ($rowMedia) {
            $rowMedia->assign($data);
            $rowMedia->time_updated = time();

            if($uid = Pi::user()->getId()){
                $rowMedia->updated_by = $uid;
            }

            $rowMedia->save();
        } else {
            $rowMedia = $modelDoc->createRow($data);
            $rowMedia->save();
        }

        return $rowMedia->id;
    }
}
