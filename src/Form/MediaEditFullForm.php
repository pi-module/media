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
 * @author Frédéric TISSOT <contact@espritdev.fr>
 */ 
class MediaEditFullForm extends MediaEditForm
{
    /**
     * Initalizing form 
     */
    public function init()
    {
        $this->add(array(
            'name'       => 'filename',
            'options'    => array(
                'label'     => __('Filename'),
            ),
        ));

        parent::init();

        $this->remove('submit');



        $this->add(array(
            'name'       => 'uid',
            'options'    => array(
                'label'     => __('User ID'),
            ),
        ));

        $this->add(array(
            'name'       => 'geoloc_latitude',
            'options'    => array(
                'label'     => __('GPS Latitude'),
            ),
        ));

        $this->add(array(
            'name'       => 'geoloc_longitude',
            'options'    => array(
                'label'     => __('GPS Longitude'),
            ),
        ));

        $this->add(array(
            'name'       => 'featured',
            'type'      => 'checkbox',
            'options'    => array(
                'label'     => __('Featured'),
            ),
        ));

        $this->add(array(
            'name'       => 'active',
            'type'       => 'Select',
            'options'    => array(
                'label'     => __('Active'),
                'value_options' => array(
                    0 => __('Disabled'),
                    1 => __('Enabled'),
                ),
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
