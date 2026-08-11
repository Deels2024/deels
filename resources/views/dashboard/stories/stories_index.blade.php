@extends('layouts.admin.app_neon')

@section('title') {{$title ?? 'Мои сторис'}}  @parent @endsection

@section('page-css')

    <link rel="stylesheet" href="/assets/css/style_emoji.css">
    <link rel="stylesheet" href="/dist/css/new_campaign/index.css">

@endsection
@section('content')
    <style>
        .blocked_image {
            position: relative;
            z-index: 1;
        }
        .blocked_image:before {
            z-index: 2;
            width: 100%;
            height: 100%;
            position: absolute;
            top: 0;
            bottom: 0;
            left: 0;
            right: 0;
            content: "❌";
            color: #ff0000;
            font-size: 100px;
            font-weight: 100;
            display: flex;
            align-content: start;
            justify-content: center;
            background: rgba(0, 0, 0, 0.5);
            flex-wrap: wrap;
            overflow: hidden;
        }
        .copystories-item__content {
            z-index: 3;
        }
    </style>
    <main class="account-main">
        <div class="account-main__head">
            <h1 class="account-main__title">Мои сторис</h1>
        </div>
        <div class="account-info">
            <a href="{{route('stories.create')}}" class="d-flex ai-center gap-6">
                <svg width="63" height="63" viewBox="0 0 63 63" fill="none" xmlns="http://www.w3.org/2000/svg">
                    <mask id="path-1-inside-1_773_2455" fill="white">
                        <path fill-rule="evenodd" clip-rule="evenodd" d="M31.4999 46.2879C39.667 46.2879 46.2878 39.6672 46.2878 31.5C46.2878 23.3329 39.667 16.7122 31.4999 16.7122C23.3328 16.7122 16.712 23.3329 16.712 31.5C16.712 39.6672 23.3328 46.2879 31.4999 46.2879ZM31.6134 24C31.6134 23.4478 31.1657 23 30.6134 23C30.0612 23 29.6134 23.4478 29.6134 24V29.6135H24C23.4477 29.6135 23 30.0612 23 30.6135C23 31.1658 23.4477 31.6135 24 31.6135H29.6134V37.227C29.6134 37.7793 30.0612 38.227 30.6134 38.227C31.1657 38.227 31.6134 37.7793 31.6134 37.227V31.6135H37.2269C37.7792 31.6135 38.2269 31.1658 38.2269 30.6135C38.2269 30.0612 37.7792 29.6135 37.2269 29.6135H31.6134V24Z"></path>
                    </mask>
                    <path fill-rule="evenodd" clip-rule="evenodd" d="M31.4999 46.2879C39.667 46.2879 46.2878 39.6672 46.2878 31.5C46.2878 23.3329 39.667 16.7122 31.4999 16.7122C23.3328 16.7122 16.712 23.3329 16.712 31.5C16.712 39.6672 23.3328 46.2879 31.4999 46.2879ZM31.6134 24C31.6134 23.4478 31.1657 23 30.6134 23C30.0612 23 29.6134 23.4478 29.6134 24V29.6135H24C23.4477 29.6135 23 30.0612 23 30.6135C23 31.1658 23.4477 31.6135 24 31.6135H29.6134V37.227C29.6134 37.7793 30.0612 38.227 30.6134 38.227C31.1657 38.227 31.6134 37.7793 31.6134 37.227V31.6135H37.2269C37.7792 31.6135 38.2269 31.1658 38.2269 30.6135C38.2269 30.0612 37.7792 29.6135 37.2269 29.6135H31.6134V24Z" fill="#00F0FF"></path>
                    <path d="M29.6134 29.6135V31.6135H31.6134V29.6135H29.6134ZM29.6134 31.6135H31.6134V29.6135H29.6134V31.6135ZM31.6134 31.6135V29.6135H29.6134V31.6135H31.6134ZM31.6134 29.6135H29.6134V31.6135H31.6134V29.6135ZM44.2878 31.5C44.2878 38.5626 38.5625 44.2879 31.4999 44.2879V48.2879C40.7716 48.2879 48.2878 40.7717 48.2878 31.5H44.2878ZM31.4999 18.7122C38.5625 18.7122 44.2878 24.4375 44.2878 31.5H48.2878C48.2878 22.2283 40.7716 14.7122 31.4999 14.7122V18.7122ZM18.712 31.5C18.712 24.4375 24.4374 18.7122 31.4999 18.7122V14.7122C22.2282 14.7122 14.712 22.2283 14.712 31.5H18.712ZM31.4999 44.2879C24.4374 44.2879 18.712 38.5626 18.712 31.5H14.712C14.712 40.7717 22.2282 48.2879 31.4999 48.2879V44.2879ZM30.6134 25C30.0612 25 29.6134 24.5523 29.6134 24H33.6134C33.6134 22.3432 32.2703 21 30.6134 21V25ZM31.6134 24C31.6134 24.5523 31.1657 25 30.6134 25V21C28.9566 21 27.6134 22.3432 27.6134 24H31.6134ZM31.6134 29.6135V24H27.6134V29.6135H31.6134ZM24 31.6135H29.6134V27.6135H24V31.6135ZM25 30.6135C25 31.1658 24.5523 31.6135 24 31.6135V27.6135C22.3431 27.6135 21 28.9567 21 30.6135H25ZM24 29.6135C24.5523 29.6135 25 30.0612 25 30.6135H21C21 32.2704 22.3431 33.6135 24 33.6135V29.6135ZM29.6134 29.6135H24V33.6135H29.6134V29.6135ZM31.6134 37.227V31.6135H27.6134V37.227H31.6134ZM30.6134 36.227C31.1657 36.227 31.6134 36.6747 31.6134 37.227H27.6134C27.6134 38.8838 28.9566 40.227 30.6134 40.227V36.227ZM29.6134 37.227C29.6134 36.6747 30.0612 36.227 30.6134 36.227V40.227C32.2703 40.227 33.6134 38.8838 33.6134 37.227H29.6134ZM29.6134 31.6135V37.227H33.6134V31.6135H29.6134ZM37.2269 29.6135H31.6134V33.6135H37.2269V29.6135ZM36.2269 30.6135C36.2269 30.0612 36.6746 29.6135 37.2269 29.6135V33.6135C38.8838 33.6135 40.2269 32.2704 40.2269 30.6135H36.2269ZM37.2269 31.6135C36.6746 31.6135 36.2269 31.1658 36.2269 30.6135H40.2269C40.2269 28.9567 38.8838 27.6135 37.2269 27.6135V31.6135ZM31.6134 31.6135H37.2269V27.6135H31.6134V31.6135ZM29.6134 24V29.6135H33.6134V24H29.6134Z" fill="#00F0FF" mask="url(#path-1-inside-1_773_2455)"></path>
                    <path d="M16.5677 1H9C4.58172 1 1 4.58173 1 9V15.9323M1 46.4323V54C1 58.4183 4.58172 62 9 62H16.5677M62 46.4323V54C62 58.4183 58.4183 62 54 62H47.0677M62 15.9323V9C62 4.58172 58.4183 1 54 1H47.0677" stroke="#00F0FF" stroke-width="2"></path>
                </svg>
                <span class="fw-600 fz-5">Создать сторис</span>
            </a>
            <div class="d-block mb-4 mt-7">
                <ul class="main-content__switch lk_switch">
                    <li class="main-content__switch-link main-content__switch-link_donate {{!isset($_GET['type']) || isset($_GET['type']) && $_GET['type'] != 'participant' ? 'main-content__switch-link_active' : ''}}">
                        <a class="main-content__switch-link" href="{{ route('user_stories') }}">Все</a>
                    </li>
                    <li class="main-content__switch-link main-content__switch-link_comments {{isset($_GET['type']) && $_GET['type'] == 'participant' ? 'main-content__switch-link_active' : ''}}">
                        <a class="main-content__switch-link" href="{{ route('user_stories') }}?type=participant">Ответы на челленджи</a>
                    </li>
                </ul>
            </div>
            <style>
                @media (max-width: 768px) {
                    .mobile-list {
                        grid-template-columns: 1fr;
                    }
                }
            </style>
            @if(count($stories) > 0)
            <div class="copystories-list mb-7 mobile-list">

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
                    <a href="#story-popup" class="copystories-item show_story {{$story->declined ? 'blocked_image' : ''}}" data-route="{{route('stories.preview', ['id' => $story->id, 'user_id' => Auth::user()->id ?? null])}}" data-story="{{$story->id}}" data-type="{{$story->type}}" data-paid="{{$story->paid}}" data-amount="{{$story->amount}}">
                        @include('stories.parts.preview', ['story' => $story, 'class' => 'copystories-item__img'])
                        <div class="copystories-item__content">
                            <div class="play-btn copystories-btn"></div>
                                @if($story->blocked_at)
                                    <span style="color: #ff0000">[ Не оплачено ]</span>
                                @else

                                @if($story->declined)
                                    [ Отклонена ]
                                    @else
                                    {{!$story->active ? '[ На модерации ]' : ''}}
                                @endif

                                    @if($story->is_ad)
                                        [ Реклама ]
                                    @endif

                                @if($story->declined)
                                    <div style="margin:0 10px; padding: 5px 10px; font-size: 12px; color: #ff0000;background-color: rgba(68, 68, 68, 0.9);">
                                        {!! $story->getReasons()!!}
                                    </div>
                                @endif

                            @endif
                            @include('stories.parts.stats', ['story' => $story])
                            @if(!$story->is_ad)
                            <div class="story_delete" data-id="{{$story->id}}" style="display: flex;align-items: center">
                                <span class="actions-link" style="background-image: url(/dist/images/admin_icons/icon-del.svg); margin-right:5px" ></span>
                                Удалить
                            </div>
                            @endif
                        </div>
                    </a>
                @endforeach
            </div>
            @else
                <div class="profile-statistics text-center">
                    {{isset($_GET['type']) && $_GET['type'] == 'participant' ? 'Ответы на челленджи отсутствуют' : 'Сторис отсутствуют'}}
                    @if(isset($_GET['type']) && $_GET['type'] == 'participant')
                        <br>
                        <a href="{{route('challenges.catalog')}}?type=active" class="hero-btn hero-btn-dark mt-6">Участвовать в челленджах</a>
                    @endif
                </div>
            @endif
        </div>
    </main>
    @include('stories.modal')
@endsection
@push('after_scripts')
    <script>
        $(document).ready(function () {
            $('.story_delete').on('click', function (e) {
                e.preventDefault();
                e.stopImmediatePropagation();

                var $that = $(this);
                if (!confirm("<?php echo trans('app.are_you_sure'); ?>")) {
                    return false;
                }

                var story_id = $(this).attr('data-id');

                $.ajax({
                    type: 'POST',
                    url: '{{ route('stories.remove') }}',
                    data: {story_id: story_id},
                    success: function (data) {
                        if (data.success) {
                            $that.parents('.copystories-item').remove();
                            $('.alert-container').html('<div class="alert success"> <span class="closebtn">&times;</span>Сторис удалена!</div>')
                        } else {
                            if(data.error) {
                                alert(data.error)
                            } else {
                                alert('Невозможно удалить сторис')
                            }

                        }
                    }
                });
            });
        });
    </script>


@endpush
