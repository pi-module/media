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
class AppEditFilter extends InputFilter
{
    /**
     * Initializing validator and filter
     */
    public function __construct()
    {
        $this->add([
            'name'     => 'title',
            'required' => true,
            'filters'  => [
                [
                    'name' => 'StringTrim',
                ],
            ],
        ]);

        $this->add([
            'name'     => 'id',
            'required' => false,
        ]);

        $this->add([
            'name'     => 'appkey',
            'required' => true,
        ]);
    }
}
