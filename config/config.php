<?php
/**
 * Pi Engine (http://pialog.org)
 *
 * @link         http://code.pialog.org for the Pi Engine source repository
 * @copyright    Copyright (c) Pi Engine http://pialog.org
 * @license      http://pialog.org/license.txt BSD 3-Clause License
 */

/**
 * Module config
 * 
 * @author Zongshu Lin <lin40553024@163.com>
 */
return array(
    'category' => array(
        array(
            'name'  => 'general',
            'title' => _t('General'),
        ),
        array(
            'name'  => 'form',
            'title' => _t('Form'),
        ),
        array(
            'name'  => 'validator',
            'title' => _t('Validator'),
        ),
        array(
            'name'  => 'image',
            'title' => _t('Image'),
        ),
    ),

    'item' => array(
        // General
        'page_limit'      => array(
            'category'    => 'general',
            'title'       => _t('List page limit'),
            'description' => _t('Maximum count of media resources on a list page.'),
            'value'       => 20,
            'filter'      => 'int',
        ),
        'license_values'      => array(
            'category'    => 'general',
            'title'       => _t('License values'),
            'description' => _t('Pipe separated'),
            'edit'        => 'text',
            'value'       => '',
        ),
        // Form
        'form_description' => array(
            'category'    => 'form',
            'title'       => _t('Show description on form'),
            'description' => '',
            'edit'        => 'checkbox',
            'filter'      => 'number_int',
            'value'       => 1
        ),
        'form_season' => array(
            'category'    => 'form',
            'title'       => _t('Show season on form'),
            'description' => '',
            'edit'        => 'checkbox',
            'filter'      => 'number_int',
            'value'       => 1
        ),
        'form_license_type' => array(
            'category'    => 'form',
            'title'       => _t('Show license on form'),
            'description' => '',
            'edit'        => 'checkbox',
            'filter'      => 'number_int',
            'value'       => 1
        ),
        'form_copyright' => array(
            'category'    => 'form',
            'title'       => _t('Show copyright on form'),
            'description' => '',
            'edit'        => 'checkbox',
            'filter'      => 'number_int',
            'value'       => 1
        ),
        // Media
        'extension'       => array(
            'category'    => 'validator',
            'title'       => _t('File extension'),
            'description' => _t('Extensions for files allowed to upload.'),
            'value'       => 'jpg,jpeg,png,gif', //'pdf,rar,zip,doc,txt,docx,xls,xlsx,ppt,pptx,jpg,jpeg,png,gif',
        ),
        'max_size'        => array(
            'category'    => 'validator',
            'title'       => _t('Max file size'),
            'description' => _t('Maximum size for files allowed to upload (in KB).'),
            'value'       => 2048,
            'filter'      => 'int',
        ),
        // Image
        'image_maxw'    => array(
            'category'    => 'image',
            'title'       => _t('Max Image width (upload)'),
            'description' => '',
            'edit'        => 'text',
            'filter'      => 'number_int',
            'value'       => 2000
        ),
        'image_maxh'    => array(
            'category'    => 'image',
            'title'       => _t('Max Image height (upload)'),
            'description' => '',
            'edit'        => 'text',
            'filter'      => 'number_int',
            'value'       => 2000
        ),

        'image_minw'    => array(
            'category'    => 'image',
            'title'       => _t('Min Image width (upload)'),
            'description' => '',
            'edit'        => 'text',
            'filter'      => 'number_int',
            'value'       => 600
        ),
        'image_minh'    => array(
            'category'    => 'image',
            'title'       => _t('Min Image height (upload)'),
            'description' => '',
            'edit'        => 'text',
            'filter'      => 'number_int',
            'value'       => 600
        ),
        'image_quality'   => array(
            'category'    => 'image',
            'title'       => _t('Image quality'),
            'description' => _t('Between 0 to 100 and support both of JPG and PNG, default is 75'),
            'edit'        => 'text',
            'filter'      => 'number_int',
            'value'       => 75
        ),
        'image_watermark' => array(
            'category'    => 'image',
            'title'       => _t('Add Watermark'),
            'description' => '',
            'edit'        => 'checkbox',
            'filter'      => 'number_int',
            'value'       => 0
        ),
        'image_watermark_source' => array(
            'category'    => 'image',
            'title'       => _t('Watermark Image'),
            'description' => '',
            'edit'        => 'text',
            'filter'      => 'string',
            'value'       => ''
        ),
        'image_watermark_position' => array(
            'title'       => _t('Watermark Position'),
            'description' => '',
            'filter'      => 'text',
            'value'       => 'bottom-right',
            'category'    => 'image',
            'edit'                     => array(
                'type'                 => 'select',
                'options'              => array(
                    'options'          => array(
                        'top-left'     => _t('Top Left'),
                        'top-right'    => _t('Top Right'),
                        'bottom-left'  => _t('Bottom Left'),
                        'bottom-right' => _t('Bottom Right'),
                    ),
                ),
            ),
        ),
        'freemium_max_gallery_images'   => array(
            'category'    => 'image',
            'title'       => _t('Max gallery images for freemium related items'),
            'edit'        => 'text',
            'filter'      => 'number_int',
            'value'       => 2
        ),
        'freemium_alert_msg'      => array(
            'category'    => 'image',
            'title'       => _t('Alert message for freemium item'),
            'edit'        => 'text',
            'value'       => _t("Freemium item limitations... You can't do this action"),
        ),

        'image_largew'    => array(
            'category'    => 'image',
            'title'       => _t('Large Image width'),
            'description' => '',
            'edit'        => 'text',
            'filter'      => 'number_int',
            'value'       => 1200
        ),
        'image_largeh'    => array(
            'category'    => 'image',
            'title'       => _t('Large Image height'),
            'description' => '',
            'edit'        => 'text',
            'filter'      => 'number_int',
            'value'       => 1200
        ),
        'image_itemw' => array(
            'category' => 'image',
            'title' => _a('Item Image width'),
            'description' => '',
            'edit' => 'text',
            'filter' => 'number_int',
            'value' => 800
        ),
        'image_itemh' => array(
            'category' => 'image',
            'title' => _a('Item Image height'),
            'description' => '',
            'edit' => 'text',
            'filter' => 'number_int',
            'value' => 800
        ),
        'image_mediumw' => array(
            'category' => 'image',
            'title' => _a('Medium Image width'),
            'description' => '',
            'edit' => 'text',
            'filter' => 'number_int',
            'value' => 500
        ),
        'image_mediumh' => array(
            'category' => 'image',
            'title' => _a('Medium Image height'),
            'description' => '',
            'edit' => 'text',
            'filter' => 'number_int',
            'value' => 500
        ),
        'image_thumbw' => array(
            'category' => 'image',
            'title' => _a('Thumb Image width'),
            'description' => '',
            'edit' => 'text',
            'filter' => 'number_int',
            'value' => 250
        ),
        'image_thumbh' => array(
            'category' => 'image',
            'title' => _a('Thumb Image height'),
            'description' => '',
            'edit' => 'text',
            'filter' => 'number_int',
            'value' => 250
        ),
    ),
);
