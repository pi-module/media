Dropzone.autoDiscover = false;

var refreshFormList = function(formList){
    var inputName = formList.attr('data-input-name');
    var inputElement = $('[name='+inputName+']');
    var inputValues = inputElement.val();

    if(inputValues){
        $.ajax({
            url: formlistUrl + "?ids=" + inputValues,
            cache: false
        }).done(function( html ) {
            formList.html( html );

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
    }
};

var loadList  = function(){
    $.ajax({
        url: listUrl,
        cache: false
    }).done(function( html ) {
        $( "#media_gallery" ).html( html );

        var inputName = $('#addMediaModal').attr('data-input-name');
        var inputCurrentArray = $('[name="'+ inputName +'"]').val().split(",");

        inputCurrentArray.forEach(function(value){
            $('[data-media-id="'+value+'"]').addClass('checked');
        });

        $('#media_gallery .table').DataTable({
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
            }
        });
    });
};

$(function() {
    // Dropzone class:
    var myDropzone = new Dropzone("#dropzone-media-form", { url: uploadUrl});

    myDropzone.on("complete", function(file) {
        loadList();
    });

    $('#mediaModalSaveBtn').click(function(){

        var inputName = $('#addMediaModal').attr('data-input-name');

        var checkedMedia = [];

        $('#media_gallery [data-media-id].checked').each(function(){
            checkedMedia.push($(this).attr('data-media-id'));
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
        $( "#media_gallery" ).html('');
    }).on('shown.bs.modal', function (event) {

        loadList();

        var button = $(event.relatedTarget);
        var inputName = button.attr('data-input-name');
        var mediaGallery = button.attr('data-media-gallery');

        $(this).attr('data-input-name', inputName);
        $(this).attr('data-media-gallery', mediaGallery);
    });

    $( document ).on('click', '#media_gallery a.do-ajax', function(e){
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



    $('#editMediaModalSaveBtn').click(function(){
        console.log("$('#editMediaModalSaveBtn').click(function(){});");
    });

    $(document).on('show.bs.modal', '#editMediaModal',  function (event) {
        $( "#editMediaModalContent" ).html('');
    }).on('shown.bs.modal', '#editMediaModal', function (event) {

        var button = $(event.relatedTarget);
        var mediaId = button.attr('data-media-id');

        $.ajax({
            url: mediaFormAction + '?id=' + mediaId,
            cache: false
        }).done(function( html ) {
            $( "#editMediaModalContent" ).html( html );
        });
    });
});