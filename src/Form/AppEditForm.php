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
 * @author Zongshu Lin <lin40553024@163.com>
 */
class AppEditForm extends BaseForm
{
    /**
     * Initalizing form
     */
    public function init()
    {
        $this->add([
            'name'       => 'title',
            'options'    => [
                'label' => __('Application Title'),
            ],
            'attributes' => [
                'type' => 'text',
            ],
        ]);

        $this->add([
            'name' => 'security',
            'type' => 'csrf',
        ]);

        $this->add([
            'name'       => 'id',
            'attributes' => [
                'id'   => 'id',
                'type' => 'hidden',
            ],
        ]);

        $this->add([
            'name'       => 'appkey',
            'attributes' => [
                'id'   => 'appkey',
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
