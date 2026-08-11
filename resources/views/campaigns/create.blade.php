@extends('layouts.admin.app_neon')

@section('title')
    @if(! empty($title))
        {{$title}}
    @endif  @parent
@endsection

@section('content')

    <div class="account__content new">
        <form action="" id="startCampaignForm" class="form-horizontal" method="post"
              enctype="multipart/form-data"> @csrf
            <h2 class="account__title account__title-pos">
                Создать копилку
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
                    <div class="banks__open new__input absolute-dropdown"><span class="category_title">Выберите категорию</span>
                        <img src="/dist/images/icons/arrow_down.svg" alt=""/>
                        <div class="new__hide form-field dropdown-list">
                            <label for=""></label>
                            <ul>
                                @foreach($categories as $category)
                                    <li class="categoryLi campaign_category"
                                        data-value="{{ $category->id }}">{{ $category->category_name }}</li>
                                @endforeach
                            </ul>
                            <input
                                    type="hidden"
                                    name="category"
                                    id="categoryId"
                                    value="{{old('category') ?? 'Выберите категорию'}}"
                                    class="required_input"
                            >
                            <small></small>
                        </div>
                    </div>
                </div>

                <div class="new__name form-field">
                    <label for="name" class="new__label">Название</label>
                    <input id="name"
                           class="new__input required_input"
                           name="title"
                           type="text"
                           placeholder="Максимум 200 слов"
                           value="{{old('title')}}">
                    <small></small>
                </div>

            </div>
            <div class="new__description form-field">
                <label for="description" class="new__label">Описание копилки</label>
                <textarea minlength="70"
                          class="new__input description_field"
                          name="description"
                          id="description"
                          rows="8"
                          placeholder="Введите минимум 70 символов"
                          required>{{old('description')}}</textarea>
                <small></small>
                <button class="moneybox_generate btn btn-small">Сгенерировать описание при помощи ИИ
                    <span class="ai_helper_cost">
                         {{env('AI_STORAGE_COST', 50)}} <img src="/dist/img/deels_cur.svg" class="small_coin">
                    </span>
                </button>
            </div>
            <div class="new__title">
{{--                Вы получите 80 % от суммы финансирования--}}
            </div>
            <div class="flex">
                <div class="new__purpose form-field">
                    <label for="purpose" class="new__label">Цель</label>
                    <input id="purpose" class="new__input required_input" name="goal"
                           value="{{old('goal')}}"
                           type="number"
                           placeholder="Укажите сумму ₽ "
                           min="500"
                           required
                    >
                    <small></small>
                </div>
                <div class="new__video form-field">
                    <label for="video" class="new__label">Видео</label>
                    <input id="video"
                           class="new__input"
                           value="{{old('video')}}" name="video"
                           type="text"
                           placeholder="https://youtube.com/example"
                    >
                    <small></small>
                </div>
            </div>
            <div class="new__img form-field">
                <div class="new__label">Главное изображение</div>
                <div class="new__img-info">
                    <div class="new__load">Загрузить изображение</div>
                    <div class="new__recomend">Рекомендуемый размер 1000 х 700 px</div>
                </div>
                <input
                        class="filePreviewUploadMain required_input"
                        type="file"
                        name="mainImg"
                        accept="image/png, image/jpeg"
                >
                <small></small>
                <div class="new__img-main previewContainerMain"></div>
            </div>
            <div class="new__add new__img form-field">
                <div class="new__label">Доп. Изображения</div>
                <div class="new__img-info">
                    <div class="new__load">Загрузить изображение</div>
                    <div class="new__recomend">Рекомендуемый размер 1000 х 700 px</div>
                </div>
                <input class="filePreviewUpload"
                       type="file"
                       name="files[]"
                       accept="image/png, image/jpeg"
                       multiple
                >
                <small></small>
                <div class="new__img-adds previewContainer"></div>
            </div>
            <div class="new__code form-field mt-1">
{{--                <label for="code" class="new__label">Код подтверждения из письма</label>--}}
{{--                <style>--}}
{{--                    input:focus::placeholder {--}}
{{--                        color: transparent;--}}
{{--                    }--}}
{{--                    input:focus::-webkit-input-placeholder { color:transparent; }--}}
{{--                    input:focus:-moz-placeholder { color:transparent; } /* FF 4-18 */--}}
{{--                    input:focus::-moz-placeholder { color:transparent; } /* FF 19+ */--}}
{{--                    input:focus:-ms-input-placeholder { color:transparent; } /* IE 10+ */--}}
{{--                </style>--}}
{{--                <div class="d-flex" style="flex-wrap: wrap">--}}
{{--                    <input id="code"--}}
{{--                           style="width: 45%"--}}
{{--                           value="{{old('code')}}"--}}
{{--                           class="new__input new__input-code required_input"--}}
{{--                           type="text"--}}
{{--                           name="code"--}}
{{--                           placeholder="Ввести код">--}}
{{--                    <div class="new__btn sendCodeBtn"  style="width: 35%">Получить код</div>--}}
{{--                    <br>--}}
{{--                    <small style="width: 100%"></small>--}}
{{--                </div>--}}

                <div class="mt-1 d-flex gap-4">
                    <button class="btn btn_fill mt-0">Сохранить</button>
                    <a class="btn mt-0" href="{{ route('dashboard') }}">Отмена</a>
                </div>

            </div>

            <br>
            <br>


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

        $('#startCampaignForm').submit(function () {
            let error = false;

            if ($('#description').val().length < 70) {
                $('#description').css('border', '1px solid red');
                error = true;
            }

            if (error) {
                return false;
            }else{
                $('#description').css('border', '1px solid green');
            }
        });

        $('.categoryLi').click(function () {
            $('#categoryId').val($(this).data('value'));
        })


        $('.filePreviewUploadMain').change(function () {
            readUrl(this, '.previewContainerMain');
        });

        $('.filePreviewUpload').change(function () {
            readUrl(this, '.previewContainer');
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
                        let html = '<img src="' + e.target.result + '">'
                        $(container).append(html);
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
        @if(old('category'))
        @foreach($categories as $category)
            @if($category->id == old('category'))

                $('.category_title').text('{{$category->category_name}}');
            @endif
        @endforeach
                {{--$('.campaign_category[data-value="{{old('category')}}"]').trigger('click');--}}
        @endif
        $('.sendCodeBtn').click(function () {
            alert('Код был отправлен вам на почту');
            $.post('/user/sendEmailCode', {email: '{{Auth::user()->email}}'}, function () {
                $('.withdrawBtn').show();
            });
            $(this).attr('disabled', 'disabled')
        });
        $(document).on('click', '.moneybox_generate', function(e) {

            $('.alert').remove();
            var category_field = $('.category_title');
            var generate_button = $(this);
            var generate_button_text = $(this).text();

            if(!category_field.attr('data-selected')) {
                $('.alert-container').html('<div class="alert danger"> <span class="closebtn">&times;</span>Укажите категорию копилки</div>')
                return false;
            } else {

            }

            var category = category_field.text();
            var name = $('input[name="title"]').val();
            var description = $('.description_field').val();


            $.ajax({
                url: '{{route('services.moneybox')}}',
                type: 'POST',
                data: {category : category, name: name,description: description, user_id: '{{Auth::user()->id}}',_token : '{{csrf_token()}}'},
                beforeSend: function() {
                    generate_button.prop('disabled', true);
                    generate_button.addClass('disabled');
                    generate_button.text('Выполняем генерацию...');
                },
                complete: function() {
                    generate_button.prop('disabled', false);
                    generate_button.removeClass('disabled');
                    generate_button.text(generate_button_text);
                },
                success: function(data) {
                    console.log(data);
                    if(data.success) {
                        $('.alert-container').html('<div class="alert success"> <span class="closebtn">&times;</span>Успешно!</div>');
                        if(data.name) {
                            $('input[name="title"]').val(data.name);
                        }
                        if(data.description) {
                            $('.description_field').val(data.description);
                        }
                    } else {
                        $('.alert-container').html('<div class="alert danger"> <span class="closebtn">&times;</span>'+data.error+'</div>')
                    }

                },
                error: function(xhr, ajaxOptions, thrownError) {
                    generate_button.prop('disabled', false);
                    generate_button.text(generate_button_text);
                    console.log(xhr);
                    console.log(ajaxOptions);
                    console.log(thrownError);
                    $('.alert-container').html('<div class="alert danger"> <span class="closebtn">&times;</span>Произошла ошибка. Попробуйте выполнить запрос позже или обратитесь к администратору.</div>')
                }
            });
        });
    </script>
@endsection
