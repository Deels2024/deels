@extends('layouts.admin.app_neon')

@section('title') {{$title ?? 'Мой кошелек'}}  @parent @endsection

@section('page-css')

    <link rel="stylesheet" href="/assets/css/style_emoji.css">
    <link rel="stylesheet" href="/dist/css/new_campaign/index.css">

@endsection

@section('content')
    <main class="account-main">
        <div class="account-main__head">
            <h1 class="account-main__title">Мой кошелек</h1>
        </div>
        <div class="account-info">
            <div class="wallet">
                <div class="wallet-content">
                    <div class="wallet-content__head">
                        <div class="wallet-info">
                            <div class="wallet-info__title">Мои дилсы:</div>
                            <div class="wallet-info__balance">{{number_format($user->wallet_balance, 0, ',', ',')}}</div>
                            <div class="wallet-info__text">1 ₽ = 100 дилсов</div>
                        </div>
                    </div>
                    <div class="wallet-content__head">
                        <div class="wallet-info">
                            <div class="wallet-info__title">Мои средства:</div>
                            <div class="wallet-info__balance rub">{{number_format($user->profit_balance, 1, ',', ',')}}</div>
                            <div class="wallet-info__text">заработанные в DEELS</div>
                        </div>
                    </div>
                    <div class="wallet-content__footer">
                        <button data-mfp-src="#deposit" class="btn btn_fill" data-popup-link="deposit">Пополнить</button>
                        <button data-mfp-src="#withdraw" class="btn btn_outline" data-popup-link="">Вывести</button>
                    </div>
                </div>
            </div>

{{--            <?php--}}
{{--            $requested_withdrawal = \App\Models\WithdrawalRequest::where('wallet', true)->where('user_id', \Illuminate\Support\Facades\Auth::user()->id)->where('status', 'pending')->first();--}}
{{--            ?>--}}
{{--            @if($requested_withdrawal)--}}
{{--                {{var_dump($requested_withdrawal->withdrawal_amount)}}--}}
{{--                <div style="border-radius: 10px; padding: 10px;background: #0d102c; text-align: center; margin-bottom: 20px;">Перевод средств будет выполнен в срок от 3 до 30 рабочих дней.</div>--}}
{{--            @endif--}}

            <div>

                <div class="d-block mb-7">
                    <ul class="main-content__switch lk_switch">
                        <li class="main-content__switch-link main-content__switch-link_donate {{!isset($_GET['type']) ? 'main-content__switch-link_active' : ''}}">
                            <a class="main-content__switch-link" href="{{ route('user_wallet') }}">История движений дилсов</a>
                        </li>
                        <li class="main-content__switch-link main-content__switch-link_comments {{isset($_GET['type']) && $_GET['type'] == 'billing' ? 'main-content__switch-link_active' : ''}}">
                            <a class="main-content__switch-link" href="{{ route('user_wallet') }}?type=billing">История пополнения</a>
                        </li>
                        <li class="main-content__switch-link main-content__switch-link_comments {{isset($_GET['type']) && $_GET['type'] == 'donate' ? 'main-content__switch-link_active' : ''}}">
                            <a class="main-content__switch-link" href="{{ route('user_wallet') }}?type=donate">История донатов</a>
                        </li>

                    </ul>
                </div>

                @if($transactions && count($transactions) > 0)

                    <div class="wallet-wrap">
                        <table class="wallet-table">
                            <thead>
                            <tr>
                                <th>Вид операции</th>
                                <th>Дата</th>
                                <th>Сумма</th>
                                @if(isset($_GET['type']))
                                    <th>Статус</th>
                                @endif
                            </tr>
                            </thead>
                            <tbody>
                            @foreach($transactions as $transaction)
                                @php
                                    $transaction_meta = $transaction->meta ?? [];
                                    if($transaction_meta && !is_array($transaction_meta)) {
                                        $transaction_meta = json_decode($transaction_meta, true);
                                    }
                                @endphp
                                <tr>
                                    <td>
                                        {!! $transaction->getDescription() !!}
                                    </td>
                                    <td><b>{{\Carbon\Carbon::parse($transaction->created_at)->format('d.m.Y H:i')}}</b></td>
                                    <td>
                                        <b style="display: flex; align-items: center; justify-content: center">
                                            @if(isset($_GET['type']) && $_GET['type'] == 'donate')
                                                {!! $transaction->getAmount(true) !!}
                                            @else
                                                {!! $transaction->getAmount() !!}
                                            @endif
                                        </b>
                                    </td>
                                    @if(isset($_GET['type']))
                                        <td>
                                            @if(isset($transaction->total_amount))
                                                {!! $transaction->getStatusIco() !!}
                                            @else
                                                   <svg version="1.1" xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" width="20" height="20" viewBox="0 0 20 20"><path fill="#04AA6D" d="M9.5 20c-2.538 0-4.923-0.988-6.718-2.782s-2.782-4.18-2.782-6.717c0-2.538 0.988-4.923 2.782-6.718s4.18-2.783 6.718-2.783c2.538 0 4.923 0.988 6.718 2.783s2.782 4.18 2.782 6.718-0.988 4.923-2.782 6.717c-1.794 1.794-4.18 2.782-6.718 2.782zM9.5 2c-4.687 0-8.5 3.813-8.5 8.5s3.813 8.5 8.5 8.5 8.5-3.813 8.5-8.5-3.813-8.5-8.5-8.5z"></path><path fill="#04AA6D" d="M7.5 14.5c-0.128 0-0.256-0.049-0.354-0.146l-3-3c-0.195-0.195-0.195-0.512 0-0.707s0.512-0.195 0.707 0l2.646 2.646 6.646-6.646c0.195-0.195 0.512-0.195 0.707 0s0.195 0.512 0 0.707l-7 7c-0.098 0.098-0.226 0.146-0.354 0.146z"></path></svg>
                                            @endif
                                        </td>
                                    @endif
                                </tr>
                                </tr>
                            @endforeach
                            </tbody>
                        </table>
                    </div>
                    <div class="mb-4">
                        @if($transactions instanceof \Illuminate\Pagination\LengthAwarePaginator )
                            {{$transactions->links()}}
                        @endif
                    </div>
                @else
                    Нет данных
                @endif
            </div>
        </div>

        <div class="popup popup--sm mfp-hide" id="deposit">
            <div class="popup-head">
                <h2 class="popup-head__title text-center">Введите сумму пополнения</h2>
            </div>
            <div class="popup-body">
                <form action="{{route('wallet_deposit')}}" method="post">
                    @csrf
                    <div class="d-flex flex-column gap-6 ai-center">
                        <input type="hidden" name="user_id" value="{{$user->id}}">
                        <input class="only-num new__input" style="border-top: none; border-left: none; border-right: none" required="" autofocus="" name="amount" placeholder="Введите сумму" min="100">
                        <button class="btn btn_fill wallet_deposit" type="submit">Подтвердить</button>
                    </div>
                </form>
            </div>
        </div>

        <div class="popup popup--sm mfp-hide" id="deposit-success">
            <div class="popup-head">
                <h2 class="popup-head__title text-center">Кошелек успешно пополнен!</h2>
            </div>
            <div class="popup-body d-flex flex-column ai-center mb-7">
                <p class="text-center">Сумма будет отображена на вашем балансе.</p>
            </div>
            <div class="popup-footer text-center">
                <button class="btn btn_fill magnific_close">Подтвердить</button>
            </div>
        </div>

        <div class="popup popup--sm mfp-hide" id="withdraw">
            <div class="popup-head">
                <h2 class="popup-head__title text-center">Введите сумму для вывода</h2>
            </div>
            <div class="popup-body">
                <form id="walletWithdraw">
                    <div class="d-flex flex-column gap-6 ai-center">
                        <div class="form-group">
                            <input class="only-num new__input" style="border-top: none; border-left: none; border-right: none" name="withdraw_input" type="text" required autofocus placeholder="Введите сумму">
                        </div>


                        <div class="d-flex ai-center flex-column">
                            <b class="mb-5 fz-6">Доступно к выводу:</b>
                            <div class="d-flex ai-center mb-2">
                                <b class="fz-6">{{number_format($user->withdraw_balance, 0, ',', ',')}} ₽</b>
                            </div>
                            <small style="text-align: center">Ваши дилсы + ваши средства.<br>Комиссия проекта составляет 20% от суммы вывода. <br>Вы получите 80% от запрашиваемой суммы.</small>
                        </div>
                        <div class="d-flex ai-center flex-column">
                            <div class="form-group">
                                <input class="only-num new__input" name="contacts" style="border-top: none; border-left: none; border-right: none" type="text" required autofocus placeholder="Контакты для связи" value="{{$user->contacts}}">
                                <small style="text-align: center"><br>Укажите контакты в любой соц. сети или telegram для связи</small>
                            </div>
                            <div class="form-group">
                                <input class="only-num new__input" style="border-top: none; border-left: none; border-right: none" name="fio" type="text" required autofocus placeholder="ФИО">
                            </div>
                            <div class="form-group">
                                <input class="only-num new__input" style="border-top: none; border-left: none; border-right: none" name="phone" type="text" required autofocus placeholder="Номер телефона">
                            </div>
                            <div class="form-group">
                                <input class="only-num new__input" style="border-top: none; border-left: none; border-right: none" name="schet" type="text" required autofocus placeholder="Номер счета">
                            </div>
                            <div class="form-group">
                                <input class="only-num new__input" style="border-top: none; border-left: none; border-right: none" name="bik" type="text" required autofocus placeholder="БИК">
                            </div>
                            <div class="form-group">
                                <input class="only-num new__input" style="border-top: none; border-left: none; border-right: none" name="bank" type="text" required autofocus placeholder="Банк-получатель">
                            </div>
                            <div class="form-group">
                                <input class="only-num new__input" style="border-top: none; border-left: none; border-right: none" name="korr" type="text" required autofocus placeholder="Корр. счет">
                            </div>
                            <div class="form-group">
                                <input class="only-num new__input" style="border-top: none; border-left: none; border-right: none" name="inn" type="text" required autofocus placeholder="ИНН банка">
                            </div>
                            <div class="form-group">
                                <input class="only-num new__input" style="border-top: none; border-left: none; border-right: none" name="kpp" type="text" required autofocus placeholder="КПП">
                            </div>
                        </div>
                        <button class="btn btn_fill btn_withdraw" type="submit">Подтвердить</button>
                    </div>
                </form>
            </div>
        </div>

        <!-- Подтверждение вывода средств -->
        <div class="popup popup--sm mfp-hide" id="withdraw-success">
            <div class="popup-head">
                <div class="popup-head__title text-center">Вывод на сумму <span id="withdrawSum"></span> подтвержден!</div>
            </div>
            <div class="popup-body d-flex flex-column ai-center mb-7">
                <p class="text-center">Перевод средств будет выполнен в срок от 3 до 30 рабочих дней.</p>
            </div>
            <div class="popup-footer text-center">
                <a href="{{route('user_wallet')}}" class="btn btn_fill" type="submit">Ок</a>
            </div>
        </div>
    </main>
@endsection
@push('after_scripts')
    <script>
        @if(isset($_GET['deposit']))
            $( document ).ready(function() {
                $('[data-popup-link="deposit"]').trigger('click');
                var newURL = location.href.split("?")[0];
                window.history.pushState('object', document.title, newURL);
            });
        @endif
        $('[data-popup-link]').magnificPopup({
            type:'inline',
            midClick: true
        });
        @if(isset($_GET['Success']) && $_GET['Success'])
        $.magnificPopup.open({
            items: {
                src: '#deposit-success'
            },
            type: 'inline'
        });
        var newURL = location.href.split("?")[0];
        window.history.pushState('object', document.title, newURL);
        @endif
        $('.magnific_close').on( "click", function(e) {
            e.preventDefault();
            $.magnificPopup.close();
        });

        $('.btn_withdraw').on('click', function (e) {
            e.preventDefault();
            var amount = $('[name="withdraw_input"]').val();
            var contacts = $('[name="contacts"]').val();
            var user_id = '{{\Illuminate\Support\Facades\Auth::user()->id}}';
            $.ajax({
                type: 'POST',
                url: '{!! route('withdraw_wallet_request') !!}',
                data: {
                    _token: '{{ csrf_token() }}',
                    amount: amount, user_id: user_id, contacts: contacts,
                    fio: $('[name="fio"]').val(),
                    phone: $('[name="phone"]').val(),
                    schet: $('[name="schet"]').val(),
                    bik: $('[name="bik"]').val(),
                    bank: $('[name="bank"]').val(),
                    korr: $('[name="korr"]').val(),
                    inn: $('[name="inn"]').val(),
                    kpp: $('[name="kpp"]').val()},
                success: function (data) {
                    console.log(data);
                    if (data.success) {
                        $('#withdrawSum').html(amount);
                        $.magnificPopup.open({
                            items: {
                                src: '#withdraw-success'
                            },
                            type: 'inline'
                        });
                    } else {
                        $('.alert-container').html('<div class="alert danger"> <span class="closebtn">&times;</span>'+data.errors+'</div>')
                    }
                }
            });
        });
        $('.wallet_deposit').on('click', function (e) {
            e.preventDefault();
            var amount = $(this).parents('form').find('[name="amount"]').val();
            if(amount < 100) {
                $('.alert-container').html('<div class="alert danger"> <span class="closebtn">&times;</span> Минимальная сумма для пополнения: 100 ₽</div>')
            } else {
                $(this).parents('form').submit();
            }

        });

    </script>
@endpush
