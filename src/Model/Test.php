<?php
/**
 * Pi Engine (http://pialog.org)
 *
 * @link         http://code.pialog.org for the Pi Engine source repository
 * @copyright    Copyright (c) Pi Engine http://pialog.org
 * @license      http://pialog.org/license.txt BSD 3-Clause License
 */

namespace Module\Media\Model;

use Pi;
use Pi\Application\Model\Model;

/**
 * Model class for Test
 *
 * @author Frédéric TISSOT <contact@espritdev.fr>
 */
class Test extends Model
{
    /**
     * {@inheritDoc}
     */
    protected $columns = array(
        'id',
        'title',
        'main_image',
        'additional_images',
    );

    protected $mediaLinks = array('main_image', 'additional_images');
}
