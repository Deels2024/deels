@extends('layouts.admin.app_neon')

@section('title')
    @if(! empty($title))
        {{$title}}
    @endif
    @parent
@endsection

@section('content')

    <?php
        $challenge_id = null;
        if(isset($_GET['challenge']) && $_GET['challenge']) {
            $challenge = \App\Models\Challenge::find($_GET['challenge']);
            if($challenge) {
                $challenge_id = $_GET['challenge'];
            }
        }

        $campaign_id = null;
        if(isset($_GET['campaign']) && $_GET['campaign']) {
            $campaign = \App\Models\Campaign::where('id', $_GET['campaign'])
                ->where('user_id', \Illuminate\Support\Facades\Auth::id())
                ->first();
            if($campaign) {
                $campaign_id = $_GET['campaign'];
            }
        }

        $battle_id = null;
        if(isset($_GET['battle']) && $_GET['battle']) {
            $battle = \App\Models\Battle::find($_GET['battle']);
            if($battle) {
                $battle_id = $_GET['battle'];
            }
        }
        $is_useful = !empty($isUseful) && ($challenge_id || $battle_id);
        $online_report = request()->boolean('online_report') && ($challenge_id || $battle_id);
    ?>
    <div class="account__content new mb-4">
        <form id="startCampaignForm" class="form-horizontal" method="post" action="{{route('stories.store.web')}}"
              enctype="multipart/form-data">
            @csrf
            <input type="hidden" name="challenge_id" value="{{$challenge_id}}">
            <input type="hidden" name="battle_id" value="{{$battle_id}}">
            <input type="hidden" name="is_useful" value="{{$is_useful ? 1 : 0}}">
            <input type="hidden" name="online_report" value="{{$online_report ? 1 : 0}}">
            <input type="hidden" name="campaign_id" value="{{$campaign_id}}">
            <h1 class="account__title account__title-pos">
                {{$online_report
                    ? 'Снять онлайн-сторис'
                    : ($is_useful
                    ? 'Добавить полезное для '.($challenge_id ? 'челленджа: '.$challenge->title : 'батла: '.$battle->title)
                    : ($challenge_id ? 'Участвовать в челлендже: '.$challenge->title : ($campaign_id ? 'Записать сторис для копилки: '.$campaign->title : 'Создать сторис')))}}
            </h1>

            <br>


            <style>
                .account__title {
                    top: 0;
                }
                /*.btn {*/
                /*    margin-top: 0!important;*/
                /*    margin-bottom: 0!important;*/
                /*}*/
                .account__content {
                    margin-top: 0!important;
                }
                .vjs-control {
                    display: none!important;
                }
                video {
                    background: #0e102c;
                    border-radius: 6px;
                }
                .story_buttons {
                    display: flex;
                    align-items: center;
                    justify-content: center;
                    position: absolute;
                    bottom: 40px;
                    left: 0;
                    right: 0;
                }
                .timer {
                    display: flex;
                    align-items: center;
                    justify-content: center;
                    position: absolute;
                    bottom: 20px;
                    left: 0;
                    right: 0;
                }

                .story_buttons svg {
                    width: auto;
                    height: 80px;
                    fill: #ffffff;
                }
                .stop_btn, .play_btn, .record_btn {
                    cursor: pointer;
                }
                .stop_btn svg, .record_btn svg{
                    fill: #FC3939;
                }

                .video_block.player_block {
                    width: 360px;
                    height: 640px;
                    position: relative;
                    background: #0e102c;
                }
                .video_record .video-js {
                    position: absolute;
                    width: 100%;
                    height: 100%;
                    left: 50%;
                    top: 50%;
                    -webkit-transform: translate(-50%, -50%);
                    transform: translate(-50%, -50%);
                }

                @media screen and (max-width: 600px) {
                    .video_record, video{
                        max-width: 100%;
                    }
                    .video_block.player_block {
                        width: 281px;
                        height: 500px;
                        margin: 0 auto;
                        display: block;
                    }
                    .save_story {
                        margin-top: 20px;
                    }
                }

                .top_btns {
                    display: flex;
                    align-items: center;
                    margin: 20px 0;
                }
                .top_btns .btn {
                    margin-left: 20px;
                }
                .top_btns .btn:first-child {
                    margin-left: 0;
                }
                .top_btns .btn_toggle {
                    min-height: 48px;
                    touch-action: manipulation;
                    -webkit-tap-highlight-color: transparent;
                }

                .save_story.is-loading,
                .story_submit.is-loading {
                    cursor: wait;
                    opacity: .75;
                    pointer-events: none;
                }

                @media screen and (max-width: 1024px) {
                    .top_btns {
                        display: none;
                    }
                    .btn.btn_fill {
                        margin-top: 15px!important;
                    }
                }

            </style>

            @if(session('error'))
                {!! session('error') !!}
            @endif
            @if($campaign_id || $online_report)
            @else
                <div class="top_btns">
                    <button type="button" class="btn btn_toggle btn_fill" data-target="video_upload" aria-pressed="true">Загрузить</button>
                    <button type="button" class="btn btn_toggle" data-target="video_record" aria-pressed="false">Записать</button>
                </div>
            @endif

            @if($challenge_id && !$is_useful)
                @if($challenge->cost && $challenge->cost > 0)
                    <div class="min_participants" style="margin-bottom: 20px;max-width: 340px;"><span>Стоимость участия:</span> <div class="btn_pill">{{number_format($challenge->cost, 0)}} <img class="coin" src="/dist/images/deels_coin_large.png" srcset="/dist/images/deels_coin_large.png" alt="DEELS" style="width: 12px; height: auto; margin-left: 2px;"></div></div>
                @endif
            @endif


            <div class="video_block video_record player_block"  style="display: {{$campaign_id || $online_report ? 'block' : 'none'}}">
                <video id="myVideo"  class="video-js vjs-default-skin" style="width: 100%" loop></video>
               <div class="story_controls" style="display: none;">

               </div>
                <div class="story_buttons">
                    <div class="record_btn"><svg xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" version="1.1" id="mdi-record-circle-outline" width="24" height="24" viewBox="0 0 24 24"><path d="M12,2A10,10 0 0,0 2,12A10,10 0 0,0 12,22A10,10 0 0,0 22,12A10,10 0 0,0 12,2M12,4A8,8 0 0,1 20,12A8,8 0 0,1 12,20A8,8 0 0,1 4,12A8,8 0 0,1 12,4M12,9A3,3 0 0,0 9,12A3,3 0 0,0 12,15A3,3 0 0,0 15,12A3,3 0 0,0 12,9Z" /></svg></div>
                    <div class="stop_btn" style="display: none"><svg xmlns="http://www.w3.org/2000/svg" enable-background="new 0 0 24 24" height="24" viewBox="0 0 24 24" width="24"><g><rect fill="none" height="24" width="24"/></g><g><g><rect height="8" width="8" x="8" y="8"/><path d="M12,2C6.48,2,2,6.48,2,12s4.48,10,10,10s10-4.48,10-10S17.52,2,12,2z M12,20c-4.41,0-8-3.59-8-8c0-4.41,3.59-8,8-8 s8,3.59,8,8C20,16.41,16.41,20,12,20z"/></g></g></svg></div>
                    <div class="play_btn" style="display: none"><svg xmlns="http://www.w3.org/2000/svg" height="24" viewBox="0 0 24 24" width="24"><path d="M0 0h24v24H0V0z" fill="none"/><path d="M10 16.5l6-4.5-6-4.5zM12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm0 18c-4.41 0-8-3.59-8-8s3.59-8 8-8 8 3.59 8 8-3.59 8-8 8z"/></svg></div>
                </div>
                <div class="timer" id="timer" style="display: none">00:00:00</div>
            </div>

            @if(!$campaign_id && !$online_report)
            <div class="video_block video_upload">
                <div class="new__img form-field">
                    <div class="new__label new__label--large">Медиа-контент</div>
                    <div class="new__img-info">
                        <div class="new__load"  style="margin-top: 0">Загрузить медиа</div>
                    </div>
                    <input
                            id="mainImg"
                            class="filePreviewUploadMain required_input"
                            type="file"
                            name="mainImg"
                            accept="image/jpeg,image/jpg,image/png,image/heif,image/heic,.jpeg,.jpg,.png,.heif,.heic,video/mp4,video/x-m4v,video/mov,video/avi,video/mpeg,video/*"
                    >
                    <small></small>
                    <div class="new__img-main previewContainerMain" style="max-width: 320px;"></div>
                </div>
            </div>

            <div class="video_block video_upload">
                <div class="new__img form-field">
                    <div class="new__label new__label--large">Обложка для сторис</div>
                    <div class="new__img-info">
                        <div class="new__load"  style="margin-top: 0">Загрузить файл</div>
                    </div>
                    <input
                            class="filePreviewUploadCover"
                            type="file"
                            name="cover"
                    >
                    <small></small>
                    <div class="new__img-main previewContainerCover" style="max-width: 320px;"></div>
                </div>
            </div>
            @endif


            <input type="hidden" name="video">
            <input type="hidden" name="user_id" value="{{Auth::user()->id ?? null}}">


            @if(!$challenge_id)

                @if(env('ORD_ENABLE'))
                <div class="flex flex-column align_start mt-6 mb-6">
                    <div class="new__video form-field flex" style="justify-content: start">
                        <input id="is_ad"
                               class="new__input"
                               value="1" name="is_ad"
                               type="checkbox"
                                {{ old('is_ad') ? 'checked' : '' }}
                        >
                        <small></small>
                        <label for="is_ad" class="new__label new__label--large ml-4" style="margin-bottom: 0">Это реклама</label>
                    </div>

                    <div class="w-100 mt-6 ads_data" style="display: none">

                        <div class="new__purpose form-field w-100">
                            <label for="ads_data_1" class="new__label new__label--large">Рекламодатель</label>
                            <input id="ads_data_1" class="new__input required_input" name="ads_data[advertiser]"
                                   value="{{ old('ads_data.advertiser', '') }}"
                                   type="text"
                                   placeholder="Укажите название или ФИО,ИНН"
                            >
                            <small></small>
                        </div>

                        <div class="new__purpose form-field w-100 mt-4 erid_input">
                            <label for="ads_data_2" class="new__label new__label--large">Ерид айди</label>
                            <input id="ads_data_2" class="new__input required_input" name="ads_data[erid]"
                                   value="{{ old('ads_data.erid', '') }}"
                                   type="text"
                                   placeholder="Укажите ерид"
                            >
                            <small></small>
                        </div>
                        <div class="new__purpose form-field w-100 mt-4 ">
                            <div class="new__video form-field flex mb-6" style="justify-content: start">
                                <input id="get_erid"
                                       class="new__input"
                                       value="1" name="get_erid"
                                       type="checkbox"
                                        {{ old('get_erid') ? 'checked' : '' }}
                                >
                                <small></small>
                                <label for="get_erid" class="new__label new__label--large ml-4" style="margin-bottom: 0">Получить ЕРИД автоматически</label>
                            </div>
                        </div>

                        <div class="new__purpose form-field w-100 mt-4 ">
                            <label for="ads_data_3" class="new__label new__label--large">Доп. ссылка если есть</label>
                            <input id="ads_data_3" class="new__input required_input" name="ads_data[additional_link]"
                                   value="{{ old('ads_data.additional_link', '') }}"
                                   type="text"
                                   placeholder=""
                            >
                            <small></small>
                        </div>
                    </div>
                </div>
                @endif

                <div class="new__description form-field scenario" style="display: none">
                    <div class="box">
                        <div class="box__content">
                            <h3>Сценарий сторис</h3>
                            <p class="scenario_content"></p>
                        </div>
                    </div>
                </div>
                <div class="new__description form-field">
                    <label for="description" class="new__label new__label--large">Описание сторис</label>
                    <textarea minlength="0"
                              class="new__input stories_description"
                              name="description"
                              id="description"
                              rows="8"
                              placeholder="Введите текст...">{{old('description')}}</textarea>
                    <small></small>
                </div>

                <div class="new__description form-field">
                    <button class="use_generation btn btn-small">Воспользоваться помощью ИИ</button>
                </div>

                <div class="new__description form-field use_generation_block" style="display: none">
                    <div class="mb-4">
                        <div class="form-field mb-4">
                            <label for="name" class="new__label new__label--large">О чем будет ваша сторис?</label>
                            <input id="name"
                                   class="new__input required_input description_field"
                                   name="description_field"
                                   type="text"
                                   placeholder="Например: Я хочу осуществить путешествие на Северный полюс"
                                   value="{{old('title')}}">
                            <small></small>
                        </div>
                        @if(isset(Auth::user()->limits['chatgpt']['tries']) && Auth::user()->limits['chatgpt']['tries'] <= 0)
                            <div class="copystories_generate btn btn-small" type="button">Воспользоваться помощью ИИ
                                <span class="ai_helper_cost">
                                 {{env('AI_STORAGE_COST', 50)}} <img src="/dist/img/deels_cur.svg" class="small_coin">
                            </span>
                            </div>
                        @else
                            <div class="copystories_generate btn btn-small" type="button">Воспользоваться помощью ИИ</div>
                        @endif
                        <button class="btn btn-small btn-grey use_generation_cancel ml-2" type="button">Отмена</button>
                    </div>

                </div>


                <div class="flex align_start mt-6">
                    <div class="new__video form-field flex" style="justify-content: start">
                        <input id="paid"
                               class="new__input"
                               value="1" name="paid"
                               type="checkbox"
                        >
                        <small></small>
                        <label for="paid" class="new__label new__label--large ml-4" style="margin-bottom: 0">Платный просмотр</label>
                    </div>
                    <div class="new__purpose form-field">
                        <label for="amount" class="new__label new__label--large">Стоимость просмотра</label>
                        <input id="amount" class="new__input required_input" name="amount"
                               value="{{old('amount')}}"
                               max="999999999"
                               type="number"
                               placeholder="Укажите сумму в дилсах "
                        >
                        <small></small>
                    </div>

                </div>

            @endif
            @if($challenge_id && !$is_useful && $challenge->frozen)
                Челлендж заморожен до {{\Carbon\Carbon::parse($challenge->frozen_at)->addHours(48)->format('d.m.Y H:i')}}
            @endif
            @if($challenge_id && !$is_useful && $challenge->frozen)
            @else
                <button class="btn btn_fill video_block save_story video_record mt-4" style="display: {{$campaign_id || $online_report ? 'flex' : 'none'}}">{{$online_report ? 'Опубликовать' : ($is_useful ? 'Добавить' : ($challenge_id && !$challenge->frozen ? 'Участвовать' : 'Создать'))}}</button>
                @if(!$campaign_id && !$online_report)
                <button type="submit" class="btn btn_fill video_block video_upload story_submit" style="display: flex; align-items: center">

                    @if($is_useful)
                        Добавить
                    @elseif($challenge_id)
                        @if($challenge->cost && $challenge->cost > 0)
                            Опубликовать за {{number_format($challenge->cost, 0)}} <img class="coin" src="/dist/images/deels_coin_large.png" srcset="/dist/images/deels_coin_large.png" alt="DEELS" style="width: 20px; height: auto; margin-left: 5px;">
                        @else
                            Опубликовать
                        @endif
                    @else
                        {{$challenge_id ? 'Участвовать' : 'Создать'}}
                    @endif
                </button>
                @endif
            @endif
        </form>
    </div>

@endsection

@section('page-js')
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

        document.addEventListener('DOMContentLoaded', function() {
            const isAdCheckbox = document.getElementById('is_ad');
            const getEridCheckbox = document.getElementById('get_erid');
            const adsDataBlock = document.querySelector('.ads_data');
            const eridBlock = document.querySelector('.erid_input');
            if (!isAdCheckbox || !getEridCheckbox || !adsDataBlock || !eridBlock) {
                return;
            }
            const adsDataInputs = adsDataBlock.querySelectorAll('input[type="text"]');

            // Function to toggle visibility and clear inputs
            function toggleAdsData() {
                if (isAdCheckbox.checked) {
                    adsDataBlock.style.display = 'block';
                } else {
                    adsDataBlock.style.display = 'none';
                    // Clear all input values
                    adsDataInputs.forEach(input => {
                        input.value = '';
                    });
                }
            }
            function toggleErid() {
                if (getEridCheckbox.checked) {
                    eridBlock.style.display = 'none';
                } else {
                    eridBlock.style.display = 'block';
                }
            }


            // Initial state on page load
            toggleAdsData();
            toggleErid();

            // Add event listener for checkbox change
            isAdCheckbox.addEventListener('change', toggleAdsData);
            getEridCheckbox.addEventListener('change', toggleErid);
        });

        $('.filePreviewUploadMain').change(function () {
            readUrl(this, '.previewContainerMain', true);
        });

        $('.filePreviewUpload').change(function () {
            readUrl(this, '.previewContainer');
        });
        $('.filePreviewUploadCover').change(function () {
            readUrl(this, '.previewContainerCover');
        });


        var storyMaxUploadMb = {{ (int) config('media.stories.max_upload_mb') }};
        var storyMaxUploadBytes = storyMaxUploadMb * 1024 * 1024;
        var storyImageMaxUploadMb = {{ (int) config('media.stories.image_max_upload_mb') }};
        var storyImageMaxUploadBytes = storyImageMaxUploadMb * 1024 * 1024;
        var storyAllowedImageExtensions = @json(config('media.stories.image_allowed_extensions'));
        var storyUnsupportedImageExtensions = @json(config('media.stories.image_unsupported_extensions'));
        var storyImageFormatError = 'Этот формат не поддерживается. Пожалуйста, загрузите фото в формате JPEG, JPG, PNG, HEIF или HEIC';
        var storyVideoDurationError = 'Длительность видео превышает 60 сек. Пожалуйста, загрузите более короткое видео для сторис';

        function showStoryUploadError(message) {
            $('.alert-container').html('<div class="alert danger"> <span class="closebtn">&times;</span>' + message + '</div>');
        }

        function resetStoryMainFile(input) {
            input.value = '';
            $('.previewContainerMain').html('');
        }

        function readUrl(input, container, video) {
            if (input.files.length > 3) {
                alert('Вы можете выбрать не более 3х изображений');
                return false;
            }
            if (input.files && input.files[0]) {

                var filesAmount = input.files.length;

                for (let i = 0; i < filesAmount; i++) {
                    let fileItem = input.files[i];
                    let extension = (fileItem.name.split('.').pop() || '').toLowerCase();
                    let isAllowedStoryImage = storyAllowedImageExtensions.indexOf(extension) !== -1 || fileItem.type.indexOf('image/') === 0;

                    if (isAllowedStoryImage && fileItem.size > storyImageMaxUploadBytes) {
                        showStoryUploadError('Вес файла превышает ' + storyImageMaxUploadMb + ' Мб. Пожалуйста, загрузите файл до  ' + storyImageMaxUploadMb + ' Мб');
                        resetStoryMainFile(input);
                        return false;
                    }

                    if (!isAllowedStoryImage && fileItem.size > storyMaxUploadBytes) {
                        showStoryUploadError('Вес файла превышает ' + storyMaxUploadMb + ' Мб. Пожалуйста, загрузите файл до  ' + storyMaxUploadMb + ' Мб');
                        resetStoryMainFile(input);
                        return false;
                    }

                    if (storyUnsupportedImageExtensions.indexOf(extension) !== -1) {
                        showStoryUploadError(storyImageFormatError);
                        resetStoryMainFile(input);
                        return false;
                    }

                    if (
                        fileItem.type.indexOf('image/') === 0 &&
                        storyAllowedImageExtensions.indexOf(extension) === -1
                    ) {
                        showStoryUploadError(storyImageFormatError);
                        resetStoryMainFile(input);
                        return false;
                    }

                    if (
                        video != undefined &&
                        fileItem.type.indexOf('video/') !== 0 &&
                        storyAllowedImageExtensions.indexOf(extension) === -1
                    ) {
                        showStoryUploadError(storyImageFormatError);
                        resetStoryMainFile(input);
                        return false;
                    }

                    let reader = new FileReader();
                reader.onload = (e) => {
                        var match = e.target.result.match(/^data:([^/]+)\/([^;]+);/) || [];
                        var type = match[1];
                        // console.log(type);
                        var format = match[2];
                        let html = '<img src="' + e.target.result + '">';

                        if(type == 'video') {
                            var video_data = e.target.result;
                            video_data = video_data.replace("/quicktime", "/mp4");
                            html = '<video controls class="video story-preview-video" src="'+video_data+'" style="width: 100%; max-width:203px;max-height:360px" type="video/mp4" loop playsinline>Ваш браузер не поддерживает HTML5 видео.</video>'
                        }
                        $(container).html(html);
                        var previewVideo = $(container).find('.story-preview-video')[0];
                        if (previewVideo) {
                            previewVideo.onloadedmetadata = function () {
                                if (previewVideo.duration > 60) {
                                    showStoryUploadError(storyVideoDurationError);
                                    resetStoryMainFile(input);
                                }
                            };
                        }

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
        var storyIsRecording = false;
        var playbackTimerFrame = null;
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


        if ($(window).width() < 960) {
            options = {
                controls: false,
                bigPlayButton: false,
                autoMuteDevice: true,
                // loop: false,
                width: 320,
                height: 240,
                // fluid: true,
                plugins: {
                    record: {
                        maxLength: 240,
                        debug: true,
                        audio: true,
                        video: true,
                    }
                }
            };
        }


        var clearTime;
        var seconds = 0,
            minutes = 0,
            hours = 0;
        var secs, mins, gethours;

        function startWatch() {
            if (seconds === 60) {
                seconds = 0;
                minutes = minutes + 1;
            }
            mins = minutes < 10 ? "0" + minutes + ": " : minutes + ": ";
            if (minutes === 60) {
                minutes = 0;
                hours = hours + 1;
            }
            gethours = hours < 10 ? "0" + hours + ": " : hours + ": ";
            secs = seconds < 10 ? "0" + seconds : seconds;

            var x = document.getElementById("timer");
            x.innerHTML = gethours + mins + secs;
            seconds++;

            clearTime = setTimeout("startWatch( )", 1000);
        }
        function startTime() {
            if (seconds === 0 && minutes === 0 && hours === 0) {
                startWatch();
            }
        }

        function pauseTime() {
            if (seconds !== 0 || minutes !== 0 || hours !== 0) {
                var x = document.getElementById("timer");
                var stopTime = gethours + mins + secs;
                x.innerHTML = stopTime;
                clearTimeout(clearTime);
            }
        }
        function stopTime() {
            if (seconds !== 0 || minutes !== 0 || hours !== 0) {
                seconds = 0;
                minutes = 0;
                hours = 0;
                secs = "0" + seconds;
                mins = "0" + minutes + ": ";
                gethours = "0" + hours + ": ";
                var x = document.getElementById("timer");
                var stopTime = gethours + mins + secs;
                x.innerHTML = stopTime;
                clearTimeout(clearTime);
            }
        }

        function formatStoryTime(value) {
            var totalSeconds = Math.max(0, Math.floor(Number(value) || 0));
            var playbackHours = Math.floor(totalSeconds / 3600);
            var playbackMinutes = Math.floor((totalSeconds % 3600) / 60);
            var playbackSeconds = totalSeconds % 60;

            return [playbackHours, playbackMinutes, playbackSeconds]
                .map(function (part) { return String(part).padStart(2, '0'); })
                .join(':');
        }

        function showPlaybackTime() {
            var videoElement = player.el().querySelector('video');
            var currentTime = videoElement ? videoElement.currentTime : player.currentTime();
            document.getElementById('timer').textContent = formatStoryTime(currentTime);
        }

        function startPlaybackTimer() {
            cancelAnimationFrame(playbackTimerFrame);

            function updatePlaybackTimer() {
                showPlaybackTime();
                var videoElement = player.el().querySelector('video');
                if (videoElement && !videoElement.paused) {
                    playbackTimerFrame = requestAnimationFrame(updatePlaybackTimer);
                }
            }

            updatePlaybackTimer();
        }

        function stopPlaybackTimer() {
            cancelAnimationFrame(playbackTimerFrame);
            playbackTimerFrame = null;
            showPlaybackTime();
        }

        applyVideoWorkaround();

        var player = videojs('myVideo', options, function() {
            // print version information at startup
            var msg = 'Using video.js ' + videojs.VERSION +
                ' with videojs-record ' + videojs.getPluginVersion('record') +
                ' and recordrtc ' + RecordRTC.version;
            videojs.log(msg);
        });

        // error handling
        player.on('deviceError', function() {
            console.log('device error:', player.deviceErrorCode);
        });

        player.on('error', function(element, error) {
            console.error(error);
        });

        // user clicked the record button and started recording
        player.on('startRecord', function() {
            console.log('started recording!');
        });

        // user completed recording and stream is available
        player.on('finishRecord', function() {
            // the blob object contains the recorded data that
            // can be downloaded by the user, stored on server etc.
            console.log('finished recording: ', player.recordedData);

            if ($(window).width() < 960) {
                blob = new Blob([player.recordedData.video], { type: 'video/webm' });
            } else {
                blob = player.recordedData;
            }

            console.log(blob.size);
            storyIsRecording = false;
            pauseTime();
            player.currentTime(0);
            showPlaybackTime();
        });

        player.on('timeupdate', function () {
            if (!storyIsRecording) {
                showPlaybackTime();
            }
        });
        player.on('play', startPlaybackTimer);
        player.on('pause', stopPlaybackTimer);
        player.on('ended', stopPlaybackTimer);

        $('.record_btn').on('click', function (e) {
            stopTime();
            player.record().getDevice();
        });
        $('.stop_btn').on('click', function (e) {
            if (storyIsRecording) {
                player.record().stop();
                player.record().stopDevice();
            }
            $('.stop_btn').hide();
            $('.play_btn').show();
            $('.record_btn').show();
            $('video').trigger('pause');
            setTimeout(function(){
                $('video').get(0).pause();
                $('video').get(0).currentTime = 0;
            }, 1);

            pauseTime();

        });
         $('.play_btn').on('click', function (e) {
             $('.stop_btn').show();
             $('.record_btn').hide();
             $('.play_btn').hide();
             $('video').trigger('play');
        });


        player.on('deviceReady', function() {
            storyIsRecording = true;
            player.record().start();
            $('.record_btn').hide();
            $('.play_btn').hide();
            $('.stop_btn').show();
            $('.timer').show();
            startTime();
        });
        $('.btn_toggle').on('click', function (e) {
            e.preventDefault();
            var target = $(this).attr('data-target');
            $('.btn_toggle').removeClass('btn_fill').attr('aria-pressed', 'false');
            $('.video_block').hide();
            $(this).addClass('btn_fill').attr('aria-pressed', 'true');
            $('.'+target).show();
        });

        $('#startCampaignForm').on('submit', function () {
            var submitButton = $(this).find('.story_submit:visible');
            if (!submitButton.length || submitButton.prop('disabled')) {
                return;
            }

            submitButton
                .data('original-label', submitButton.html())
                .addClass('is-loading')
                .prop('disabled', true)
                .text('Отправляем...');
        });

        $('.save_story').on('click', function (e) {
            e.preventDefault();

            var saveButton = $(this);
            if (saveButton.hasClass('is-loading')) {
                return;
            }

            if (!blob) {
                showStoryUploadError('Сначала запишите видео');
                return;
            }

            var formData = new FormData();

            var description = $('#description').val()
            var challenge_id = $('[name="challenge_id"]').val()
            var campaign_id = $('[name="campaign_id"]').val()
            var paid = 0;

            if ($('[name="paid"]').is(':checked')) {
                paid = 1;
            }
            var amountField = $('[name="amount"]');
            var amount = amountField.length && amountField.val() !== '' ? amountField.val() : 0;

            formData.append('video', blob);
            formData.append('description', description);
            formData.append('challenge_id', challenge_id);
            formData.append('battle_id', $('[name="battle_id"]').val());
            formData.append('is_useful', $('[name="is_useful"]').val());
            formData.append('online_report', $('[name="online_report"]').val());
            formData.append('campaign_id', campaign_id);
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
                beforeSend: function () {
                    saveButton
                        .data('original-label', saveButton.html())
                        .addClass('is-loading')
                        .prop('disabled', true)
                        .text('Отправляем...');
                },
                success: function (response) {
                   if(response.success) {
                       window.location.replace(@json($online_report || $is_useful
                           ? ($challenge_id ? route('deels.public.challenges.show', ['id' => $challenge_id]) : route('deels.public.battles.show', ['id' => $battle_id]))
                           : route('user_stories')));
                   } else {
                       $('.alert-container').html('<div class="alert danger"> <span class="closebtn">&times;</span> '+(response.error || 'Не удалось создать сторис')+'</div>')
                       saveButton.removeClass('is-loading').prop('disabled', false).html(saveButton.data('original-label'));
                   }
                },
                error: function (xhr) {
                    var message = xhr.responseJSON && xhr.responseJSON.error
                        ? xhr.responseJSON.error
                        : 'Не удалось создать сторис';
                    $('.alert-container').html('<div class="alert danger"> <span class="closebtn">&times;</span> '+message+'</div>')
                    saveButton.removeClass('is-loading').prop('disabled', false).html(saveButton.data('original-label'));
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
