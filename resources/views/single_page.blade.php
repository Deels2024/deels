@extends('layouts.neon.app')
@section('title')
    @if ($page->slug==='about-us')
        Платформа для творчества Deels  - Заработок онлайн через создание контента
        @push('meta-data')
            <meta name="description" content="О нас  |  Deels.ru  -  платформа для творчества и продвижения контента | Заработок на сторис  | Участие в челленджах  |  Растущие сообщество талантливых создателей и энтузиастов">
        @endpush
    @elseif( ! empty($title))
        {{ $title }} |
    @endif @parent
@endsection

@section('content')

    @if ($page->slug==='about-us')
    <!-- hero  -->
    <div class="hero">
        <div class="container">
            <div class="hero__row">
                <div class="hero-content">
                    <!-- <div class="hero-content-bg" style="background-image: url(img/animation.svg);"></div> -->
                    <div class="hero-title">Что такое <div class="hero-title__item"><span class="text-accent">deels</span>?</div></div>
                    <div class="hero-list">
                        <a href="#hero-video-popup" class="hero-list__item" data-video-link="/dist/videos/IMG_9229.MOV">
                            <img src="/img/video.png" alt="">
                        </a>
                        <a href="#hero-video-popup" class="hero-list__item" data-video-link="/dist/videos/IMG_9233.MOV">
                            <img src="/img/video2.png" alt="">
                        </a>
                        <a href="#hero-video-popup" class="hero-list__item" data-video-link="/dist/videos/IMG_9231.MOV">
                            <img src="/img/video3.png" alt="">
                        </a>
                    </div>

                    <div class="buttons-row">
                        <a href="{{route('stories.create')}}" class="hero-btn">Создать сторис</a>
                        <a href="{{route('start_campaign')}}" class="hero-btn hero-btn-dark"  onclick="window.location='{{route('start_campaign')}}'">Начать копить</a>
                    </div>

                </div>
                <div class="hero-img">
                    <img src="/img/hero-img.png" alt="">
                </div>
            </div>
        </div>
    </div>
    <!-- End hero  -->
    @endif


    {!! (str_replace(
    ['%CAMPAIGNS_COUNT%', '%FINANCED%', '%MONEY%', '%BACKERS%'],
    [
        \App\Models\Campaign::count(),
        \App\Models\Campaign::join('payments', 'campaign_id', 'campaigns.id')
                                          ->where('payments.status', 'success')
                                          ->count()+156,
        get_amount(\App\Models\Payment::whereStatus('success')->sum('amount')+513879),
        \App\Models\User::count()
     ],
    $page->post_content
    )) !!}

    @if(\Request::path() == 'about-us')
        @php
            $campaignsCount = \App\Models\Campaign::count();
               $usersCount = \App\Models\User::count();
               $fundRaised = \App\Models\Payment::whereStatus('success')->sum('amount');
               $fundedCampaignsCount = \App\Models\Campaign::join('payments', 'campaign_id', 'campaigns.id')
                   ->where('payments.status', 'success')
                   ->count();

                   $campaign = \App\Models\Campaign::first();
                   $campaignRepository = new \App\Repositories\CampaignRepository($campaign);
               $storiesCount = \App\Models\Story::active()->notMainStory()->count();
               $storiesDonatedCount = \Illuminate\Support\Facades\DB::table('transactions')->where('meta', 'like', '%{"get":"story"%')->sum('amount');;
               $storiesCommentsCount = \App\Models\Comment::whereNotNull('story_id')->where('approved', true)->count();
               $storiesViewsCount = \App\Models\View::count();

               $fundedCampaigns = $campaignRepository->fundedCampaigns(8);
               $newCampaigns = $campaignRepository->newCampaigns(8, $fundedCampaigns->pluck('id'));
               $latestFundedCampaigns = $campaignRepository->latestFundedCampaigns(8);
        @endphp
        @include('partials.home.stats')
        @include('partials.home.whydeels')
        @include('partials.home.bottom_start')
    @endif

@endsection
