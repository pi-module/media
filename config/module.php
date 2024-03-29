<?php
/**
 * Pi Engine (http://pialog.org)
 *
 * @link         http://code.pialog.org for the Pi Engine source repository
 * @copyright    Copyright (c) Pi Engine http://pialog.org
 * @license      http://pialog.org/license.txt BSD 3-Clause License
 */

/**
 * Module config and meta
 *
 * @author Zongshu Lin <lin40553024@163.com>
 */
return [
    // Module meta
    'meta'       => [
        'title'       => _a('Media'),
        'description' => _a('Module for media resources and APIs.'),
        'version'     => '1.3.0',
        'license'     => 'New BSD',
        'logo'        => 'image/logo.png',
        'readme'      => 'README.md',
        'clonable'    => false,
        'icon'        => 'fa-image',
    ],
    // Author information
    'author'     => [
        'Dev'     => 'Zongshu Lin; Frederic Tissot',
        'Design'  => '@marc-pi, @esprit-dev',
        'QA'      => '@marc-pi',
        'Email'   => 'zongshu@eefocus.com',
        'Website' => 'http://www.github.com/linzongshu',
        'Credits' => 'Pi Engine Team.',
    ],
    // Module dependency: list of module directory names, optional
    'dependency' => [
    ],
    // Maintenance resources
    'resource'   => [
        'database'   => [
            'sqlfile' => 'sql/mysql.sql',
        ],
        // Database meta
        'navigation' => 'navigation.php',
        'config'     => 'config.php',
        'permission' => 'permission.php',
    ],
];
