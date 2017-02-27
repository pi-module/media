<?php
/**
 * Pi Engine (http://pialog.org)
 *
 * @link         http://code.pialog.org for the Pi Engine source repository
 * @copyright    Copyright (c) Pi Engine http://pialog.org
 * @license      http://pialog.org/license.txt BSD 3-Clause License
 */

namespace Module\Media\Controller\Admin;

use Pi\Mvc\Controller\ActionController;
use Pi\Paginator\Paginator;
use Zend\Db\Sql\Expression;
use Pi;

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

    }
}
