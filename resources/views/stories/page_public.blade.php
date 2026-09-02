@extends('layouts.neon.app')

@section('title'){{ $title }} — Deels@endsection

@push('meta-data')
    <meta name="description" content="{{ $seoDescription }}">
@endpush

@section('content')
<div class="deels-source-home light_theme light_there">
    <main class="source-section">
        <div class="container">
            <a href="{{ route('stories.catalog') }}" class="source-text-link">← Все истории</a>

            <section class="deels-public-story">
                <div class="deels-public-story-media">
                    @if($preview['type'] === 'video' && !$story->paid)
                        <video src="{{ $preview['url'] }}" poster="{{ $preview['poster'] ?? '' }}" controls playsinline preload="metadata"></video>
                    @elseif($preview['type'] === 'video' && !empty($preview['poster']))
                        <img src="{{ $preview['poster'] }}" alt="{{ $title }}">
                        <div class="deels-public-story-lock">🔒 Платная история</div>
                    @else
                        <img src="{{ $preview['url'] }}" alt="{{ $title }}">
                    @endif
                </div>

                <div class="deels-public-story-copy">
                    <span class="eyebrow">✦ История Deels</span>
                    <h1>{{ $title }}</h1>
                    <p>{{ $seoDescription }}</p>

                    <div class="deels-public-story-author">
                        <span class="avatar avatar-small">
                            {{ $story->user ? mb_strtoupper(mb_substr($story->user->username ?: $story->user->fullname, 0, 2)) : 'D' }}
                        </span>
                        <div>
                            <strong>{{ $story->user ? '@'.$story->user->username : '@deels' }}</strong>
                            <span>{{ optional($story->created_at)->diffForHumans() }}</span>
                        </div>
                    </div>

                    <div class="deels-public-story-actions">
                        @if($story->paid)
                            <a href="{{ route('login') }}" class="button button-primary">Открыть историю</a>
                        @else
                            <a href="#story-popup" class="button button-primary show_story"
                               data-route="{{ route('stories.preview', ['id' => $story->id, 'user_id' => Auth::id()]) }}"
                               data-story="{{ $story->id }}"
                               data-type="{{ $story->type }}"
                               data-paid="{{ $story->paid }}"
                               data-amount="{{ $story->amount }}">Смотреть в Deels →</a>
                        @endif
                        <a href="{{ route('challenges.catalog') }}" class="button button-soft">Найти челлендж</a>
                    </div>
                </div>
            </section>
        </div>
    </main>
</div>

@include('stories.modal')
@endsection
