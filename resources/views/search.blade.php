@extends('layouts.neon.app')
@section('title')
    @if( ! empty($title))
        {{ $title }} |
    @endif @if(request('page'))
        Страница {{request('page')}}
    @endif @parent
@endsection

@section('content')
    <div class="background__dark"></div>
    <div class="catalog__filter-menu catalog__mobile">
        <a href="" id="filterClose">
            <img src="/dist/images/icons/close.svg"/>
        </a>
        <ul class="catalog__filter-list catalog__filter-banks">
            <li class="catalog__filter-title">Копилки</li>
            <li data-type="all">Все копилки</li>
            <li data-type="new" class="catalog__filter-active">Новые копилки</li>
            <li data-type="funded">Финансируемые</li>
            {{--            <li data-type="big">Большие копилки</li>--}}
        </ul>
        <ul class="catalog__filter-list catalog__filter-category">
            @foreach(\App\Models\Category::all() as $category)
                <li data-id="{{$category->id}}">{{$category->category_name}}</li>
            @endforeach
        </ul>
        <div class="catalog__filter-title">Осталось дней</div>
        <div id="slider-range-mobile"></div>
    </div>
    @if(count($campaigns) > 0)
        <div class="catalog">
            <div class="container">
                <h2>
                    Каталог копилок
                    <span>Каталог копилок</span>
                </h2>
                <div class="owl-carousel owl-theme catalog__carousel">
                    @foreach(\App\Models\Category::all() as $category)
                        <a class="catalog__item" data-id="{{$category->id}}">
                            <div class="catalog__item-img">
                                <img src="/dist/images/catalog/{{$loop->iteration}}.png"/>
                            </div>
                            <div class="catalog__subtitle">{{$category->category_name}}</div>
                        </a>
                    @endforeach
                </div>
                <div class="catalog__filter flex">
                    <div class="catalog__mobile catalog__filter-open">
                        Открыть фильтр
                    </div>

                    <div class="catalog__selects catalog__desk">
                        <div class="catalog__select">
                            <form>
                                <div class="__select" data-state="">
                                    <div class="__select__title" data-default="Копилки">
                                        Копилки
                                    </div>
                                    <div class="__select__content">
                                        <input
                                                id="singleSelect0"
                                                class="__select__input"
                                                type="radio"
                                                name="singleSelect"
                                                checked
                                        />
                                        <label for="singleSelect0" class="__select__label"
                                        >Копилки</label
                                        >
                                        <input
                                                data-type="all"
                                                id="singleSelect1"
                                                class="__select__input"
                                                type="radio"
                                                name="singleSelect"
                                        />
                                        <label for="singleSelect1" class="__select__label"
                                        >Все копилки</label
                                        >
                                        <input
                                                data-type="new"
                                                id="singleSelect2"
                                                class="__select__input"
                                                type="radio"
                                                name="singleSelect"
                                        />
                                        <label for="singleSelect2" class="__select__label"
                                        >Новые копилки</label
                                        >
                                        <input
                                                data-type="funded"
                                                id="singleSelect3"
                                                class="__select__input"
                                                type="radio"
                                                name="singleSelect"
                                        />
                                        <label for="singleSelect3" class="__select__label"
                                        >Финансируемые</label
                                        >
                                        <input
                                                data-type="big"
                                                id="singleSelect4"
                                                class="__select__input"
                                                type="radio"
                                                name="singleSelect"
                                        />
                                        <label for="singleSelect4" class="__select__label"
                                        >Большие копилки</label
                                        >
                                    </div>
                                </div>
                            </form>
                        </div>
                        <div class="catalog__range">
                            <form class="form-horizontal" method="post" id="form">
                                <div class="form-group">
                                    <label for="days" class="control-label"
                                    >Осталось дней</label
                                    >
                                    <div class="slider__wrape">
                                        <div id="slider-range"></div>
                                    </div>
                                </div>
                            </form>
                        </div>
                    </div>
                    <div class="filter__cancel catalog__desk">Сбросить фильтры</div>
                </div>
                <div class="catalog__content flex" style="justify-content: flex-start">
                    @foreach($campaigns as $nc)
                        @php
                            $percent_raised = $nc['percent_raised'] ?? '';
                        @endphp
                        <a href="{{route('campaign_single', $nc['slug'])}}" class="bank__item catalog__content-item">
                            <img src="{{ $nc['preview'] }}" alt=""/>
                            <div class="bank__content">
                                <div class="bank__title">
                                    <div class="bank__title-text">
                                        {{$nc['title']}}
                                    </div>
                                    <span class="bank__title-blur">{{$nc['title']}}</span>
                                </div>
                                <div class="bank__purpose">
                                    Цель: {!! get_amount($nc['goal']) !!}
                                    <span class="bank__purpose-blur">Цель: {!! get_amount($nc['goal']) !!}</span>
                                </div>
                                <div class="bank__text">Прогресс: {!! $percent_raised !!}%</div>
                                <div class="bank__text">Осталось дней: - ∞</div>
                                <div class="bank__text">
                                    Финансировано: {!! get_amount($nc['donated']) !!}</div>
                                <div class="bank__user">
                                    <img class="bank__img magnific_image circle-img"
                                         src="{!! $nc['user']['avatar'] !!}"/>
                                    <div class="bank__user-text">{{$nc['user']['fullname']}}</div>
                                </div>
                            </div>
                        </a>
                    @endforeach
                </div>
                @if ($campaigns->lastPage() > 1)

                    <div class="mb-4 mt-4">
                        {{$campaigns->links()}}
                    </div>
                @else
                    <div class="mb-4"></div>
                @endif
            </div>
        </div>
    @else
        <div class="catalog">
            <div class="container">
                <h2>
                    Каталог копилок
                    <span>Каталог копилок</span>
                </h2>
                <div class="catalog__content flex" style="justify-content: flex-start">
                    Копилки не найдены
                </div>
            </div>
        </div>
    @endif

    @if(count($users) > 0)
        <div class="catalog" style="margin-bottom: 80px">
            <div class="container">
                <h2>
                    Пользователи
                    <span>Пользователи</span>
                </h2>
                <div class="catalog__content flex" style="justify-content: flex-start">
                    @foreach($users as $user)
                        <a href="{{route('user.profile', $user->id)}}" class="bank__user user__block">
                            <img class="bank__img magnific_image circle-img tops-story__avatar"
                                 src="{!! $user->avatar() !!}" data-image="{!! $user->avatar() !!}"/>
                            <div class="bank__user-text">{{$user->fullname}}</div>
                        </a>
                    @endforeach
                </div>
                @if ($users->lastPage() > 1)

                    <div class="mb-4 mt-4">
                        {{$users->links()}}
                    </div>
                @else
                    <div class="mb-4"></div>
                @endif
            </div>

        </div>
    @else
        <div class="catalog" style="margin-bottom: 80px">
            <div class="container">
                <h2>
                    Пользователи
                    <span>Пользователи</span>
                </h2>
                <div class="catalog__content flex" style="justify-content: flex-start">
                    Пользователи не найдены
                </div>
            </div>
        </div>
    @endif

    <div class="catalog" style="margin-bottom: 80px">
        <div class="container">
            <h2>
                Сторис
                <span>Сторис</span>
            </h2>
            @if(count($stories) > 0)
                <div class="copystories-list catalog-list">
                    @foreach($stories as $story)
                        @include('stories.story_item', ['story' => $story, 'list' => true])
                    @endforeach
                </div>
                @if ($stories->lastPage() > 1)

                    <div class="mb-4 mt-4">
                        {{$stories->onEachSide(1)->links()}}
                    </div>
                @else
                    <div class="mb-4"></div>
                @endif
            @else
                <div class="catalog__content flex" style="justify-content: flex-start">
                    Сторис не найдены
                </div>
            @endif
        </div>
    </div>
@endsection

@section('additional_scripts')
    <link rel="stylesheet" href="/dist/css/nouislider.css"/>
    <script>
        let filter = {
            type: '',
            category: '',
            days_left: '',
        };

        $('.catalog__filter-list li').click(function () {
            $('.sidebar__menu li').removeClass('active');
            $(this).parent().addClass('active')
            submitFilter();
        });

        $('.catalog__item').click(function () {
            filter.category = $(this).data('id')
            if ($(this).data('type'))
                filter.type = $(this).data('type')
            submitFilter();
        });

        $('.__select__input').change(function () {
            filter.type = $(this).data('type')
            submitFilter();
        });

        slider.noUiSlider.set('end.one', function () {
            filter.days_left = slider.noUiSlider.get();
            submitFilter();
        });

        function submitFilter() {
            $.get('/campaigns/filter', filter, function (ans) {
                $('.catalog__filter').after(ans);
            });
        }
    </script>
@endsection
