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
class ToolsController extends ActionController
{
    /**
     * List all media
     * 
     * @return ViewModel
     */
    public function indexAction()
    {
        $this->view()->assign(array(
            'title'      => _a('Tools'),
        ));
    }

    public function fillDescriptionAction()
    {
        $mediaModel = Pi::model('doc', 'media');
        $select = $mediaModel->select();

        $select->where(array(
            new \Zend\Db\Sql\Predicate\Like('description', ''),
            new \Zend\Db\Sql\Predicate\NotLike('filename', ''),
        ));

        $mediaCollection = Pi::model('doc', 'media')->selectWith($select);

        if($mediaCollection->count()){
            foreach($mediaCollection as $mediaEntity){
                preg_match('#(.*)\.(.*)$#', $mediaEntity->filename, $matches);

                $mediaEntity->description = ucfirst(str_replace('-', ' ', $matches[1]));
                $mediaEntity->save();
            }

            $messenger = $this->plugin('flashMessenger');
            $messenger->addSuccessMessage(__('Descriptions are filled successfully'));
        } else {
            $messenger = $this->plugin('flashMessenger');
            $messenger->addMessage(__('Description are filled yet'));
        }

        $this->redirect()->toRoute(null, array('action' => 'index'));
    }
}
