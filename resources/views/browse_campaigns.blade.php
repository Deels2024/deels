@extends('layouts.neon.app')

@php
    $activeType = request('type', 'funded');
    $campaignTypes = [
        'funded' => 'Набирают поддержку',
        'new' => 'Новые',
        'all' => 'Все',
        'big' => 'Большие цели',
        'fully_donated' => 'Цель достигнута',
    ];
    $catalogTitle = $filteredCategory
        ? 'Копилки · '.$filteredCategory->category_name
        : 'Копилки Deels';
@endphp

@section('title', $catalogTitle.' — поддержка целей участников')

@push('meta-data')
    <meta name="description" content="Копилки Deels помогают участникам превращать результаты челленджей и личные идеи в реальные цели. Поддерживайте авторов или откройте собственный сбор.">
@endpush

@section('content')
<main class="source-funds-page">
    <section class="source-funds-hero" aria-labelledby="funds-title">
        <div class="container">
            @include('partials.deels.platform_switcher', ['activePlatform' => 'campaigns'])

            <div class="source-funds-hero__grid">
                <div class="source-funds-hero__copy">
                    <span class="eyebrow">DEELS SUPPORT · идеи становятся реальностью</span>
                    <h1 id="funds-title">{{ $filteredCategory ? $filteredCategory->category_name : 'Копилки' }}</h1>
                    <p>Показывайте путь в историях, объединяйте людей вокруг цели и получайте прозрачную поддержку сообщества.</p>
                    <div class="source-catalog-actions">
                        <a href="{{ route('start_campaign') }}" class="button button-primary">+ Создать копилку</a>
                        <a href="{{ route('challenges.catalog') }}" class="button button-glass">Найти челлендж</a>
                    </div>
                </div>

                <aside class="source-funds-flow" aria-label="Как связаны возможности Deels">
                    <span class="source-funds-flow__label"><i></i> ЕДИНАЯ ЭКОСИСТЕМА</span>
                    <div class="source-funds-flow__steps">
                        <div><b>✦</b><span><strong>Челлендж</strong>Начните движение</span></div>
                        <i aria-hidden="true">→</i>
                        <div><b>▶</b><span><strong>История</strong>Покажите прогресс</span></div>
                        <i aria-hidden="true">→</i>
                        <div><b>♡</b><span><strong>Поддержка</strong>Достигните цели</span></div>
                    </div>
                    <div class="source-funds-flow__total">
                        <strong>{{ number_format((int) $campaigns->total(), 0, ',', ' ') }}</strong>
                        <span>целей в текущей подборке</span>
                    </div>
                </aside>
            </div>
        </div>
    </section>

    <section class="source-funds-content" aria-labelledby="funds-catalog-title">
        <div class="container">
            <div class="source-funds-heading">
                <div>
                    <span>Цели сообщества</span>
                    <h2 id="funds-catalog-title">{{ $campaignTypes[$activeType] ?? 'Копилки' }}</h2>
                </div>
                @if($filteredCategory)
                    <a href="{{ route('deels.public.campaigns.index', ['type' => $activeType]) }}">Сбросить категорию ×</a>
                @endif
            </div>

            <nav class="source-funds-filters" aria-label="Фильтр копилок">
                @foreach($campaignTypes as $type => $label)
                    @php
                        $typeUrl = $filteredCategory
                            ? route('campaigns.category', ['slug' => $filteredCategory->slug, 'type' => $type])
                            : route('deels.public.campaigns.index', ['type' => $type]);
                    @endphp
                    <a href="{{ $typeUrl }}"
                       class="{{ $activeType === $type ? 'active' : '' }}"
                       @if($activeType === $type) aria-current="page" @endif>
                        {{ $label }}
                    </a>
                @endforeach
            </nav>

            @if($categories->count())
                <nav class="source-funds-categories" aria-label="Категории копилок">
                    <a href="{{ route('deels.public.campaigns.index', ['type' => $activeType]) }}"
                       class="{{ !$filteredCategory ? 'active' : '' }}">Все темы</a>
                    @foreach($categories as $category)
                        <a href="{{ route('campaigns.category', ['slug' => $category->slug, 'type' => $activeType]) }}"
                           class="{{ $filteredCategory && (int) $filteredCategory->id === (int) $category->id ? 'active' : '' }}">
                            {{ $category->category_name }}
                            <span>{{ number_format((int) $category->campaigns_count, 0, ',', ' ') }}</span>
                        </a>
                    @endforeach
                </nav>
            @endif

            @if($campaigns->count())
                <div class="source-funds-grid">
                    @foreach($campaigns as $campaign)
                        @php
                            $campaignMedia = $campaign->feature_img_url();
                            $campaignCover = ($campaignMedia->thumbnail ?? null)
                                ?: (($campaignMedia->feature_image ?? null) ?: '/uploads/placeholder-image.png');
                            $campaignUrl = route('deels.public.campaigns.show', ['slug' => $campaign->slug]);
                            $percent = max(0, min(100, (float) $campaign->percent_raised()));
                            $raised = (float) ($campaign->success_payments_sum_amount ?? 0);
                            $supporters = (int) ($campaign->success_payments_count ?? 0);
                            $ownerName = $campaign->user->fullname ?: ($campaign->user->username ?: 'Участник Deels');
                            $ownerProfileUrl = (int) $campaign->user->id > 0
                                ? route('user.profile', $campaign->user->id)
                                : null;
                        @endphp

                        <article class="source-fund-card">
                            <a href="{{ $campaignUrl }}" class="source-fund-card__media">
                                <img src="{{ $campaignCover }}" alt="Копилка «{{ $campaign->title }}»" loading="lazy" width="720" height="900">
                                <span class="source-fund-card__shade"></span>
                                <span class="source-fund-card__badge">{{ $percent >= 100 ? 'Цель достигнута' : 'Копилка' }}</span>
                                <span class="source-fund-card__percent">{{ number_format($percent, 0, ',', ' ') }}%</span>
                                <span class="source-fund-card__open" aria-hidden="true">→</span>
                            </a>

                            <div class="source-fund-card__body">
                                <div class="source-fund-card__category">
                                    {{ $campaign->get_category?->category_name ?? 'Цель участника' }}
                                </div>
                                <h3><a href="{{ $campaignUrl }}">{{ $campaign->title }}</a></h3>
                                @if($campaign->short_description)
                                    <p>{{ \Illuminate\Support\Str::limit(strip_tags($campaign->short_description), 105) }}</p>
                                @endif

                                <div class="source-fund-card__progress" role="progressbar"
                                     aria-valuenow="{{ (int) $percent }}" aria-valuemin="0" aria-valuemax="100"
                                     aria-label="Собрано {{ number_format($percent, 0, ',', ' ') }} процентов">
                                    <span style="--fund-progress: {{ $percent }}%"></span>
                                </div>

                                <dl class="source-fund-card__stats">
                                    <div><dt>Собрано</dt><dd>{!! get_amount($raised) !!}</dd></div>
                                    <div><dt>Цель</dt><dd>{!! get_amount($campaign->goal) !!}</dd></div>
                                    <div><dt>Поддержали</dt><dd>{{ number_format($supporters, 0, ',', ' ') }}</dd></div>
                                </dl>

                                <div class="source-fund-card__footer">
                                    @if($ownerProfileUrl)
                                        <a href="{{ $ownerProfileUrl }}" class="source-fund-card__author">
                                            <img src="{{ $campaign->user->avatar() }}" alt="" loading="lazy" width="40" height="40">
                                            <span>{{ $ownerName }}</span>
                                        </a>
                                    @else
                                        <span class="source-fund-card__author">
                                            <img src="{{ $campaign->user->avatar() }}" alt="" loading="lazy" width="40" height="40">
                                            <span>{{ $ownerName }}</span>
                                        </span>
                                    @endif
                                    <a href="{{ $campaignUrl }}" class="source-fund-card__cta">Поддержать</a>
                                </div>
                            </div>
                        </article>
                    @endforeach
                </div>

                @if($campaigns->lastPage() > 1)
                    <div class="source-catalog-pagination">
                        {{ $campaigns->withQueryString()->links() }}
                    </div>
                @endif
            @else
                <div class="source-funds-empty" role="status">
                    <span aria-hidden="true">♡</span>
                    <h2>В этой подборке пока нет копилок</h2>
                    <p>Смените фильтр или создайте цель, которую поддержит сообщество Deels.</p>
                    <a href="{{ route('start_campaign') }}" class="button button-primary">Создать копилку</a>
                </div>
            @endif

            <aside class="source-funds-about">
                <div>
                    <span>Не просто сбор средств</span>
                    <h2>Цель с живой историей</h2>
                </div>
                <p>В Deels копилка связана с контентом и активностью автора: участники видят прогресс, поддерживают публикации и понимают, как идея превращается в результат.</p>
            </aside>
        </div>
    </section>
</main>
@endsection
