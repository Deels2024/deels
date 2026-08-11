@extends('layouts.admin.app_neon')

@section('title')
    @if(! empty($title))
        {!!$title!!}
    @endif  @parent
@endsection

@section('content')

    <style>
        .fixed_width {
            max-width: 300px;
            min-width: 200px;
            width: 100%;
            word-break: break-all;
        }

        .account__content {
            min-width: 0;
        }

        .admin-main {
            max-width: 100%;
        }

        .user-notify-form {
            display: flex;
            width: 260px;
            gap: 8px;
            align-items: flex-start;
            margin-top: 8px;
        }

        .user-notify-form textarea {
            width: 180px;
            min-width: 0;
            min-height: 38px;
            padding: 8px;
            resize: vertical;
        }

        .user-notify-form .btn {
            min-height: 38px;
            padding: 8px 12px;
            font-size: 12px;
            line-height: 14px;
        }

        .users-table-scroll {
            width: 100%;
            max-width: 100%;
            overflow-x: auto;
            overflow-y: hidden;
            -webkit-overflow-scrolling: touch;
        }

        .users-table-scroll table {
            width: max-content !important;
            min-width: 1200px;
        }

        .users-table-scroll th,
        .users-table-scroll td {
            white-space: normal;
        }

        .users-table-scroll .actions {
            display: flex;
            flex-wrap: nowrap;
            gap: 4px;
        }
    </style>
    <main class="admin-main">
        <div class="account-main__head">
            <div class="account-main__head-side">
                <h1 class="account-main__title">Пользователи</h1><span>Общее : {!!number_format($users_count)!!}</span>
            </div>

            <div class="account-main__head-side"><a class="btn btn_fill d-flex ai-center download_excel"
                                                    href="{{\Illuminate\Support\Str::contains(request()->fullUrl(),'page') ? request()->fullUrl().'&excel=1' : '?excel=1'}}">
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

        <div class="d-flex mb-4">
            <a href="{{route('users')}}" class="btn btn-sm mr-2" style="padding: 10px; font-size: 12px; line-height: 12px; height: auto; min-height: auto;">По возрастанию</a>
            <a href="{{route('users')}}?type=DESC" class="btn btn-sm mr-2" style="padding: 10px; font-size: 12px; line-height: 12px; height: auto; min-height: auto;">По убыванию</a>
            <a href="{{route('users')}}?unsubscribed=true" class="btn btn-sm mr-2" style="padding: 10px; font-size: 12px; line-height: 12px; height: auto; min-height: auto; background-color: #454545; border-color: transparent!important;">Отписанные</a>
            <a href="{{route('users')}}?deleted=true" class="btn btn-sm mr-2" style="padding: 10px; font-size: 12px; line-height: 12px; height: auto; min-height: auto; background-color: #ff0000; border-color: transparent!important;">Удаленные</a>
            <a href="{{route('users')}}?suspicious_moderation=1" class="btn btn-sm mr-2" style="padding: 10px; font-size: 12px; line-height: 12px; height: auto; min-height: auto; background-color: #d97706; border-color: transparent!important;">Подозрительные на модерации</a>
        </div>
{{--        <button class="aside-nav-btn">Открыть меню</button>--}}
        <form class="form form--admin" action="">
            {{--            <div class="form-row">--}}
            {{--                <div class="form-field form-field--6">--}}
            {{--                    <input type="date" name="date-from">--}}
            {{--                </div>--}}
            {{--                <div class="form-field form-field--6">--}}
            {{--                    <input type="date" name="date-to">--}}
            {{--                </div>--}}
            {{--            </div>--}}
            <div class="form-field">
                <input type="text" value="{{request('q')}}" name="q" placeholder="Поиск пользователей">
            </div>
            {{--            <div class="form-row">--}}
            {{--                <div class="form-field form-field--6">--}}
            {{--                    <input type="text" typemode="numeric" name="cost-from" placeholder="Сумма от">--}}
            {{--                </div>--}}
            {{--                <div class="form-field form-field--6">--}}
            {{--                    <input type="text" typemode="numeric" name="cost-to" placeholder="Сумма до">--}}
            {{--                </div>--}}
            {{--            </div>--}}
            {{--            <label class="d-flex ai-center mt-1 mb-2">--}}
            {{--                <input class="mr-1" type="checkbox"><span>Пополненые</span>--}}
            {{--            </label>--}}
            <button class="form__btn btn btn_fill">Поиск</button>
        </form>
        <div class="admin-table users-table-scroll">
            <table>
                <thead>
                <tr>
                    <th>ID</th>
                    <th>Имя</th>
                    <th>E-mail</th>
                    <th>Ник</th>
{{--                    <th>Адрес</th>--}}
                    <th>Телефон</th>
                    <th>Подтвержден</th>
{{--                    <th>Реферальный код</th>--}}
                    <th>Дата создания аккаунта</th>
                    <th>Данные</th>
                    @if(isset($_GET['deleted']))
                        <th>Дата удаления аккаунта</th>
                    @endif
                    <th>Сумма донатов</th>
                    @if(!isset($_GET['deleted']))
                    <th>Действие</th>
                    @endif
                </tr>
                </thead>
                @if($users->count() > 0)
                    <tbody>
                    @foreach($users as $user)
                        <tr>
                            <td>
                                <p>{!! $user->id ?? '-' !!}</p>
                                @if($user->suspicious_moderation_pending)
                                    <span class="badge badge-pill badge-warning">Ручная модерация</span>
                                @elseif($user->is_suspicious)
                                    <span class="badge badge-pill badge-secondary">Подозрительный</span>
                                @endif
                            </td>
                            <td>
                                <p>{!! $user->name ?? '-' !!}</p>
                            </td>
                            <td>
                                <p class="fixed_width">{!!$user->email!!}</p>
                            </td>
                            <td>
                                <p class="fixed_width">{!!$user->username!!}</p>
                            </td>
{{--                            <td>--}}
{{--                                <p>{!!$user->address!!}</p>--}}
{{--                            </td>--}}
                            <td>
                                <p>
                                    {!!$user->phone!!}
                                    {!! $user->phoneVerify && $user->phoneVerify->token ? '<br><span class="badge badge-pill badge-secondary">Код: '.$user->phoneVerify->token.'</span>' : '' !!}
                                </p>
                            </td>
                            <td>
                                @if($user->phoneVerify)
                                <p>{!! $user->phoneVerify->is_verified ? '<span class="badge badge-pill badge-success">Да</span>' : '<span class="badge badge-pill badge-danger">Нет</span>' !!} </p>
                                @endif
                            </td>

{{--                            <td>--}}
{{--                                <p>{!!$user->referral_code!!}</p>--}}
{{--                            </td>--}}
                            <td>
                                <p>{!!$user->created_at!!}</p>
                            </td>
                            <td>
                                <p>
                                    @if($user->ip_address)
                                        IP: {{$user->ip_address}}
                                   @endif
                                    @if($user->user_data)
                                        ---<br>
                                        @php
                                        $user_data = json_decode($user->user_data, true);
                                        @endphp
                                        Устройство: {{$user_data['device'] ?? 'Не определено'}}<br>
                                        Браузер: {{$user_data['browser'] ?? 'Не определено'}}<br>
                                        Приложение: {{isset($user_data['is_app']) ? 'Да' : 'Нет'}}<br>
{{--                                        IP: {{$user_data['ip_address'] ?? 'Не определено'}}<br>--}}
                                   @endif

                                </p>
                            </td>

                            @if(isset($_GET['deleted']))
                                <td>
                                    <p>{!!$user->deleted_at!!}</p>
                                </td>
                            @endif
                            <td>
                                <p>{{\App\Models\Payment::query()->where('user_id', $user->id)->where('status', 'success')->sum('amount')}}
                                    ₽</p>
                            </td>
                            @if(!isset($_GET['deleted']))
                            <td>
                                <div class="d-flex fd-column">
                                    <div class="actions mb-1">
                                        <a class="actions-link" href="{!!route('user.profile', $user->id)!!}"
                                           style="background-image: url(/dist/images/admin_icons/icon-eye.svg)" target="_blank" data-toggle="tooltip"  title="Открыть профиль"></a>
                                        <a class="actions-link" href="{!!route('users_edit', $user->id)!!}"
                                           style="background-image: url(/dist/images/admin_icons/icon-edit.svg)" data-toggle="tooltip"  title="Редактировать"></a>
                                        <a class="actions-link" href="{!!route('user_status', [$user->id, 'block'])!!}"
                                           style="background-image: url(/dist/images/admin_icons/icon-block.svg)" data-toggle="tooltip"  title="Заблокировать"></a>
                                        <a class="actions-link" href="{!!route('user_delete', $user->id)!!}"
                                           style="background-image: url(/dist/images/admin_icons/icon-del.svg)" data-toggle="tooltip"  title="Удалить"></a>
                                        <a class="actions-link" href="{!!route('campaign_admin_search', ['user'=>$user->id])!!}" style="background-image: url(/dist/images/admin_icons/icon-fav.svg)" data-toggle="tooltip"  title="Показать копилки пользователя"></a>
                                        {{--                                        <a class="actions-link" href="#" style="background-image: url(/dist/images/admin_icons/icon-add.svg)"></a>--}}

                                        <a class="actions-link" href="{!!route('user_auth', $user->id)!!}"
                                           style="background-image: url(/dist/images/admin_icons/icon-avatar.svg); background-color: #ff0000" data-toggle="tooltip"  title="Авторизоваться пользователем"></a>
                                    </div>
                                    <form class="user-notify-form" method="post" action="{{ route('users_notify', $user->id) }}">
                                        @csrf
                                        <textarea name="text" placeholder="Сообщение пользователю в чат" required></textarea>
                                        <button class="btn btn_fill" type="submit">Отправить</button>
                                    </form>
                                    @if($user->suspicious_moderation_pending)
                                        <div class="d-flex mt-1" style="gap: 8px">
                                            <form method="post" action="{{ route('users_suspicious_moderation', $user->id) }}">
                                                @csrf
                                                <input type="hidden" name="action" value="skip">
                                                <button class="btn btn-small btn-success btn-sm" type="submit">Пропустить</button>
                                            </form>
                                            <form method="post" action="{{ route('users_suspicious_moderation', $user->id) }}">
                                                @csrf
                                                <input type="hidden" name="action" value="block">
                                                <button class="btn btn-small btn-danger btn-sm" type="submit">Заблокировать</button>
                                            </form>
                                        </div>
                                    @endif
                                </div>
                            </td>
                            @endif
                        </tr>
                    @endforeach
                    </tbody>
                @endif
            </table>
        </div>
        {!! $users->withQueryString()->links() !!}
        {{--        <ul class="pagination">--}}
        {{--            <li class="pagination-item"><a class="pagination-link pagination-link--prev" href="#"></a></li>--}}
        {{--            <li class="pagination-item"><a class="pagination-link" href="#">1</a></li>--}}
        {{--            <li class="pagination-item"><a class="pagination-link" href="#">2</a></li>--}}
        {{--            <li class="pagination-item"><a class="pagination-link" href="#">3</a></li>--}}
        {{--            <li class="pagination-item"><a class="pagination-link" href="#">4</a></li>--}}
        {{--            <li class="pagination-item"><a class="pagination-link" href="#">5</a></li>--}}
        {{--            <li class="pagination-item"><a class="pagination-link" href="#">...</a></li>--}}
        {{--            <li class="pagination-item"><a class="pagination-link active" href="#">1780</a></li>--}}
        {{--            <li class="pagination-item"><a class="pagination-link" href="#">1781</a></li>--}}
        {{--            <li class="pagination-item"><a class="pagination-link pagination-link--next" href="#"></a></li>--}}
        {{--        </ul>--}}
    </main>
@endsection

@section('page-js')

@endsection
