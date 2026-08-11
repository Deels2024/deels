
<script type="text/javascript"><!--
    $(document).on('click', 'a.thumbnail', function(e) {
        e.preventDefault();

        if ( $(this).closest('#modal-filemanager').hasClass('ckeditorBrowse') ){
            //Stop the script if request come from ckeditor image browse
            return;
        }

        $target = $('[data-current-img="true"]');
        var img_src = $(this).find('img').attr('src');
        var name = $(this).find('img').attr('title');
        var img_id = $(this).parent().find('input').val();

        $target.find('a[data-toggle="filemanager"]').html('<img src="'+img_src+'" class="img-thumbnail" />');
        $target.find('.image-input').val(img_id);
        $target.find('.upload_file_name').html(name);

        //$('#thumb-image').find('img').attr('src', $(this).find('img').attr('src'));
        //$('#input-image').val($(this).parent().find('input').val());

        $('#modal-filemanager').modal('hide');
    });

    $(document).on('click', '.modal-footer .pagination a', function(e) {
        e.preventDefault();
        $('#modal-filemanager').load($(this).attr('href'));
    });

    $(document).on('click', '#button-refresh', function(e) {
        e.preventDefault();
        $('#modal-filemanager').load($(this).attr('href'));
    });

    $(document).on('keydown', 'input[name="filemanager-search"]', function(e) {
        if (e.which == 13) {
            $('#button-search').trigger('click');
        }
    });

    $(document).on('click', '#button-search', function(e) {
        var url = '{{route('load_filemanager')}}';
        var filter_name = $('input[name="filemanager-search"]').val();
        if (filter_name) {
            url += '?filter_name=' + encodeURIComponent(filter_name);
        }
        $('#modal-filemanager').load(url);
    });
    //--></script>
<script type="text/javascript"><!--
    $(document).on('click', '#button-upload, #button-upload2', function() {
        $('#form-upload').remove();

        $('body').prepend('<form enctype="multipart/form-data" id="form-upload" style="display: none;"><input type="hidden" name="_token" value="{{csrf_token()}}" /><input type="file" name="files[]" value="" multiple="multiple" /></form>');

        $("#form-upload input[name='files[]']").trigger('click');

        if (typeof timer != 'undefined') {
            clearInterval(timer);
        }

        timer = setInterval(function() {
            if ($("#form-upload input[name='files[]']").val() != '') {
                clearInterval(timer);

                $.ajax({
                    url: '{{route('post_media_upload')}}',
                    type: 'post',
                    /*dataType: 'json',*/
                    data: new FormData($('#form-upload')[0]),
                    cache: false,
                    contentType: false,
                    processData: false,
                    beforeSend: function() {
                        $('#button-upload i').replaceWith('<i class="fa fa-circle-o-notch fa-spin"></i>');
                        $('#button-upload').prop('disabled', true);
                    },
                    complete: function() {
                        $('#button-upload i').replaceWith('<i class="fa fa-upload"></i>');
                        $('#button-upload').prop('disabled', false);
                    },
                    xhr: function () {
                        var xhr = new window.XMLHttpRequest();
                        //Download progress
                        xhr.upload.addEventListener("progress", function (evt) {
                            if (evt.lengthComputable) {
                                var percentComplete = (evt.loaded / evt.total) * 100;
                                percentComplete = Math.round(percentComplete);
                                $('#statusMsg').html('<div class="progress"> <div class="progress-bar progress-bar-success" role="progressbar" aria-valuenow="'+percentComplete+'" aria-valuemin="0" aria-valuemax="100" style="width: '+percentComplete+'%">'+percentComplete+'% Uploaded </div> </div>');
                            }
                        }, false);
                        return xhr;
                    },
                    success: function(json) {
                       if (json.success){
                           $('#statusMsg').html('<p class="alert alert-success">'+json.msg+'</p>');
                           $('#button-refresh').trigger('click');
                       }else {
                           $('#statusMsg').html('<p class="alert alert-danger">'+json.msg+'</p>');
                       }
                    },
                    error: function(xhr, ajaxOptions, thrownError) {
                        var msg = '';
                        if (typeof thrownError !== 'undefined'){
                            msg += thrownError + '<br />';
                        }

                        if (typeof xhr.responseJSON !== 'undefined' && xhr.responseJSON.message){
                            msg += xhr.responseJSON.message + '<br />';
                        }
                        if (typeof xhr.responseJSON !== 'undefined' && xhr.responseJSON.file){
                            msg += xhr.responseJSON.file + '<br />';
                        }

                        if (typeof xhr.responseJSON !== 'undefined' && xhr.responseJSON.line){
                            msg += ' line number '+ xhr.responseJSON.line;
                        }

                        $('#statusMsg').html('<p class="alert alert-warning">'+msg+' </p>');
                    }
                });
            }
        }, 500);
    });

    $(document).on('click', '#modal-filemanager #button-delete', function(e) {
        if (!confirm('Are you sure?')) {
            return false;
        }

        var media_ids = $('input[name^=\'media_ids\']:checked').map(function() {
            return $(this).val();
        }).get().join(",");

        $.ajax({
            url: '{{route('delete_media')}}',
            type: 'POST',
            data: {media_ids : media_ids, _token : '{{csrf_token()}}'},
            beforeSend: function() {
                $('#button-delete').prop('disabled', true);
            },
            complete: function() {
                $('#button-delete').prop('disabled', false);
            },
            success: function(json) {
                if (json.success){
                    $('#statusMsg').html('<p class="alert alert-success">'+json.msg+'</p>');
                    $('#button-refresh').trigger('click');
                }else {
                    $('#statusMsg').html('<p class="alert alert-warning">'+json.msg+'</p>');
                }
            },
            error: function(xhr, ajaxOptions, thrownError) {
                $('#statusMsg').html('<p class="alert alert-warning">'+thrownError+'</p>');
            }
        });


    });
    //--></script>



<script type="text/javascript">

    $(document).ready(function(){
        $(document).on('click', 'a[data-toggle="filemanager"]', function(e) {
            var $element = $(this);
            var $popover = $element.data('bs.popover'); // element has bs popover?

            e.preventDefault();

            $('.image-wrap').removeAttr('data-current-img');
            $element.closest('.image-wrap').attr('data-current-img', 'true');


            // destroy all image popovers
            $('a[data-toggle="filemanager"]').popover('dispose');

            // remove flickering (do not re-add popover when clicking for removal)
            if ($popover) {
                return;
            }

            $element.popover({
                html: true,
                placement: 'right',
                trigger: 'manual',
                content: function() {
                    return '<button type="button" id="button-image" class="btn btn-primary"><i class="fa fa-pencil"></i></button> <button type="button" id="button-clear" class="btn btn-danger"><i class="fa fa-trash-o"></i></button>';
                }
            });

            $element.popover('show');

            $('#button-image').on('click', function() {
                var $button = $(this);
                var $icon   = $button.find('> i');

                $('#modal-filemanager').remove();

                 $.ajax({
                     url: '{{route('load_filemanager')}}',
                     dataType: 'html',
                     beforeSend: function() {
                         $button.prop('disabled', true);
                         if ($icon.length) {
                             $icon.attr('class', 'fa fa-circle-o-notch fa-spin');
                         }
                     },
                     complete: function() {
                         $button.prop('disabled', false);

                         if ($icon.length) {
                             $icon.attr('class', 'fa fa-pencil');
                         }
                     },
                     success: function(html) {
                         $('body').append('<div id="modal-filemanager" class="modal">' + html + '</div>');
                         $('#modal-filemanager').modal('show');
                     }
                 });

                $element.popover('dispose');
            });

            $(document).on('click','#button-clear', function() {
                //$element.find('img').attr('src', $element.find('img').attr('data-placeholder'));
                $element.html('<img src="{{route('home')}}/assets/images/upload-icon.png" alt="" class="img-fluid">');
                $element.parent().find('input').val('');
                $element.popover('dispose');
            });
        });
    })

</script>

<script type="text/javascript">

    $(document).ready(function(){
        if( typeof(CKEDITOR) !== "undefined" ) {
            CKEDITOR.on('dialogDefinition', function (ev) {
                //dialogDefinition is a ckeditor event it's fired when ckeditor dialog instance is called
                var dialogName = ev.data.name;
                var dialogDefinition = ev.data.definition;
                if (dialogName == 'image') { //dialogName is name of dialog and identify which dialog is fired.
                    var infoTab = dialogDefinition.getContents('info'); // get tab of the dialog
                    var browse = infoTab.get('browse'); //get browse server button
                    browse.onClick = function () {
                        $('#modal-filemanager').remove();
                        $.ajax({
                            url: '{{route('load_filemanager')}}',
                            dataType: 'html',
                            success: function (html) {
                                $('body').append('<div id="modal-filemanager" class="modal ckeditorBrowse">' + html + '</div>');
                                $('#modal-filemanager').modal('show');
                            }
                        });

                        $(document).on('click', '.ckeditorBrowse a.thumbnail', function () {
                            var img_src = $(this).attr('data-image-size-original');
                            var img_alt = $(this).find('img').attr('alt');
                            var dialog = CKEDITOR.dialog.getCurrent();
                            dialog.setValueOf('info', 'txtUrl', img_src);
                            dialog.setValueOf('info', 'txtAlt', img_alt);
                            $('#modal-filemanager').modal('hide');
                        });
                    };

                }
            });
        }

    });

</script>