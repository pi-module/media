<?php
/**
 * Pi Engine (http://pialog.org)
 *
 * @link            http://code.pialog.org for the Pi Engine source repository
 * @copyright       Copyright (c) Pi Engine http://pialog.org
 * @license         http://pialog.org/license.txt BSD 3-Clause License
 */

namespace Module\Media\Api;

use Pi;
use Pi\Application\Api\AbstractApi;

class Resize extends AbstractApi
{
    /**
     * Module name
     * @var string
     */
    protected $module = 'media';

    /**
     * Resize by media object or id
     * @param $media
     * @return mixed
     */
    public function resize($media){

        if(is_numeric($media)){
            $media = Pi::model('doc', $this->module)->find($media);
        }

        $publicPath = 'upload/media' . $media['path'] . $media['filename'];
        $helper = Pi::service('view')->getHelper('resize');

        return $helper($publicPath, $media['cropping']);
    }
}
