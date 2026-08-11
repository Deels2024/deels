@extends('layouts.admin.app_neon')

@section('title') @if(! empty($title)) {!!$title!!} @endif  @parent @endsection

@section('content')
    <h2 class="account__title account__title-pos">
        Автоплатежи
    </h2>
    <div class="account__pre">Копилки, на которые Вы подписались</div>

    <div class="account__table table table__margin">
        <table border="2" bordercolor="#5B3C68" cellspacing="0" width="100%">
            <thead>
            <tr>
                <th>Кампания</th>
                <th width="40%">Дата списания</th>
                <th>Сумма</th>
                <th>Действие</th>
            </tr>
            </thead>
            <tbody>
            @if($campaigns->count() > 0)
                @foreach($campaigns as $campaign)
                    <tr>
                        <td><a href="{!!route('campaign_single', $campaign->id)!!}"> {!!$campaign->title!!} </a></td>
                        <td>{!!$campaign->autopayDate->format('d.m.Y')!!}</td>
                        <td>{!!get_amount($campaign->autopayAmount*100, false, true)!!}</td>
                        <td><a href="{{route('autopayments_delete', $campaign->id)}}">Отписаться</a></td>
                    </tr>
                @endforeach
            @endif
            </tbody>
        </table>
    </div>

@endsection
