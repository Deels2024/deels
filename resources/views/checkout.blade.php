@extends('layouts.neon.app')
@section('title')
    @if( ! empty($title))
        {!! $title !!} |
    @endif @parent
@endsection

@section('content')

    <div class="background__dark"></div>

    <div class="sign">
        <div class="container">
            <h2>Вклад в копилку
                <span>Вклад в копилку</span>
            </h2>
            {{--            <div class="deposit__pre account__pre account__pre-big">Гостевой заказ или--}}
            {{--                <a href="{{route('login')}}" class="">Войти</a>--}}
            {{--            </div>--}}
            <script src="https://securepay.tinkoff.ru/html/payForm/js/tinkoff_v2.js"></script>
            <div class="deposit__pre account__pre account__pre-big">
                <form name="TinkoffPayForm" action="{{route('campaign_donate')}}" class="form deposit__form form-horizontal TinkoffPayForm" method="post" enctype="multipart/form-data"> @csrf
                    @csrf
                    @php
                        if(session('cart.cart_type') == 'reward'){
                        $donation_amount = $reward->amount;
                        }else{
                        $donation_amount = session('cart.amount');
                        }
                        $donation_amount = (int)str_replace('.', '', $donation_amount);

                    @endphp
                    @if(isset($_GET['auto']))
                        <input type="hidden" name="auto" value="1">
                    @endif
                    <input type="hidden" name="donation_amount" value="{{$donation_amount}}">
                    <input type="hidden" name="campaign_id" value="{{$campaign->id}}">
                    <div class="deposit__name" style="display: none">
                        <label for="name">Полное имя*</label>
                        <input type="text" name="name" id="name" value="@if(Auth::check()){!!auth()->user()->fullname!!}@else{!! old('full_name') !!}@endif" placeholder="Артем">
                        <div class="deposit__name-hide">Заполните это поле</div>
                    </div>
                    <div class="deposit__name" style="display: none">
                        <label for="email">Email</label>
                        <input type="text" value="@if(Auth::check()){!!auth()->user()->email!!}@else{!! old('email') !!}@endif" name="email" id="email" placeholder="1234@gmail.com">
                        <div class="deposit__name-hide">Заполните это поле</div>
                    </div>


                    <div class="account__pre account__pre-big">Общая сумма {!!get_amount($donation_amount, false, true)!!}</div>
                    <div class="deposit__title">{!!$campaign->title!!} - {!!get_amount($donation_amount, false, true)!!}</div>
                    <div class="deposit__title">Итог - {!!get_amount($donation_amount, false, true)!!}</div>
                    <input type="submit" class="deposit__btn deposit__btn_pay btn btn_fill" value="Оплатить ">
                    <div id="tinkoffWidgetContainer1" style="margin-top: 20px"></div>
                    <?php /*
                    <input type="button" style="display: block" onclick="window.location='{{$qr['Data']}}'" class="deposit__btn btn btn_fill" value="Оплатить по СБП">
                    */?>
                    <div class="deposit__text">
                        Вы также признаете и соглашаетесь с <a href="/docs/file1.docx" download>Условиями использования</a> и
                        <a href="/docs/file3.docx" download>Политикой конфиденциальности</a>.
                    </div>
                </form>
            </div>
        </div>
    </div>

@endsection

@section('page-js')
@endsection
