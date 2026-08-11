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

            <div class="contributing-to">
                <p class="contributing-to-name"><strong> @lang('app.you_are_contributing_to') {{$campaign->user->fullname}}</strong>
                </p>
                <br>
                <div class="campaign__title title">{{$campaign->title}}</div>
            </div>

            <hr/>

            <div class="row" style="display: flex">
                <div class="col-md-3">
                    <div class="stripe-button-container">
                        <form action="/yandex_kassa/pay" method="post">
                            <button class="btn btn-info">
                                Оплатить через Я.Кассу
                            </button>
                            <input type="hidden" name="amount" value="">
                        </form>
                    </div>
                </div>
            </div>


        </div>
    </section>

@endsection
