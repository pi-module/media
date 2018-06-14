<?php
/**
 * Pi Engine (http://pialog.org)
 *
 * @link         http://code.pialog.org for the Pi Engine source repository
 * @copyright    Copyright (c) Pi Engine http://pialog.org
 * @license      http://pialog.org/license.txt BSD 3-Clause License
 */

namespace Module\Media\Controller\Admin;

use Module\Media\Form\TestForm;
use Pi\Mvc\Controller\ActionController;
use Pi\Paginator\Paginator;
use Pi;

/**
 * Test controller
 * 
 * @author Frédéric TISSOT <contact@espritdev.fr>
 */
class TestController extends ActionController
{
    /**
     * List all media
     * 
     * @return ViewModel
     */
    public function indexAction()
    {
        $page   = (int) $this->params('p', 1);
        $limit  = (int) $this->config('page_limit') > 0
            ? $this->config('page_limit') : 20;
        $offset = ($page - 1) * $limit;
        
        $where = array();
        $params = array();

        $action = $this->params('action');

        // Get media list
        $module = $this->getModule();
        $resultset = Pi::api('test', $module)->getList(
            $where,
            $limit,
            $offset
        );
        
        // Total count
        $totalCount = $this->getModel('test')->count($where);

        // Paginator
        $paginator = Paginator::factory($totalCount, array(
            'page' => $page,
            'url_options'   => array(
                'page_param' => 'p',
                'params'     => array_filter(array_merge(array(
                    'module'        => $this->getModule(),
                    'controller'    => 'test',
                    'action'        => 'index',
                ), $params)),
            ),
        ));

        $navTabs = array(
            array(
                'active'    => $action == 'index',
                'label'     => _a('All tests'),
                'href'      => $this->url('', array(
                    'action'    => 'index',
                )),
            ),
            array(
                'active'    => $action == 'add',
                'label'     => _a('Add test'),
                'href'      => $this->url('', array(
                    'action'    => 'add',
                )),
            ),
        );
        
        $this->view()->assign(array(
            'title'      => _a('Resource List'),
            'tests'     => $resultset,
            'paginator'  => $paginator,
            'tabs'       => $navTabs,
        ));
    }

    public function addAction()
    {
        $this->editAction();
    }

    public function editAction()
    {
        $action = $this->params('action');
        $id = $this->params('id');
        $form = new TestForm();

        if($id){
            $row = Pi::model('test', 'media')->find($id);
            $form->setData($row->toArray());
        }

        if ($this->request->isPost()) {
            $post = $this->request->getPost();
            $form->setData($post);
            if ($form->isValid()) {
                $data = $form->getData();

                if($data['id']){
                    Pi::api('test', $this->getModule())->update($data['id'], $data);
                } else {
                    Pi::api('test', $this->getModule())->add($data);
                }

                return $this->redirect()->toRoute('', array('action' => 'index'));
            }
        }

        $navTabs = array(
            array(
                'active'    => $action == 'index',
                'label'     => _a('All tests'),
                'href'      => $this->url('', array(
                    'action'    => 'index',
                )),
            ),
            array(
                'active'    => $action == 'add',
                'label'     => _a('Add test'),
                'href'      => $this->url('', array(
                    'action'    => 'add',
                )),
            ),
        );

        $this->view()->setTemplate('test-edit');


        $this->view()->assign(array(
            'form'  => $form,
            'tabs'       => $navTabs,
        ));
    }

    public function removeAction()
    {
        $id = $this->params('id');

        Pi::model('test', 'media')->find($id)->delete();

        return $this->redirect()->toRoute('', array('action' => 'index'));
    }
}
