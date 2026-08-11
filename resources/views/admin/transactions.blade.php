@extends('layouts.admin.app_neon')

@section('title')
    @if(! empty($title))
        {!!$title!!}
    @endif  @parent
@endsection

@section('content')
    <style>
        .tools_block {
            padding: 20px 10px 20px 10px; background: #0d0f2c; border: 1px solid #b224ef; border-radius: 10px; position: relative; margin-top: 40px; max-width: 100%;
        }
        .tools_block .form-row {
            align-items: center; display:flex; width: 100%; max-width: 100%;
        }
        .tools_block .form__btn {
            padding: 10px
        }
        .tools_block input {
            margin-top: 0!important;
            margin-bottom: 5px!important;
        }
        .tools_block .tools_block_title {
            position: absolute; top: -9px; border-radius: 4px;background: #0d0f2c; letter-spacing:1px; font-weight:600; padding: 4px 8px; font-size: 9px; text-transform: uppercase
        }
        .form-field--4 {
            width: calc(30% - 10px);
        }
        .form-field-3 {
            width: calc(25% - 10px);
        }
        .form-field-checkbox {
            display: flex;
            align-items: center;
            gap: 8px;
            color: #fff;
            min-width: 240px;
            margin-bottom: 5px;
        }
        .form-field-checkbox input {
            width: auto;
            margin: 0!important;
        }

    </style>
    <main class="admin-main">
        <div class="account-main__head">
            <div class="account-main__head-side">
                <h1 class="account-main__title">Транзакции дилсов</h1>
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
        <form class="form form--admin tools_block" action="">
            <span class="tools_block_title">Фильтр</span>

            <div class="form-row">
                <div class="form-field form-field--4">
                    <input type="text" value="{{request('user_id')}}" name="user_id" placeholder="Поиск по ID пользователя">
                </div>
                <div class="form-field form-field--3">
                    <input type="date" value="{{request('date_from')}}" name="date_from">
                </div>
                <div class="form-field form-field--3">
                    <input type="date" name="date_to" value="{{request('date_to')}}">
                </div>
                <div class="form-field form-field--3">
                    <button class="form__btn btn btn_fill">Поиск</button>
                </div>

            </div>
            <div class="form-row">
                <label class="d-flex ai-center mt-1 mb-1">
                    <input class="mr-1" type="checkbox" name="show_project_transactions" value="1" {{ ($show_project_transactions ?? request()->boolean('show_project_transactions')) ? 'checked' : '' }}><span>Показать транзакции проекта</span>
                </label>
            </div>

        </form>

        <form class="form form--admin tools_block" action="" method="POST">
            @csrf
            <span class="tools_block_title">Пополнить кошелек</span>

            <div class="form-row">
                <input type="text" value="{{request('user_id')}}" name="user_id" placeholder="ID пользователя" style="margin: 0; width: auto; min-width: 35%">
                <input type="text" value="{{request('coins')}}" name="coins" placeholder="Кол-во дилсов" style="margin: 0; width: auto; min-width: 35%">
                <button class="form__btn btn btn_fill">Пополнить</button>
            </div>

        </form>

        <style>
            .counting {
                width: 100%;
                margin-top: 30px;
                display: flex;
                align-items: center;
                justify-content: space-between;
                background:#0d0f2c;
                border-radius: 10px;
                overflow: hidden;
            }
            .counting .block {
                padding: 10px;
                display: inline-flex;
                flex-direction: column;
                align-items: center;
                justify-content: center;
                width: 50%;
                height: 100%;
                text-align: center;
                min-height: 110px;
            }
            .counting .digits {
                display: flex;
                margin-top: 5px;
                font-size: 20px;
                font-weight: 600;
                align-items: center;
                gap: 5px;

            }
            .counting .digits.large {
                font-size: 40px;
            }
        </style>
        <div class="counting">
            <div class="block">
                Пополнено:
                <span class="digits">{{number_format(floatval($deposit), 0, ',', ',')}} <img src="/dist/img/deels_cur.svg" class="medium_coin"></span>
                <span class="digits">{{number_format(floatval($deposit/100), 0, ',', ',')}} <span class="ruble-sign">₽</span></span>
            </div>
            <div  class="block" style="background: linear-gradient(90deg, #B224EF 0%, #7579FF 100%);">
                Прибыль проекта:
                <span class="digits large">{{number_format(floatval($commission), 0, ',', ',')}} <span class="ruble-sign">₽</span></span>
                <small>(20% от пополнений + списания за хранения и донаты в стрим)</small>
            </div>
        </div>
        <div class="admin-table">
            <table>
                <thead>
                <tr>
                    <th>ID</th>
                    <th>Пользователь</th>
                    <th>Описание</th>
                    <th>Дата</th>
                    <th>Сумма <img src="/dist/img/deels_cur.svg" class="small_coin"></th>
                </tr>
                </thead>
                <tbody>
                @if($transactions->count() > 0)
                    @foreach($transactions as $transaction)
                        <tr>
                            <td>
                                <p>{{$transaction->id}}</p>
                            </td>
                            <td>
                                <p><a href="{!!route('payment_view', $transaction->id)!!}">
                                        @if ($transaction->user_id)
                                            {{\App\Models\User::find($transaction->user_id)->username ?? 'Пользователь удален'}}
                                        @else
                                            {{$transaction->email ?? $transaction->payable->username ?? $transaction->payable->name ?? 'E-mail не указан'}}
                                        @endif
                                    </a></p>
                            </td>
                            <td>
                                <p>
                                    {{$transaction->getDescription()}}
                                </p>
                            </td>
                            <td>
                                <p>{!!$transaction->created_at->format('d.m.Y H:i')!!}</p>
                            </td>
                            <td>
                                <p>{!! $transaction->amount !!}</p>
                            </td>
                        </tr>
                    @endforeach
                @endif
                </tbody>
            </table>

            {!! $transactions->links() !!}
        </div>
        <style>
            .admin-table .btn {
                padding: 5px 5px !important;
                margin-bottom: 5px;
            }
        </style>

    </main>

@endsection
