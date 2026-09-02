@php
    $routeName = request()->route()?->getName();
    $canonical = request()->url();
    $metaTitle = trim($__env->yieldContent('title')) ?: 'Deels — челленджи, короткие видео и добрые дела';
    $metaDescription = $seoDescription ?? null;
    $metaImage = $seoImage ?? null;
    $emitDescription = false;

    if (isset($challenge)) {
        $canonical = route('deels.public.challenges.show', $challenge->id);
        $metaTitle = 'Челлендж «'.$challenge->title.'» — Deels';
        $metaDescription = \Illuminate\Support\Str::limit(trim(strip_tags((string)($challenge->description ?? $challenge->title))), 160);
        $metaImage = $challenge->thumbnail ?: ($challenge->path ?? null);
        $emitDescription = true;
    } elseif (isset($battle)) {
        $canonical = route('deels.public.battles.show', $battle->id);
        $metaTitle = 'Баттл «'.$battle->title.'» — Deels';
        $metaDescription = \Illuminate\Support\Str::limit(trim(strip_tags((string)($battle->description ?? $battle->title))), 160);
        $metaImage = $battle->thumbnail ?: ($battle->path ?? null);
        $emitDescription = true;
    } elseif (isset($story)) {
        $canonical = route('deels.public.stories.show', $story->id);
        $metaTitle = ($story->title ?: 'История участника').' — Deels';
        $metaDescription = $metaDescription ?: 'История участника Deels — смотрите вертикальное видео и находите новые идеи.';
    } elseif (isset($campaign)) {
        $canonical = route('deels.public.campaigns.show', $campaign->slug);
        $metaTitle = ($campaign->title ?: 'Копилка').' — Deels';
        if (!$metaImage && method_exists($campaign, 'feature_img_url') && $campaign->feature_img_url()) {
            $campaignImage = $campaign->feature_img_url();
            $metaImage = $campaignImage->thumbnail ?? $campaignImage->feature_image ?? null;
        }
    } elseif ($routeName === 'deels.public.battles.index') {
        $canonical = route('deels.public.battles.index');
        $metaTitle = 'Видео-баттлы Deels — выбирайте сильнейшего';
        $metaDescription = 'Смотрите пары вертикальных видео, выбирайте сторону и голосуйте за участников баттлов Deels.';
        $emitDescription = true;
    } elseif ($routeName === 'deels.public.campaigns.index' || $routeName === 'browse_campaign') {
        $canonical = route('deels.public.campaigns.index');
    } elseif ($routeName === 'stories.catalog') {
        $metaDescription = 'Смотрите истории Deels — вертикальные видео, творчество, победы и реальные истории участников.';
        $emitDescription = true;
    } elseif ($routeName === 'password.request') {
        $metaDescription = 'Восстановление доступа к учетной записи Deels.';
        $emitDescription = true;
    } elseif ($routeName === 'login') {
        $metaDescription = 'Войти в Deels и продолжить участие в челленджах, баттлах и историях.';
        $emitDescription = true;
    } elseif (!empty($description)) {
        $metaDescription = $description;
    }

    if ($metaImage && !\Illuminate\Support\Str::startsWith($metaImage, ['http://', 'https://'])) {
        $metaImage = url($metaImage);
    }
    $metaImage = $metaImage ?: url('/images/favicons/apple-touch-icon.png');

    $privatePage = request()->is('dashboard/*')
        || request()->is('login*')
        || request()->is('register*')
        || request()->is('password/*')
        || request()->is('checkout*')
        || request()->is('admin*');
@endphp

@if(empty(request()->query()))
<link rel="canonical" href="{{ $canonical }}">
@endif

@if($emitDescription && $metaDescription)
<meta name="description" content="{{ $metaDescription }}">
@endif
<meta name="robots" content="{{ $privatePage ? 'noindex,nofollow,noarchive' : 'index,follow,max-image-preview:large,max-snippet:-1,max-video-preview:-1' }}">
<meta property="og:locale" content="ru_RU">
<meta property="og:site_name" content="Deels">
<meta property="og:type" content="{{ isset($challenge) || isset($battle) || isset($story) ? 'article' : 'website' }}">
<meta property="og:url" content="{{ $canonical }}">
<meta property="og:title" content="{{ $metaTitle }}">
@if($metaDescription)<meta property="og:description" content="{{ $metaDescription }}">@endif
<meta property="og:image" content="{{ $metaImage }}">
<meta name="twitter:card" content="summary_large_image">
<meta name="twitter:title" content="{{ $metaTitle }}">
@if($metaDescription)<meta name="twitter:description" content="{{ $metaDescription }}">@endif
<meta name="twitter:image" content="{{ $metaImage }}">
<meta name="apple-itunes-app" content="app-id=6480409656">

@if(request()->routeIs('home'))
<script type="application/ld+json">{!! json_encode([
    '@context' => 'https://schema.org',
    '@type' => 'WebSite',
    'name' => 'Deels',
    'url' => url('/'),
    'inLanguage' => 'ru-RU',
    'potentialAction' => [
        '@type' => 'SearchAction',
        'target' => url('/search').'?q={search_term_string}',
        'query-input' => 'required name=search_term_string',
    ],
], JSON_UNESCAPED_UNICODE|JSON_UNESCAPED_SLASHES) !!}</script>
@endif

@if(isset($challenge) || isset($battle) || isset($story))
<script type="application/ld+json">{!! json_encode([
    '@context' => 'https://schema.org',
    '@type' => 'VideoObject',
    'name' => $metaTitle,
    'description' => $metaDescription ?: 'Видео в Deels',
    'thumbnailUrl' => [$metaImage],
    'url' => $canonical,
    'uploadDate' => optional(($challenge ?? $battle ?? $story)->created_at)->toAtomString(),
], JSON_UNESCAPED_UNICODE|JSON_UNESCAPED_SLASHES) !!}</script>
@endif
