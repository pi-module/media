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
    public function __construct($name = null, $option = [])
    {
        $module         = Pi::service('module')->current();
        $this->config   = Pi::service('registry')->config->read($module);
        $this->thumbUrl = (isset($option['thumbUrl'])) ? $option['thumbUrl'] : '';
        parent::__construct($name);
    }

    /**
     * Initalizing form
     */
    public function init()
    {

        $this->setAttribute("enctype", "multipart/form-data");

        $this->add([
            'name'       => 'title',
            'options'    => [
                'label' => __('Title'),
            ],
            'attributes' => [
                'type'     => 'text',
                'required' => true,
            ],
        ]);

        if ($this->config['form_description']) {
            $this->add([
                'name'       => 'description',
                'options'    => [
                    'label' => __('Description'),
                ],
                'attributes' => [
                    'type'     => 'textarea',
                    'cols'     => 10,
                    'rows'     => 5,
                    'required' => false,
                ],
            ]);
        }

        $this->add([
            'name'       => 'file',
            'options'    => [
                'label' => __($this->thumbUrl ? "Change file" : "File"),
            ],
            'attributes' => [
                'type' => 'file',
            ],
        ]);

        if ($this->thumbUrl) {
            $this->add([
                'name'       => 'imageview',
                'type'       => 'Module\Media\Form\Element\ImageCrop', // Laminas\Form\Element\Image
                'options'    => [
                    'label' => __('Uploaded image'),
                ],
                'attributes' => [
                    'src' => $this->thumbUrl,
                ],
            ]);

            $this->add([
                'name'    => 'cropping',
                'type'    => 'hidden',
                'options' => [
                    'label' => __('Cropping data'),
                ],
            ]);
        }

        $this->add([
            'name'    => 'season',
            'type'    => 'select',
            'options' => [
                'label'         => __('Season'),
                'value_options' => [
                    '' => __('No season'),
                    4  => __('Spring'),
                    1  => __('Summer'),
                    3  => __('Autumn'),
                    2  => __('Winter'),
                ],
            ],
        ]);


        if ($this->config['form_license_type'] && isset($this->config['license_values']) && !empty($this->config['license_values'])) {

            $licenseValues = explode('|', $this->config['license_values']);

            $values = [
                '' => __('Choose a license type'),
            ];

            foreach ($licenseValues as $licenseValue) {
                $values[] = $licenseValue;
            }

            $this->add([
                'name'    => 'license_type',
                'type'    => 'select',
                'options' => [
                    'label'         => __('Licence type'),
                    'value_options' => $values,
                ],
            ]);
        }

        if ($this->config['form_copyright']) {
            $this->add([
                'name'    => 'copyright',
                'options' => [
                    'label' => __('Copyright'),
                ],
            ]);
        }

        $this->add([
            'name'       => 'id',
            'attributes' => [
                'id'   => 'id',
                'type' => 'hidden',
            ],
        ]);

        $this->add([
            'name'       => 'submit',
            'attributes' => [
                'value' => __('Submit'),
            ],
            'type'       => 'submit',
        ]);
    }
}
