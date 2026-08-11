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

    </style>
    <main class="admin-main">
        <div class="account-main__head">
            <div class="account-main__head-side">
                <h1 class="account-main__title">Логи</h1>
            </div>
        </div>

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
                display: block;
                margin-top: 5px;
                font-size: 20px;
                font-weight: 600;
            }
            .counting .digits.large {
                font-size: 40px;
            }
        </style>

        <div class="admin-table">
            <table>
                <thead>
                <tr>
                    <th>Тип</th>
                    <th>Описание</th>
                    <th>IP</th>
                    <th>Дата</th>
                </tr>
                </thead>
                <tbody>
                @if(count($logs) > 0)
                    @foreach($logs as $log)
                        <tr>
                            <td>
                                <p>{{$log->type}}</p>
                            </td>
                            <td>
                                <p>
                                    {{$log->description}}
                                </p>
                            </td>

                            <td>
                                {{$log->ip}}
                            </td>
                             <td>
                                {{\Carbon\Carbon::parse($log->created_at)->format('d.m.Y H:i')}}
                            </td>



                        </tr>
                    @endforeach
                @else
                    <td colspan="5">
                        Нет записей
                    </td>
                @endif
                </tbody>
            </table>
            {{ $logs->links() }}

        </div>
        <style>
            .admin-table .btn {
                padding: 5px 5px !important;
                margin-bottom: 5px;
            }
        </style>

    </main>

@endsection
