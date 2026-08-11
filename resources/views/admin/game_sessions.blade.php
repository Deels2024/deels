@extends('layouts.admin.app_neon')
@section('title')
    @if( ! empty($title))
        {{ $title }}
    @endif @parent
@endsection

@section('content')

    <main class="admin-main">
        <div class="account-main__head">
            <div class="account-main__head-title">
                <h1 class="account-main__title">{{$title}}
                </h1>
            </div>
        </div>

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
            .col-12 {
                display: block;
                width: 100%;
                margin-bottom: 10px;
            }
            .settings_row  {
                margin-bottom: 20px;
                width: 100%;
            }
            .col-12 input {
                width: 100%;
            }

        </style>

        <form class="form form--admin tools_block" action="">
            <span class="tools_block_title">Фильтр</span>

            <div class="form-row">
                <div class="form-field form-field--4">
                    <input type="text" value="{{request('user_id')}}" name="user_id" placeholder="Поиск по ID пользователя">
                </div>
                <div class="form-field form-field--3">
                    <div class="" style="width: 200px">
                        <select name="game" style="margin: 0">
                            <option value="" disabled selected>Тип игры</option>
                            <option value="chests" {{request('game') == 'chests' ? 'selected' : ''}}>
                                Сундуки
                            </option>
                            <option value="wheel" {{request('game') == 'wheel' ? 'selected' : ''}}>
                                Колесо фартуны
                            </option>
                        </select>
                    </div>
                </div>
                <div class="form-field form-field--3">
                    <div class="" style="width: 200px">
                        <select name="status" style="margin: 0">
                            <option value="" disabled selected>Статус</option>
                            <option value="started" {{request('status') == 'started' ? 'selected' : ''}}>
                                Начата
                            </option>
                            <option value="win" {{request('status') == 'win' ? 'selected' : ''}}>
                               Выигрыш
                            </option>
                            <option value="fail" {{request('status') == 'fail' ? 'selected' : ''}}>
                               Проигрыш
                            </option>
                            <option value="aborted" {{request('status') == 'aborted' ? 'selected' : ''}}>
                                Прервана
                            </option>
                        </select>
                    </div>
                </div>

                <div class="form-field form-field--3">
                    <button class="form__btn btn btn_fill">Поиск</button>
                </div>

            </div>

        </form>

        <div class="admin-table">
            <table>
                <thead>
                <tr>
                    <th>ID</th>
                    <th>Пользователь</th>
                    <th>Тип игры</th>
                    <th>Статус</th>
                    <th>Сумма</th>
                    <th>Дата</th>
                </tr>
                </thead>
                <tbody>
                @if($sessions->count() > 0)
                    @foreach($sessions as $session)
                        <tr>
                            <td>
                                <p>{{$session->id}}</p>
                            </td>
                            <td>
                                <p>
                                    @if ($session->user_id)
                                       (ID {{$session->user_id}}) {{\App\Models\User::find($session->user_id)->username ?? 'Пользователь удален'}}
                                    @else
                                        -
                                    @endif
                                </p>
                            </td>
                            <td>
                                <p>
                                    {{$session->getGameTitle()}}
                                </p>
                            </td>
                            <td>
                                <p>
                                    {{$session->getStatus()}}
                                </p>
                            </td>
                            <td>
                                <p>
                                    {{$session->prize}}
                                </p>
                            </td>
                            <td>
                                <p>{!! \Carbon\Carbon::parse($session->updated_at)->format('d.m.Y H:i') !!}</p>
                            </td>
                        </tr>
                    @endforeach
                @else
                    <tr><td colspan="6">Сесии не найдены</td></tr>
                @endif
                </tbody>
            </table>

            {!! $sessions->links() !!}
        </div>
        <style>
            .admin-table .btn {
                padding: 5px 5px !important;
                margin-bottom: 5px;
            }
        </style>

    </main>

@endsection

