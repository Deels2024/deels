@extends('layouts.admin.app_neon')

@section('title') @if(! empty($title)) {{$title}} @endif  @parent @endsection


@section('content')
    <div class="account__content new">
        <form action="" id="startCampaignForm" class="form-horizontal" method="post"
              enctype="multipart/form-data"> @csrf
            @if(isset($campaign))
                <input type="hidden" name="status" value="{{in_array($campaign->status, [0,1]) ? 3 : $campaign->status}}">
                @if($campaign->status == 3)
                    <input type="hidden" name="re_moderation" value="1">
                @endif
            @endif
            <h2 class="account__title account__title-pos">
                Обоновить копилку
            </h2>
            <div class="account__pre account__pre-big">Изображение, обновления, награды и часто задаваемые вопросы будут
                доступны после создания копилки.
            </div>
            <div class="new__title">
                Информация о копилке
            </div>
            <div class="flex">
                <div class="new__category">
                    <div class="new__label">
                        Категория
                    </div>
                    <div class="banks__open new__input">
                        <span>  @foreach($categories as $category)
                                @if ($category->id===$campaign->category_id)
                                    {{ $category->category_name }}
                                    @break
                                @endif
                            @endforeach</span>
                        <img src="/dist/images/icons/arrow_down.svg" alt=""/>
                        <div class="new__hide">
                            <ul>
                                @foreach($categories as $category)
                                    <li data-value="{{ $category->id }}">{{ $category->category_name }}</li>
                                @endforeach
                            </ul>
                            <input type="hidden" name="category"
                                   value="{{old('category',$campaign->category_id)}}">
                        </div>
                    </div>
                </div>
                <div class="new__name">
                    <label for="name" class="new__label">Название</label>
                    <input id="name" value="{{old('title', $campaign->title) }}" class="new__input" name="title"
                           type="text" placeholder="Максимум 200 слов">
                </div>
            </div>
            <div class="new__description">
                <label for="description" class="new__label">Описание копилки</label>
                <textarea class="new__input" name="description" id="description" rows="8"
                          placeholder="Введите текст...">{{ old('description',$campaign->description) }}</textarea>
            </div>
            <div class="new__title">
{{--                Вы получите 80 % от суммы финансирования--}}
            </div>
            <div class="flex">
                <div class="new__purpose">
                    <label for="purpose" class="new__label">Цель</label>
                    <input id="purpose" value="{{ $campaign->goal }}" class="new__input" name="goal" type="number"
                           placeholder="Укажите сумму ₽ ">
                </div>
                <div class="new__video">
                    <label for="video" class="new__label">Видео</label>
                    <input value="{{ $campaign->video }}" id="video" class="new__input" name="video" type="text"
                           placeholder="https://youtube.com/example">
                </div>
            </div>
            @if(Auth::user()->is_admin())
                <div class="flex" style="margin-top: 35px">
                    <div class="new__purpose">
                        <label for="meta_title" class="new__label">Meta Title</label>
                        <input value="{{ $campaign->meta_title }}" id="meta_title" class="new__input" name="meta_title"
                               type="text">
                    </div>
                    <div class="new__purpose">
                        <label for="meta_description" class="new__label">Meta Description</label>
                        <input value="{{ $campaign->meta_description }}" id="meta_description" class="new__input"
                               name="meta_description" type="text">
                    </div>
                </div>
            @endif
            <div class="new__img">
                <div class="new__label">Главное изображение</div>
                <div class="new__img-info">
                    <div class="new__load">Загрузить изображение</div>
                    <div class="new__recomend">Рекомендуемый размер 1000 х 700 px</div>
                </div>
                <input class="filePreviewUploadMain" type="file" name="mainImg" accept="image/png, image/jpeg">
                <div class="new__img-main previewContainerMain">
                    <img src="{{ $campaign->feature_img_url()->feature_image }}" alt="">
                </div>
            </div>
            <div class="new__add new__img">
                <div class="new__label">Доп. Изображения</div>
                <div class="new__img-info">
                    <div class="new__load">Загрузить изображение</div>
                    <div class="new__recomend">Рекомендуемый размер 1000 х 700 px</div>
                </div>
                <input class="filePreviewUpload" type="file" name="files[]" accept="image/png, image/jpeg" multiple>
                <div class="new__img-adds previewContainer">
                    @if ($campaign->images)
                        @foreach($campaign->images as $image)
                            <img src="{{media_image_uri($image)->original}}" alt="">
                        @endforeach
                    @endif
                </div>
            </div>
            <div class="new__code form-field mt-1 d-flex gap-4">
                <button class="btn btn_fill mt-0">Сохранить</button>
                <a class="btn mt-0" href="{{ route('dashboard') }}">Отмена</a>
            </div>
        </form>

        @if (session()->has('created') || session()->has('updated'))
            <a class="profile-dignity__link row ai-center dignity-popup-link" id="popup_opener" href="#dignity"></a>
            <div class="popup dignity mfp-hide" id="dignity">
                <div class="popup-head">
                    <h5 class="popup-head__title">
                        Копилка успешно {{session()->has('created') ? 'создана!' : 'отредактирована!' }}
                    </h5>
                </div>
                <div class="popup-body">
                    <p>
                        Модерация занимает
                        до 24 часов,после этого Ваша
                        копилка
                        появится в личном кабинете

                        <br>

                        <a class="btn btn_fill d-flex ai-center download_excel w-25 justify-content-center"
                           href="/"
                           style="margin-top: 25px;max-width: 300px;text-align: center">
                            Вернуться на главную
                        </a>
                    </p>
                </div>
            </div>
        @endif
    </div>
@endsection

@section('page-js')
    <script></script>
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

        $('#popup_opener').click() //open alert modal if exists

        $('.filePreviewUploadMain').change(function () {
            readUrl(this, '.previewContainerMain');
        });

        $('.filePreviewUpload').change(function () {
            readUrl(this, '.previewContainer');
        });

        $('.categoryLi').click(function () {
            $('#categoryId').val($(this).data('value'));
        })

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
                        let html = '<div><img src="' + e.target.result + '"><i onclick="removePreview(imageIndex); $(this).parent().remove();" class="fa fa-close"></i></div>'
                        $(container).append(html);
                    }

                    reader.readAsDataURL(input.files[i]);
                }
            }
        }

        function removePreview(key) {
            $('.filePreviewUpload')[0].files.splice(key, 1);
        }

        let delay = 5000; //кол-во милисекунд, через которое нужно удалить класс active

        function successfullVisible() { //добавляет класс active блоку successfull и удаляет его через delay секунд
            $('.successfull').addClass('active')
            setInterval(successfullHide, delay);
        }

        function successfullHide() { //удаляет класс active у блока successfull
            $('.successfull').removeClass('active')
        }

        $('.successfull-close').click(successfullHide);
        @if (session()->has('created'))
        successfullVisible()
        @endif
    </script>

@endsection
