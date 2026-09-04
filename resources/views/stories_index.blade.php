@extends('layouts.neon.app')

@section('title', 'Истории Deels — вертикальные видео, челленджи и баттлы')

@push('meta-data')
    <meta name="description" content="Смотрите вертикальные истории участников Deels, ответы на челленджи и баттлы. Публикуйте своё видео и получайте поддержку сообщества.">
@endpush

@section('content')
@php
    $activeType = request('type', 'all');
    $storyFilters = [
        'all' => 'Для вас',
        'popular' => 'Популярные',
        'new' => 'Новые',
        'paid' => 'За донаты',
    ];
@endphp

<main class="catalog source-feed-catalog">
    <section class="source-feed-hero" aria-labelledby="stories-title">
        <div class="container">
            @include('partials.deels.platform_switcher', ['activePlatform' => 'stories'])

            <div class="source-feed-hero__grid">
                <div>
                    <span class="source-feed-kicker"><i></i> DEELS LIVE</span>
                    <h1 id="stories-title">Истории,<br><span>которые двигают</span></h1>
                    <p>Вертикальные видео из челленджей и баттлов, живые результаты и идеи участников — в одной ленте.</p>
                    <div class="source-catalog-actions">
                        <a href="{{ route('stories.create') }}" class="button button-primary">+ Создать историю</a>
                        <a href="{{ route('challenges.catalog') }}" class="button button-glass">Найти челлендж</a>
                    </div>
                </div>

                <div class="source-feed-device" aria-hidden="true">
                    <div class="source-feed-device__screen">
                        <span class="source-feed-device__live">LIVE</span>
                        <strong>Твой момент<br>может стать<br>новым трендом</strong>
                        <div><span>▶</span><span>♡</span><span>◎</span></div>
                    </div>
                    <span class="source-feed-device__orbit source-feed-device__orbit--one">✦ Челлендж</span>
                    <span class="source-feed-device__orbit source-feed-device__orbit--two">⚡ Баттл</span>
                </div>
            </div>
        </div>
    </section>

    <section class="source-feed-content" aria-labelledby="stories-feed-title">
        <div class="container">
            <div class="source-feed-toolbar">
                <div>
                    <span class="source-feed-toolbar__eyebrow">Лента сообщества</span>
                    <h2 id="stories-feed-title">{{ $storyFilters[$activeType] ?? 'Истории' }}</h2>
                </div>
                <span class="source-feed-toolbar__count">{{ number_format((int) $stories->total(), 0, ',', ' ') }} публикаций</span>
            </div>

            <nav class="source-feed-filters" aria-label="Фильтр историй">
                @foreach($storyFilters as $type => $label)
                    <a href="{{ $type === 'all' ? route('stories.catalog') : route('stories.catalog', ['type' => $type]) }}"
                       class="{{ $activeType === $type ? 'active' : '' }}"
                       @if($activeType === $type) aria-current="page" @endif>
                        {{ $label }}
                    </a>
                @endforeach
            </nav>

            @if($stories->count())
                <div id="stories-list" class="copystories-list catalog-list" aria-live="polite">
                    @include('stories.partials.list_items', ['stories' => $stories])
                </div>

                <div id="stories-bottom-loader" role="status" aria-live="polite" hidden>
                    <span class="loader-dot"></span>
                    <span class="loader-dot"></span>
                    <span class="loader-dot"></span>
                    <span>Загружаем истории…</span>
                </div>
                <div id="stories-load-error" class="source-feed-load-error" role="alert" hidden>
                    <span>Не удалось продолжить ленту. Проверьте соединение и попробуйте ещё раз.</span>
                    <button type="button" id="stories-retry-button">Повторить</button>
                </div>
                <div id="stories-scroll-sentinel" aria-hidden="true"></div>
                <div id="stories-more-wrap" hidden>
                    <button type="button" class="button button-glass" id="stories-more-button">Показать ещё</button>
                </div>
            @else
                <div class="source-feed-empty" role="status">
                    <span aria-hidden="true">▶</span>
                    <h2>В этой ленте пока тихо</h2>
                    <p>Выберите другой фильтр или опубликуйте первую историю.</p>
                    <a href="{{ route('stories.create') }}" class="button button-primary">Создать историю</a>
                </div>
            @endif
        </div>
    </section>
</main>

@include('stories.modal')
@endsection

@push('after_scripts')
<script>
    $(function () {
        $(document).on('click', '.story_delete', function (event) {
            event.preventDefault();
            event.stopImmediatePropagation();

            const $trigger = $(this);
            if (!confirm(@json(trans('app.are_you_sure')))) {
                return;
            }

            $.ajax({
                type: 'POST',
                url: @json(route('stories.remove')),
                data: { story_id: $trigger.attr('data-id') },
                success: function (response) {
                    if (response && response.success) {
                        $trigger.closest('.copystories-item').remove();
                        $('.alert-container').html('<div class="alert success"><span class="closebtn">&times;</span>История удалена.</div>');
                        return;
                    }

                    $('.alert-container').html('<div class="alert danger"><span class="closebtn">&times;</span>Не удалось удалить историю.</div>');
                },
                error: function () {
                    $('.alert-container').html('<div class="alert danger"><span class="closebtn">&times;</span>Ошибка соединения. Попробуйте ещё раз.</div>');
                }
            });
        });

        (function initStoriesLoadMore() {
            let currentPage = {{ (int) $stories->currentPage() }};
            let hasMore = {{ $stories->hasMorePages() ? 'true' : 'false' }};
            let isLoading = false;
            let loadFailed = false;
            let autoLoadsUsed = 0;
            const maxAutoLoads = 3;
            const $list = $('#stories-list');
            const $sentinel = $('#stories-scroll-sentinel');
            const $moreWrap = $('#stories-more-wrap');
            const $moreButton = $('#stories-more-button');
            const $retryButton = $('#stories-retry-button');
            const $loader = $('#stories-bottom-loader');
            const $loadError = $('#stories-load-error');

            if (!$list.length) {
                return;
            }

            window.storyCatalogPagination = {
                currentPage: currentPage,
                hasMore: hasMore
            };

            function setControlsState() {
                $loadError.prop('hidden', !loadFailed);

                if (!hasMore || loadFailed) {
                    $sentinel.attr('hidden', true);
                    $moreWrap.prop('hidden', true);
                    return;
                }

                if (autoLoadsUsed >= maxAutoLoads) {
                    $sentinel.attr('hidden', true);
                    $moreWrap.prop('hidden', false);
                    return;
                }

                $moreWrap.prop('hidden', true);
                $sentinel.removeAttr('hidden');
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

                params.forEach(function (value, key) {
                    if (key !== 'page') {
                        data[key] = value;
                    }
                });

                if (excludeIds.length) {
                    data.exclude_ids = excludeIds.join(',');
                }

                return data;
            }

            function showLoadFailure() {
                loadFailed = true;
                setControlsState();
            }

            function loadStories(triggerType) {
                if (isLoading || !hasMore || loadFailed) {
                    return;
                }

                if (triggerType === 'scroll' && autoLoadsUsed >= maxAutoLoads) {
                    setControlsState();
                    return;
                }

                isLoading = true;
                $list.attr('aria-busy', 'true');
                $loader.prop('hidden', false);
                $moreButton.prop('disabled', true).text('Загрузка…');

                $.ajax({
                    url: @json(route('stories.catalog')),
                    type: 'GET',
                    dataType: 'json',
                    data: buildRequestData(currentPage + 1),
                    success: function (response) {
                        if (!response || !response.success) {
                            showLoadFailure();
                            return;
                        }

                        if (response.html) {
                            const $incoming = $('<div>').html(response.html);
                            $incoming.find('[data-story]').each(function () {
                                const storyId = $(this).attr('data-story');
                                if (storyId && $list.find('[data-story="' + storyId + '"]').length) {
                                    $(this).remove();
                                }
                            });
                            $list.append($incoming.html());
                        }

                        const serverPage = parseInt(response.current_page, 10);
                        currentPage = Number.isFinite(serverPage) && serverPage > currentPage
                            ? serverPage
                            : currentPage + 1;
                        hasMore = Boolean(response.has_more);
                        window.storyCatalogPagination.currentPage = currentPage;
                        window.storyCatalogPagination.hasMore = hasMore;

                        if (triggerType === 'scroll') {
                            autoLoadsUsed += 1;
                        }
                    },
                    error: showLoadFailure,
                    complete: function () {
                        isLoading = false;
                        $list.removeAttr('aria-busy');
                        $loader.prop('hidden', true);
                        $moreButton.prop('disabled', false).text('Показать ещё');
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
                }, { rootMargin: '240px 0px' });

                if ($sentinel.length) {
                    observer.observe($sentinel[0]);
                }
            } else if (hasMore) {
                autoLoadsUsed = maxAutoLoads;
            }

            $moreButton.on('click', function () {
                loadStories('button');
            });
            $retryButton.on('click', function () {
                loadFailed = false;
                setControlsState();
                loadStories('button');
            });

            setControlsState();
        })();
    });
</script>
@endpush
