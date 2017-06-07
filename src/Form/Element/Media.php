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

        $assetHelper = Pi::service('view')->gethelper('assetModule');
        $jsHelper = Pi::service('view')->gethelper('js');
        $jsHelper($assetHelper('js/dropzone.js', 'media'));
        $jsHelper($assetHelper('js/jquery-ui.custom.min.js', 'media'));
        $jsHelper($assetHelper('js/jquery.dataTables.min.js', 'media'));
        $jsHelper($assetHelper('js/dataTables.bootstrap.min.js', 'media'));

        $jsHelper($assetHelper('js/modal.js', 'media'));

        $cssHelper = Pi::service('view')->gethelper('css');
        $cssHelper($assetHelper('css/dropzone.css', 'media'));
        $cssHelper($assetHelper('css/dataTables.bootstrap.css', 'media'));
        $cssHelper($assetHelper('css/media.css', 'media'));

        $uploadMaxSize = Pi::service('module')->config('max_size', 'media') . ' ko';
        $uploadMaxDimensions = Pi::service('module')->config('image_maxw', 'media') . ' x ' . Pi::service('module')->config('image_maxh', 'media') . " px";

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

        $currentSelectedMediaUrl = Pi::service('url')->assemble(Pi::engine()->section() == 'admin' ? 'admin' : 'default', array(
            'module' => 'media',
            'controller' => 'modal',
            'action' => 'currentSelectedMedia',
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

        $fromModule = $this->getOption('module') ?: 'media';
        $maxGalleryImages = Pi::service('module')->config('freemium_max_gallery_images', $fromModule);
        $freemiumMsg = Pi::service('module')->config('freemium_alert_msg', $fromModule);


        $closeTitle = __("Close");
        $confirmTitle = __("Confirm");
        $freemiumAlertTitle = __("Alert Freemium account limitations");
        $seasonAlertTitle = __("Alert season duplicates");
        $seasonAlertMsg = __("Alert season duplicates : you must have online one picture of each season only");
        $confirmDeleteHeaderTitle = __("Delete media");
        $confirmDeleteActionTitle = __("Do you confirm you want to delete this media definitively ?");

        $maxAlertTitle = __("Max media alert");
        $maxAlertMsg = __("Max media alert : you have reach maximum of picture for this field");

        $checkedMediaTitle = __("Your selection");
        $formModalTitle = __("Edit");
        $formModalSaveBtn = __("Save");
        $formModalCancelBtn = __("Cancel");

        $colThumbnail = __("Thumbnail");
        $colTitle = __("Title");
        $colDate = __("Date");
        $colSeason = __("Season");
        $sProcessing = __("Loading in progress");
        $sSearch = __("Search&nbsp;:");

        $sLengthMenu = __("Show _MENU_ elements");
        $sInfo = __("Show elements _START_ to _END_ on _TOTAL_ elements");
        $sInfoEmpty = __("Show elements 0 to 0 on 0 element");
        $sInfoFiltered = __("(filtered from _MAX_ elements)");
        $sLoadingRecords = __("Loading in progress...");
        $sZeroRecords = __("No element to display");
        $sEmptyTable = __("No data available in the table");
        $sFirst = __("First");
        $sPrevious = __("Previous");
        $sNext = __("Next");
        $sLast = __("Last");

        $modalHtml = <<<HTML
        
<div id="addMediaModal" class="modal fade" tabindex="-1" role="dialog">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                <h4 class="modal-title">{$modalTitle} <span class="max"></span></h4>
            </div>
            <div class="modal-body">
            
                <div id="dropzone-media-form" class="dropzone"></div>
                <br />
                
                <div id="media_gallery">
                    <table class="table table-striped" data-sProcessing="{$sProcessing}" data-sSearch="{$sSearch}" data-sLengthMenu="{$sLengthMenu}" data-sInfo="{$sInfo}" data-sInfoEmpty="{$sInfoEmpty}" data-sInfoFiltered="{$sInfoFiltered}" data-sLoadingRecords="{$sLoadingRecords}" data-sZeroRecords="{$sZeroRecords}" data-sEmptyTable="{$sEmptyTable}" data-sFirst="{$sFirst}" data-sPrevious="{$sPrevious}" data-sNext="{$sNext}" data-sLast="{$sLast}">
                        <thead>
                        <tr>
                            <th></th>
                            <th>{$colThumbnail}</th>
                            <th class="media-th-title">$colTitle</th>
                            <th>{$colDate}</th>
                            <th>{$colSeason}</th>
                            <th></th>
                        </tr>
                        </thead>
                        <tbody>
                        </tbody>
                    </table>
                </div>
                <hr />
                <div id="selectedMediaListModal">
                    <h4>{$checkedMediaTitle} :</h4>
                    <ul class="list"></ul>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-default" data-dismiss="modal">{$formModalCancelBtn}</button>
                <button id="mediaModalSaveBtn" type="button" class="btn btn-primary" data-dismiss="modal">{$saveBtnTitle}</button>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="removeMediaModal" tabindex="-1" role="dialog" aria-labelledby="myModalLabel">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                <h4 class="modal-title" id="myModalLabel">$confirmDeleteHeaderTitle</h4>
            </div>
            <div class="modal-body">
                $confirmDeleteActionTitle
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-default" data-dismiss="modal">$closeTitle</button>
                <a id="removeMediaModalBtn" type="button" class="btn btn-primary">$confirmTitle</a>
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

<div class="modal fade" id="freemiumAlert" tabindex="-1" role="dialog" aria-labelledby="myModalLabel">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                <h4 class="modal-title" id="myModalLabel">{$freemiumAlertTitle}</h4>
            </div>
            <div class="modal-body" id="editMediaModalContent">
                {$freemiumMsg}
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="seasonAlert" tabindex="-1" role="dialog" aria-labelledby="myModalLabel">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                <h4 class="modal-title" id="myModalLabel">{$seasonAlertTitle}</h4>
            </div>
            <div class="modal-body" id="editMediaModalContent">
                {$seasonAlertMsg}
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="maxAlert" tabindex="-1" role="dialog" aria-labelledby="myModalLabel">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                <h4 class="modal-title" id="myModalLabel">{$maxAlertTitle}</h4>
            </div>
            <div class="modal-body" id="editMediaModalContent">
                {$maxAlertMsg}
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>

<script>
    var uploadUrl = "{$uploadUrl}";
    var uploadMaxSize = "{$uploadMaxSize}";
    var uploadMaxDimensions = "{$uploadMaxDimensions}";
    var listUrl = "{$listUrl}";
    var currentSelectedMediaUrl = "{$currentSelectedMediaUrl}";
    var formlistUrl = "{$formlistUrl}";
    var mediaFormAction = "{$mediaFormAction}";
</script>
HTML;

        $addLabel = __("Choose or add new media file");
        $name = $this->getName();
        $isMediaGallery = $this->getOption('media_gallery') ? 1 : 0;
        $isMediaSeason = $this->getOption('media_season') ? 1 : 0;
        $isMediaSeasonRecommended = $this->getOption('media_season_recommended') ? 1 : 0;
        $isFreemium = $this->getOption('is_freemium') ? 1 : 0;
        $canConnectLists = $this->getOption('can_connect_lists') ? 1 : 0;

        $isMediaSeasonRecommendedMsg = $isMediaSeason ? __("We recommend you to fill 4 pictures for all seasons") : '';

        if($isMediaSeason){
            $isMediaGallery = 1;
        }

        $noMediaLabel = __('No media for now...');
        $loader = $assetHelper('image/spinner.gif', 'media');
        $maxGalleryImagesMsg = '';
        $maxGalleryImagesConstrain = '';

        if(!$isMediaGallery){
            $maxGalleryImagesConstrain = '';
            $maxGalleryImagesMsg = __('1 picture only');
        } elseif($isMediaSeason){
            $maxGalleryImagesConstrain = 4;
            $maxGalleryImagesMsg = __('1 picture per season only = 4 pictures');
        } else if($isFreemium && $maxGalleryImages > 0){
            $maxGalleryImagesConstrain = $maxGalleryImages;
            $maxGalleryImagesMsg = ($isFreemium && $maxGalleryImages > 0) ? sprintf(__('Max %s pictures'), $maxGalleryImages) : '';
        }

        $description = <<<HTML
        
<div class="panel panel-default">
  <div class="panel-heading"><button class="btn btn-primary btn-sm" data-input-name="{$name}" data-media-season="{$isMediaSeason}" data-media-gallery="{$isMediaGallery}" data-max-gallery-images="{$maxGalleryImagesConstrain}" data-max-msg="{$maxGalleryImagesMsg}" data-toggle="modal" type="button" data-target="#addMediaModal">
    <span class="glyphicon glyphicon-picture"></span> {$addLabel}</button>
    &nbsp;&nbsp;&nbsp;<strong>{$maxGalleryImagesMsg}</strong> &nbsp;&nbsp;&nbsp; <span class="label label-warning label-lg additional_info hide">{$isMediaSeasonRecommendedMsg}</span>
  </div>
  <div class="panel-body">
    <div class="media-form-list media-form-list-{$name}" data-can-connect-lists="{$canConnectLists}" data-media-season="{$isMediaSeason}" data-media-season-recommended="{$isMediaSeasonRecommended}" data-input-name="{$name}" data-freemium="{$isFreemium}" data-max-gallery-images="{$maxGalleryImagesConstrain}">
        <div class="ajax-spinner hide">
            <img src="{$loader}" class="ajax-spinner-loader" alt="" />
        </div>
        <ul id="sortable" class="sortable-list">
            <li class="ui-state-default">{$noMediaLabel}</li>
        </ul>
    </div>
  </div>
</div>
HTML;

        if(!isset($GLOBALS['isMediaModalLoaded'])){
            $GLOBALS['isMediaModalLoaded'] = true;

            $cropView = new \Zend\View\Model\ViewModel;
            $cropView->setTemplate('media:front/partial/crop');
            $cropView->setVariable('module', 'media');
            $cropView->setVariable('controller', 'list');
            $cropHtml = Pi::service('view')->render($cropView);

            /* @var \Pi\Application\Service\View $view */
            $view = Pi::service('view');
            $view->getHelper('footScript')->addHtml($modalHtml);
            $view->getHelper('footScript')->addHtml($cropHtml);
        }

        $this->attributes['id'] = 'media_input_' . $this->getName();
        $this->attributes['class'] = 'media-input hide';
        $this->attributes['description'] = $description;

        return $this->attributes;
    }
}