@extends('layouts.neon.app')

@section('title') {{'Сторис на платформе Deels - Заработок онлайн через создание контента для творческих людей'}} @parent @endsection
@push('meta-data')
    <meta name="description" content="Сторис  |  Deels.ru  -  платформа для творчества и продвижения контента | Заработок на сторис  | Участие в челленджах  |  Растущие сообщество талантливых создателей и энтузиастов">
@endpush
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
    <div class="catalog">
        <div class="container">
            <h1 class="mb-7">
                {{isset($_GET['type']) && isset($types[$_GET['type']]) ? $types[$_GET['type']].' сторис' : 'Сторис'}}
            </h1>
            <style>
                .copystories-list.catalog-list {
                    grid-template-columns: 1fr 1fr 1fr 1fr;
                }

                #stories-bottom-loader {
                    display: none;
                    align-items: center;
                    justify-content: center;
                    gap: 10px;
                    margin: 16px 0 24px;
                    color: #9aa0a6;
                }

                #stories-bottom-loader .loader-dot {
                    width: 10px;
                    height: 10px;
                    border-radius: 50%;
                    background: #00F0FF;
                    animation: storiesLoaderPulse 1.1s infinite ease-in-out;
                }

                #stories-bottom-loader .loader-dot:nth-child(2) {
                    animation-delay: 0.15s;
                }

                #stories-bottom-loader .loader-dot:nth-child(3) {
                    animation-delay: 0.3s;
                }

                @keyframes storiesLoaderPulse {
                    0%, 80%, 100% { opacity: 0.35; transform: scale(0.85); }
                    40% { opacity: 1; transform: scale(1); }
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
                                <div class="__select__title" data-default="{{$types[$_GET['type'] ?? 'Все']}}">
                                    {{$types[$_GET['type'] ?? 'Все']}}
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
                <div id="stories-list" class="copystories-list catalog-list">
                    @include('stories.partials.list_items', ['stories' => $stories])
                </div>
            @endif
            <div id="stories-bottom-loader">
                <span class="loader-dot"></span>
                <span class="loader-dot"></span>
                <span class="loader-dot"></span>
                <span>Загружаем сторис...</span>
            </div>
            <div id="stories-scroll-sentinel" class="mb-4 mt-4"></div>
            <div class="mb-4 mt-4 text-center" id="stories-more-wrap" style="display:none;">
                <button type="button" class="btn btn-small" id="stories-more-button">Показать еще</button>
            </div>
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

        (function initStoriesLoadMore() {
            let currentPage = {{ (int) $stories->currentPage() }};
            let hasMore = {{ $stories->hasMorePages() ? 'true' : 'false' }};
            let isLoading = false;
            let autoLoadsUsed = 0;
            const maxAutoLoads = 3;
            const $list = $('#stories-list');
            const $sentinel = $('#stories-scroll-sentinel');
            const $moreWrap = $('#stories-more-wrap');
            const $moreButton = $('#stories-more-button');
            const $loader = $('#stories-bottom-loader');

            if (!$list.length) {
                return;
            }

            window.storyCatalogPagination = {
                currentPage: currentPage,
                hasMore: hasMore
            };

            function setControlsState() {
                if (!hasMore) {
                    $sentinel.hide();
                    $moreWrap.hide();
                    return;
                }

                if (autoLoadsUsed >= maxAutoLoads) {
                    $sentinel.hide();
                    $moreWrap.show();
                    return;
                }

                $moreWrap.hide();
                $sentinel.show();
            }

            function buildRequestData(nextPage) {
                const params = new URLSearchParams(window.location.search);
                const data = { page: nextPage };
                const excludeIds = [];

                $list.find('[data-story]').each(function () {
                    const storyId = parseInt($(this).attr('data-story'), 10);
                    if (!Number.isNaN(storyId) && excludeIds.indexOf(storyId) === -1) {
                        excludeIds.push(storyId);
                    }
                });

                params.forEach((value, key) => {
                    if (key !== 'page') {
                        data[key] = value;
                    }
                });

                if (excludeIds.length) {
                    data.exclude_ids = excludeIds.join(',');
                }

                return data;
            }

            function loadStories(triggerType) {
                if (isLoading || !hasMore) {
                    return;
                }

                if (triggerType === 'scroll' && autoLoadsUsed >= maxAutoLoads) {
                    setControlsState();
                    return;
                }

                isLoading = true;
                $loader.css('display', 'flex');
                $moreButton.prop('disabled', true).text('Загрузка...');

                $.ajax({
                    url: '{{ route('stories.catalog') }}',
                    type: 'GET',
                    dataType: 'json',
                    data: buildRequestData(currentPage + 1),
                    success: function (response) {
                        if (!response || !response.success) {
                            return;
                        }

                        if (response.html) {
                            const $incoming = $('<div>').html(response.html);
                            $incoming.find('[data-story]').each(function () {
                                const storyId = $(this).attr('data-story');
                                if (!storyId) {
                                    return;
                                }

                                if ($list.find('[data-story="' + storyId + '"]').length) {
                                    $(this).remove();
                                }
                            });

                            $list.append($incoming.html());
                        }

                        const serverPage = parseInt(response.current_page, 10);
                        if (Number.isFinite(serverPage) && serverPage > currentPage) {
                            currentPage = serverPage;
                        } else {
                            currentPage += 1;
                        }
                        hasMore = !!response.has_more;
                        window.storyCatalogPagination.currentPage = currentPage;
                        window.storyCatalogPagination.hasMore = hasMore;

                        if (triggerType === 'scroll') {
                            autoLoadsUsed += 1;
                        }
                    },
                    complete: function () {
                        isLoading = false;
                        $loader.hide();
                        $moreButton.prop('disabled', false).text('Показать еще');
                        setControlsState();
                    }
                });
            }

            if ('IntersectionObserver' in window) {
                const observer = new IntersectionObserver(function (entries) {
                    entries.forEach(function (entry) {
                        if (entry.isIntersecting) {
                            loadStories('scroll');
                        }
                    });
                }, {
                    rootMargin: '200px 0px 200px 0px'
                });

                if ($sentinel.length) {
                    observer.observe($sentinel[0]);
                }
            }

            $moreButton.on('click', function () {
                loadStories('button');
            });

            setControlsState();
        })();


        function submitFilter() {
            location.href = '/stories?' +
                (filter.type.length > 1 ? `type=${filter.type}` : '') +
                (filter.category >= 1 ? `category=${filter.category}` : '') +
                (filter.days_left.length > 1 ? `&days_left=${filter.days_left}` : '')
        }
    </script>
{{--    @include('dashboard.stories.stories_scripts')--}}

@endpush
