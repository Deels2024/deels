@extends('layouts.new.app')

@section('title') @if(! empty($title)) {{$title}} @endif  @parent @endsection

@section('page-css')
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-beta.1/dist/css/select2.min.css" rel="stylesheet"/>
    <style>
        #tags {
            width: 100%;
        }
    </style>
@endsection

@section('content')
    <section class="page-top">
        <div class="wrapper">
            <div class="breadcrumbs">
                <ul>
                    <li>
                        <a href="/">Главная</a>
                    </li>
                    <li>
                        <a href="/dashboard">Личный кабинет</a>
                    </li>
                    <li>Редактировать новость</li>
                </ul>
            </div>
            <div class="page-top__title title title_big">Личный кабинет</div>
        </div>
    </section>

    <section class="cabinet">
        <div class="wrapper cabinet__wrap flex">
            <div class="cabinet__left mobile-window">
                <div class="mobile-window__closed img-contain">
                    <img src="/images/icons/close.svg" alt="">
                </div>
                <div class="cabinet-sidebar">
                    @include('admin.menu')
                </div>
            </div>
            <div class="cabinet__right">
                <div class="cabinet__right-title">Редактировать новость</div>
                <div class="cabinet__filter filter-mobile btn">
                    <img src="/images/icons/filter.svg" alt="">
                    <span>Открыть меню</span>
                </div>
                <div class="cabinet__box">
                    <div class="start-company">
                        <form action="" id="startCampaignForm" class="form-horizontal" method="post" enctype="multipart/form-data"> @csrf
                            <div class="start-company__body">
                                <div class="start-company__title">Содержание новости</div>
                                <div class="start-company__box">
                                    <div class="start-company__label flex">
                                        <div class="start-company__caption">Название</div>
                                        <div class="start-company__block">
                                            <input type="text" value="{{ $news->title }}" name="title" class="start-company__field field" placeholder="Максимум 200 символов" required>
                                        </div>
                                    </div>
                                    <div class="start-company__label flex wysiwyg">
                                        <div class="start-company__caption">Текст новости</div>
                                        <div class="start-company__block" style="width: 940px">
                                            <textarea name="text" id="description" class="start-company__field field" required>{{ $news->text }}</textarea>
                                        </div>
                                    </div>
                                    <div class="start-company__bottom">
                                        <button class="start-company__btn btn">Обновить новость</button>
                                    </div>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </section>

@endsection

@section('page-js')
    <script src="//cdnjs.cloudflare.com/ajax/libs/moment.js/2.9.0/moment-with-locales.js"></script>
    <script src="{{asset('assets/plugins/bootstrap-datepicker/js/bootstrap-datetimepicker.min.js')}}"></script>




    <!-- include libraries(jQuery, bootstrap) -->
    <link href="{{asset('assets/css/bootstrap.min.css')}}" rel="stylesheet">
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/3.4.1/js/bootstrap.min.js"></script>

    <!-- include summernote css/js -->
    <link href="https://cdn.jsdelivr.net/npm/summernote@0.8.18/dist/summernote.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/summernote@0.8.18/dist/summernote.min.js"></script>
    <script src="{{asset('assets/js/summernoteLang.js')}}"></script>


    <script>
		if ($(window).width() >= 1024) {
			$('#description').summernote({
				height: 200,
				tooltip: false,
				lang: 'ru-RU',
				toolbar: [
					['style', ['style']],
					['font', ['bold', 'underline', 'italic']],
					// ['fontname', ['fontname']],
					['color', ['color']],
					['para', ['ul', 'ol', 'paragraph']],
					['table', ['table']],
					['insert', ['link', 'picture']],
					['view', ['fullscreen', 'codeview']],
				],
				styleTags: [
					'p',
					'pre', 'h1', 'h2', 'h3', 'h4', 'h5', 'h6',
				],
				callbacks: {
					onImageUpload: function (image) {
						uploadImage(image[0]);
					}
				}
			});
		}

		function uploadImage(image) {
			var data = new FormData();
			data.append("image", image);
			$.ajax({
				url: '/dashboard/media/upload',
				cache: false,
				contentType: false,
				processData: false,
				data: data,
				type: "post",
				success: function (url) {
					var image = $('<img class="img-fluid">').attr('src', url);
					$('#description').summernote("insertNode", image[0]);
				},
				error: function (data) {
					console.log(data);
				}
			});
		}

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
    </script>


    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-beta.1/dist/js/select2.min.js"></script>
    <script>
		$('#tags').select2({
			tags: true
		});
		$(function () {
			$('#start_date, #end_date').datetimepicker({format: 'YYYY-MM-DD'});
		});
    </script>
@endsection
