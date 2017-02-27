<?php
/**
 * Pi Engine (http://pialog.org)
 *
 * @link         http://code.pialog.org for the Pi Engine source repository
 * @copyright    Copyright (c) Pi Engine http://pialog.org
 * @license      http://pialog.org/license.txt BSD 3-Clause License
 */

namespace Module\Media\Controller\Admin;

use Pi;
use Pi\Mvc\Controller\ActionController;
use Pi\File\Transfer\Upload;

/**
 * Index controller
 *
 * @author Hossein Azizabadi <azizabadi@faragostaresh.com>
 */
class ManageController extends ActionController
{
    public function listAction()
    {
        return array();
    }

    public function addAction()
    {
        // Set params
        $params = array();

        // Get file type
        $file = $this->request->getFiles();

        var_dump($_POST);
        var_dump($_FILES);
        var_dump('ééééé.jpg');die();

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
        $id = Pi::api('doc', 'media')->upload($params, 'POST');

        $url = Pi::api('doc', 'media')->getUrl($id);

        // Check
        if (!$id) {
            $response = array(
                'status'    => 0,
                'message'   => __('Operation failed.')
            );
        } else {
            $response = array(
                'status' => 1,
                'message' => __('Media attach '),
                'id' => $id,
                'title' => '',
                'time_create' => '',
                'type' => '',
                'hits' => '',
                'size' => '',
                'preview' => $url,
            );
        }

        return $response;
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

        // Mark media as deleted
        $this->getModel('doc')->update(
            array('time_deleted' => time()),
            array('id' => $id)
        );

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

    }
}