@extends('layouts.admin.app_neon')

@section('title')
    @if(! empty($title))
        {{$title}}
    @endif  @parent
@endsection

@section('content')


    <div class="account__content">
        <div class="account-main">
            <div class="account-main__head flex-column ai-start">
                @if(isset($battle))
                    <h1 class="account-main__title">Редактировать DEELS-батл</h1>
                @else
                    <h1 class="account-main__title">Создать новый DEELS-батл</h1>
                    <p>Заполните информацию ниже для создания </p>
                @endif
            </div>
            <div class="account-info">
                <section class="challenge pb-8">
                                    @if ($errors->any())
                                        <div style="margin-bottom: 30px">
                                            <ul>
                                                @foreach ($errors->all() as $error)
                                                    <li style="color: red;margin-bottom: 10px">{{$error}}</li>
                                                @endforeach
                                            </ul>

                                        </div>
                                    @endif
                    <form id="startCampaignForm" class="form-horizontal challenge-form" method="post" action="{{isset($battle) ? route('battles.update.web') : route('battles.store.web')}}"
                          enctype="multipart/form-data"> @csrf
                        @if(isset($battle))
                            <input type="hidden" name="challenge_id" value="{{$battle->id}}">
                        @endif
                    <div style="max-width: 380px; width: 100%;">
                            <label>
                                <p>Название батла</p>
                                <input type="text" placeholder="Название" name="title" value="{{old('title') ??  $battle->title ?? ''}}">
                            </label>
                            <label>
                                <p>Опишите что нужно будет сделать участникам</p>
                                <textarea rows="4" placeholder="Введите текст..."  name="description">{{old('description') ??  $battle->description ?? ''}}</textarea>
                            </label>
                            @if(isset($battle))
                                <div class="challenge-info__media">
                                    <a href="#" class="challenge-media challenge-media--video" data-route="{{route('battles.preview', ['id' => $battle->id, 'user_id' => Auth::user()->id ?? null])}}">
                                        @if($battle->type == 'video' && $battle->video_preview)
                                            <video src="{{$battle->video_preview}}" poster="{{$battle->thumbnail}}" muted loop autoplay playsinline></video>
                                        @else
                                            <img src="{{$battle->thumbnail ?: $battle->path}}" alt="{{$battle->title}}">
                                        @endif
                                    </a>
                                </div>
                            @endif
                            <p class="mb-3">{{isset($battle) ? 'Заменить' : 'Добавить'}} обложку батла (видео или фото)</p>
                            <label>
                                <input
                                        class="filePreviewUploadMain"
                                        type="file"
                                        name="mainImg"
                                >
                                <span class="form-file-area mb-8">
                                    <svg viewBox='0 0 300 100' preserveAspectRatio='none'>
                                        <path d='M0,0 300,0 300,100 0,100z' vector-effect='non-scaling-stroke'/>
                                    </svg>
                                </span>
                            </label>
                            <div class="d-flex jc-center mb-8">
                                <div class="d-flex flex-column gap-3" style="max-width: 130px; width: 100%;">
                                    <div class="new__img-main previewContainerMain" style="max-width: 130px; display: none">

                                    </div>
                                    <a href="#" class="preview_replace" style="border: 1px solid;font-size: 12px;text-align: center;
													border-radius: 5px;padding: 0.6em 1em;display: none">Заменить</a>

                                </div>
                            </div>

                            <label>
                                <p>Минимальное кол-во участников</p>
                                <input type="number" name="min_participants" placeholder="Кол-во участников" min="1" value="{{old('min_participants') ??  $battle->min_participants ?? 3}}">
                                <small class="form-hint">батл начнется только после того, как это количество участников будет набрано</small>
                            </label>

                            <label>
                                <p>Стоимость участия в дилсах</p>
                                <input type="number" name="cost" placeholder="Стоимость участия" value="{{old('cost') ??  $battle->cost ?? ''}}">
                                <small class="form-hint">Каждый участник будет должен оплатить эту сумму, чтобы принять участие</small>
                            </label>

                            <label>
                                <p>Продолжительность (дней)</p>
                                <input type="number" name="days" placeholder="Укажите количество дней" min="1" value="{{old('days') ??  $battle->days ?? 1}}">
                                <small class="form-hint">Сколько дней будет проводиться батл после начала</small>
                            </label>

                            <div class="mb-8">
                                <p class="mb-6">Критерии выбора победителя:</p>
{{--                                <label class="d-flex ai-center gap-3 mb-5">--}}
{{--                                    <input type="checkbox" name="criteria[]" value="by_views" {{(old('criteria') && in_array('by_views', old('criteria'))) || (isset($battle) && $battle->by_views) ? 'checked' : ''}}>--}}
{{--                                    <span>Лидер просмотров</span>--}}
{{--                                </label>--}}
                                <label class="d-flex ai-center gap-3 mb-5">
                                    <input type="checkbox" name="criteria[]" value="by_likes" {{(old('criteria') && in_array('by_likes', old('criteria'))) || (isset($battle) && $battle->by_likes) ? 'checked' : 'checked'}}>
                                    <span>Лидер лайков</span>
                                </label>
{{--                                <label class="d-flex ai-center gap-3 mb-5">--}}
{{--                                    <input type="checkbox" name="criteria[]" value="by_comments" {{(old('criteria') && in_array('by_comments', old('criteria'))) || (isset($battle) && $battle->by_comments) ? 'checked' : ''}}>--}}
{{--                                    <span>Лидер комментариев</span>--}}
{{--                                </label>--}}
                            </div>
                            <label>
                                <p class="mb-3">{{isset($battle) ? 'Сумма выигрыша в дилсах' : 'Сумма выигрыша в дилсах (минимум 5 000)'}}</p>
                                <input type="tel" inputmode="numeric" placeholder="Введите сумму" name="amount" max="9999999" value="{{old('amount') ?? $battle->amount ?? 5000}}" data-coin {{isset($battle) ? 'disable readonly' : ''}}>
                                @if(isset($battle))
                                    <p class="mb-5" style="margin-top: -25px; font-size: 14px; opacity: .5">Вы не можете изменить сумму</p>
                                @endif
                            </label>
                        </div>
                        @if(!isset($battle) ||( !$battle->finished  && !$battle->declined))
                            <div class="d-flex ai-center gap-3 flex-wrap">
                                <button class="challenge-btn challenge-btn--fill" type="submit">{{isset($battle) ? 'Изменить' : 'Создать'}}</button>
                            </div>
                        @endif
                    </form>
                </section>
            </div>
        </div>
    </div>
    @include('challenges.modal')
    @include('stories.modal')

@endsection

@section('page-js')

    <script>
        $('.challenge-media--video:not(.show_story)').on('click', function (e) {
            e.preventDefault();
            var route = $(this).attr('data-route');

            $.ajax({
                type: 'GET',
                url: route,
                success: function (data) {
                    if(data.success) {
                        showChallenge(data.data);
                    } else {
                        $('.alert-container').html('<div class="alert danger"> <span class="closebtn">&times;</span> '+data.error+'</div>')
                    }
                }
            });
        });
    </script>

    <script src="{{ext_asset('/dist/js/validations.js')}}"></script>
    <script src="//cdnjs.cloudflare.com/ajax/libs/moment.js/2.9.0/moment-with-locales.js"></script>
    <script src="{{asset('assets/plugins/bootstrap-datepicker/js/bootstrap-datetimepicker.min.js')}}"></script>

    <script src="https://stackpath.bootstrapcdn.com/bootstrap/3.4.1/js/bootstrap.min.js"></script>

    <!-- include summernote css/js -->
    <link href="https://cdn.jsdelivr.net/npm/summernote@0.8.18/dist/summernote.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/summernote@0.8.18/dist/summernote.min.js"></script>
    <script src="{{asset('assets/js/summernoteLang.js')}}"></script>

    <script src="https://unpkg.com/@popperjs/core@2"></script>
    <script src="https://unpkg.com/tippy.js@6"></script>

    <script>


        $('.filePreviewUploadMain').change(function () {
            readUrl(this, '.previewContainerMain');
            $('.previewContainerMain').show();
            $('.preview_replace').show();
        });


        function readUrl(input, container) {
            if (input.files.length > 3) {
                alert('Вы можете выбрать не более 3х изображений');
                return false;
            }
            if (input.files && input.files[0]) {
                var filesAmount = input.files.length;

                for (let i = 0; i < filesAmount; i++) {
                    let reader = new FileReader();
                reader.onload = (e) => {
                        var match = e.target.result.match(/^data:([^/]+)\/([^;]+);/) || [];
                        var type = match[1];
                        console.log(type);
                        var format = match[2];
                        let html = '<img src="' + e.target.result + '">'
                        if(type == 'video') {
                            var video_data = e.target.result;
                            video_data = video_data.replace("/quicktime", "/mp4");
                            html = '<video controls class="video" src="'+video_data+'#t=0.001" style="width: 100%; max-width:203px;max-height:360px" type="video/mp4" loop playsinline>Ваш браузер не поддерживает HTML5 видео.</video>'
                        }
                        $(container).html(html);
                    }

                    reader.readAsDataURL(input.files[i]);
                }
            }
        }

        function removePreview(key) {
            $('.filePreviewUpload')[0].files.splice(key, 1);
        }
    </script>


    <script>

    </script>
@endsection

@push('after_scripts')
    <link href="/plugins/video.js/dist/video-js.min.css" rel="stylesheet">
    <link href="/plugins/videojs.record.css" rel="stylesheet">
    <link href="/plugins/examples.css" rel="stylesheet">

{{--    <script src="/plugins/video.js/dist/video.min.js"></script>--}}
    <script src="https://vjs.zencdn.net/8.6.1/video.min.js"></script>
    <script src="/plugins/recordrtc/RecordRTC.js"></script>
    <script src="/plugins/webrtc-adapter/out/adapter.js"></script>

    <script src="/plugins/videojs.record.js?v=1"></script>
    <script src="/plugins/browser-workarounds.js"></script>
    <script>
        var blob;
        var options = {
            controls: false,
            bigPlayButton: false,
            autoMuteDevice: true,
            width: 1280,
            height: 720,
            fluid: true,
            plugins: {
                record: {
                    audio: true,
                    maxLength: 240,
                    debug: true,
                    video: {
                        aspectRatio: {ideal: 9 / 16, min: 9 / 16, max: 9 / 16},
                        facingMode: 'user',
                        // width: { min: 360, ideal: 720, max: 1080 },
                        height: { min: 640, ideal: 1280, max: 1920 }
                    },
                    // dimensions of captured video frames
                    // frameWidth: 720,
                    // frameHeight: 1280,
                    // videoMimeType: 'video/webm;codecs=H264',
                    displayMilliseconds: true,
                }
            }
        };



        $('.preview_replace').on('click', function (e) {
            e.preventDefault();
            $('.filePreviewUploadMain').trigger('click');
        });

        $('.save_story').on('click', function (e) {
            e.preventDefault();

            var formData = new FormData();

            var description = $('#description').val()
            var paid = 0;

            if ($('[name="paid"]').is(':checked')) {
                paid = 1;
            }
            var amount = $('[name="amount"]').val();

            formData.append('video', blob);
            formData.append('description', description);
            formData.append('paid', paid);
            formData.append('amount', amount);
            formData.append('blob', 1);
            formData.append('user_id', {{\Illuminate\Support\Facades\Auth::user()->id ?? null}});
            $.ajax({
                url: '{{route('stories.store.web')}}',
                method: 'POST',
                data: formData,
                processData: false,
                contentType: false,
                success: function (response) {
                   if(response.success) {
                       window.location.replace("{{route('user_stories')}}");
                   } else {
                       $('.alert-container').html('<div class="alert danger"> <span class="closebtn">&times;</span> '+response.error+'</div>')
                   }
                },
                error: function () {
                }
            });
        });

        $(document).on('click', '.use_generation', function(e) {
            e.preventDefault();
            $('.use_generation').hide();
            $('.use_generation_block').show();
        });

         $(document).on('click', '.use_generation_cancel', function(e) {
             e.preventDefault();
            $('.use_generation').show();
            $('.use_generation_block').hide();
        });



        $(document).on('click', '.copystories_generate', function(e) {

            var generate_button =  $(this);
            var generate_button_text =  $(this).text();
            $('.alert').remove();
            var description = $('.description_field').val();

            if(!description) {
                $('.alert-container').html('<div class="alert danger"> <span class="closebtn">&times;</span>Укажите о чем ваша сторис!</div>')
                return false;
            } else {

            }

            $.ajax({
                url: '{{route('services.copystories')}}',
                type: 'POST',
                data: {description : description, user_id: '{{Auth::user()->id}}', _token : '{{csrf_token()}}'},
                beforeSend: function() {
                    generate_button.prop('disabled', true);
                    generate_button.addClass('disabled');
                    generate_button.text('Выполняем генерацию...');
                    $('.use_generation_cancel').hide();
                },
                complete: function() {
                    generate_button.prop('disabled', false);
                    generate_button.removeClass('disabled');
                    generate_button.text(generate_button_text);
                    $('.use_generation_cancel').show();
                },
                success: function(data) {
                    console.log(data);


                    if(data.success) {
                        $('.alert-container').html('<div class="alert success"> <span class="closebtn">&times;</span>Успешно!</div>');
                        if(data.scenario) {
                            $('.scenario').show();
                            $('.scenario_content').text(data.scenario)
                        }
                        if(data.description) {
                            $('.stories_description').val(data.description);
                        }
                        $('.use_generation').show();
                        $('.use_generation_block').hide();
                        $('.description_field').val('');
                        $('.use_generation_cancel').show();
                    } else {
                        $('.alert-container').html('<div class="alert danger"> <span class="closebtn">&times;</span>'+data.error+'</div>')
                    }

                },
                error: function(xhr, ajaxOptions, thrownError) {
                    generate_button.prop('disabled', false);
                    generate_button.text(generate_button_text);
                    $('.use_generation_cancel').show();
                    console.log(xhr);
                    console.log(ajaxOptions);
                    console.log(thrownError);
                    $('.alert-container').html('<div class="alert danger"> <span class="closebtn">&times;</span>Произошла ошибка. Попробуйте выполнить запрос еще раз.</div>')
                }
            });
        });
    </script>
@endpush
