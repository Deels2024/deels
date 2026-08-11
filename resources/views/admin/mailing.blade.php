@extends('layouts.admin.app_neon')



@section('content')
    <link href="https://cdn.jsdelivr.net/npm/summernote@0.8.18/dist/summernote-lite.min.css" rel="stylesheet">
    <style>
        .note-editor .note-editing-area .note-editable {
            background: #fff;
        }

        th {
            text-align: left;
        }

        .note-modal-backdrop {
            display: none !important;
        }

        @media (min-width: 768px) {
            .note-modal-content {
                margin: 150px auto !important;
            }
        }

        .note-modal-body .note-input {
            width: 86% !important;
            display: block !important;
            border: 1px solid #ededef !important;
            background: #fff !important;
            outline: 0 !important;
            padding: 6px 4px !important;
            font-size: 14px !important;
            -ms-box-sizing: border-box !important;
            box-sizing: border-box !important;
            opacity: 1 !important;
        }

        .note-modal-body input {
            color: #000000!important;
        }

        .note-form-group {
            padding-bottom: 10px !important;
        }
        .note-form-group input{ margin-bottom: 0!important;}

        .badge {
            display: inline-block;
            padding: 0.25em 0.4em;
            font-size: 75%;
            font-weight: 700;
            line-height: 1;
            text-align: center;
            white-space: nowrap;
            vertical-align: baseline;
            border-radius: 0.25rem;
        }
        .badge-pill {
            padding-right: 0.6em;
            padding-left: 0.6em;
            border-radius: 10rem;
        }
        .badge-success {
            color: #fff;
            background-color: #28a745;
        }
        .badge-primary {
            color: #fff;
            background-color: #007bff;
        }
        .badge-secondary {
            color: #fff;
            background-color: #6c757d;
        }
        .badge-danger {
            color: #fff;
            background-color: #dc3545;
        }
        .badge-warning {
            color: #212529;
            background-color: #ffc107;
        }
        .badge-info {
            color: #fff;
            background-color: #17a2b8;
        }
        .badge-light {
            color: #212529;
            background-color: #f8f9fa;
        }
        .badge-dark {
            color: #fff;
            background-color: #343a40;
        }

    </style>
    <main class="admin-main">
        <div class="account-main__head">
            <div class="account-main__head-side">
                <h1 class="account-main__title">Создать рассылку</h1>
            </div>
        </div>
{{--        <button class="aside-nav-btn">Открыть меню</button>--}}
        <form class="form form--admin" action="" method="post">
            @csrf
            <div class="form-field">
                <input type="text" name="mail-theme" placeholder="Тема сообщения" required>
            </div>
            <div class="form-field">
                <textarea style="height: 300px" name="message" id="summernote" rows="10" placeholder="Текст рассылки"></textarea>
                <small style="color: #ffffff">Используйте [username], если хотите обращаться к пользователю по нику.</small><br><br>
            </div>
            <label class="d-flex ai-center mt-1 mb-1">
                <input class="mr-1" type="checkbox" name="sendAllUsers"><span>Отправить всем пользователям</span>
            </label>
            <label class="d-flex ai-center mt-1 mb-1">
                <input class="mr-1" type="checkbox" name="sendCampaignUsers"><span>Отправить пользователям с копилками</span>
            </label>
            <div class="form-field">
                <input type="text" name="specificUser" placeholder="Вести E-mail пользователя">
            </div>
            <div class="form-row mb-2">
                <label class="d-flex ai-center mr-1 mt-1 mb-1">
                    <input class="mr-1" type="checkbox" name="sendByTime"><span>Отправить по времени</span>
                </label>
                <div class="form-field flex-1">
                    <input type="datetime-local" name="date" placeholder="Ввести дату">
                </div>
            </div>
            <label class="d-flex ai-center mt-1 mb-4">
                <input class="mr-1" type="checkbox" name="gmail_exclude"><span>Исключить получателей с почтой gmail.com</span>
            </label>
            <button class="form__btn btn btn_fill">Отправить</button>
        </form>
        <div class="admin-table">
            <table>
                <thead>
                <tr>
                    <th>#</th>
                    <th>Заголовок</th>
                    <th>Получатели</th>
                    <th>Дата отправки</th>
                    <th>Статус</th>
                    <th>Отправлено</th>
                    <th>Открыто</th>
                    <th>Нажатий</th>
                    <th></th>
                </tr>
                </thead>
                <tbody>
{{--                @foreach(\App\Models\Mailing::where('clicked','>',0)->where('delivered','>',5)->get() as $mailing)--}}
                @foreach($mailings as $mailing)
                    <tr>
                        <td>
                            <p>{{$mailing->id}}</p>
                        </td>
                        <td>
                            <p>
                                {{$mailing->subject}}
                            </p>
                        </td>
                        <td>
                            <p>

                                @if(isset($mailing->users[0]) && $mailing->users[0] === 'all')
                                    С копилками
                                @elseif($mailing->users)
                                    Заданный список
                                @else
                                    Все пользователи
                                @endif
                            </p>
                        </td>
                        <td>
                            <p>
                                {{\Carbon\Carbon::parse($mailing->sent_at)->format('d.m.Y H:i')}}
                            </p>
                        </td>
                        <td>
                            <p><span class="badge badge-pill badge-{{$mailing->getStatusColor()}}">{{$mailing->getStatus()}}</span></p>
                        </td>
                        <td>
                            <p>
                                @php
                                    $fails_count = $mailing->fails_count();
                                @endphp
                                {{$mailing->success_count() ?? 0}}
                                @if($fails_count > 0)
                                    ⚠️ <span style="color: #ff0000" title="Ошибок">({{$fails_count}})</span>
                                @endif
                                / {{$mailing->receivers_count ?? '--'}}

                            </p>
                        </td>
                        <td>
                            <p>{{$mailing->opened ?? 0}}</p>
                        </td>
                        <td>
                            <p>{{$mailing->clicked ?? 0}}</p>
                        </td>
                        <td>
                            <p>
                                <a class="actions-link" href="{{route('mailing_mails', [$mailing->id])}}" style="background-image: url(/dist/images/admin_icons/icon-eye.svg)"></a>
                            </p>
                        </td>
                    </tr>
                @endforeach
                </tbody>
            </table>
            {!! $mailings->links() !!}
        </div>
    </main>

@endsection

@section('page-js')
    <script src="https://cdn.jsdelivr.net/npm/summernote@0.8.18/dist/summernote-lite.min.js"></script>
    <script src="/dist/js/summernote-image.js"></script>
    <script src="/dist/js/summernote-image-ru.lang.js"></script>
    <script type="text/javascript">
		$(document).ready(function () {
            // $.summernote.dom.emptyPara = "<div></div>"; // js
			$('#summernote').summernote({
                minHeight: 100,
				popover: {
					image: [
						['custom', ['imageAttributes']],
						['imagesize', ['imageSize100', 'imageSize50', 'imageSize25']],
						['float', ['floatLeft', 'floatRight', 'floatNone']],
						['remove', ['removeMedia']]
					],
				},
				lang: 'ru-RU', // Change to your chosen language
				imageAttributes: {
					icon: '<i class="note-icon-pencil"/>',
					removeEmpty: false, // true = remove attributes | false = leave empty if present
					disableUpload: false // true = don't display Upload Options | Display Upload Options
				},
                callbacks: {
                    onImageUpload: function(files) {
                        for (let i = 0; i < files.length; i++) {
                            uploadImageToServer(files[i]);
                        }
                    },
                    onMediaDelete: function(target) {
                        const src = target.attr('src');
                        deleteImageFromServer(src, target);
                    },
                    onPaste: function(e) {
                        const clipboardData = e.originalEvent.clipboardData;
                        if (clipboardData && clipboardData.items) {
                            for (let i = 0; i < clipboardData.items.length; i++) {
                                const item = clipboardData.items[i];
                                if (item.type.indexOf("image") !== -1) {
                                    e.preventDefault();
                                    const file = item.getAsFile();
                                    const reader = new FileReader();
                                    reader.onload = function(evt) {
                                        const imgNode = $('<img>').attr('src', evt.target.result).attr('alt', '');
                                        $('#summernote').summernote('insertNode', imgNode[0]);
                                    };
                                    reader.readAsDataURL(file);
                                }
                            }
                        }
                    }
                }
			});

            function uploadImageToServer(file) {
                const formData = new FormData();
                formData.append('image', file);

                $.ajax({
                    url: '/summernote/upload',
                    method: 'POST',
                    data: formData,
                    processData: false,
                    contentType: false,
                    headers: {
                        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                    },
                    success: function(response) {
                        if (response.success) {
                            const imgNode = $('<img>')
                                .attr('src', response.url)
                                .attr('alt', '')
                                .css('max-width', '100%');
                            $('#summernote').summernote('insertNode', imgNode[0]);
                        } else {
                            console.error('Upload failed:', response.message);
                            alert('Upload failed: ' + response.message);
                        }
                    },
                    error: function(xhr, status, error) {
                        console.error('Upload error:', error);
                        alert('Upload error: ' + error);
                    }
                });
            }

            function deleteImageFromServer(src, target) {
                if (confirm('Are you sure you want to delete this image?')) {
                    $.ajax({
                        url: '/summernote/delete',
                        method: 'DELETE',
                        data: {
                            src: src
                        },
                        headers: {
                            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                        },
                        success: function(response) {
                            if (response.success) {
                                target.remove();
                            } else {
                                console.error('Delete failed:', response.message);
                                alert('Delete failed: ' + response.message);
                            }
                        },
                        error: function(xhr, status, error) {
                            console.error('Delete error:', error);
                            alert('Delete error: ' + error);
                        }
                    });
                }
            }

		});
    </script>
@endsection
