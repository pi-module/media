<?php
/**
 * Pi Engine (http://pialog.org)
 *
 * @link            http://code.pialog.org for the Pi Engine source repository
 * @copyright       Copyright (c) Pi Engine http://pialog.org
 * @license         http://pialog.org/license.txt New BSD License
 */

/**
 * @author Hossein Azizabadi <azizabadi@faragostaresh.com>
 */
namespace Module\Media\Form\Element;

use Pi;

class Media extends \Zend\Form\Element\Text
{
    /**
     * @return array
     */
    public function getAttributes()
    {
        $modalTitle = __("Media gallery");
        $saveBtnTitle = __("Add selected media");

        $jQueryHelper = Pi::service('view')->gethelper('jQuery');
        $jQueryHelper();
        $jQueryHelper('ui/jquery-ui.min.js');

        $assetHelper = Pi::service('view')->gethelper('assetModule');
        $jsHelper = Pi::service('view')->gethelper('js');
        $jsHelper($assetHelper('js/dropzone.js'));
        $jsHelper($assetHelper('js/jquery.dataTables.min.js'));
        $jsHelper($assetHelper('js/dataTables.bootstrap.min.js'));

        $jsHelper($assetHelper('js/modal.js'));

        $cssHelper = Pi::service('view')->gethelper('css');
        $cssHelper($assetHelper('css/dropzone.css'));
//        $cssHelper($assetHelper('css/jquery.dataTables.min.css'));
        $cssHelper($assetHelper('css/dataTables.bootstrap.css'));
        $cssHelper($assetHelper('css/media.css'));

        $uploadUrl = Pi::service('url')->assemble(Pi::engine()->section() == 'admin' ? 'admin' : 'default', array(
            'module' => 'media',
            'controller' => 'modal',
            'action' => 'upload',
        ));

        $listUrl = Pi::service('url')->assemble(Pi::engine()->section() == 'admin' ? 'admin' : 'default', array(
            'module' => 'media',
            'controller' => 'modal',
            'action' => 'list',
        ));

        $formlistUrl = Pi::service('url')->assemble(Pi::engine()->section() == 'admin' ? 'admin' : 'default', array(
            'module' => 'media',
            'controller' => 'modal',
            'action' => 'formlist',
        ));

        $mediaFormAction = Pi::service('url')->assemble(Pi::engine()->section() == 'admin' ? 'admin' : 'default', array(
            'module' => 'media',
            'controller' => 'modal',
            'action' => 'mediaform',
        ));


        $formModalTitle = __("Edit");
        $formModalSaveBtn = __("Save");
        $formModalCancelBtn = __("Cancel");

        $modalHtml = <<<HTML
        
<div id="addMediaModal" class="modal fade" tabindex="-1" role="dialog">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                <h4 class="modal-title">{$modalTitle}</h4>
            </div>
            <div class="modal-body">
            
                <div id="dropzone-media-form" class="dropzone"></div>
                <br />
                <div id="media_gallery"></div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-default" data-dismiss="modal">{$formModalCancelBtn}</button>
                <button id="mediaModalSaveBtn" type="button" class="btn btn-primary" data-dismiss="modal">{$saveBtnTitle}</button>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="editMediaModal" tabindex="-1" role="dialog" aria-labelledby="myModalLabel">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                <h4 class="modal-title" id="myModalLabel">{$formModalTitle}</h4>
            </div>
            <div class="modal-body" id="editMediaModalContent">

            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
                <button id="editMediaModalSaveBtn" type="button" class="btn btn-primary">{$formModalSaveBtn}</button>
            </div>
        </div>
    </div>
</div>

<script>
    var uploadUrl = "{$uploadUrl}";
    var listUrl = "{$listUrl}";
    var formlistUrl = "{$formlistUrl}";
    var mediaFormAction = "{$mediaFormAction}";
</script>
HTML;

        $name = $this->getName();
        $label = $this->getLabel();
        $isMediaGallery = $this->getOption('media_gallery') ? 1 : 0;

        $description = <<<HTML
        
<div class="panel panel-default">
  <div class="panel-heading"><button class="btn btn-primary btn-sm" data-input-name="{$name}" data-media-gallery="{$isMediaGallery}" data-toggle="modal" type="button" data-target="#addMediaModal"><span class="glyphicon glyphicon-picture"></span> {$label}</button></div>
  <div class="panel-body">
    <div class="media-form-list media-form-list-{$name}" data-input-name="{$name}" >
        <ul id="sortable">
            <li class="ui-state-default">?</li>
        </ul>
    </div>
    <!--<p class="text-center small">fezfzfefez</p>-->
  </div>
</div>
HTML;

        if(!isset($GLOBALS['isMediaModalLoaded'])){
            $GLOBALS['isMediaModalLoaded'] = true;

            $description .= $modalHtml;

            $cropView = new \Zend\View\Model\ViewModel;
            $cropView->setTemplate('front/partial/crop');
            $cropView->setVariable('module', 'media');
            $cropView->setVariable('controller', 'list');

            $cropHtml = Pi::service('view')->render($cropView);

            $description .= $cropHtml;
        }

        $this->attributes['class'] = 'media-input hide';
        $this->attributes['description'] = $description;

        return $this->attributes;
    }
}