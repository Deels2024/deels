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

        <form class="form form--admin tools_block" action="{{route('admin_games')}}"  method="POST">
            @csrf
            <span class="tools_block_title">Сундуки</span>
            <input type="hidden" value="chests" name="type">
            <div class="form-row">
                <div class="settings_row">
                    <label for="title" class="col-12">Выдача после просмотра n ой сторис</label>
                    <div class="col-12">
                        <input type="number" class="form-control" value="{{$chests ? $chests->settings['stories'] : ""}}" name="settings[stories]" placeholder="Укажите значение">
                    </div>
                </div>
            </div>
            <div class="form-row">
                <div class="settings_row">
                    <label for="title" class="col-12">Выдача после проведения в ленте n времени, мин</label>
                    <div class="col-12">
                        <input type="number" class="form-control" value="{{$chests ? $chests->settings['time'] : ""}}" name="settings[time]" placeholder="Укажите значение">
                    </div>
                </div>
            </div>
            <div class="form-row">
                <div class="settings_row">
                    <label for="title" class="col-12">Выдача после n лайков в ленте</label>
                    <div class="col-12">
                        <input type="number" class="form-control" value="{{$chests ? $chests->settings['likes'] : ""}}" name="settings[likes]" placeholder="Укажите значение">
                    </div>
                </div>
            </div>
            <div class="form-row">
                <div class="settings_row">
                    <label for="title" class="col-12">Вознаграждение при выигрыше</label>
                    <div class="col-12">
                        <input type="number" class="form-control" value="{{$chests ? $chests->settings['prize'] : ""}}" name="settings[prize]" placeholder="Укажите значение">
                    </div>
                </div>
            </div>
            <div class="form-row">
                <div class="settings_row">
                    <label for="title" class="col-12">Шанс выпадения игры в видео, %</label>
                    <div class="col-12">
                        <input type="number" class="form-control" value="{{$chests ? $chests->settings['rare'] : ""}}" max="100" name="settings[rare]" placeholder="Укажите значение">
                    </div>
                </div>
            </div>

            <div class="form-row">
                <div class="settings_row">
                    <label for="title" class="col-12">Шанс выпадения дилсов в сундуке, %</label>
                    <div class="col-12">
                        <input type="number" class="form-control" value="{{$chests && isset($chests->settings['chance']) ? $chests->settings['chance'] : ""}}" max="100" name="settings[chance]" placeholder="Укажите значение">
                    </div>
                </div>
            </div>

            <div class="form-row">
                <div class="settings_row">
                    <label for="title" class="col-12">Появление сундуков</label>
                    <div class="col-12">
                        <select class="form-control" name="settings[game_spawn]">
                            <option value="start" {{$chests && isset($chests->settings['game_spawn']) && $chests->settings['game_spawn'] == 'start' ? 'selected' : ''}}>Начало</option>
                            <option value="mid" {{$chests && isset($chests->settings['game_spawn']) && $chests->settings['game_spawn'] == 'mid' ? 'selected' : ''}}>Середина</option>
                            <option value="end" {{$chests && isset($chests->settings['game_spawn']) && $chests->settings['game_spawn'] == 'end' ? 'selected' : ''}}>Конец</option>
                        </select>
                    </div>
                </div>
            </div>


            <button class="form__btn btn btn_fill">Обновить</button>

        </form>

        <form class="form form--admin tools_block" action="{{route('admin_games')}}"  method="POST">
            @csrf
            <span class="tools_block_title">Колесо фортуны</span>
            <input type="hidden" value="wheel" name="type">


            <div class="form-row">
                <div class="settings_row">
                    <label for="title" class="col-12">Тип выигрыша</label>
                    <div class="col-12">
                        <select class="form-control" name="settings[win_type]">
                            <option value="r" {{$wheel && isset($wheel->settings['win_type']) && $wheel->settings['win_type'] == 'none' ? 'selected' : ''}}>Ничего</option>
                            <option value="mid" {{$wheel && isset($wheel->settings['win_type']) && $wheel->settings['win_type'] == 'mid' ? 'selected' : ''}}>Средний</option>
                            <option value="rare" {{$wheel && isset($wheel->settings['win_type']) && $wheel->settings['win_type'] == 'rare' ? 'selected' : ''}}>Редкий</option>
                        </select>
                    </div>
                </div>
            </div>

            <div class="form-row">
                <div class="settings_row">
                    <label for="title" class="col-12">Выдача после просмотра n ой сторис</label>
                    <div class="col-12">
                        <input type="number" class="form-control" value="{{$wheel && isset($wheel->settings['stories']) ? $wheel->settings['stories'] : ""}}" name="settings[stories]" placeholder="Укажите значение">
                    </div>
                </div>
            </div>
            <div class="form-row">
                <div class="settings_row">
                    <label for="title" class="col-12">Выдача после проведения в ленте n времени, мин</label>
                    <div class="col-12">
                        <input type="number" class="form-control" value="{{$wheel && isset($wheel->settings['time']) ? $wheel->settings['time'] : ""}}" name="settings[time]" placeholder="Укажите значение">
                    </div>
                </div>
            </div>
            <div class="form-row">
                <div class="settings_row">
                    <label for="title" class="col-12">Выдача после n лайков в ленте</label>
                    <div class="col-12">
                        <input type="number" class="form-control" value="{{$wheel && isset($wheel->settings['likes']) ? $wheel->settings['likes'] : ""}}" name="settings[likes]" placeholder="Укажите значение">
                    </div>
                </div>
            </div>

            <div class="form-row">
                <div class="settings_row">
                    <label for="title" class="col-12">Редкость, %</label>
                    <div class="col-12">
                        <input type="number" class="form-control" value="{{$wheel && isset($wheel->settings['rare']) ? $wheel->settings['rare'] : ""}}" max="100" name="settings[rare]" placeholder="Укажите значение">
                    </div>
                </div>
            </div>

            <div class="form-row">
                <div class="settings_row">
                    <label for="title" class="col-12">Шанс выпадения дилсов, %</label>
                    <div class="col-12">
                        <input type="number" class="form-control" value="{{$wheel && isset($wheel->settings['chance']) ? $wheel->settings['chance'] : ""}}" max="100" name="settings[chance]" placeholder="Укажите значение">
                    </div>
                </div>
            </div>

            <div class="form-row">
                <div class="settings_row">
                    <label for="title" class="col-12">Редкость появления</label>
                    <div class="col-12">
                        <select class="form-control" name="settings[game_spawn]">
                            <option value="start" {{$wheel && isset($wheel->settings['game_spawn']) && $wheel->settings['game_spawn'] == 'start' ? 'selected' : ''}}>Начало</option>
                            <option value="mid" {{$wheel && isset($wheel->settings['game_spawn']) && $wheel->settings['game_spawn'] == 'mid' ? 'selected' : ''}}>Середина</option>
                            <option value="end" {{$wheel && isset($wheel->settings['game_spawn']) && $wheel->settings['game_spawn'] == 'end' ? 'selected' : ''}}>Конец</option>
                        </select>
                    </div>
                </div>
            </div>

            <div class="form-row">
                @for ($i = 0; $i <= 7; $i++)
                <div class="settings_row">
                    <label for="title" class="col-12">Значение ячейки {{$i+1}}</label>

                    <div class="col-12">
                        <input type="number" class="form-control" value="{{$wheel && isset($wheel->settings['cell'][$i]) ? $wheel->settings['cell'][$i] : ""}}" name="settings[cell][{{$i}}]" placeholder="Укажите значение {{$i+1}}">
                    </div>

                </div>
                @endfor
            </div>


            <button class="form__btn btn btn_fill">Обновить</button>

        </form>
    </main>

@endsection

