# Media Module

## Intro
This module is used for managing media file, such as image, document, video, etc.. This module can be configured as server
media center either at remote web host or local web host, and it only provides service APIs and media management, you need to
develop your uploading page for user to choose file and execute upload.

## How to implement to new module
### First steps
In order to add a media field to an existing model (item, news, etc..), you have to :
1. Add a new column to the table (mysql.sql + updater) : INT if only one media selection, on TEXT if multiple selection
2. Declare this column into model file (<code>protected $columns</code>)
3. Add this to the same file : 
<code>protected $mediaLinks = array('main_image', [others fields......]);</code>
4. Edit form class (where the media plugin will be insert for inserting new media to the field)
5. Put this : 
```php
$this->add(array(
    'name' => 'main_image',
    'type' => 'Module\Media\Form\Element\Media',
    'options' => array(
        'label' => __('Main image'),
        'media_gallery' => true, // OPTIONAL : specify if field is multiple media or not
        'media_season' => true, // OPTIONAL : add season feature
        'media_season_recommended' => true, // OPTIONAL : add warning message
        'can_connect_lists' => true, // OPTIONAL : multiple field can be connected for drag and drop media on each field
        'module' => 'guide', // OPTIONAL : Specify module for custom config (freemium, max media on field, etc...)
    ),
));
```
6. Add a filter for this field :
```php
$this->add(array(
    'name' => 'main_image',
    'required' => false,
));
```
7. For migrate current media files, please take example from News module : <code>usr/module/news/src/Api/Story.php::migrateMedia();</code> and adapt code for your needs

### Samples
* Samples of using media helpers into templates here :
usr/module/media/template/admin/test-index.phtml

* Media Helper, we have to cast the output to String, with echo function or caster as follow :
	* If not casted : duplicated instance of the same helper / sames parameters (with, height, quality...) : clone the helper is not a good solution for performance

<?php $shareImage = (string) Pi::api('doc','media')->getSingleLinkUrl($fieldValue)->thumb(800, 600); ?>
<?php echo Pi::api('doc','media')->getSingleLinkUrl($fieldValue)->thumb(800, 600); ?>

* In order to use custom config sizes (current module), we have to use :
<?php $shareImage = (string) Pi::api('doc','media')->getSingleLinkUrl($fieldValue)->thumb(800, 600); ?>
<?php Pi::api('doc','media')->getSingleLinkUrl($story['main_image'])->setConfigModule('news')->thumb('medium'); ?>
Thumb method parameters can be dimensions (w / h) or size code : large / item (if module support that) / medium / thumbnail
Where setConfigModule() method set config module to use.
If module has no custom parameters, media module will provide default sizes for each size code.

* For getting gallery media (multiple selection), you can use :
<code>$galleryImages = Pi::api('doc','media')->getGalleryLinkData($fieldValue, 320, 200)</code>

* For getting picture media tag (multiple sizes for multiple devices), you can use :
<code>Pi::api('doc','media')->getSingleLinkPictureTag($fieldValue, array(320,768,900,1200), 90)</code>
Where array(320,768,900,1200) is all needed sizes.

### More resize commands here :
https://github.com/tck/zf2-imageresizer#command-list

## Activate grayscaling
For testing / find usages of old media system into templates :
Your non migrated images - e.g.e using old method instead of new media helpers - will be White&Black
You can use for that purpose one of these 2 methods : 
* Add SetEnv TEST_MEDIA 1 into your www htaccess
* Or create (empty) file at www directory name "MEDIA_TEST_FLAG" without extension

## PI Core Upgrade
If Jquery Ui from Pi Core is updated, custom jquery ui from this module must be also updated with only sortable component and dependencies :
()widget.js, data.js, scroll-parent.js, widgets/sortable.js, widgets/mouse.js)