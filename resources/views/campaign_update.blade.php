@extends('layouts.app')
@section('title') @if( ! empty($title)) {{ $title }} | @endif @parent @endsection


@section('meta-data')
    <meta name="description" content="{{$campaign->short_description ? $campaign->short_description : $campaign->description}}" />

    <!-- Twitter Card data -->
    <meta name="twitter:card" content="{{$campaign->short_description ? $campaign->short_description : $campaign->description}}">
    {{--<meta name="twitter:site" content="@publisher_handle">--}}
    <meta name="twitter:title" content="@if( ! empty($title)) {{ $title }} @endif">
    <meta name="twitter:description" content="{{$campaign->short_description ? $campaign->short_description : $campaign->description}}">
    {{--<meta name="twitter:creator" content="@author_handle" />--}}
    <!-- Twitter Summary card images must be at least 120x120px -->
    <meta name="twitter:image" content="{!! $campaign->feature_img_url()->feature_image !!}">

    <!-- Open Graph data -->
    <meta property="og:title" content="@if( ! empty($title)) {{ $title }} @endif" />
    <meta property="og:url" content="{{route('campaign_single', $campaign->slug)}}" />
    <meta property="og:image" content="{{$campaign->feature_img_url()->feature_image}}" />
    <meta property="og:type" content="article" />
    <meta property="og:description" content="{{$campaign->short_description ? $campaign->short_description : $campaign->description}}" />

@endsection

@section('content')

    <section class="campaign-details-wrap">

        <div class="page-header section-bg-gradient">
            <div class="container">
                <div class="row">
                    <div class="col-md-12">
                        <h1>{{$campaign->title}}</h1>

                        <nav aria-label="breadcrumb" class="hu-breadcrumb">
                            <ol class="breadcrumb">
                                <li class="breadcrumb-item"><a href="{{route('home')}}"><i class="fa fa-home"></i> @lang('app.home')</a></li>
                                <li class="breadcrumb-item"><a href="{{route('browse_campaigns')}}">@lang('app.campaigns')</a></li>
                                <li class="breadcrumb-item" aria-current="page">
                                    <a href="{{route('campaign_single', $campaign->slug)}}">{!! $campaign->title !!}</a>
                                </li>

                                <li class="breadcrumb-item active" aria-current="page">
                                    @lang('app.updates')
                                </li>
                            </ol>
                        </nav>
                    </div>
                </div>
            </div>
        </div>

        <div class="container">
            <div class="row">
                <div class="col-md-8">

                    @include('admin.flash_msg')
                    @include('single_campaign_header')

                    <div class="campaign-decription">

                        @if($campaign->updates->count() > 0)
                            @foreach($campaign->updates as $update)
                                <div class="update-wrap">
                                    <h2 class="update-title"> {{$update->title}} </h2>
                                    <p class="text-muted"> {{$update->created_at->format('j F Y')}} </p>

                                    @if($update->image_id)
                                        <div class="campaign-update-img-wrap">
                                            <img src="{!! media_image_uri($update->image_id)->original !!}" class="img-fluid" />
                                        </div>
                                    @endif

                                    {!! safe_output(nl2br($update->description)) !!}
                                </div>
                            @endforeach

                        @else

                            <div class="no-data">
                                <i class="fa fa-bell-o"></i><h1>@lang('app.no_update')</h1>
                            </div>
                        @endif

                    </div>

                </div>

                <div class="col-md-4">
                    @include('campaign_single_sidebar')
                </div>

            </div>
        </div>


    </section>

    @include('footer_get_start_section')



@endsection