Dropzone.autoDiscover = false;


var checkFormCanBeSubmit = function(){
    var incompleteListCount = $( '.media-form-list .invalid-media').length;
    $( '.media-form-list').parents('form').find('input[type=submit]').prop('disabled', incompleteListCount ? true : false);

    $('.media-form-list').each(function(){
        var invalidMedia = $(this).find('.invalid-media');

        if(invalidMedia.length == 0){
            $(this).find('.media-list-incomplete').removeClass('media-list-incomplete');
        } else {
            $(this).find('.media-list-incomplete').addClass('media-list-incomplete');
        }
    });
};

var refreshFormList = function(formList){
    var inputName = formList.attr('data-input-name');
    var inputElement = $('[name='+inputName+']');
    var inputValues = inputElement.val();

    formList.find('.ajax-spinner').removeClass('hide');
    formList.find('.sortable-list').remove();

    $.ajax({
        url: formlistUrl + "?ids=" + inputValues,
        cache: false
    }).done(function( html ) {
        formList.html( html );

        formList.parents('.col-sm-5.js-form-element').removeClass('col-sm-5').addClass('col-sm-9');

        checkFormCanBeSubmit();

        if(inputValues.split(',').length > 1){
            $( '.media-form-list[data-input-name='+inputName+'] .media-list-sortable' ).sortable({
                update: function( event, ui ) {
                    var mediaElements = $(this).children('[data-media-id]');

                    var newIds = [];
                    mediaElements.each(function(){
                        newIds.push($(this).attr('data-media-id'));
                    });

                    inputElement.val(newIds.join());
                }
            }).disableSelection();
        }
    });
};

var addMediaToModal = function(media){
    var container = $('#selectedMediaListModal .list');
    var html = container.html();
    var mediaTmpl = '<li>' +
        '<button class="btn btn-default btn-xs unlink-media-btn">' +
        '<span class="glyphicon glyphicon-remove"></span>' +
        '</button>' +
        '<img data-selected-media-id="'+media.id+'" class="thumbnail" src="' + media.img + '" />' +
        '</li>';
    container.html(html + mediaTmpl);

    container.find('.unlink-media-btn').on('click', function(){
        var img = $(this).parents('li').find('img[data-selected-media-id]');
        var mediaId = img.attr('data-selected-media-id');
        $(this).parents('li').remove();
        $('[data-media-id='+mediaId+']').removeClass('checked');
    });
};

var removeMediaToModal = function(media){
    var container = $('#selectedMediaListModal .list');
    container.find('[data-selected-media-id='+media.id+']').parents('li').remove();
};

var initDataTable = function(){
    $('#media_gallery .table').DataTable({
        "lengthMenu": [[5, 10, 20], [5, 10, 20]],
        "bDestroy": true,
        "ordering": false,
        "processing": true,
        "serverSide": true,
        "ajax": listUrl,
        "columns": [
            {
                "data": "checked",
                "className": "checked-column"
            },
            { "data": "img" },
            { "data": "title" },
            { "data": "date" },
            { "data": "removeBtn" }
        ],
        "language" : {
            "sProcessing":     "Traitement en cours...",
            "sSearch":         "Rechercher&nbsp;:",
            "sLengthMenu":     "Afficher _MENU_ &eacute;l&eacute;ments",
            "sInfo":           "Affichage de l'&eacute;l&eacute;ment _START_ &agrave; _END_ sur _TOTAL_ &eacute;l&eacute;ments",
            "sInfoEmpty":      "Affichage de l'&eacute;l&eacute;ment 0 &agrave; 0 sur 0 &eacute;l&eacute;ment",
            "sInfoFiltered":   "(filtr&eacute; de _MAX_ &eacute;l&eacute;ments au total)",
            "sInfoPostFix":    "",
            "sLoadingRecords": "Chargement en cours...",
            "sZeroRecords":    "Aucun &eacute;l&eacute;ment &agrave; afficher",
            "sEmptyTable":     "Aucune donn&eacute;e disponible dans le tableau",
            "oPaginate": {
                "sFirst":      "Premier",
                "sPrevious":   "Pr&eacute;c&eacute;dent",
                "sNext":       "Suivant",
                "sLast":       "Dernier"
            },
            "oAria": {
                "sSortAscending":  ": activer pour trier la colonne par ordre croissant",
                "sSortDescending": ": activer pour trier la colonne par ordre d&eacute;croissant"
            }
        },
        "initComplete": function(settings, json) {

        }
    }).on( 'draw.dt', function () {

        var container = $('#selectedMediaListModal .list');
        var selectedMedia = container.find('[data-selected-media-id]');

        selectedMedia.each(function(){
            var id = $(this).attr('data-selected-media-id');
            $('[data-media-id="'+id+'"]').addClass('checked');
        });
    } );
};

var loadList  = function(){
    var inputName = $('#addMediaModal').attr('data-input-name');
    var inputCurrent = $('[name="'+ inputName +'"]').val();

    if(inputCurrent){
        $.ajax({
            url: currentSelectedMediaUrl + "?ids=" + inputCurrent,
            cache: false,
            dataType: 'json'
        }).done(function( data ) {
            data.forEach(function(media){
                addMediaToModal(media);
            });

            initDataTable();
        });
    } else {
        initDataTable();
    }
};

var myDropzone;

$(function() {

    $(document).on('click', '.btn-unlink-action', function(){
        var mediaId = $(this).data('media-id');

        var input = $(this).parents('.js-form-element').find('.media-input');

        var currentInputValue = input.val();
        var currentInputValueArray = currentInputValue.split(',');

        var newInputValueArray = currentInputValueArray.filter(function(item) {
            return item != mediaId
        });

        input.val(newInputValueArray.join()).change();
    });

    // Dropzone class:
    myDropzone = new Dropzone("#dropzone-media-form", {
        url: uploadUrl,
        dictDefaultMessage: "Drop files here to upload new files<br />(or select existing files below)",
        init: function(){
            this.on('resetFiles', function() {
                if(this.files.length != 0){
                    for(i=0; i<this.files.length; i++){
                        this.files[i].previewElement.remove();
                    }
                    this.files.length = 0;
                }

                $('#dropzone-media-form').removeClass('dz-started');
            });
        }
    });

    myDropzone.on("processing", function(file) {
        $('.ajax-spinner').show();
    });

    myDropzone.on("success", function(file) {
        $('#media_gallery .table').DataTable().ajax.reload(function(){
            $('#media_gallery .table tbody tr:first-child').click();
        });
    });

    $('#mediaModalSaveBtn').click(function(){
        var inputName = $('#addMediaModal').attr('data-input-name');
        var checkedMedia = [];
        var container = $('#selectedMediaListModal .list');
        var selectedMedia = container.find('[data-selected-media-id]');

        selectedMedia.each(function(){
            var id = $(this).attr('data-selected-media-id');
            checkedMedia.push(id);
        });

        $('[name="'+ inputName +'"]').val(checkedMedia.join()).change();

    });

    $( ".media-form-list" ).each(function(){
        refreshFormList($(this));
    });

    $('.media-input').change(function(){
        var formListElement = $('.media-form-list[data-input-name='+$(this).attr('name')+']');
        refreshFormList(formListElement);
    });

    $('#addMediaModal').on('show.bs.modal', function (event) {

        var button = $(event.relatedTarget);
        var inputName = button.attr('data-input-name');
        var mediaGallery = button.attr('data-media-gallery');

        $(this).attr('data-input-name', inputName);
        $(this).attr('data-media-gallery', mediaGallery);

        $('#selectedMediaListModal .list').html('');

        myDropzone.emit("resetFiles");

        loadList();
    });

    $( document ).on('click', '[data-media-id]', function(e){
        if($("#addMediaModal").attr('data-media-gallery') == 0){
            $('[data-media-id]').removeClass('checked');
            $('#selectedMediaListModal .list').html('');
        }

        $(this).toggleClass('checked');

        var media = {
            id: $(this).attr('data-media-id'),
            img: $(this).attr('data-media-img')
        };

        if($(this).hasClass('checked')){
            addMediaToModal(media);
        } else {
            removeMediaToModal(media);
        }
    });

    $( document ).on('click', '#media_gallery a.remove-media-ajax', function(e){
        e.preventDefault();
        e.stopImmediatePropagation();

        if(!$(this).is('[disabled]')){

            $('#removeMediaModal').modal();

            var mediaTr = $(this).parents('tr[data-media-id]');
            var mediaId = mediaTr.attr('data-media-id');

            $('#removeMediaModalBtn').attr('href', $(this).attr('data-href')).attr('data-media-id-to-remove', mediaId);
        } else {
            return false;
        }
    });

    $( document ).on('click', '#removeMediaModalBtn', function(e){
        e.preventDefault();
        e.stopImmediatePropagation();

        $('#removeMediaModal').modal('hide');

        var mediaId = $(this).attr('data-media-id-to-remove');
        var media = {
            id: mediaId
        };

        $.ajax({
            url: $(this).attr('href'),
            cache: false
        }).done(function( html ) {
            $('#media_gallery .table').DataTable().ajax.reload(null, false);
            removeMediaToModal(media);
        });
    });

    $(document).on('submit', '#editMediaModalContent form',  function (event) {
        event.preventDefault();

        var form = $(this);

        // Create an FormData object
        var data = new FormData(form[0]);

        $.ajax({
            url: $(this).attr('action'),
            type: $(this).attr('method'),
            enctype: 'multipart/form-data',
            dataType: "json",
            data: data,
            processData: false,
            contentType: false,
            success: function(data) {
                if(data.status == 0){
                    console.log(data);


                    $('#editMediaModalContent').html(data.content);
                    parseCrop();
                } else {
                    $('#editMediaModal').modal('hide');

                    var mediaId = form.find('[name=id]').val();
                    var d = new Date();

                    $('[data-media-id='+ mediaId +'] img').attr('src', data.url + '?' + d.getTime());
                    $('.invalid-media[data-media-id='+ mediaId +']').removeClass('invalid-media');

                    checkFormCanBeSubmit();
                }
            }
        });
    });

    $('#editMediaModalSaveBtn').click(function(){
        var form = $('#editMediaModalContent form');

        form.find(':submit').click();
    });

    $(document).on('show.bs.modal', '#editMediaModal',  function (event) {
        $( "#editMediaModalContent" ).html('');
    }).on('hidden.bs.modal', '#editMediaModal',  function (event) {
        $( "#editMediaModalContent" ).html('');
    }).on('shown.bs.modal', '#editMediaModal', function (event) {

        var button = $(event.relatedTarget);
        var mediaId = button.attr('data-media-id');

        $.ajax({
            url: mediaFormAction + '?id=' + mediaId,
            cache: false,
            dataType: "json",
        }).done(function( data ) {
            $( "#editMediaModalContent" ).html( data.content );
            parseCrop();
        });
    });
});