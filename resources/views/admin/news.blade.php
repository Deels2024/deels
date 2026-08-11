@extends('layouts.new.app')

@section('title') @if(! empty($title)) {!!$title!!} @endif  @parent @endsection

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
                    <li>Новости</li>
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
                <div class="cabinet__right-title">Новости</div>
                <div class="cabinet__filter filter-mobile btn">
                    <img src="/images/icons/filter.svg" alt="">
                    <span>Открыть меню</span>
                </div>
                <div class="cabinet__box">
                    <div class="cabinet__top flex">
                        <div class="cabinet__caption">Новости</div>
                        {{--                        <div class="cabinet__selects flex">--}}
                        {{--                            <div class="cabinet__selects-caption">Сортровать:</div>--}}
                        {{--                            <div class="select">--}}
                        {{--                                <div class="select-title flex">--}}
                        {{--                                    <div class="select-title__value text">По умолчанию</div>--}}
                        {{--                                    <div class="select-title__arrow img-contain">--}}
                        {{--                                        <img src="/images/icons/sidebar-arrow.svg" alt="">--}}
                        {{--                                    </div>--}}
                        {{--                                </div>--}}
                        {{--                                <div class="select-options">--}}
                        {{--                                    <div class="select-options__value text" data-value="1">По умолчанию</div>--}}
                        {{--                                    <div class="select-options__value text" data-value="2">По популярности</div>--}}
                        {{--                                    <div class="select-options__value text" data-value="3">По дате</div>--}}
                        {{--                                    <input type="hidden" name="type" value="1">--}}
                        {{--                                </div>--}}
                        {{--                            </div>--}}
                        {{--                        </div>--}}
                    </div>
                    @if($news->count() > 0)
                        <div class="cabinet__scroll">
                            <div class="cabinet-table">
                                <div class="cabinet-table__thead">
                                    <div class="cabinet-table__tr flex">
                                        <div class="cabinet-table__ht">#</div>
                                        <div class="cabinet-table__ht">Название</div>
                                        <div class="cabinet-table__ht">Дата создания</div>
                                        <div class="cabinet-table__ht">Дата изменения</div>
                                    </div>
                                </div>
                                @foreach($news as $newsItem)
                                    <div class="cabinet-table__tbody">
                                        <div class="cabinet-table__tr flex">
                                            <div class="cabinet-table__td">
                                                {{$newsItem->id}}
                                            </div>
                                            <div class="cabinet-table__td">
                                                <a href="{{route('new_edit_page', $newsItem->id)}}">{!!$newsItem->title!!}</a>
                                            </div>
                                            <div class="cabinet-table__td">{!!$newsItem->created_at->format('d.m.Y')!!}</div>
                                            <div class="cabinet-table__td">{!!$newsItem->updated_at->format('d.m.Y')!!}</div>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        </div>

                        @if ($news->lastPage() > 1)
                            <nav class="navigation pagination" role="navigation">
                                <div class="nav-links">
                                    <a class="prew page-numbers" href="{{ ($news->currentPage() === 1) ? ' javascript:void(0)' : $news->previousPageUrl() }}">
                                        <i>
                                            <svg width="20" height="20" viewBox="0 0 20 20" fill="none" xmlns="http://www.w3.org/2000/svg">
                                                <path d="M6.53223 16.82C6.8047 17.102 7.25818 17.102 7.54017 16.82C7.81264 16.5475 7.81264 16.0941 7.54017 15.8222L2.42934 10.7114L19.2944 10.7114C19.6875 10.7108 20 10.3983 20 10.0051C20 9.612 19.6875 9.28935 19.2944 9.28935L2.42934 9.28935L7.54017 4.18805C7.81264 3.90606 7.81264 3.45194 7.54017 3.18011C7.25818 2.89812 6.80407 2.89812 6.53223 3.18011L0.211497 9.50085C-0.0704969 9.77331 -0.0704969 10.2268 0.211497 10.4986L6.53223 16.82Z" fill="#999999"/>
                                            </svg>
                                        </i>
                                    </a>
                                    @for ($i = 1; $i <= $news->lastPage(); $i++)
                                        @if ($i===$news->currentPage())
                                            <span class="page-numbers current"><span class="meta-nav screen-reader-text"></span>{{$i}}</span>
                                        @else
                                            <a class="page-numbers" href="{{$news->url($i)}}">
                                                <span class="meta-nav screen-reader-text"></span>
                                                {{$i}}
                                            </a>
                                        @endif
                                    @endfor
                                    <a class="next page-numbers" href="{{ ($news->currentPage() === $news->lastPage()) ? ' javascript:void(0)' : $news->nextPageUrl() }}">
                                        <i>
                                            <svg width="20" height="20" viewBox="0 0 20 20" fill="none" xmlns="http://www.w3.org/2000/svg">
                                                <path d="M13.4678 3.18C13.1953 2.89801 12.7418 2.89801 12.4598 3.18C12.1874 3.45247 12.1874 3.90595 12.4598 4.17778L17.5707 9.28861H0.705621C0.31248 9.28924 0 9.60172 0 9.99486C0 10.388 0.31248 10.7106 0.705621 10.7106H17.5707L12.4598 15.8119C12.1874 16.0939 12.1874 16.5481 12.4598 16.8199C12.7418 17.1019 13.1959 17.1019 13.4678 16.8199L19.7885 10.4992C20.0705 10.2267 20.0705 9.77321 19.7885 9.50137L13.4678 3.18Z" fill="#999999"/>
                                            </svg>
                                        </i>
                                    </a>
                                </div>
                            </nav>
                        @endif
                    @endif
                </div>
            </div>
        </div>
    </section>
@endsection
