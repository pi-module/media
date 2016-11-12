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
use Module\Media\Form\TestForm;
use Module\Media\Form\TestFilter;

class TestController extends ActionController
{
    public function indexAction()
    {
        $form = new TestForm();
        $this->view()->assign(array(
            'form'  => $form,
        ));
    }
}