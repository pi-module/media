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
 * Model class for Link
 *
 * @author Frédéric TISSOT <contact@espritdev.fr>
 */
class Link extends Model
{
    /**
     * {@inheritDoc}
     */
    protected $columns = [
        'id',
        'module',
        'object_name',
        'object_id',
        'field',
        'media_id',
    ];
}
