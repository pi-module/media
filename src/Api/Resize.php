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
     * Resize
     *
     * @param $media
     *
     * @return mixed
     * @throws \Exception
     */
    public function resize($media)
    {

        if (is_numeric($media)) {
            $media = Pi::model('doc', $this->module)->find($media);
        }

        $publicPath = !empty($media['path']) ? 'upload/media' . $media['path'] . $media['filename'] : '';
        $helper     = Pi::service('view')->getHelper('resize');

        $config = Pi::service('registry')->config->read('media');

        /**
         * Set default sizes from media config
         */

        $helper->setDefaultSizes([
            'large'     => [
                'width'  => $config['image_largew'],
                'height' => $config['image_largeh'],
            ],
            'item'      => [
                'width'  => $config['image_itemw'],
                'height' => $config['image_itemh'],
            ],
            'medium'    => [
                'width'  => $config['image_mediumw'],
                'height' => $config['image_mediumh'],
            ],
            'thumbnail' => [
                'width'  => $config['image_thumbw'],
                'height' => $config['image_thumbh'],
            ],
        ]);

        $helper = $helper($publicPath, !empty($media['cropping']) ? $media['cropping'] : '');

        if (getenv('TEST_MEDIA') || is_file('MEDIA_TEST_FLAG')) {
            $helper->grayscale();
        }

        if (!empty($media['time_created'])) {
            $helper->setTimestamp($media['time_updated'] ?: $media['time_created']);
        }

        return $helper;
    }

    public function resizeFormList($media)
    {
        return Pi::url(trim($this->resize($media)->thumbCrop(300, 200), '/'));
    }
}
