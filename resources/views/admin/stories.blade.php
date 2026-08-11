@extends('layouts.admin.app_neon')
@section('title')
    @if( ! empty($title))
        {{ $title }} |
    @endif @parent
@endsection

@section('content')

    <main class="admin-main">
        <div class="account-main__head">
            <div class="account-main__head-title">
                <h1 class="account-main__title">Модерация сторис
                    {{isset($_GET['story_id']) ? ' ID'.$_GET['story_id'] :''}}
                  @if(isset($_GET['type']))
                    @if($_GET['type'] == 'declined')
                        [Отклоненные]
                    @endif
                      @if($_GET['type'] == 'active')
                        [Одобренные]
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
            <a href="{{route('admin_stories')}}" class="btn btn-sm mr-2" style="padding: 10px; font-size: 12px; line-height: 12px; height: auto; min-height: auto;">На модерации</a>
            <a href="{{route('admin_stories')}}?type=declined" class="btn btn-sm mr-2" style="padding: 10px; font-size: 12px; line-height: 12px; height: auto; min-height: auto;">Отклоненные</a>
            <a href="{{route('admin_stories')}}?type=active" class="btn btn-sm mr-2" style="padding: 10px; font-size: 12px; line-height: 12px; height: auto; min-height: auto;">Одобренные</a>
            <a href="{{route('admin_stories')}}?type=blocked" class="btn btn-sm mr-2" style="padding: 10px; font-size: 12px; line-height: 12px; height: auto; min-height: auto;">Заблокированные</a>
        </div>

        <form action="{{route('admin_stories')}}" type="GET" class="d-flex mb-4 align-items-center">
            <div class="">
                <input id="story_id" class="new__input" type="text" placeholder="№ сторис" name="story_id" value="{{$_GET['story_id'] ?? ''}}">
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

        <form class="form form--admin tools_block" action="{{route('admin_stories.add_likes')}}"  method="POST">
            @csrf
            <span class="tools_block_title">Добавить лайки</span>

            <div class="form-row">
                <input type="text" value="{{request('story_id')}}" name="story_id" placeholder="ID сторис" style="margin: 0; width: auto; min-width: 35%">
                <input type="text" value="{{request('likes')}}" name="likes" placeholder="Кол-во лайков" style="margin: 0; width: auto; min-width: 35%">
                <button class="form__btn btn btn_fill">Добавить</button>
            </div>

        </form>
        <div class="comments bg-dark">
            <style>
                @media (max-width: 768px) {
                    .mobile-list {
                        grid-template-columns: 1fr;
                    }
                }
            </style>
            @if($stories)
                <div class="copystories-list mobile-list">
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
                                @if($story->declined)
                                    [ Отклонена ]
                                @else
                                    {{!$story->active ? '[ На модерации ]' : ''}}
                                @endif
                                @if($story->is_ad)
                                    [ Реклама ]
                                @endif
                                @if(isset($_GET['type']) && $_GET['type'] == 'declined')
                                    <div style="padding: 5px 10px; font-size: 12px; color: #ff0000;background-color: rgba(68, 68, 68, 0.9);">
                                        {!! $story->getReasons()!!}
                                    </div>
                                @endif
                                    @if($story->challenge)
                                        <div class="story-badge" href="">
                                            <svg width="16" height="16" viewBox="0 0 16 16" fill="none" xmlns="http://www.w3.org/2000/svg">
                                                <path class="st0" d="M9.8,8.3l-3.7,5.5l0.8-3.7L7.1,9l0,0L5.7,8.8l1.9-5.6L9,3.4L7.6,6.9L7.3,7.8L8.3,8L9.8,8.3z" stroke="white" stroke-width="1.5"></path>
                                            </svg>
                                            <span>Челлендж: <b>{{$story->challenge->title}}</b></span>
                                        </div>
                                    @endif
                                    @if($story->blocked_at)
                                        <br><small style="display: block; text-align: center">Заблокирована до<br>{{\Carbon\Carbon::parse($story->blocked_at)->format('d.m.Y H:i')}}</small>
                                    @endif

                                    @include('stories.parts.stats', ['story' => $story])
                                <div class="actions">
                                    @if(!$story->declined)
                                        <div class="actions-link comment_action" data-action="trash" title="Отклонить"
                                           data-id="{{$story->id}}" href="javascript:;"
                                           style="cursor:pointer;background-image: url(/dist/images/admin_icons/icon-del.svg)"></div>
                                    @endif
                                    @if (!$story->active || $story->declined)
                                        <div class="actions-link comment_action" data-action="approve"
                                           data-id="{{$story->id}}" href="javascript:;"
                                           style="cursor:pointer;background-image: url(/dist/images/admin_icons/icon-checkmark.svg)"></div>
                                    @endif
                                        <div class="actions-link comment_action" data-action="delete" title="Удалить"
                                             data-id="{{$story->id}}" href="javascript:;"
                                             style="cursor:pointer;background-image: url(/dist/images/admin_icons/icon-cancel.svg); background-size: 12px"></div>
                                </div>
                                    <div style="padding: 5px 10px; font-size: 11px; color: #fff;background-color: rgba(0, 0, 0, 0.3);">
                                        {{\Carbon\Carbon::parse($story->created_at)->format('d.m.Y H:i')}}
                                    </div>
                                @if(count($story->tags) > 0)

                                    @php
                                        $tags = $story->tags()->pluck('title')->toArray();
                                    @endphp
                                    <div class="tags" style="padding: 5px 10px;font-size: 12px">
                                        Теги: {{implode(', ',$tags)}}
                                    </div>
                                @endif
                            </div>
                        </div>
                    @endforeach
                </div>
            @endif

            @if($stories->count() && isset($asdasd))
                @foreach($stories as $story)
                    <div class="comment-item d-flex">
                        <p class="comment-id comment-id--head mr-1">#{{$story->id}}</p>
                        <div class="comment-item__content">
                            <div class="comment-head">
                                @if($story->user)
                                    <div class="avatar avatar--sm mr-1">
                                        <img src="{{$story->user->avatar()}}" class="magnific_image circle-img"
                                             data-image="{{$story->user->avatar()}}" alt="{{$story->user->fullname}}">
                                    </div>
                                    <div class="comment-head__content">
                                        <p class="comment-email">{{$story->user->fullname}} ({{$story->user->email}}
                                            )@if($story->user_id===Auth::id())
                                                (Вы)
                                            @endif</p>
                                        <p class="comment-date">{{$story->created_at->diffForHumans()}}</p>
                                    </div>
                                @else
                                    Пользователь ID{{$story->user_id}} отсутствует в базе
                                @endif
                            </div>
                            <div class="comment-body copystories-list">
                                <p class="comment-text" style="display: flex; align-items: center">
                                    <span style="max-width: 300px;">
                                        @if($story->type == 'video')
                                            <video src="{{$story->path}}" style="max-width: 100%" loop controls></video>
                                        @else
                                            <img src="{{$story->path}}" alt="">
                                        @endif
                                        @if($story->declined)
                                            <br>
                                            [ Отклонена ]
                                        @endif
                                    </span>

                                    @if($story->description)
                                        <span style="margin-left: 10px">
                                           Описание:<br><br>
                                            {{$story->description}}
                                        </span>
                                    @endif

                                </p>
                                <div class="d-flex ai-center">
                                    <div class="actions">
                                        @if(!$story->declined)
                                            <a class="actions-link comment_action" data-action="trash"
                                               data-id="{{$story->id}}" href="javascript:;"
                                               style="background-image: url(/dist/images/admin_icons/icon-del.svg)"></a>
                                        @endif
                                        @if (!$story->active || $story->declined)
                                            <a class="actions-link comment_action" data-action="approve"
                                               data-id="{{$story->id}}" href="javascript:;"
                                               style="background-image: url(/dist/images/admin_icons/icon-checkmark.svg)"></a>
                                        @endif
                                    </div>
                                    <p class="comment-id comment-id--body mr-1">#{{$story->id}}</p>
                                </div>
                            </div>
                        </div>
                    </div>
                @endforeach
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
                if ($that.attr('data-action') === 'trash') {
                    if (!confirm("Отклонить сторис?")) {
                        return false;
                    }
                }

                if ($that.attr('data-action') === 'delete') {
                    if (!confirm("Удалить сторис?")) {
                        return false;
                    }
                }

                var action = $that.data('action');

                $.ajax({
                    type: 'POST',
                    url: '{{ route('admin_stories.confirm') }}',
                    data: {story_id: $that.data('id'), action: action, _token: '{{ csrf_token() }}'},
                    success: function (data) {
                        console.log(data);
                        if (data.success == 1) {

                            if (action == 'trash') {
                                alert('Сторис отклонена');
                                $that.remove();
                            }

                            if (action == 'delete') {
                                alert('Сторис удалена');
                                $that.remove();
                            }

                            if (action == 'approve') {
                                alert('Сторис подтверждена');
                                $that.remove();
                            }
                        }
                    }
                });
            });
        });
    </script>

@endsection
