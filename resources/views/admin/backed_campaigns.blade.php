@extends('layouts.admin.app_neon')

@section('title')
    @if(! empty($title))
        {!!$title!!}
    @endif  @parent
@endsection

@section('page-css')

    <link rel="stylesheet" href="/assets/css/style_emoji.css">
    <link rel="stylesheet" href="/dist/css/new_campaign/index.css">

@endsection

@section('content')

    <h2 class="account__title account__title-pos">
        Поддержанные копилки
    </h2>

    @include('dashboard.campaigns.tabs')

    {{--    <div class="account__pre">Копилки, которые Вы поддержали</div>--}}



    @if($payments->count() > 0)
        <div class="account__table table table__margin mb-5">
            <table border="2" bordercolor="#5B3C68" cellspacing="0" width="100%">
                <thead>
                <tr>
                    {{--                <th>Спонсор</th>--}}
                    <th width="40%">Кампания</th>
                    <th>Дата</th>
                    <th>Сумма</th>
                </tr>
                </thead>
                <tbody>
                @foreach($payments as $payment)
                    <tr>
                        {{--                        <td><a href="{!!route('payment_view', $payment->id)!!}"> {!!$payment->email!!} </a></td>--}}
                        <td> @if($payment->campaign)
                                {{--                                <a href="{!!route('payment_view', $payment->id)!!}">{!!$payment->campaign->title!!}</a>--}}
                                {!!$payment->campaign->title!!}
                            @else
                                @lang('app.campaign_deleted')
                            @endif</td>
                        <td>{!!$payment->created_at->format('d.m.Y')!!}</td>
                        <td><strong>{!!get_amount($payment->amount)!!}</strong></td>
                    </tr>
                @endforeach
                </tbody>
            </table>
        </div>
    @else
        <div class="profile-statistics text-center">
            Нет поддержанных копилок
            <br>
            <a href="/campaigns" class="hero-btn hero-btn-dark mt-6">Выбрать копилки</a>
        </div>
    @endif

@endsection
