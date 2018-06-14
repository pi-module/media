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

/**
 * Class for initializing form of edit application page
 * 
 * @author Frédéric TISSOT <contact@espritdev.fr>
 */ 
class TestForm extends BaseForm
{
    /**
     * Initalizing form 
     */
    public function init()
    {
        $this->add(array(
            'name'       => 'id',
            'type'      => 'hidden',
        ));

        $this->add(array(
            'name'       => 'title',
            'options'    => array(
                'label'     => __('Application Title'),
            ),
            'attributes' => array(
                'type'      => 'text',
            ),
        ));

        $this->add(array(
            'name' => 'main_image',
            'type' => 'Module\Media\Form\Element\Media',
            'options' => array(
                'label' => __('Main image'),
            ),
        ));

        $this->add(array(
            'name' => 'additional_images',
            'type' => 'Module\Media\Form\Element\Media',
            'options' => array(
                'label' => __('Additional images'),
                'media_gallery' => true,
            ),
        ));

        $this->add(array(
            'name'       => 'submit',
            'attributes' => array(               
                'value'     => __('Save this test'),
                'class'     => 'btn btn-primary',
            ),
            'type'       => 'submit',
        ));
    }
}
