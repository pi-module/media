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
 * Class for initializing form of edit media page
 * 
 * @author Zongshu Lin <lin40553024@163.com>
 */ 
class MediaEditForm extends BaseForm
{
    public function __construct($name = null, $option = array())
    {
        $module = Pi::service('module')->current();
        $this->config = Pi::service('registry')->config->read($module);
        $this->thumbUrl = (isset($option['thumbUrl'])) ? $option['thumbUrl'] : '';
        parent::__construct($name);
    }

    /**
     * Initalizing form 
     */
    public function init()
    {
        $this->add(array(
            'name'       => 'title',
            'options'    => array(
                'label'     => __('Title'),
            ),
            'attributes' => array(
                'type'      => 'text',
            ),
        ));

        $this->add(array(
            'name'       => 'description',
            'options'    => array(
                'label'     => __('Description'),
            ),
            'attributes' => array(
                'type'      => 'textarea',
                'cols'      => 10,
                'rows'      => 5,
            ),
        ));

        if($this->thumbUrl){
            $this->add(array(
                'name' => 'imageview',
                'type' => 'Module\Media\Form\Element\ImageCrop', // Zend\Form\Element\Image
                'options' => array(
                    'label' => __('Uploaded image'),
                ),
                'attributes' => array(
                    'src' => $this->thumbUrl,
                ),
            ));

            $this->add(array(
                'name' => 'cropping',
                'type' => 'text',
                'options' => array(
                    'label' => __('Cropping data'),
                ),
            ));
        }

        $this->add(array(
            'name'       => 'season',
            'type'       => 'Select',
            'options'    => array(
                'label'     => __('Season'),
                'value_options' => array(
                    '' => __('Choose a season'),
                    1 => __('Summer'),
                    2 => __('Winter'),
                ),
            ),
            'attributes' => array(
                'type'      => 'select',
            ),
        ));

        $this->add(array(
            'name'       => 'id',
            'attributes' => array(
                'id'        => 'id',
                'type'      => 'hidden',
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
