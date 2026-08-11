@extends('layouts.new.app')
@section('title') @if( ! empty($title)) {{ $title }} | @endif @parent @endsection

@section('content')
    <section class="page-top">
        <div class="wrapper">
            <div class="breadcrumbs">
                <ul>
                    <li>
                        <a href="/">Главная</a>
                    </li>
                    <li>{!! $title !!}</li>
                </ul>
            </div>
        </div>
    </section>




    <section class="contacts">
        <div class="wrapper contacts__wrap">

            <div class="payment-received">
                <h1><i class="fa fa-check-circle-o"></i> @lang('app.payment_thank_you')</h1>
                <p>@lang('app.payment_receive_successfully')</p>
                <a href="{{route('browse_campaigns')}}" class="btn btn-filled">@lang('app.home')</a>
            </div>

        </div>

    </section>


@endsection

@section('page-js')


@endsection
