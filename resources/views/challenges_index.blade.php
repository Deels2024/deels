@extends('layouts.neon.app')

@section('title') {{'Челленджи на платформе Deels - Заработок онлайн на контенте для творческих людей'}} @parent @endsection

@push('meta-data')
    <meta name="description" content="Челленджи | Deels.ru — платформа для творчества, сторис, баттлов и участия в челленджах">
@endpush

@section('content')
@php
    $activeType = request('type', 'all');
    $filters = [
        'all' => 'Все',
        'active' => 'Активные',
        'participant' => 'Я участвую',
        'finished' => 'Завершённые',
    ];
@endphp

<div class="source-catalog light_theme light_there">
    <section class="source-catalog-hero">
        <div class="container">
            <span class="eyebrow">✦ Выбирай свой вызов</span>
            <h1>Челленджи</h1>
            <p>Тренды, творчество, спорт, музыка и добрые дела — найди идею, которую захочется повторить.</p>
            <div class="source-catalog-actions">
                @auth
                    <a href="{{ route('challenges.create') }}" class="button button-primary">+ Создать челлендж</a>
                @else
                    <a href="{{ route('login') }}" class="button button-primary">+ Создать челлендж</a>
                @endauth
                <a href="{{ route('stories.catalog') }}" class="button button-glass">▶ Смотреть ленту</a>
            </div>
        </div>
    </section>

    <div class="container">
        <nav class="source-filter-row" aria-label="Фильтры челленджей">
            @foreach($filters as $type => $label)
                @if($type !== 'participant' || auth()->check())
                    <a href="{{ route('challenges.catalog', ['type' => $type]) }}" class="{{ $activeType === $type ? 'active' : '' }}">{{ $label }}</a>
                @endif
            @endforeach
        </nav>

        @if($challenges && count($challenges))
            <div class="source-contest-grid">
                @foreach($challenges as $contest)
                    @php
                        $isBattle = $contest->getTable() === 'battles';
                        $detailRoute = route($isBattle ? 'battle_page' : 'challenge_page', $contest->id);
                        $participants = $contest->stories()->active()->count();
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
                                <span class="source-contest-save">♡</span>
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
                        <div class="source-contest-state">{{ $state }}</div>
                    </article>
                @endforeach
            </div>
        @else
            <div class="source-catalog-empty">
                <h2>Пока ничего не найдено</h2>
                <p>Попробуй другой фильтр или создай свой челлендж.</p>
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
