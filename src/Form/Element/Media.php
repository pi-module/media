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
use Zend\Form\Element\Button as ZendButton;

class Media extends ZendButton
{
    /**
     * @return array
     */
    public function getAttributes()
    {
        $newUploadTabLabel = __("New upload");
        $galleryTabLabel = __("Pickup from gallery");

        $assetHelper = Pi::service('view')->gethelper('assetModule');
        $jsHelper = Pi::service('view')->gethelper('js');
        $jsHelper($assetHelper('js/dropzone.js'));

        $cssHelper = Pi::service('view')->gethelper('css');
        $cssHelper($assetHelper('css/dropzone.css'));
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

        $modalHtml = <<<HTML
        
<div id="addMediaModal" class="modal fade" tabindex="-1" role="dialog">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                <h4 class="modal-title">Modal title</h4>
            </div>
            <div class="modal-body">
                
                <!-- Nav tabs -->
                <ul class="nav nav-tabs" role="tablist">
                    <li role="presentation" class="active"><a href="#media_upload" aria-controls="home" role="tab" data-toggle="tab">{$newUploadTabLabel}</a></li>
                    <li role="presentation"><a href="#media_gallery" aria-controls="media_gallery" role="tab" data-toggle="tab">{$galleryTabLabel}</a></li>
                </ul>
                
                <!-- Tab panes -->
                <div class="tab-content">
                    <div role="tabpanel" class="tab-pane active" id="media_upload">
                        <div id="dropzone-media-form" class="dropzone" action="{$uploadUrl}"></div>                       
                    </div>
                    <div role="tabpanel" class="tab-pane" id="media_gallery"></div>
                </div>
                
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
                <button type="button" class="btn btn-primary">Save changes</button>
            </div>
        </div>
    </div>
</div>

<script>
    $('#addMediaModal a[data-toggle="tab"]').on('shown.bs.tab', function (e) {
          if($(e.target).attr('href') == '#media_gallery'){
              $.ajax({
                  url: "{$listUrl}",
                  cache: false
              }).done(function( html ) {
                    $( "#media_gallery" ).html( html );                    
              });
          }
    });
    
    $( document ).on('click', '#media_gallery a:not(.no-ajax)', function(e){
        e.preventDefault();
        $.ajax({
              url: $(this).attr('href'),
              cache: false
        }).done(function( html ) {
            $( "#media_gallery" ).html( html );                                
        });
    });
</script>
HTML;

        $this->attributes = array(
            'class' => 'btn btn-primary btn-sm',
            'data-toggle' => 'modal',
            'type' => 'button',
            'data-target' => '#addMediaModal',
            'description' => $modalHtml,
        );

        return $this->attributes;
    }
}