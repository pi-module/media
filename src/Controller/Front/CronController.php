<?php
/**
 * Pi Engine (http://pialog.org)
 *
 * @link            http://code.pialog.org for the Pi Engine source repository
 * @copyright       Copyright (c) Pi Engine http://pialog.org
 * @license         http://pialog.org/license.txt New BSD License
 */

/**
 * @author Frédéric TISSOT
 */
namespace Module\Media\Controller\Front;

use Pi;
use Pi\Mvc\Controller\ActionController;

class CronController extends ActionController
{
    public function cleanSoftDeletedMediaAction()
    {
        $options    = Pi::service('media')->getOption('local', 'options');
        $rootPath   = $options['root_path'];

        $mediaModel = Pi::model('doc', 'media');
        $select = $mediaModel->select();
        $select->where('time_deleted > 0');

        $mediaCollection = Pi::model('doc', 'media')->selectWith($select);

        $removedMedia = array();

        foreach($mediaCollection as $mediaEntity){
            $fullPath = $rootPath . $mediaEntity->path . $mediaEntity->filename;

            if(is_file($fullPath)){
                unlink($fullPath);
            }

            $mediaEntity->delete();
            $removedMedia[] = $fullPath;
        }

        $this->response->setStatusCode(200);

        return array(
            'message' => "Ok",
            'removedMediaCount' => count($removedMedia),
            'removedMediaPaths' => $removedMedia,
        );
    }
}