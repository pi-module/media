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

        if($this->season == 0){
            $this->season = null;
        }

        return parent::save($rePopulate, $filter);
    }

    public function delete()
    {
        Pi::api('doc', 'media')->removeImageCache($this);

        return parent::delete();
    }
}
