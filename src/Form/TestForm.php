<?php
/**
 * Pi Engine (http://pialog.org)
 *
 * @link         http://code.pialog.org for the Pi Engine source repository
 * @copyright    Copyright (c) Pi Engine http://pialog.org
 * @license      http://pialog.org/license.txt BSD 3-Clause License
 */

namespace Module\Media\Form;

use Pi;
use Pi\Form\Form as BaseForm;

class TestForm extends BaseForm
{
    public function init()
    {
        $this->add(array(
            'name' => 'image',
            'options' => array(
                'label' => __('Upload image'),
            ),
            'attributes' => array(
                'type' => 'file',
                'description' => '',
            )
        ));

        $this->add(array(
            'name' => 'imageManager',
            'type' => 'Module\Media\Form\Element\Media',
            'options' => array(
                'label' => __('Image manager'),
            ),
            'attributes' => array(
                'link' => '',
            ),
        ));

        $this->add(array(
            'name'       => 'submit',
            'attributes' => array(
                'value'     => __('Submit'),
            ),
            'type'       => 'submit',
        ));
    }
}
