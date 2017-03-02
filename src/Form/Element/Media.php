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

        $assetHelper = Pi::service('view')->gethelper('assetModule');
        $jsHelper = Pi::service('view')->gethelper('js');
        $jsHelper($assetHelper('js/dropzone.js'));

        $cssHelper = Pi::service('view')->gethelper('css');
        $cssHelper($assetHelper('css/dropzone.css'));
        $cssHelper($assetHelper('css/modal.css'));

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
                <h4 class="modal-title">{$modalTitle}</h4>
            </div>
            <div class="modal-body">
            
                <div id="dropzone-media-form" class="dropzone"></div>
                <br />
                <div id="media_gallery"></div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
                <button id="mediaModalSaveBtn" type="button" class="btn btn-primary" data-dismiss="modal">{$saveBtnTitle}</button>
            </div>
        </div>
    </div>
</div>

<script>
    
    Dropzone.autoDiscover = false;
    
    $(function() {
        // Dropzone class:
        var myDropzone = new Dropzone("#dropzone-media-form", { url: "{$uploadUrl}"});
        
        myDropzone.on("complete", function(file) {
            loadList();
        });
        
        $('#mediaModalSaveBtn').click(function(){
            
            var inputName = $('#addMediaModal').attr('data-input-name');
            
            var checkedMedia = [];
            
            $('[data-media-id].checked').each(function(){
                checkedMedia.push($(this).attr('data-media-id'));
            });
            
            
            $('[name="'+ inputName +'"]').val(checkedMedia.join());
        });
    })
    
    function loadList(){
        $.ajax({
            url: "{$listUrl}",
            cache: false
        }).done(function( html ) {
            $( "#media_gallery" ).html( html );  
            
            var inputName = $('#addMediaModal').attr('data-input-name');
            var inputCurrentArray = $('[name="'+ inputName +'"]').val().split(",");
        
            inputCurrentArray.forEach(function(value){
                $('[data-media-id="'+value+'"]').addClass('checked');
            });
        });
    }
    
    $('#addMediaModal').on('show.bs.modal', function (event) {
        $( "#media_gallery" ).html('');
    }).on('shown.bs.modal', function (event) {
        
        loadList();
        
        var button = $(event.relatedTarget);
        var inputName = button.attr('data-input-name');
        var mediaGallery = button.attr('data-media-gallery');
        
        $(this).attr('data-input-name', inputName);
        $(this).attr('data-media-gallery', mediaGallery);
    })
    
    $( document ).on('click', '#media_gallery a:not(.no-ajax)', function(e){
        e.preventDefault();
        $.ajax({
              url: $(this).attr('href'),
              cache: false
        }).done(function( html ) {
            $( "#media_gallery" ).html( html );                                
        });
    });
    
    $( document ).on('click', '[data-media-id]', function(e){
        e.preventDefault();
        
        if($("#addMediaModal").attr('data-media-gallery') == 0){
            $('[data-media-id]').removeClass('checked');            
        }
        
        $(this).toggleClass('checked');
    });
    
</script>
HTML;

        $name = $this->getName();
        $label = $this->getLabel();

        $isMediaGallery = $this->getOption('media_gallery') ? 1 : 0;

        $description = <<<HTML
<button class="btn btn-primary btn-sm" data-input-name="{$name}" data-media-gallery="{$isMediaGallery}" data-toggle="modal" type="button" data-target="#addMediaModal"><span class="glyphicon glyphicon-picture"></span> {$label}</button>
HTML;

        $this->setLabel('');

        if(!isset($GLOBALS['isMediaModalLoaded'])){
            $description .= $modalHtml;
            $GLOBALS['isMediaModalLoaded'] = true;
        }

        $this->attributes['description'] = $description;

        return $this->attributes;
    }
}