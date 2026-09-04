@php
    $legacyBattleCatalog = request()->routeIs('challenges.catalog') && request('content') === 'battles';
    $platformLinks = [
        [
            'label' => 'Челленджи',
            'url' => route('challenges.catalog'),
            'active' => (request()->routeIs('challenges.catalog') && !$legacyBattleCatalog)
                || request()->routeIs('challenge_page', 'deels.public.challenges.show'),
        ],
        [
            'label' => 'Баттлы',
            'url' => route('deels.public.battles.index'),
            'active' => $legacyBattleCatalog
                || request()->routeIs('battle_page', 'deels.public.battles.*'),
        ],
        [
            'label' => 'Истории',
            'url' => route('stories.catalog', ['type' => 'popular']),
            'active' => request()->routeIs('stories.catalog', 'deels.public.stories.show'),
        ],
        [
            'label' => 'Копилки',
            'url' => route('deels.public.campaigns.index'),
            'active' => request()->routeIs(
                'browse_campaign',
                'campaign_single',
                'campaigns.category',
                'deels.public.campaigns.*'
            ),
        ],
    ];
@endphp

@foreach($platformLinks as $platformLink)
    <li class="deels-source-nav-link{{ $platformLink['active'] ? ' active' : '' }}">
        <a href="{{ $platformLink['url'] }}" @if($platformLink['active']) aria-current="page" @endif>
            {{ $platformLink['label'] }}
        </a>
    </li>
@endforeach
