@extends('layouts.admin.app_neon')
@section('title')
    @if( ! empty($title))
        {{ $title }} |
    @endif @parent
@endsection

@section('content')

    <main class="admin-main">
        <div class="account-main__head">
            <div class="account-main__head-title">
                <h1 class="account-main__title">История лайков
                    {{isset($_GET['story_id']) ? ' ID'.$_GET['story_id'] :''}}
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

        </style>




        <div class="comments bg-dark">

            <form action="{{route('admin_stories_likes')}}" type="GET" class="d-flex mb-4 align-items-center">
                <div class="">
                    <input id="story_id" class="new__input" type="text" placeholder="№ сторис" name="story_id" value="{{$_GET['story_id'] ?? ''}}">
                </div>
                <div class="" style="width: 200px">
                    <select name="challenge" style="margin: 0">
                        <option value="" disabled selected>Челлендж</option>
                        <option value="">
                          -
                        </option>
                        @foreach($challenges as $challenge)
                            <option value="{{$challenge->id}}" {{ old('challenge') == $challenge->id || request('challenge') == $challenge->id ? 'selected' : '' }}>
                                {{$challenge->title}}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="" style="width: 200px">
                    <select name="battle" style="margin: 0">
                        <option value="" disabled selected>Батл</option>
                        <option value="">
                            -
                        </option>
                        @foreach($battles as $battle)
                            <option value="{{$battle->id}}" {{ old('battle') == $battle->id || request('battle') == $battle->id ? 'selected' : '' }}>
                                {{$battle->title}}
                            </option>
                        @endforeach
                    </select>
                </div>


                <div class="ml-4">
                    <button type="submit" class="btn btn-small ml-4" style="padding: 5px; max-height: auto">Применить</button>
                </div>
            </form>
            @if($likes)
                <div class="admin-table">
                    <table>
                        <thead>
                        <tr>

                            <th>ID</th>
                            <th>Story</th>
                            <th>Пользователь</th>
                            <th>Тел</th>
                            <th>E-mail</th>

                            <th>Регистрация</th>
                            <th>Активация</th>
                            <th>Дата лайка</th>
                            <th>IP</th>

                        </tr>
                        </thead>
                        <tbody>
                        @foreach($likes as $like)
                            <tr>

                                <td>{{$like->id}}</td>
                                <td>#{{$like->story_id}}<br>
                                @if($like->story)
                                    @if($like->story->challenge)
                                        Челлендж: {{$like->story->challenge->title}}
                                    @endif
                                    @if($like->story->battle)
                                         Батл: {{$like->story->battle->title}}
                                    @endif

                                @endif
                                </td>
                                <td>{{$like->user ? $like->user->username.' (ID'.$like->user->id.')' : ''}}</td>
                                <td>{{$like->user ? $like->user->phone : ''}}</td>
                                <td>{{$like->user ? $like->user->email : ''}}</td>
                                <td>{{$like->user ? \Carbon\Carbon::parse($like->user->created_at)->format('d.m.Y H:i') : ''}}</td>
                                <td>{{$like->user ? $like->user->getPhoneVerifyDate() : ''}}</td>
                                <td>{{\Carbon\Carbon::parse($like->created_at)->format('d.m.Y H:i')}}</td>
                                <td>{{$like->ip_address ? $like->ip_address.' / ' : ''}} {{$like->user ? $like->user->ip_address : ''}}</td>
                            </tr>
                        @endforeach
                        </tbody>
                    </table>
                </div>
            @endif


        </div>
        {{$likes->withQueryString()->links()}}
    </main>

    @include('stories.modal')

@endsection

@section('page-js')
{{--    @include('dashboard.stories.stories_scripts')--}}
    <script>
        $(document).ready(function () {
            $('body').on('click', '.comment_action', function (e) {
                e.preventDefault();
                e.stopPropagation();

                var $that = $(this);
                if ($that.attr('data-action') === 'trash') {
                    if (!confirm("<?php echo trans('app.are_you_sure'); ?>")) {
                        return false;
                    }
                }

                var action = $that.data('action');

                $.ajax({
                    type: 'POST',
                    url: '{{ route('admin_stories.confirm') }}',
                    data: {story_id: $that.data('id'), action: action, _token: '{{ csrf_token() }}'},
                    success: function (data) {
                        console.log(data);
                        if (data.success == 1) {

                            if (action == 'approve') {
                                alert('Сторис подтверждена');
                                $that.remove();
                            } else {
                                alert('Сторис отклонена');
                                $that.parents('.comment-item').remove();
                            }

                        }
                    }
                });
            });
        });
    </script>

@endsection

