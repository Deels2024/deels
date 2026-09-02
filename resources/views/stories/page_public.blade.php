@extends('layouts.neon.app')

@section('title'){{ $title }} — Deels@endsection

@push('meta-data')
    <meta name="description" content="{{ $seoDescription }}">
    <style>
        .deels-public-story{display:grid;grid-template-columns:minmax(280px,420px) minmax(0,1fr);gap:64px;align-items:center;margin-top:34px}.deels-public-story-media{position:relative;aspect-ratio:9/16;overflow:hidden;border-radius:32px;background:#24102f;box-shadow:0 28px 74px rgba(56,21,83,.22)}.deels-public-story-media img,.deels-public-story-media video{width:100%;height:100%;display:block;object-fit:cover}.deels-public-story-lock{position:absolute;left:16px;bottom:16px;padding:9px 12px;border-radius:12px;color:#fff;background:rgba(24,8,34,.72);backdrop-filter:blur(10px);font-size:12px;font-weight:800}.deels-public-story-copy h1{max-width:760px;margin:14px 0 18px;font-size:clamp(46px,6vw,76px);line-height:.96;letter-spacing:-.055em}.deels-public-story-copy>p{max-width:650px;margin:0;color:#756b80;font-size:17px;line-height:1.65}.deels-public-story-author{display:flex;align-items:center;gap:12px;margin:28px 0}.deels-public-story-author>div{display:grid;gap:2px}.deels-public-story-author span{color:#756b80;font-size:12px}.deels-public-story-actions{display:flex;gap:10px;flex-wrap:wrap}@media(max-width:760px){.deels-public-story{grid-template-columns:1fr;gap:30px}.deels-public-story-media{width:min(88vw,390px);margin:auto;border-radius:26px}.deels-public-story-copy h1{font-size:42px}.deels-public-story-actions .button{flex:1;min-width:150px}}
    </style>
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
