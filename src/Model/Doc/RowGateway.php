<?php
/**
 * Pi Engine (http://pialog.org)
 *
 * @link            http://code.pialog.org for the Pi Engine source repository
 * @copyright       Copyright (c) Pi Engine http://pialog.org
 * @license         http://pialog.org/license.txt BSD 3-Clause License
 */

namespace Module\Media\Model\Doc;

use \Pi;

class RowGateway extends \Pi\Db\RowGateway\RowGateway
{
    public function save($rePopulate = true, $filter = true)
    {
        Pi::api('doc', 'media')->removeImageCache($this);

        if(isset($this->season) && $this->season == 0){
            $this->season = null;
        }

        if(!isset($this->cropping) || !$this->cropping){

            $options    = Pi::service('media')->getOption('local', 'options');
            $rootPath   = $options['root_path'];

            $ratio = Pi::api('doc', 'media')->getRatio();

            $filepath = $rootPath . $this->path . $this->filename;
            list($width, $height) = getimagesize($filepath, $info);

            $currentRatio = $width / $height;

            /**
             * Default ratio
             */
            $cropParams = array(
                'x' => 0,
                'y' => 0,
                'width' => $width,
                'height' => $height,
                'scaleX' => 1,
                'scaleY' => 1,
            );

            if ($currentRatio > $ratio) {
                /**
                 * Too much width
                 */
                $newWidth = round($height * $ratio);
                $offset = round(($width - $newWidth) / 2);

                $cropParams['width'] = $newWidth;
                $cropParams['x'] = $offset;

            } else if($currentRatio < $ratio){
                /**
                 * To much height
                 */
                $newHeight = round($width / $ratio);
                $offset = round(($height - $newHeight) / 2);

                $cropParams['height'] = $newHeight;
                $cropParams['y'] = $offset;
            }

            $this->cropping = json_encode($cropParams);
        }

        return parent::save($rePopulate, $filter);
    }

    public function delete()
    {
        Pi::api('doc', 'media')->removeImageCache($this);

        return parent::delete();
    }
}
