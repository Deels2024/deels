@extends('layouts.neon.app')

@section('title') {{$title ?? 'Профиль пользователя '.$user->fullname}}  @parent @endsection

@section('content')
    <div class="background">
        <div class="background__filter">
            <div class="account">
                <div class="container account__container">
                    <section class="profile-section">
                        <div class="profile-section-header">
                            <h3 class="mb">
                                Профиль пользователя
                            </h3>

                            @if(Auth::user())
                                <div class="story__button-list">
                                    <button class="story__button story__button--light story__button_copy-link follow_button {{Auth::user()->isFollowing($user) ? 'active' : ''}}" data-user="{{$user->id}}" type="button"></button>
                                    <button class="story__button story__button--light story__button_copy-link chat_btn" data-user="{{$user->id}}" type="button"></button>
                                </div>
                            @endif
                        </div>


                        <div class="profile_block_top">

                            <div class="profile_block">
                                <img class="profile_block__avatar profile-avatar avatar avatar--lg" style="background-image: url({!! $user->avatar() !!}" data-image="{!! $user->avatar() !!})"/>
                                <div class="profile_block__info">
                                    <div class="profile_block__name">{{$user->fullname}}</div>
                                    @if($user->status)
                                        <span class="profile_block__status">{{$user->status}}</span>
                                    @endif
                                </div>
                            </div>

                            <div class="profile_block__stats">
                                <div class="profile_block__stats__item">
                                    <div class="profile_block__stats__title">Создано копилок</div>
                                    <div class="profile_block__stats__number">{{count($campaigns)}}</div>
                                </div>
                                <div class="profile_block__stats__item">
                                    <div class="profile_block__stats__title">Отправлено донатов</div>
                                    <div class="profile_block__stats__number">{{number_format(abs($transactions), 0, ',', ',')}}</div>
                                </div>
                                <div class="profile_block__stats__item">
                                    <div class="profile_block__stats__title">Получено лайков</div>
                                    <div class="profile_block__stats__number">{{$likes_count}}</div>
                                </div>
                            </div>

                        </div>


                        @if(count($campaigns) > 0)
                            <div class="profile_block__relations">
                            <h3 class="profile_block__relations__title">
                               Копилки
                            </h3>
                            <div class="owl-carousel owl-theme profile__carousel">
                                @foreach($campaigns as $campaign)
                                    @php
                                        $percent_raised = $campaign->percent_raised();
                                    @endphp
                                    <a href="{{route('campaign_single', $campaign->slug)}}" class="profile__campaign bank__item">
                                        <div class="lozad profile__campaign_image profile__campaign_block" style="background-image: url({{ $campaign->feature_img_url()->thumbnail ?? $campaign->feature_img_url()->feature_image }})"/></div>
                                        <div class="bank__content profile__campaign_block">
                                            <div class="bank__title">
                                                <div class="bank__title-text">
                                                    {{$campaign->title}}
                                                </div>
                                                <span class="bank__title-blur"
                                                >{{$campaign->title}}</span
                                                >
                                            </div>
                                            <div class="bank__purpose">
                                                Цель: {!! get_amount($campaign->goal) !!}
                                                <span class="bank__purpose-blur">Цель: {!! get_amount($campaign->goal) !!} </span>
                                            </div>
                                            <div class="bank__text">Прогресс: {!! $campaign->percent_raised() !!}%</div>
                                            <div class="bank__text">Осталось дней: - ∞</div>
                                            <div class="bank__text">
                                                Финансировано: {!! get_amount($campaign->success_payments->sum('amount')) !!}</div>
                                        </div>
                                    </a>
                                @endforeach
                            </div>
                        </div>
                        @endif
                        @if($contests->isNotEmpty() || $hiddenContestsCount > 0)
                            <div class="profile_block__relations">
                                <h3 class="profile_block__relations__title">
                                    Челленджи и батлы
                                </h3>
                                <div class="owl-carousel owl-theme index-carousel profile-contests-carousel">
                                    @foreach($contests as $contest)
                                        @if($contest->profile_contest_type === 'battle')
                                            @include('battles.battle_item', [
                                                'battle' => $contest,
                                                'route' => route('battle_page', $contest->id),
                                            ])
                                        @else
                                            @include('challenges.challenge_item', [
                                                'challenge' => $contest,
                                                'route' => route('challenge_page', $contest->id),
                                            ])
                                        @endif
                                    @endforeach
                                    @if($hiddenContestsCount > 0)
                                        <div class="challenge-card profile-contests-hidden-card">
                                            <span>Еще {{$hiddenContestsCount}} {{trans_choice('numbers.challenge_battles', $hiddenContestsCount)}}</span>
                                        </div>
                                    @endif
                                </div>
                            </div>
                        @endif
                        @if(count($stories) > 0)
                            <div class="profile_block__relations">
                                <h3 class="profile_block__relations__title">
                                    Сторис
                                </h3>
                                <div class="owl-carousel owl-theme index-carousel profile-stories-carousel">
                                    @foreach($stories as $story)
                                        @include('stories.story_item', ['story' => $story])
                                    @endforeach
                                </div>
                            </div>
                        @endif

                    </section>
                </div>
            </div>
        </div>
    </div>
    @include('stories.modal')
@endsection
@push('after_scripts')
<script>
    $('.index-carousel').owlCarousel({
        margin: 20,
        loop: false,
        dots: true,
        nav: true,
        slideBy: 'page',
        responsive: {
            0: {
                items: 1
            },
            480: {
                items: 2
            },
            768: {
                items: 3
            },
            1024: {
                items: 4
            },
            1280: {
                items: 5
            }
        },
        navText: [
            '<svg xmlns="http://www.w3.org/2000/svg" width="16" height="33" viewBox="0 0 16 33" fill="none"><path d="M15 32L1.70148 18.5601C1.4869 18.3459 1.30952 18.0436 1.1866 17.6826C1.06368 17.3216 0.999397 16.9142 1.00001 16.5C0.999397 16.0858 1.06368 15.6784 1.1866 15.3174C1.30952 14.9564 1.4869 14.6541 1.70148 14.4399L15 1" stroke="#00F0FF"/></svg>',
            '<svg xmlns="http://www.w3.org/2000/svg" width="16" height="33" viewBox="0 0 16 33" fill="none"><path d="M1 32L14.2985 18.5601C14.5131 18.3459 14.6905 18.0436 14.8134 17.6826C14.9363 17.3216 15.0006 16.9142 15 16.5C15.0006 16.0858 14.9363 15.6784 14.8134 15.3174C14.6905 14.9564 14.5131 14.6541 14.2985 14.4399L1 1" stroke="#00F0FF"/></svg>'
        ],
    });
    $('body').on('click', '.follow_button', function (e) {
        e.preventDefault();
        var like_btn = $(this);
        var follow_id = $(this).attr('data-user');
        $.ajax({
            type: 'POST',
            url: '{{route('user.follow_toggle')}}',
            data: {user_id: '{{Auth::user()->id ?? null}}', follow_id: follow_id},
            success: function (data) {
                if(data.success) {
                    $('.alert-container').html('<div class="alert success"> <span class="closebtn">&times;</span>'+data.message+'</div>');
                    like_btn.toggleClass('active');
                } else {
                    $('.alert-container').html('<div class="alert danger"> <span class="closebtn">&times;</span>'+data.error+'</div>');
                }
            }
        });
    });
</script>
@endpush
@section('page-css')
    <link rel="stylesheet" href="/assets/css/style_emoji.css">
    <link rel="stylesheet" href="/dist/css/new_campaign/index.css">
    <style>
        .profile-contests-hidden-card {
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 24px;
            color: #fff;
            text-align: center;
            font-size: 16px;
            font-weight: 600;
            line-height: 1.3;
            border: 1px solid rgba(0, 240, 255, .7);
        }
        .profile-contests-hidden-card::before,
        .profile-contests-hidden-card::after {
            display: none;
        }
        .profile-contests-hidden-card span {
            position: relative;
            z-index: 2;
        }
        .profile-contests-carousel .challenge-card,
        .profile-stories-carousel .challenge-card {
            width: 100% !important;
        }
        .profile-contests-carousel .owl-dot span,
        .profile-stories-carousel .owl-dot span {
            width: 6px;
            height: 6px;
            margin: 0 3px;
        }
    </style>
@endsection
