@extends('layouts.admin.app_neon')
@section('title')
    @if( ! empty($title))
        {{ $title }}
    @endif
@endsection

@section('content')

    <main class="admin-main">
        <div class="account-main__head">
            <div class="account-main__head-title">
                <h1 class="account-main__title">{{$title ?? 'Модерация ответов'}}
                    {{isset($_GET['story_id']) ? ' ID'.$_GET['story_id'] :''}}
                    @if(isset($_GET['type']))
                        @if($_GET['type'] == 'frozen')
                            [На проверке]
                        @endif
                        @if($_GET['type'] == 'banned')
                            [Заблокированные]
                        @endif
                    @endif
                </h1>
            </div>
        </div>

        <style>
            .tools_block {
                padding: 20px 10px 20px 10px; background: #0d0f2c; border: 1px solid #b224ef; border-radius: 10px; position: relative; margin-top: 40px; max-width: 100%;
            }
            .tools_block .form-row {
                align-items: center; display:flex; width: 100%; max-width: 100%;
            }
            .tools_block .form__btn {
                padding: 10px
            }
            .tools_block input {
                margin-top: 0!important;
                margin-bottom: 5px!important;
            }
            .tools_block .tools_block_title {
                position: absolute; top: -9px; border-radius: 4px;background: #0d0f2c; letter-spacing:1px; font-weight:600; padding: 4px 8px; font-size: 9px; text-transform: uppercase
            }
            .form-field--4 {
                width: calc(30% - 10px);
            }
            .form-field-3 {
                width: calc(25% - 10px);
            }

        </style>

        <div class="d-flex mb-4">
            <a href="{{route('admin_challenges_stories')}}" class="btn btn-sm mr-2" style="padding: 10px; font-size: 12px; line-height: 12px; height: auto; min-height: auto;">Все</a>
            <a href="{{route('admin_challenges_stories')}}?type=frozen" class="btn btn-sm mr-2" style="padding: 10px; font-size: 12px; line-height: 12px; height: auto; min-height: auto;">На проверке</a>
            <a href="{{route('admin_challenges_stories')}}?type=banned" class="btn btn-sm mr-2" style="padding: 10px; font-size: 12px; line-height: 12px; height: auto; min-height: auto;">Заблокированные</a>
        </div>

        <form action="{{route('admin_challenges_stories')}}" type="GET" class="d-flex mb-4 align-items-center">
            <div class="">
                <input id="story_id" class="new__input" type="text" placeholder="№ сторис" name="story_id" value="{{$_GET['story_id'] ?? ''}}">
            </div>
            <div class="ml-3">
                <input id="story_id" class="new__input" type="text" placeholder="№ челленджа" name="challenge_id" value="{{$_GET['challenge_id'] ?? ''}}">
            </div>

            @if(isset($_GET['type']))
                <input type="hidden" name="type" value="{{$_GET['type']}}">
            @endif
            @if(isset($_GET['type']) && $_GET['type'] == 'declined')
                <div class="" style="margin-left: 10px">
                    <label class="d-flex ai-center mt-1 mb-2">
                        <input class="mr-1" type="checkbox" name="ai_moderated" value="1" {{isset($_GET['ai_moderated']) ? 'checked' : ''}}><span>Отклонено ИИ</span>
                    </label>
                </div>
            @endif
            <div class="ml-4">
                <button type="submit" class="btn btn-small ml-4" style="padding: 5px; max-height: auto">Применить</button>
            </div>
        </form>

        <div class="comments bg-dark">

            @if($stories)
                <div class="copystories-list">
                    @foreach($stories as $story)
                        @php
                            $is_viewed = false;
                            if(Auth::user()) {
                                $view = \App\Models\View::where('user_id', Auth::user()->id)->where('story_id', $story->id)->first();
                                if($view) {
                                    $is_viewed = true;
                                }
                            }
                        @endphp
                        <div class="copystories-item">
                            @include('stories.parts.preview', ['story' => $story, 'class' => 'copystories-item__img'])
                            <div class="copystories-item__content">

                                @if($story->paid && $story->amount > 0)
                                    <div class="" style="position: absolute;top: 5px; background: rgba(0,0,0,0.8); color: #ffffff; font-size: 9px; text-transform: uppercase; padding: 4px">Платно</div>
                                @endif
                                <a href="#story-popup" class="show_story" data-route="{{route('stories.preview', ['id' => $story->id, 'user_id' => Auth::user()->id ?? null])}}" data-story="{{$story->id}}" data-type="{{$story->type}}" data-paid="{{$story->paid}}" data-amount="{{$story->amount}}">
                                    <span style="background-color: #8169FC;padding: 10px;border-radius: 50%; overflow: hidden;display: flex;align-items: center">
                                        <svg xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" version="1.1" id="mdi-eye" width="24" height="24" viewBox="0 0 24 24"><path d="M12,9A3,3 0 0,0 9,12A3,3 0 0,0 12,15A3,3 0 0,0 15,12A3,3 0 0,0 12,9M12,17A5,5 0 0,1 7,12A5,5 0 0,1 12,7A5,5 0 0,1 17,12A5,5 0 0,1 12,17M12,4.5C7,4.5 2.73,7.61 1,12C2.73,16.39 7,19.5 12,19.5C17,19.5 21.27,16.39 23,12C21.27,7.61 17,4.5 12,4.5Z" fill="#ffffff"/></svg>
                                    </span>
                                </a>
                                <div style="text-align: center">
                                    {!! $story->frozen && !$story->banned ? '<br>[ На проверке ]' : '' !!}
                                    {!! $story->banned ? '<br><span style="color:#ff0000">[ Заблокировано ]</span>' : '' !!}
                                    @if($story->banned)
                                        <div style="padding: 5px 10px; font-size: 12px; color: #ff0000;background-color: rgba(68, 68, 68, 0.9);">
                                            {!! $story->banned_reason ?? 'Бан за нарушение правил'!!}
                                        </div>
                                    @endif
                                </div>

                                @if($story->challenge)
                                    <div class="story-badge" href="">
                                        <svg width="16" height="16" viewBox="0 0 16 16" fill="none" xmlns="http://www.w3.org/2000/svg">
                                            <path class="st0" d="M9.8,8.3l-3.7,5.5l0.8-3.7L7.1,9l0,0L5.7,8.8l1.9-5.6L9,3.4L7.6,6.9L7.3,7.8L8.3,8L9.8,8.3z" stroke="white" stroke-width="1.5"></path>
                                        </svg>
                                        <span>Челлендж: <b>{{$story->challenge->title}} (ID {{$story->challenge->id}})</b>
                                            <span class="challenge_frozen" {!! $story->challenge->frozen ? '' : 'style="display:none"' !!}>
                                                <br>(Заморожен)
                                            </span>
                                        </span>
                                    </div>
                                @endif

                                @include('stories.parts.stats', ['story' => $story])

                                <div class="actions">
                                    @if(!$story->frozen && !$story->banned)
                                        <div class="btn_fill comment_action" data-action="frozen"
                                             data-id="{{$story->id}}" href="javascript:;"
                                             style="cursor:pointer;">[ На проверку ]</div>
                                    @endif

                                    <div class="challenge_story_actions" {!! $story->frozen && !$story->banned ? 'style="display:inline-flex;"' : 'style="display:none;"' !!}>
                                        <div class="comment_action" data-action="approved" data-id="{{$story->id}}" href="javascript:;" style="cursor:pointer;" alt="Разморозить">[ ✅ ]</div>
                                        <div class="comment_action" data-action="banned" data-id="{{$story->id}}" href="javascript:;" style="cursor:pointer;" alt="Заблокировать">[ ❌ ]</div>
                                    </div>

                                </div>

                                <div style="padding: 5px 10px; font-size: 11px; color: #fff;background-color: rgba(0, 0, 0, 0.3);">
                                    {{\Carbon\Carbon::parse($story->created_at)->format('d.m.Y H:i')}}
                                </div>
                                @include('stories.parts.tags', ['story' => $story])
                            </div>
                        </div>
                    @endforeach
                </div>
            @endif
        </div>
        {{$stories->withQueryString()->links()}}
    </main>

    @include('stories.modal')

@endsection

@section('page-js')
    {{--    @include('dashboard.stories.stories_scripts')--}}
    <script>
        $(document).ready(function () {
            $('body').on('click', '.comment_action', function (e) {
                e.preventDefault();
                e.stopPropagation();

                var $that = $(this);
                var $actions = $(this).parents('.actions').find('.challenge_story_actions');
                var $challenge_frozen = $(this).parents('.copystories-item').find('.challenge_frozen');
                if ($that.attr('data-action') === 'frozen') {
                    if (!confirm("<?php echo trans('app.are_you_sure'); ?>")) {
                        return false;
                    }
                }

                var action = $that.data('action');

                $.ajax({
                    type: 'POST',
                    url: '{{ route('admin_challenge_stories.confirm') }}',
                    data: {story_id: $that.data('id'), action: action, _token: '{{ csrf_token() }}'},
                    success: function (data) {
                        console.log(data);
                        if (data.success == 1) {

                            if (action == 'frozen') {
                                alert('Сторис отправлена на проверку');
                                $that.remove();
                                $actions.show().css('display', 'inline-flex');
                                $challenge_frozen.show();
                            }
                            if (action == 'banned') {
                                alert('Сторис заблокирована');
                                $challenge_frozen.remove();
                                $actions.remove();
                            }
                            if (action == 'approved') {
                                alert('Сторис прошла проверку');
                                $actions.remove();
                                $challenge_frozen.remove();
                            }
                        }
                    }
                });
            });
        });
    </script>

@endsection
