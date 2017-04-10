<?php
/**
 * Pi Engine (http://pialog.org)
 *
 * @link            http://code.pialog.org for the Pi Engine source repository
 * @copyright       Copyright (c) Pi Engine http://pialog.org
 * @license         http://pialog.org/license.txt BSD 3-Clause License
 */

namespace Module\Media\Controller\Front;

use Pi;
use Pi\Mvc\Controller\ActionController;

/**
 * Public index controller
 */
class IndexController extends ActionController
{
    /**
     * Default action if none provided
     */
    public function indexAction()
    {
        $this->redirect('');
    }
}
