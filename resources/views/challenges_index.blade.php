@extends('layouts.neon.app')

@php
    $contentMode = request('content', request()->routeIs('deels.public.battles.index') ? 'battles' : 'challenges');
    $isBattlesCatalog = $contentMode === 'battles';
    $catalogTitle = $isBattlesCatalog ? 'Баттлы' : 'Челленджи';
    $canCreateCurrentContest = !$isBattlesCatalog || (auth()->check() && auth()->user()->is_admin());
@endphp

@section('title') {{$catalogTitle.' на платформе Deels'}} @parent @endsection

@push('meta-data')
    <meta name="description" content="{{$catalogTitle}} | Deels.ru — платформа для творчества, вертикальных видео и соревнований">
@endpush

@section('content')
@php
    $activeType = request('type', 'all');
    $filters = [
        'all' => 'Все',
        'active' => 'Активные',
        'rewarded' => 'С призом',
        'new' => 'Новые',
        'ending' => 'Завершаются',
        'participant' => 'Я участвую',
        'finished' => 'Завершённые',
    ];
@endphp

<div class="source-catalog light_theme light_there">
    <section class="source-catalog-hero">
        <div class="container">
            @include('partials.deels.platform_switcher', ['activePlatform' => $isBattlesCatalog ? 'battles' : 'challenges'])

            <div class="source-catalog-hero__grid">
                <div class="source-catalog-hero__copy">
                    <span class="eyebrow">DEELS ARENA · {{ $isBattlesCatalog ? 'один на один' : 'от идеи до результата' }}</span>
                    <h1>{{ $catalogTitle }}</h1>
                    <p>
                        {{ $isBattlesCatalog
                            ? 'Бросай вызов, отвечай вертикальным видео и собирай голоса сообщества. Здесь решают идея, смелость и реакция людей.'
                            : 'Выбирай вызов, снимай ответ и двигайся к результату вместе с сообществом. Лучшие идеи получают внимание и награды.' }}
                    </p>
                    <div class="source-catalog-actions">
                        @if($canCreateCurrentContest)
                            <a href="{{ route($isBattlesCatalog ? 'battles.create' : 'challenges.create') }}" class="button button-primary">+ {{ $isBattlesCatalog ? 'Создать баттл' : 'Создать челлендж' }}</a>
                        @else
                            <a href="#arena-list" class="button button-primary">Смотреть баттлы ↓</a>
                        @endif
                        <a href="{{ route('stories.catalog', ['type' => 'popular']) }}" class="button button-glass">▶ Смотреть ответы</a>
                    </div>
                </div>

                <aside class="source-arena-card" aria-label="Как работает Deels Arena">
                    <div class="source-arena-card__signal"><span></span> ARENA ONLINE</div>
                    <ol>
                        <li><b>01</b><span><strong>Выбери формат</strong>{{ $isBattlesCatalog ? 'Вызови соперника' : 'Найди идею по себе' }}</span></li>
                        <li><b>02</b><span><strong>Сними ответ</strong>Вертикальное видео прямо в Deels</span></li>
                        <li><b>03</b><span><strong>Собери реакцию</strong>Голоса, поддержка и награды</span></li>
                    </ol>
                    <div class="source-arena-card__total">
                        <strong>{{ number_format((int) $challenges->total(), 0, ',', ' ') }}</strong>
                        <span>{{ $isBattlesCatalog ? 'баттлов в каталоге' : 'челленджей в каталоге' }}</span>
                    </div>
                </aside>
            </div>
        </div>
    </section>

    <div class="container" id="arena-list">
        <nav class="source-filter-row" aria-label="Фильтры каталога">
            @foreach($filters as $type => $label)
                @if($type !== 'participant' || auth()->check())
                    @php
                        $filterRoute = $isBattlesCatalog
                            ? route('deels.public.battles.index', ['type' => $type])
                            : route('challenges.catalog', ['content' => 'challenges', 'type' => $type]);
                    @endphp
                    <a href="{{ $filterRoute }}" class="{{ $activeType === $type ? 'active' : '' }}">{{ $label }}</a>
                @endif
            @endforeach
        </nav>

        @if($challenges && count($challenges))
            <div class="source-contest-grid">
                @foreach($challenges as $contest)
                    @php
                        $isBattle = $contest->getTable() === 'battles';
                        $detailRoute = route($isBattle ? 'deels.public.battles.show' : 'deels.public.challenges.show', $contest->id);
                        $participants = (int) ($contest->active_stories_count ?? 0);
                        $reward = (int)($contest->reward_amount ?? 0);
                        $author = $contest->user ? '@'.$contest->user->username : '@deels';
                        $state = $contest->finished ? 'Завершён' : (!$contest->started ? 'Идёт набор' : 'Активен');
                    @endphp

                    <article class="source-contest-card">
                        <a href="{{ $detailRoute }}" class="source-contest-poster">
                            @if($contest->type === 'video' && $contest->video_preview)
                                <video src="{{ $contest->video_preview }}" poster="{{ $contest->thumbnail }}" muted loop autoplay playsinline></video>
                            @elseif($contest->thumbnail || $contest->path)
                                <img src="{{ $contest->thumbnail ?: $contest->path }}" alt="{{ $contest->title }}">
                            @else
                                <span style="font-size:84px;position:relative;z-index:1">{{ $isBattle ? '⚡' : '✦' }}</span>
                            @endif

                            <div class="source-contest-top">
                                <span class="source-contest-tag">{{ $isBattle ? 'Баттл' : 'Челлендж' }}</span>
                                <span class="source-contest-live"><i></i>{{ $state }}</span>
                            </div>
                            <div class="source-contest-caption">
                                <span>{{ $author }}</span>
                                <strong>{{ $contest->title }}</strong>
                            </div>
                            <span class="source-contest-play">▶</span>
                        </a>

                        <div class="source-contest-meta">
                            <div>
                                <strong>{{ $reward > 0 ? number_format($reward, 0, ',', ' ').' ₽' : ($isBattle ? 'Баттл' : 'Без приза') }}</strong>
                                <span>{{ $reward > 0 ? 'призовой фонд' : 'формат' }}</span>
                            </div>
                            <div>
                                <strong>{{ number_format($participants, 0, ',', ' ') }}</strong>
                                <span>участников</span>
                            </div>
                        </div>
                        <a href="{{ $detailRoute }}" class="source-contest-state">Открыть {{ $isBattle ? 'баттл' : 'челлендж' }} <span aria-hidden="true">→</span></a>
                    </article>
                @endforeach
            </div>
        @else
            <div class="source-catalog-empty" role="status">
                <h2>Пока ничего не найдено</h2>
                <p>Попробуй другой фильтр{{ $canCreateCurrentContest ? ' или создай свой '.($isBattlesCatalog ? 'баттл' : 'челлендж') : '' }}.</p>
            </div>
        @endif

        @if($challenges instanceof \Illuminate\Pagination\LengthAwarePaginator)
            <div class="source-catalog-pagination">{{ $challenges->links() }}</div>
        @endif
    </div>
</div>

@include('stories.modal')
@include('challenges.modal')
@endsection
