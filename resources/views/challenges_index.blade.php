@extends('layouts.neon.app')

@section('title') {{'Челленджи на платформе Deels - Заработок онлайн на контенте для творческих людей'}} @parent @endsection
@push('meta-data')
    <meta name="description" content="Челленджи  |  Deels.ru  -  платформа для творчества и продвижения контента | Заработок на сторис  | Участие в челленджах  |  Растущие сообщество талантливых создателей и энтузиастов">
@endpush
@section('content')

    @php
        $types = [
            'all' => 'Челленджи',
            'active' => 'Активные',
            'participant' => 'Я участвую',
            'finished' => 'Завершенные',
];
    @endphp

    <div class="background">
        <div class="background__filter">
            <div class="account">
                <div class="container account__container">
                    <section class="challenge pb-8">
                        <h1 class="challenge__title text-center mb-8">
                            <img src="/dist/images/party.png" class="smile smile-party" srcset="/dist/images/party-ret.png 2x" alt="смайл вечеринка">
                            Участвуй в челленджах и заработай на мечту
                            <img src="/dist/images/thumbs-up.png" class="smile smile-thumbs-up" srcset="/dist/images/thumbs-up.png 2x" alt="смайл палец вверх">
                        </h1>
                        <div class="steps__list d-flex mb-8 jc-between gap-2">
                            <div class="steps__item pl-6 pt-6 pr-6">
                                <h3 class="steps-item__name mb-4">Участвуй в челлендже</h3>
                                <div class="steps-item__text">Найди челлендж, который тебе нравится, и нажми “Участвовать”.</div>
                            </div>
                            <div class="steps__item pl-6 pt-6 pr-6">
                                <h3 class="steps-item__name mb-4">Загрузи свой видеоответ</h3>
                                <div class="steps-item__text">Сними сторис на тему, указанную
                                    в челлендже и загрузи свое видео
                                    на платформу</div>
                            </div>
                            <div class="steps__item pl-6 pt-6 pr-6">
                                <h3 class="steps-item__name mb-4">Жди результатов</h3>
                                <div class="steps-item__text">Участвуй и получай щедрое вознаграждение!</div>
                            </div>
                        </div>

                        <style>
                            body{
                                background-image: url(/dist/images/challendges_desctop.webp);
                            }
                            @media screen and (max-width: 480px) {
                                body{
                                    background-image: url(/dist/images/challendges_mobile.webp);
                                }
                                @media
                                (-webkit-min-device-pixel-ratio: 2),
                                (min-resolution: 2dppx) {
                                    background-image: url(/dist/images/challendges_mobile-куе.webp);
                                }
                            }
                            @media
                            (-webkit-min-device-pixel-ratio: 2),
                            (min-resolution: 2dppx) {
                                background-image: url(/dist/images/challendges_desctop-ret.webp);
                            }

                            .smile{
                                position: absolute;
                                width: 4rem;
                                height: 4rem;
                            }
                            .smile-party{
                                top:-1rem;
                                left: 6%;
                            }
                            .smile-thumbs-up{
                                top:-1rem;
                                right: 6%;
                            }
                            .steps__list{
                                overflow-x: scroll;
                            }
                            .steps__list::-webkit-scrollbar {
                                display: none;
                            }
                            .steps__item{
                                width: 22.5rem;
                                min-height: 9rem;
                                border-radius: 12px;
                                /*background: -webkit-gradient(linear,  left top, right top,  from(#2D104E), color-stop(43%, #232F80) , to(#8830A6));*/
                                /*background: -o-linear-gradient(left,  #2D104E 0%, #232F80 43% , #8830A6 100%);*/
                                /*background: linear-gradient(90deg,  #2D104E 0%, #232F80 43% , #8830A6 100%);*/
                                background: #8d47f6;
                                -ms-flex-negative: 0;
                                flex-shrink: 0;
                            }
                            .steps-item__text{
                                font-size: 1rem;
                                font-weight: 400;
                            }
                            @media screen and (max-width: 1100px) {
                                .smile-party{
                                    left: 0%;
                                }
                                .smile-thumbs-up{
                                    right: 0%;
                                }
                            }
                            @media screen and (max-width: 1000px) {
                                .smile-party{
                                    left: -6%;
                                }
                                .smile-thumbs-up{
                                    right: -6%;
                                }
                            }
                            @media screen and (max-width: 772px) {
                                .smile-party{
                                    left: 6%;
                                }
                                .smile-thumbs-up{
                                    right: 6%;
                                }
                            }
                            @media screen and (max-width: 420px) {
                                .smile-party{
                                    left: 12%;
                                    top: 8%;
                                    width: 1.5rem;
                                    height: 1.5rem;
                                }
                                .smile-thumbs-up{
                                    right: 12%;
                                    top: 8%;
                                    width: 1.5rem;
                                    height: 1.5rem;
                                }
                            }
                            @media screen and (max-width: 400px) {
                                .challenge__title{
                                    font-size: 32px;
                                    line-height: 39.2px;
                                }
                                .steps__item{
                                    width: 17.5rem;
                                    padding: 0.75rem;
                                    min-height: auto;
                                }
                            }
                        </style>
                        <h2 class="pt-8"> {{isset($_GET['type']) && isset($types[$_GET['type']]) ? $types[$_GET['type']] : 'Челленджи'}}</h2>
                        <div class="d-flex jc-between ai-center challenge-top">
                            <div class="challenge-top__list d-flex ai-center gap-4">
                                <a href="{{route('challenges.catalog')}}?type=all" class="btn challenge-top__btn {{isset($_GET['type']) && $_GET['type'] == 'all' || !isset($_GET['type']) ? 'challenge-top__btn--active' : ''}}">Все</a>
                                <a href="{{route('challenges.catalog')}}?type=active" class="btn challenge-top__btn {{isset($_GET['type']) && $_GET['type'] == 'active' ? 'challenge-top__btn--active' : ''}}">Активные</a>
                                @if(auth()->user())
                                <a href="{{route('challenges.catalog')}}?type=participant" class="btn challenge-top__btn {{isset($_GET['type']) && $_GET['type'] == 'participant' ? 'challenge-top__btn--active' : ''}}">Я участвую</a>
                                @endif
                                <a href="{{route('challenges.catalog')}}?type=finished" class="btn challenge-top__btn {{isset($_GET['type']) && $_GET['type'] == 'finished' ? 'challenge-top__btn--active' : ''}}">Завершенные</a>
                            </div>
                            <style>
                                .challenge-top__list{
                                    overflow-x: scroll;
                                }
                                .challenge-top__list::-webkit-scrollbar {
                                    display: none;
                                }
                                .challenge-top__btn{
                                    color:#8D46F6;
                                    background: transparent;
                                    font-weight: 400;
                                    font-size: 1.25rem;
                                    padding: 12px;
                                    border:1px solid #8D46F6;
                                    white-space: nowrap;
                                    border-radius: 20px;
                                }
                                .challenge-top__btn::before{
                                    background: transparent;
                                }
                                .challenge-top__btn:hover{
                                    background: transparent;
                                }
                                .challenge-top__btn:hover::before{
                                    background: transparent;
                                }
                                .challenge-top__btn--active{
                                    color: #fff;
                                    background: #8D46F6;
                                }
                                .challenge-top__btn--active:hover{
                                    background: #8D46F6;
                                }
                                @media screen and (max-width: 420px) {
                                    .challenge-top__btn{
                                        font-size: 1rem;
                                    }
                                }
                            </style>
                        </div>


                    @if($challenges && count($challenges))
                            <div class="challenge-grid" style="--challenge-grid: repeat(4, 1fr)">
                                @foreach($challenges as $challenge)
                                    @php($isBattle = $challenge->getTable() === 'battles')
                                    @include('challenges.challenge_item', ['route' => route($isBattle ? 'battle_page' : 'challenge_page', $challenge->id), 'isBattle' => $isBattle])
                                @endforeach
                            </div>
                        @else
                            @if(isset($_GET['type']) && isset($types[$_GET['type']]))
                                Таких челленджей пока нет!
                            @else
                                Челленджей пока нет!
                            @endif
                        @endif

                        <div class="d-flex ai-center jc-center pt-8">
                            <div class="account__pagination">
                                @if($challenges instanceof \Illuminate\Pagination\LengthAwarePaginator )
                                    {{$challenges->links()}}
                                @endif
                            </div>
                        </div>
                    </section>
                </div>
            </div>
        </div>
    </div>

    @include('stories.modal')
    @include('challenges.modal')
@endsection

@push('after_scripts')
    <script>
        function toggleFilterNav () {
            if($('.challenge-filter-btn').hasClass('active')) {
                $('.challenge-filter-btn').removeClass('active');
                $('.challenge-filter').fadeOut()
            } else {
                $('.challenge-filter-btn').addClass('active');
                $('.challenge-filter').fadeIn().css('display', 'flex')
            }
        };

        if(window.innerWidth < 787) {
            $('.challenge-filter-btn').click(toggleFilterNav);
            $('.challenge-filter input[type="radio"]').change(toggleFilterNav)
        }

        let searchParams = new URLSearchParams(window.location.search)

        let filter = {
            type: searchParams.has('type') ? searchParams.get('type') : '',
        };

        $('.filter__cancel ').click(function () {
            filter = {
                type: '',
            };
            submitFilter();
        });

        $('.challenge-filter input[type="radio"]').change(function () {
            filter.type = $(this).val()
            submitFilter();
        });

        function submitFilter() {
            location.href = '/challenges?' +
                (filter.type.length > 1 ? `type=${filter.type}` : '')
        }
    </script>
{{--    @include('dashboard.stories.stories_scripts')--}}

@endpush
