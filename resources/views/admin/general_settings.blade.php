@extends('layouts.admin.app')

@section('title')
    @if(! empty($title))
        {{$title}}
    @endif  @parent
@endsection

@section('content')

    <main class="admin-main">
        <div class="account-main__head">
            <div class="account-main__head-title">
                <h1 class="account-main__title">{{ $title }}
                </h1>
            </div>
        </div>
        <div class="comments bg-dark">

            <form action="{{route('save_settings')}}" class="form-horizontal" method="post"
                  enctype="multipart/form-data"> @csrf


                <div class="form-group row {{ $errors->has('site_name')? 'has-error':'' }}">
                    <label for="site_name" class="col-sm-4 control-label">@lang('app.site_name')</label>
                    <div class="col-sm-8">
                        <input type="text" class="form-control" id="site_name"
                               value="{{ get_option('site_name')  }}"
                               name="site_name" placeholder="@lang('app.site_name')">
                        {!! $errors->has('site_name')? '<p class="help-block">'.$errors->first('site_name').'</p>':'' !!}
                    </div>
                </div>

                <div class="form-group row {{ $errors->has('site_title')? 'has-error':'' }}">
                    <label for="site_title"
                           class="col-sm-4 control-label">@lang('app.site_title')</label>
                    <div class="col-sm-8">
                        <input type="text" class="form-control" id="site_title"
                               value="{{ get_option('site_title') }}"
                               name="site_title" placeholder="@lang('app.site_title')">
                        {!! $errors->has('site_title')? '<p class="help-block">'.$errors->first('site_title').'</p>':'' !!}
                    </div>
                </div>

                <div class="form-group row {{ $errors->has('email_address')? 'has-error':'' }}">
                    <label for="email_address"
                           class="col-sm-4 control-label">@lang('app.email_address')</label>
                    <div class="col-sm-8">
                        <input type="text" class="form-control" id="email_address"
                               value="{{ old('email_address')? old('email_address') : get_option('email_address') }}"
                               name="email_address" placeholder="@lang('app.email_address')">
                        {!! $errors->has('email_address')? '<p class="help-block">'.$errors->first('email_address').'</p>':'' !!}
                        <p class="text-info"> @lang('app.email_address_help_text')</p>
                    </div>
                </div>


                <div class="form-group row">
                    <label for="default_timezone" class="col-sm-4 control-label">
                        @lang('app.default_timezone')
                    </label>
                    <div class="col-sm-8 {{ $errors->has('default_timezone')? 'has-error':'' }}">
                        <select class="form-control select2" name="default_timezone"
                                id="default_timezone">
                            @php $saved_timezone = get_option('default_timezone'); @endphp
                            @foreach(timezone_identifiers_list() as $key=>$value)
                                <option value="{{ $value }}" {{ $saved_timezone == $value? 'selected':'' }}>{{ $value }}</option>
                            @endforeach

                        </select>


                        {!! $errors->has('default_timezone')? '<p class="help-block">'.$errors->first('default_timezone').'</p>':'' !!}
                        <p class="text-info">@lang('app.default_timezone_help_text')</p>
                    </div>
                </div>


                <div class="form-group row {{ $errors->has('date_format')? 'has-error':'' }}">
                    <label for="email_address"
                           class="col-sm-4 control-label">@lang('app.date_format')</label>
                    <div class="col-sm-8">
                        <fieldset>
                            @php $saved_date_format = get_option('date_format'); @endphp

                            <label><input type="radio" value="F j, Y"
                                          name="date_format" {{ $saved_date_format == 'F j, Y'? 'checked':'' }}> {{ date('F j, Y') }}
                                <code>F j, Y</code></label> <br/>
                            <label><input type="radio" value="Y-m-d"
                                          name="date_format" {{ $saved_date_format == 'Y-m-d'? 'checked':'' }}> {{ date('Y-m-d') }}
                                <code>Y-m-d</code></label> <br/>

                            <label><input type="radio" value="m/d/Y"
                                          name="date_format" {{ $saved_date_format == 'm/d/Y'? 'checked':'' }}> {{ date('m/d/Y') }}
                                <code>m/d/Y</code></label> <br/>

                            <label><input type="radio" value="d/m/Y"
                                          name="date_format" {{ $saved_date_format == 'd/m/Y'? 'checked':'' }}> {{ date('d/m/Y') }}
                                <code>d/m/Y</code></label> <br/>

                            <label><input type="radio" value="custom"
                                          name="date_format" {{ $saved_date_format == 'custom'? 'checked':'' }}>
                                Custom:</label>
                            <input type="text" value="{{ get_option('date_format_custom') }}"
                                   id="date_format_custom" name="date_format_custom"/>
                            <span>example: {{ date(get_option('date_format_custom')) }}</span>
                        </fieldset>
                        <p class="text-info"> @lang('app.date_format_help_text')</p>
                    </div>
                </div>

                <div class="form-group row {{ $errors->has('time_format')? 'has-error':'' }}">
                    <label for="email_address"
                           class="col-sm-4 control-label">@lang('app.time_format')</label>
                    <div class="col-sm-8">
                        <fieldset>
                            <label><input type="radio" value="g:i a"
                                          name="time_format" {{ get_option('time_format') == 'g:i a'? 'checked':'' }}> {{ date('g:i a') }}
                                <code>g:i a</code></label> <br/>
                            <label><input type="radio" value="g:i A"
                                          name="time_format" {{ get_option('time_format') == 'g:i A'? 'checked':'' }}> {{ date('g:i A') }}
                                <code>g:i A</code></label> <br/>

                            <label><input type="radio" value="H:i"
                                          name="time_format" {{ get_option('time_format') == 'H:i'? 'checked':'' }}> {{ date('H:i') }}
                                <code>H:i</code></label> <br/>

                            <label><input type="radio" value="custom"
                                          name="time_format" {{ get_option('time_format') == 'custom'? 'checked':'' }}>
                                Custom:</label>
                            <input type="text" value="{{ get_option('time_format_custom') }}"
                                   id="time_format_custom" name="time_format_custom"/>
                            <span>example: {{ date(get_option('time_format_custom')) }}</span>
                        </fieldset>
                        <p><a href="http://php.net/manual/en/function.date.php"
                              target="_blank">@lang('app.date_time_read_more')</a></p>
                    </div>
                </div>

                <div class="form-group row {{ $errors->has('currency_sign')? 'has-error':'' }}">
                    <label for="currency_sign"
                           class="col-sm-4 control-label">@lang('app.currency_sign')</label>
                    <div class="col-sm-8">

                        <?php $current_currency = get_option('currency_sign'); ?>
                        <select name="currency_sign" class="form-control select2">
                            @foreach(get_currencies() as $code => $name)
                                <option value="{{ $code }}" {{ $current_currency == $code? 'selected':'' }}> {{ $code }} </option>
                            @endforeach
                        </select>

                    </div>
                </div>

                <div class="form-group row {{ $errors->has('currency_position')? 'has-error':'' }}">
                    <label for="currency_position"
                           class="col-sm-4 control-label">@lang('app.currency_position')</label>
                    <div class="col-sm-8">
                        <?php $currency_position = get_option('currency_position'); ?>
                        <select name="currency_position" class="form-control select2">
                            <option value="left"
                                    @if($currency_position == 'left') selected="selected" @endif >@lang('app.left')</option>
                            <option value="right"
                                    @if($currency_position == 'right') selected="selected" @endif >@lang('app.right')</option>
                        </select>
                    </div>
                </div>

                <div class="form-group row {{ $errors->has('logo_settings')? 'has-error':'' }}">
                    <label for="email_address"
                           class="col-sm-4 control-label">@lang('app.logo_settings')</label>
                    <div class="col-sm-8">
                        <fieldset>
                            <label><input type="radio" value="show_site_name"
                                          name="logo_settings" {{ get_option('logo_settings') == 'show_site_name'? 'checked':'' }}> @lang('app.show_site_name')
                            </label> <br/>
                            <label><input type="radio" value="show_image"
                                          name="logo_settings" {{ get_option('logo_settings') == 'show_image'? 'checked':'' }}> @lang('app.show_image')
                            </label> <br/>
                        </fieldset>

                        {!! image_upload_form('logo', get_option('logo')) !!}

                    </div>
                </div>


                <legend>@lang('app.comments')</legend>


                <div class="form-group row {{ $errors->has('enable_comments')? 'has-error':'' }}">
                    <label class="col-md-4 control-label">@lang('app.enable_disable') </label>
                    <div class="col-md-8">
                        <label for="enable_comments" class="checkbox-inline">
                            <input type="checkbox" value="1" id="enable_comments"
                                   name="enable_comments" {{ get_option('enable_comments') == 1 ? 'checked="checked"': '' }}>
                            @lang('app.enable_comments')
                        </label>
                    </div>
                </div>

                <div class="form-group row {{ $errors->has('enable_fb_comments')? 'has-error':'' }}">
                    <label class="col-md-4 control-label">@lang('app.enable_disable') </label>
                    <div class="col-md-8">
                        <label for="enable_fb_comments" class="checkbox-inline">
                            <input type="checkbox" value="1" id="enable_fb_comments"
                                   name="enable_fb_comments" {{ get_option('enable_fb_comments') == 1 ? 'checked="checked"': '' }}>
                            @lang('app.enable_fb_comments')
                        </label>
                    </div>
                </div>


                {{--           <div class="form-group row {{ $errors->has('verification_email_after_registration')? 'has-error':'' }}">
                               <label for="email_address" class="col-sm-4 control-label">@lang('app.verification_email_after_registration')</label>
                               <div class="col-sm-8">
                                   <fieldset>
                                       <label><input type="radio" value="1" name="verification_email_after_registration" {{ get_option('verification_email_after_registration') == '1'? 'checked':'' }}> @lang('app.yes') </label> <br />
                                       <label><input type="radio" value="0" name="verification_email_after_registration" {{ get_option('verification_email_after_registration') == '0'? 'checked':'' }}> @lang('app.no') </label> <br />
                                   </fieldset>
                               </div>
                           </div>--}}

                <legend>@lang('app.cookie_settings')</legend>

                <div class="form-group row {{ $errors->has('enable_cookie_alert')? 'has-error':'' }}">
                    <label class="col-md-4 control-label">@lang('app.enable_disable') </label>
                    <div class="col-md-8">
                        <label for="enable_cookie_alert" class="checkbox-inline">
                            <input type="checkbox" value="1" id="enable_cookie_alert"
                                   name="enable_cookie_alert" {{ get_option('enable_cookie_alert') == 1 ? 'checked="checked"': '' }}>
                            @lang('app.enable_cookie_alert')
                        </label>
                    </div>
                </div>

                <div class="form-group row">
                    <label for="cookie_message"
                           class="col-sm-4 control-label">@lang('app.cookie_message')</label>
                    <div class="col-sm-8">
                                        <textarea class="form-control" id="cookie_message" name="cookie_message"
                                                  rows="6">{!! get_option('cookie_message') !!}</textarea>
                    </div>
                </div>


                <div class="form-group row">
                    <label for="cookie_learn_page"
                           class="col-sm-4 control-label">@lang('app.cookie_learn_page')</label>
                    <div class="col-sm-8">
                        @php
                            $pages = \App\Models\Post::whereType('page')->orderBy('id', 'desc')->get();
                            $selected_page = get_option('cookie_learn_page');
                        @endphp

                        <select class="select2 form-control" id="cookie_learn_page"
                                name="cookie_learn_page">
                            <option value="0">@lang('app.select_cookie_learn_more_page')</option>

                            @if($pages->count())
                                @foreach($pages as $cms_page)
                                    <option value="{{$cms_page->id}}" {!! $selected_page == $cms_page->id ? ' selected="selected" ':'' !!} >{{$cms_page->title}}</option>
                                @endforeach

                            @endif
                        </select>
                    </div>
                </div>



                <legend>Телеграм</legend>

                <div class="form-group row">
                    <label for="footer_telegram_url"
                           class="col-sm-4 control-label">Ссылка на телеграм</label>
                    <div class="col-sm-8">
                        <input class="form-control" type="text" id="footer_telegram_url" name="footer_telegram_url"
                               value="{{ get_option('footer_telegram_url', true) }}">
                    </div>
                </div>


                <div class="form-group row">
                    <label for="tg_moderators"
                           class="col-sm-4 control-label">ID пользователей</label>
                    <div class="col-sm-8">
                                        <textarea class="form-control" id="tg_moderators" name="tg_moderators"
                                                  rows="10">{!! get_option('tg_moderators', true) !!}</textarea>
                        Каждый ID с новой строки

                    </div>
                </div>


                <legend>Версия приложения</legend>


                <div class="form-group row">
                    <label for="cookie_message"
                           class="col-sm-4 control-label">Текущая версия приложения IOS</label>
                    <div class="col-sm-8">
                                        <input type="text" class="form-control" id="app_ios_version" name="app_ios_version"
                                                  rows="10" value="{!! get_option('app_ios_version', true) !!}">

                    </div>

                    <br>
                    <label for="cookie_message"
                           class="col-sm-4 control-label">Текущая версия приложения Android</label>
                    <div class="col-sm-8">
                        <input type="text" class="form-control" id="app_android_version" name="app_android_version"
                               rows="10" value="{!! get_option('app_android_version', true) !!}">

                    </div>
                </div>

                <hr/>
                <legend>Рекламный поп-ап</legend>
                <div class="form-group row">
                    <label for="ad_popup"
                           class="col-sm-4 control-label">Изображение</label>
                    <div class="col-sm-8">
                        <input class="form-control" type="file" id="ad_popup" name="ad_popup" value="{!! get_option('ad_popup', true) !!}">
                    </div>
                </div>

                @if(get_option('ad_popup_image', true))
                    <div class="form-group row">
                        <label class="col-sm-4 control-label">Текущее изображение</label>
                        <div class="col-sm-8">
                            <img src="{{get_option('ad_popup_image', true)}}" style="max-width: 350px; height: auto;">
                        </div>
                    </div>
                @endif

                <div class="form-group row">
                    <label for="ad_popup_url"
                           class="col-sm-4 control-label">Ссылка в баннере</label>
                    <div class="col-sm-8">
                        <input class="form-control" type="text" id="ad_popup_url" name="ad_popup_url" value="{!! get_option('ad_popup_url', true) !!}">
                    </div>
                </div>
                <div class="form-group row">
                    <label class="col-md-4 control-label">Отображение</label>
                    <div class="col-md-8">
                        <label for="ad_popup_active" class="checkbox-inline">
                            <input type="checkbox" value="1" id="ad_popup_active"
                                   name="ad_popup_active" {{ get_option('ad_popup_active') == 1 ? 'checked="checked"': '' }}>
                            Показывать
                        </label>
                    </div>
                </div>

                <hr/>
                <div class="form-group row">
                    <div class="col-sm-offset-4 col-sm-8">
                        <button type="submit" id="settings_save_btn"
                                class="btn btn-primary">@lang('app.save_settings')</button>
                    </div>
                </div>

            </form>
        </div>
    </main>


@endsection


@section('page-js')
    <script>
        $(document).ready(function () {
            $('input[type="checkbox"], input[type="radio"]').click(function () {
                var input_name = $(this).attr('name');
                var input_value = 0;
                if ($(this).prop('checked')) {
                    input_value = $(this).val();
                }
                $.ajax({
                    url: '{{ route('save_settings') }}',
                    type: "POST",
                    data: {[input_name]: input_value, '_token': '{{ csrf_token() }}'},
                });
            });

            $('input[name="date_format"]').click(function () {
                $('#date_format_custom').val($(this).val());
            });
            $('input[name="time_format"]').click(function () {
                $('#time_format_custom').val($(this).val());
            });

            /**
             * Send settings option value to server
             */
            $('#settings_save_btn').click(function (e) {
                e.preventDefault();

                // Disable the button to prevent multiple clicks
                var this_btn = $(this);
                this_btn.attr('disabled', 'disabled');

                // Create a FormData object to store form data including the file
                var form = this_btn.closest('form')[0];
                var form_data = new FormData(form);

                // Append the file to the FormData object
                var file_input = $('#ad_popup')[0].files[0];
                form_data.append('ad_popup', file_input);

                // Send the ajax request with the form data
                $.ajax({
                    url: '{{ route('save_settings') }}',
                    type: "POST",
                    data: form_data,
                    contentType: false,
                    processData: false,
                    success: function (data) {
                        this_btn.removeAttr('disabled');
                        if (data.success == 1) {
                            toastr.success(data.msg, '@lang('app.success')', toastr_options);
                        } else {
                            toastr.error(data.msg, '@lang('app.error')', toastr_options);
                        }
                    }
                });
            });
        });
    </script>
@endsection
