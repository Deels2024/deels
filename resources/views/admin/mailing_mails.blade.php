@extends('layouts.admin.app_neon')



@section('content')
    <link href="https://cdn.jsdelivr.net/npm/summernote@0.8.18/dist/summernote-lite.min.css" rel="stylesheet">
    <style>
        .note-editor .note-editing-area .note-editable {
            background: #fff;
        }

        th {
            text-align: left;
        }

        .note-modal-backdrop {
            display: none !important;
        }

        @media (min-width: 768px) {
            .note-modal-content {
                margin: 150px auto !important;
            }
        }

        .note-modal-body .note-input {
            width: 86% !important;
            display: block !important;
            border: 1px solid #ededef !important;
            background: #fff !important;
            outline: 0 !important;
            padding: 6px 4px !important;
            font-size: 14px !important;
            -ms-box-sizing: border-box !important;
            box-sizing: border-box !important;
            opacity: 1 !important;
        }

        .note-modal-body input {
            color: #000000!important;
        }

        .note-form-group {
            padding-bottom: 50px !important;
        }

        .badge {
            display: inline-block;
            padding: 0.25em 0.4em;
            font-size: 75%;
            font-weight: 700;
            line-height: 1;
            text-align: center;
            white-space: nowrap;
            vertical-align: baseline;
            border-radius: 0.25rem;
        }
        .badge-pill {
            padding-right: 0.6em;
            padding-left: 0.6em;
            border-radius: 10rem;
        }
        .badge-success {
            color: #fff;
            background-color: #28a745;
        }
        .badge-primary {
            color: #fff;
            background-color: #007bff;
        }
        .badge-secondary {
            color: #fff;
            background-color: #6c757d;
        }
        .badge-danger {
            color: #fff;
            background-color: #dc3545;
        }
        .badge-warning {
            color: #212529;
            background-color: #ffc107;
        }
        .badge-info {
            color: #fff;
            background-color: #17a2b8;
        }
        .badge-light {
            color: #212529;
            background-color: #f8f9fa;
        }
        .badge-dark {
            color: #fff;
            background-color: #343a40;
        }

    </style>
    <main class="admin-main">
        <div class="account-main__head">
            <div class="account-main__head-side">
                <h1 class="account-main__title">Рассылка ID {{$mailing->id}}</h1>
                <a href="{{route('mailing')}}">[ Вернуться назад ]</a>
            </div>
        </div>

        <form class="form form--admin" action="">
            <div class="form-field">
                <input type="text" value="{{request('q')}}" name="q" placeholder="Поиск пользователей">
            </div>
            <label class="d-flex ai-center mt-1 mb-4">
                <input type="checkbox" class="mr-1" value="1" name="failed" {{request('failed') ? 'checked' : ''}}><span>С ошибками</span>
            </label>
            <label class="d-flex ai-center mt-1 mb-4">
                <input type="checkbox" class="mr-1" value="1" name="pending" {{request('pending') ? 'checked' : ''}}><span>Ожидает обработку</span>
            </label>
            <label class="d-flex ai-center mt-1 mb-4">
                <input type="checkbox" class="mr-1" value="1" name="sending" {{request('sending') ? 'checked' : ''}}><span>В очереди</span>
            </label>



            <button class="form__btn btn btn_fill">Поиск</button>
            @if(!empty(request()->input()))
                <a href="{{route('mailing_mails', [$mailing->id])}}">[ Сбросить ]</a>
            @endif
        </form>
        <div class="admin-table">
            <table>
                <thead>
                <tr>
                    <th scope="col">Email</th>
                    <th scope="col">Статус</th>
                    <th scope="col"></th>
                </tr>
                </thead>
                <tbody>
                @if(count($receivers) > 0)
                @foreach($receivers as $receiver)
                    <tr>
                        <td>
                            {{$receiver->email}}
                            @if($receiver->data)
                                <code class="d-block mt-2" style="color: #ff0000">{{$receiver->data}}</code>
                            @endif
                        </td>
                        <td>
                            <span class="badge badge-pill badge-{{$receiver->getStatusColor()}}">{{$receiver->getStatus()}}</span>
                            @if($receiver->status == 'fail')
                                <br>
                                <a class="badge badge-light mt-2" href="{{route('send_single_mail', [$receiver->id])}}">Повторить отправку</a>
                                <br><a class="badge badge-danger mt-2" href="{{route('remove_single_mail', [$receiver->id])}}">Исключить</a>
                            @endif
                        </td>
                        <td>
                            <a class="actions-link" href="{{route('mailing_mails_show', [$receiver->newsletter->id])}}" style="background-image: url(/dist/images/admin_icons/icon-eye.svg)" target="_blank"></a>
                        </td>
                    </tr>
                @endforeach
                @else
                    <tr>
                        <td colspan="2">
                            @if(!empty(request()->input()))
                                Нет писем по вашему запросу
                            @else
                                Нет сформированных писем
                            @endif
                        </td>
                    </tr>
                @endif
            </table>

            {!! $receivers->links() !!}
        </div>
    </main>

@endsection
