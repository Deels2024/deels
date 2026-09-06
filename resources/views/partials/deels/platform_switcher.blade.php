@php
    $activePlatform = $activePlatform ?? '';
    $platformSections = [
        ['key' => 'challenges', 'label' => 'Челленджи', 'icon' => '✦', 'url' => route('challenges.catalog')],
        ['key' => 'battles', 'label' => 'Баттлы', 'icon' => '⚡', 'url' => route('deels.public.battles.index')],
        ['key' => 'stories', 'label' => 'Истории', 'icon' => '▶', 'url' => route('stories.catalog', ['type' => 'popular'])],
        ['key' => 'campaigns', 'label' => 'Копилки', 'icon' => '♡', 'url' => route('deels.public.campaigns.index')],
    ];
@endphp

<nav class="deels-platform-switcher" aria-label="Разделы платформы Deels">
    @foreach($platformSections as $platformSection)
        <a href="{{ $platformSection['url'] }}"
           class="{{ $activePlatform === $platformSection['key'] ? 'active' : '' }}"
           @if($activePlatform === $platformSection['key']) aria-current="page" @endif>
            <span aria-hidden="true">{{ $platformSection['icon'] }}</span>
            {{ $platformSection['label'] }}
        </a>
    @endforeach
</nav>
