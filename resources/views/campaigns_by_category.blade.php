@extends('layouts.app')
@section('title') @if( ! empty($title)) {{ $title }} | @endif @parent @endsection

@section('content')


    <div class="page-header section-bg-gradient">
        <div class="container">
            <div class="row">
                <div class="col-md-12">
                    <h1>{!! $title !!}</h1>

                    <nav aria-label="breadcrumb" class="hu-breadcrumb">
                        <ol class="breadcrumb">
                            <li class="breadcrumb-item"><a href="{{route('home')}}"><i class="fa fa-home"></i> @lang('app.home')</a></li>
                            <li class="breadcrumb-item active" aria-current="page">
                                {!! $title !!}
                            </li>
                        </ol>
                    </nav>
                </div>
            </div>
        </div>
    </div>


    <div class="container">
        <div class="row">
            <div class="box-campaign-lists">
                @php
                    $campaigns = $category->campaigns()->orderBy('id', 'desc')->paginate(32);
                @endphp
                @foreach($campaigns as $nc)
                    @php
                        $percent_raised = $nc->percent_raised();
                    @endphp

                    <div class="col-lg-3 col-md-4 col-sm-6 col-xs-12 box-campaign-item">
                        <div class="box-campaign">
                            <div class="box-campaign-content">

                                <div class="box-campaign-img">
                                    <a href="{{route('campaign_single', $nc->slug)}}"> <img src="{{ $nc->feature_img_url()->feature_image }}" /></a>
                                </div>

                                <div class="box-campaign-description">
                                    <h4>
                                        <a href="{{route('campaign_single', $nc->slug)}}"> {{$nc->title}} </a>
                                    </h4>

                                    <ul>
                                        <li><img src="{!! $nc->user->avatar() !!}" class="magnific_image circle-img"> {{$nc->user->fullname}}</li>
                                        <li>{!! $percent_raised !!}% {!! __('app.funded') !!}</li>
                                    </ul>
                                </div>

                                <div class="box-campaign-summery">
                                    <ul>
                                        <li><strong>{{$nc->days_left()}}</strong> @lang('app.days_left')</li>
                                        <li class="text-center"><strong>{!! get_amount($nc->success_payments->sum('amount')) !!}</strong> @lang('app.funded')</li>
                                        <li class="text-right"> <strong>{!! get_amount($nc->goal) !!}</strong> {!! __('app.goal') !!} </li>
                                    </ul>
                                </div>

                            </div>
                        </div>
                    </div>
                @endforeach


            </div>  <!-- #box-campaign-lists -->
        </div>


        <div class="page-pagination-wrap">
            <div class="row">
                <div class="col-md-12">
                    {!! $campaigns->withQueryString()->links() !!}
                </div>
            </div>
        </div>

    </div><!-- /.container -->

@endsection
