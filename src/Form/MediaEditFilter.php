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
use Laminas\InputFilter\InputFilter;

/**
 * Class for verifying and filtering form
 *
 * @author Zongshu Lin <lin40553024@163.com>
 */
class MediaEditFilter extends InputFilter
{
    /**
     * Initializing validator and filter
     */
    public function __construct()
    {
        $module = Pi::service('module')->current();
        $config = Pi::service('registry')->config->read($module);

        $this->add([
            'name'     => 'title',
            'required' => true,
            'filters'  => [
                [
                    'name' => 'StringTrim',
                ],
            ],
        ]);

        if ($config['form_description']) {
            $this->add([
                'name'     => 'description',
                'required' => false,
                'filters'  => [
                    [
                        'name' => 'StringTrim',
                    ],
                ],
            ]);
        }

        if ($config['form_license_type'] && isset($config['license_values']) && !empty($config['license_values'])) {
            $this->add([
                'name'     => 'license_type',
                'required' => false,
            ]);
        }

        $this->add([
            'name'     => 'season',
            'required' => false,
        ]);

        if ($config['form_copyright']) {
            $this->add([
                'name'     => 'copyright',
                'required' => false,
            ]);
        }

        $this->add([
            'name'     => 'id',
            'required' => true,
        ]);
    }
}