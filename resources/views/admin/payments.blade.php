@extends('layouts.admin.app_neon')

@section('title')
    @if(! empty($title))
        {!!$title!!}
    @endif  @parent
@endsection

@section('content')
    <main class="admin-main">
        <div class="account-main__head">
            <div class="account-main__head-side">
                <h1 class="account-main__title">Платежи</h1><span>Статистика финансирования Ваших кампаний</span>
            </div>
            <div class="account-main__head-side"><a class="btn btn_fill d-flex ai-center download_excel"
                                                    href="{{request()->query->count() ? '&excel=1' : '?excel=1'}}">
                    <svg class="mr-1" width="20" height="20" viewBox="0 0 20 20" fill="none"
                         xmlns="http://www.w3.org/2000/svg">
                        <path d="M11.6667 1.66675H5.00001C4.55798 1.66675 4.13406 1.84234 3.8215 2.1549C3.50894 2.46746 3.33334 2.89139 3.33334 3.33341V16.6667C3.33334 17.1088 3.50894 17.5327 3.8215 17.8453C4.13406 18.1578 4.55798 18.3334 5.00001 18.3334H15C15.442 18.3334 15.866 18.1578 16.1785 17.8453C16.4911 17.5327 16.6667 17.1088 16.6667 16.6667V6.66675L11.6667 1.66675Z"
                              stroke="white" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                        <path d="M11.6667 1.66675V6.66675H16.6667" stroke="white" stroke-width="2"
                              stroke-linecap="round" stroke-linejoin="round"/>
                        <path d="M10 15V10" stroke="white" stroke-width="2" stroke-linecap="round"
                              stroke-linejoin="round"/>
                        <path d="M7.5 12.5H12.5" stroke="white" stroke-width="2" stroke-linecap="round"
                              stroke-linejoin="round"/>
                    </svg>
                    Скачать Excel файл
                </a></div>
        </div>
{{--        <button class="aside-nav-btn">Открыть меню</button>--}}
        <form class="form form--admin" action="">
            <div class="form-field">
                <input type="text" value="{{request('email')}}" name="email" placeholder="Поиск по почте">
            </div>
            <div class="form-row">
                <div class="form-field form-field--6">
                    <input type="date" value="{{request('date_from')}}" name="date_from">
                </div>
                <div class="form-field form-field--6">
                    <input type="date" name="date_to" value="{{request('date_to')}}">
                </div>
            </div>
            <div class="form-field mb-2">
                <input type="text" name="company_search" value="{{request('company_search')}}"
                       placeholder="Поиск по названию компании">
            </div>
            <button class="form__btn btn btn_fill">Поиск</button>
        </form>
        <div class="admin-table">
            <table>
                <thead>
                <tr>
                    <th>ID</th>
                    <th>Спонсор</th>
                    <th>Кампания</th>
                    <th>Сторис</th>
                    <th>Дата</th>
                    <th>Сумма</th>
                </tr>
                </thead>
                <tbody>
                @if($payments->count() > 0)
                    @foreach($payments as $payment)
                        <tr>
                            <td>
                                <p>{{$payment->id}}</p>
                            </td>
                            <td>
                                <p><a href="{!!route('payment_view', $payment->id)!!}">
                                        @if ($payment->user_id)
                                            {{\App\Models\User::find($payment->user_id)->email ?? 'Пользователь удален'}}
                                        @else
                                            {{$payment->email ?? $payment->payable->email ?? 'E-mail не указан'}}
                                        @endif
                                    </a></p>
                            </td>
                            <td>
                                <p>
                                    @if($payment->campaign)
                                        <a href="{!!route('campaign_single', $payment->campaign_id)!!}">{!!$payment->campaign->title!!}</a>
                                    @else
                                        @if(!$payment->meta)
                                        @lang('app.campaign_deleted')
                                        @endif
                                    @endif
                                </p>
                            </td>
                            <td>
                                <p>
                                    {{$payment->meta['description'] ?? ''}}
                                </p>
                            </td>
                            <td>
                                <p>{!!$payment->created_at->format('d.m.Y H:i')!!}</p>
                            </td>
                            <td>
                                <p>{!! $payment->amount < 0 ? abs($payment->amount).'<img src="/dist/img/deels_cur.svg" class="small_coin">' : get_amount($payment->amount)!!}</p>
                            </td>
                        </tr>
                    @endforeach
                @endif
                </tbody>
            </table>
        </div>
        <style>
            .admin-table .btn {
                padding: 5px 5px !important;
                margin-bottom: 5px;
            }
        </style>
        {{--        <ul class="pagination">--}}
        {{--            <li class="pagination-item"><a class="pagination-link pagination-link--prev" href="#"></a></li>--}}
        {{--            <li class="pagination-item"><a class="pagination-link" href="#">1</a></li>--}}
        {{--            <li class="pagination-item"><a class="pagination-link" href="#">2</a></li>--}}
        {{--            <li class="pagination-item"><a class="pagination-link" href="#">3</a></li>--}}
        {{--            <li class="pagination-item"><a class="pagination-link" href="#">4</a></li>--}}
        {{--            <li class="pagination-item"><a class="pagination-link" href="#">5</a></li>--}}
        {{--            <li class="pagination-item"><a class="pagination-link" href="#">...</a></li>--}}
        {{--            <li class="pagination-item"><a class="pagination-link" href="#">1780</a></li>--}}
        {{--            <li class="pagination-item"><a class="pagination-link" href="#">1781</a></li>--}}
        {{--            <li class="pagination-item"><a class="pagination-link pagination-link--next" href="#"></a></li>--}}
        {{--        </ul>--}}
    </main>

    {{--        <h2 class="account__title account__title-pos">--}}
    {{--            Платежи--}}
    {{--        </h2>--}}
    {{--        <div class="account__pre">Статистика финансирования Ваших кампаний</div>--}}

    {{--        <div class="account__table table table__margin">--}}
    {{--            <table border="2" bordercolor="#5B3C68" cellspacing="0" width="100%">--}}
    {{--                <thead>--}}
    {{--                <tr>--}}
    {{--                    <th>Спонсор</th>--}}
    {{--                    <th width="40%">Кампания</th>--}}
    {{--                    <th>Дата</th>--}}
    {{--                    <th>Сумма</th>--}}
    {{--                </tr>--}}
    {{--                </thead>--}}
    {{--                <tbody>--}}
    {{--                @if($payments->count() > 0)--}}
    {{--                    @foreach($payments as $payment)--}}
    {{--                        <tr>--}}
    {{--                            <td><a href="{!!route('payment_view', $payment->id)!!}">--}}
    {{--                                    @if ($payment->user_id)--}}
    {{--                                        {{\App\User::find($payment->user_id)->email}}--}}
    {{--                                    @endif--}}
    {{--                                </a></td>--}}
    {{--                            <td> @if($payment->campaign)--}}
    {{--                                    <a href="{!!route('payment_view', $payment->id)!!}">{!!$payment->campaign->title!!}</a>--}}
    {{--                                @else--}}
    {{--                                    @lang('app.campaign_deleted')--}}
    {{--                                @endif</td>--}}
    {{--                            <td>{!!$payment->created_at->format('d.m.Y')!!}</td>--}}
    {{--                            <td><strong>{!!get_amount($payment->amount)!!}</strong></td>--}}
    {{--                        </tr>--}}
    {{--                    @endforeach--}}
    {{--                @endif--}}
    {{--                </tbody>--}}
    {{--            </table>--}}
    {{--        </div>--}}
    {{--    @endif--}}
@endsection
