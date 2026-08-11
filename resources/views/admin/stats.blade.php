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
                <h1 class="account-main__title">Статистика</h1>
            </div>
        </div>
{{--        <button class="aside-nav-btn">Открыть меню</button>--}}
        <form class="form form--admin tools_block" action="">
            <span class="tools_block_title">Фильтр</span>

            <div class="form-row">
                <div class="form-field form-field--4">
                    <input type="text" value="{{request('user_id')}}" name="user_id" placeholder="Поиск по ID пользователя">
                </div>
                <div class="form-field form-field--4">
                    <input type="text" value="{{request('username')}}" name="username" placeholder="Поиск по username">
                </div>

                <div class="form-field form-field--4">
                    <input type="text" value="{{request('story_id')}}" name="story_id" placeholder="Поиск по ID сторис">
                </div>
                <div class="form-field form-field--4">
                    <select id="gender" name="type" class=" mb-0">
                        <option value="" {{ old('type') == "" || request('type') == "" ? 'selected' : '' }}>
                            Тип действия
                        </option>
                        <option value="like" {{ old('type') == "like" || request('type') == "like" ? 'selected' : '' }}>
                            Лайк
                        </option>
                        
                        <option value="view" {{ old('type') == "view" || request('type') == "view" ? 'selected' : '' }}>
                            Просмотр
                        </option>
                        <option value="comment" {{ old('type') == "comment" || request('type') == "comment" ? 'selected' : '' }}>
                            Комментарий
                        </option>
                        
                    </select>
                </div>
                

                <div class="form-field form-field--4">
                    <input type="date" value="{{request('date_from')}}" name="date_from">
                </div>
                <div class="form-field form-field--4">
                    <input type="date" name="date_to" value="{{request('date_to')}}">
                </div>
                <div class="form-field form-field--2">
                    <button class="form__btn btn btn_fill">Поиск</button>
                    <a href="{{route('stats')}}" class="form__btn btn btn-small">Сброс</a>
                </div>
                <div class="form-field form-field">

                </div>


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
                    <th>Пользователь ID</th>
                    <th>Пользователь</th>
                    <th>Тип</th>
                    <th>Сторис</th>
                    <th>Дата</th>
                </tr>
                </thead>
                <tbody>
                @if(count($actions) > 0)
                    @foreach($actions as $action)
                        <tr>
                            <td>
                                <p>{{$action['user_id']}}</p>
                            </td>
                            <td>
                                <p>
                                @if($action['user'])
                                    {{$action['user']->username}}
                                    @else
                                    @endif
                                </p>
                            </td>

                            <td>
                                <p>
                                    @if($action['type'] == 'view')
                                        Просмотр
                                    @endif
                                        @if($action['type'] == 'comment')
                                            Комментарий
                                        @endif
                                    @if($action['type'] == 'like')
                                        Лайк
                                    @endif

                                </p>
                            </td>
                            <td>
                                <p>
                                    @if($action['story'])
                                        <a href="{{$action['story']->getStoryShareUrl()}}" target="_blank">{{$action['story']->id}} [открыть]</a>
                                        @if($action['title'])
                                            <br>Заголовок: {{$action['title']}}
                                        @endif
                                             @if($action['description'])
                                            <br>Описание: {{$action['description']}}
                                        @endif


                                        @if($action['story'])

                                        <br><br>
                                            @foreach($action['story']->tags as $tag)
                                                    #{{$tag->title}}{{!$loop->last ? ' ' : ''}}
                                        @endforeach

                                         @endif

                                    @else
                                    @endif
                                </p>
                            </td>

                            <td>
                                <p>{{\Carbon\Carbon::parse($action['created_at'])->format('d.m.Y H:i')}}</p>
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
            {{ $actions->links() }}

        </div>
        <style>
            .admin-table .btn {
                padding: 5px 5px !important;
                margin-bottom: 5px;
            }
        </style>

    </main>

@endsection
