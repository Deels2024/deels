@extends('layouts.neon.app')

@section('title') {{$title ?? 'Сторис'}}  @parent @endsection

@section('content')

    @php
        $types = [
            'Все' => 'Все',
            'new' => 'Новые',
            'paid' => 'Платные',
            'popular' => 'Популярные',
];
    @endphp

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
            @foreach($categories as $category)
                <li data-id="{{$category->id}}">{{$category->category_name}}</li>
            @endforeach
        </ul>
        <div class="catalog__filter-title">Осталось дней</div>
        <div id="slider-range-mobile"></div>
    </div>
    <div class="catalog">
        <div class="container">
            <h1 class="mb-7">
                {{isset($_GET['type']) && isset($types[$_GET['type']]) ? $types[$_GET['type']].' сторис' : 'Сторис'}}
            </h1>
            <style>
                .copystories-list.catalog-list {
                    grid-template-columns: 1fr 1fr 1fr 1fr;
                }
            </style>
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

            <div class="catalog__filter flex">
                <div class="catalog__mobile catalog__filter-open">
                    Открыть фильтр
                </div>

                <div class="catalog__selects catalog__desk">
                    <div class="catalog__select">
                        <form>

                            <div class="__select" data-state="">
                                <div class="__select__title" data-default="{{isset($_GET['type']) && $types[$_GET['type']] ? $types[$_GET['type']] : 'Все'}}">
                                    {{isset($_GET['type']) && $types[$_GET['type']] ? $types[$_GET['type']] : 'Все'}}
                                </div>
                                <div class="__select__content">
                                    <input data-type="funded" id="singleSelect0" class="__select__input" type="radio"
                                           checked name="singleSelect"/>
                                    <label for="singleSelect0" class="__select__label">Финансируемые</label>

                                    <input data-type="new" id="singleSelect1" class="__select__input" type="radio"
                                           name="singleSelect"/>
                                    <label for="singleSelect1" class="__select__label">Новые</label>

                                    <input data-type="paid" id="singleSelect2" class="__select__input" type="radio"
                                           name="singleSelect"/>
                                    <label for="singleSelect2" class="__select__label">Платные</label>


                                    <input data-type="popular" id="singleSelect3" class="__select__input" type="radio"
                                           name="singleSelect"/>
                                    <label for="singleSelect3" class="__select__label">Популярные</label>

                                </div>
                            </div>
                        </form>
                    </div>
                </div>
                @if(isset($_GET['type']))
                    <div class="filter__cancel catalog__desk">Сбросить фильтры</div>
                @endif
            </div>
            @if($stories)
                <div class="copystories-list catalog-list">
                    @foreach($stories as $story)
                        @include('stories.story_item', ['story' => $story, 'list' => true])
                    @endforeach
                </div>
            @endif
            @if ($stories->lastPage() > 1)
                <div class="mb-4 mt-4">
                    {{$stories->links()}}
                </div>
            @else
                <div class="mb-4"></div>
            @endif
        </div>
    </div>
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
                            alert('Невозможно удалить сторис')
                        }
                    }
                });
            });
        });


        let searchParams = new URLSearchParams(window.location.search)

        let filter = {
            type: searchParams.has('type') ? searchParams.get('type') : '',
            category: searchParams.has('category') ? searchParams.get('category') : '',
            days_left: searchParams.has('days_left') ? searchParams.get('days_left') : ''
        };

        $('.filter__cancel ').click(function () {
            filter = {
                type: '',
                category: '',
                days_left: ''
            };

            submitFilter();
        });

        $('.__select__input').change(function () {
            filter.type = $(this).data('type')
            submitFilter();
        });


        function submitFilter() {
            location.href = '/stories?' +
                (filter.type.length > 1 ? `type=${filter.type}` : '') +
                (filter.category >= 1 ? `category=${filter.category}` : '') +
                (filter.days_left.length > 1 ? `&days_left=${filter.days_left}` : '')
        }
    </script>
{{--    @include('dashboard.stories.stories_scripts')--}}

@endpush
