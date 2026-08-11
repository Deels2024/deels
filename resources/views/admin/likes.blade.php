@extends('layouts.admin.app_neon')
@section('title')
    @if( ! empty($title))
        {{ $title }} |
    @endif @parent
@endsection

@section('page-css')

    <link rel="stylesheet" href="/assets/css/style_emoji.css">
    <link rel="stylesheet" href="/dist/css/new_campaign/index.css">

@endsection

@section('content')

    <main class="admin-main">
        <div class="account-main__head">
            <div class="account-main__head-title">
                <h1 class="account-main__title">Мои лайки</h1>
            </div>
        </div>
{{--        <button class="aside-nav-btn">Открыть меню</button>--}}


        <div class="d-block mb-4 mt-7">
            <ul class="main-content__switch lk_switch">
                <li class="main-content__switch-link main-content__switch-link_donate {{!isset($_GET['type']) || isset($_GET['type']) && $_GET['type'] != 'campaigns' ? 'main-content__switch-link_active' : ''}}">
                    <a class="main-content__switch-link" href="{{ route('user_likes') }}">Сторис</a>
                </li>
                <li class="main-content__switch-link main-content__switch-link_comments {{isset($_GET['type']) && $_GET['type'] == 'campaigns' ? 'main-content__switch-link_active' : ''}}">
                    <a class="main-content__switch-link" href="{{ route('user_likes') }}?type=campaigns">Копилки</a>
                </li>
            </ul>
        </div>

        @if(count($stories))
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
                    <a href="#story-popup" class="copystories-item show_story" data-route="{{route('stories.preview', ['id' => $story->id, 'user_id' => Auth::user()->id ?? null])}}" data-story="{{$story->id}}" data-type="{{$story->type}}" data-paid="{{$story->paid}}" data-amount="{{$story->amount}}">
                        @include('stories.parts.preview', ['story' => $story, 'class' => 'copystories-item__img'])
                        <div class="copystories-item__content">
                            <div class="play-btn copystories-btn"></div>
                            @include('stories.parts.stats', ['story' => $story])
                        </div>
                    </a>
                @endforeach
            </div>
        @else
            @if(!isset($_GET['type']))
                <div class="profile-statistics text-center">
                    Вы не поставили лайки
                </div>
            @endif

        @endif


        @if(count($campaigns))
            <div class="account__banks flex">
                @foreach($campaigns as $nc)
                    @php
                        $percent_raised = $nc->percent_raised();
                    @endphp
                    <a
                            href="{{route('campaign_single', $nc->slug)}}"
                            class="bank__item catalog__content-item"
                    >
                        <img src="{{ $nc->feature_img_url()->feature_image }}" alt=""/>
                        <div class="bank__content">
                            <div class="bank__title">
                                <div class="bank__title-text">
                                    {{$nc->title}}
                                </div>
                                <span class="bank__title-blur"
                                >{{$nc->title}}</span
                                >
                            </div>
                            <div class="bank__purpose">
                                Цель: {!! get_amount($nc->goal) !!}
                                <span class="bank__purpose-blur">Цель: {!! get_amount($nc->goal) !!}</span>
                            </div>
                            <div class="bank__text">Прогресс: {!! $percent_raised !!}%</div>
                            <div class="bank__text">Осталось дней: - ∞</div>
                            <div class="bank__text">Финансировано: {!! get_amount($nc->success_payments->sum('amount')) !!}</div>

                            <div class="bank__user">
                                <img class="bank__img magnific_image circle-img" src="{!! $nc->user->avatar() !!}" data-image="{!! $nc->user->avatar() !!}"/>
                                <div class="bank__user-text">{{$nc->user->fullname}}</div>
                            </div>
                        </div>
                    </a>
                @endforeach
            </div>
        @else
            @if(isset($_GET['type']))
                <div class="profile-statistics text-center">
                   Вы не поставили лайки
                </div>
            @endif
        @endif


        @if(count($campaigns) > count($stories))
            {{$campaigns->links()}}
        @else
            @if(count($stories))
            {{$stories->links()}}
            @endif
        @endif
    </main>

@endsection

@section('page-js')
    <script>
        $(document).ready(function () {
            $('body').on('click', '.comment_action', function (e) {
                e.preventDefault();

                var $that = $(this);
                if ($that.attr('data-action') === 'trash') {
                    if (!confirm("<?php echo trans('app.are_you_sure'); ?>")) {
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

                            if (action == 'approve') {
                                alert('Сторис подтверждена');
                                $that.remove();
                            } else {
                                alert('Сторис отклонена');
                                $that.parents('.comment-item').remove();
                            }

                        }
                    }
                });
            });
        });
    </script>
    @include('stories.modal')
@endsection

@push('after_scripts')
{{--    @include('dashboard.stories.stories_scripts')--}}
@endpush
