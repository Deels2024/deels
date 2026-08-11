@extends('layouts.admin.app_neon')
@section('title')
    @if( ! empty($title))
        {{ $title }} |
    @endif @parent
@endsection

@section('content')
    @php
        $mode = $mode ?? 'followers';
        $emptyText = [
            'friends' => 'Нет друзей',
            'followers' => 'Нет подписок',
            'followings' => 'Нет заявок в друзья',
        ][$mode] ?? 'Нет подписок';
    @endphp

    <main class="admin-main">
        <div class="account-main__head">
            <div class="account-main__head-title">
                <h1 class="account-main__title">{{$title}}</h1>
            </div>
        </div>

        <div class="d-flex mb-4 mt-7">
            <a href="{{route('user_friends')}}" class="btn btn-sm mr-2" style="padding: 10px; font-size: 12px; line-height: 12px; height: auto; min-height: auto;">Друзья</a>
            <a href="{{route('user_followers')}}" class="btn btn-sm mr-2" style="padding: 10px; font-size: 12px; line-height: 12px; height: auto; min-height: auto;">Мои подписки</a>
            <a href="{{route('user_followings')}}" class="btn btn-sm mr-2" style="padding: 10px; font-size: 12px; line-height: 12px; height: auto; min-height: auto;">Заявки в друзья</a>
        </div>

            <div class="followers-block">
                @if(count($followers))
                    <div class="admin-table">
                        <table>
                            <thead>
                            <tr>
                                <th style="text-align: left; padding-left: 20px">Пользователь</th>
                                <th style="text-align: left">Действие</th>
                            </tr>
                            </thead>
                            <tbody>
                            @foreach($followers as $follower)
                                <tr>
                                    <td style="vertical-align: middle;">
                                        <a class="bank__user" href="{{route('user.profile', $follower->followable->id) }}">
                                            <img class="bank__img magnific_image circle-img" src="{!! $follower->followable->avatar() !!}"/>
                                            <div class="bank__user-text">{{$follower->followable->fullname}}</div>
                                        </a>
                                    </td>
                                    <td style="vertical-align: middle;">
                                        <p style="display: flex; justify-content: flex-end; align-items: center; gap: 12px; margin: 0;">
                                            @if($mode === 'followings')
                                                <a class="btn btn-small follow_request_button" data-user="{{$follower->followable->id}}">Подписаться</a>
                                            @else
                                            <a class="btn btn-small unfollow_button" data-user="{{$follower->followable->id}}">Отписаться</a>
                                            @endif
                                            @if($mode !== 'followers' || $follower->followable->canReceiveFirstMessageFrom(Auth::user()))
                                                <button class="btn btn-small btn-contain chat_btn followers-chat-btn" data-user="{{$follower->followable->id}}" type="button"></button>
                                            @endif
                                        </p>
                                    </td>
                                </tr>
                            @endforeach

                            </tbody>
                        </table>
                    </div>


                @else
                    {{$emptyText}}
                @endif
            </div>

    </main>

@endsection



@push('after_scripts')
<script>
    $('body').on('click', '.unfollow_button', function (e) {
        e.preventDefault();
        var like_btn = $(this);
        var follow_id = $(this).attr('data-user');
        $(this).toggleClass('active');
        $.ajax({
            type: 'POST',
            url: '{{route('user.follow_toggle')}}',
            data: {user_id: '{{Auth::user()->id ?? null}}', follow_id: follow_id},
            success: function (data) {
                $('.alert-container').html('<div class="alert success"> <span class="closebtn">&times;</span>Успешно!</div>');
                like_btn.parents('tr').remove();
                if($('.followers-block tbody tr').length == 0) {
                    $('.followers-block').html('{{$emptyText}}');
                }
            }
        });
    });

    $('body').on('click', '.follow_request_button', function (e) {
        e.preventDefault();
        var like_btn = $(this);
        var follow_id = $(this).attr('data-user');
        $.ajax({
            type: 'POST',
            url: '{{route('user.follow_toggle')}}',
            data: {user_id: '{{Auth::user()->id ?? null}}', follow_id: follow_id},
            success: function (data) {
                $('.alert-container').html('<div class="alert success"> <span class="closebtn">&times;</span>Успешно!</div>');
                like_btn.parents('tr').remove();
                if($('.followers-block tbody tr').length == 0) {
                    $('.followers-block').html('{{$emptyText}}');
                }
            }
        });
    });
</script>
<style>
    .followers-chat-btn {
        width: 38px;
        min-width: 38px;
        height: 38px;
        min-height: 38px;
        padding: 0;
        flex: 0 0 38px;
        background-size: 22px 22px !important;
        transition: none !important;
        transform: none !important;
    }

    .followers-chat-btn:hover {
        width: 38px;
        min-width: 38px;
        height: 38px;
        min-height: 38px;
        transform: none !important;
        background-size: 22px 22px !important;
        transition: none !important;
        background-repeat: no-repeat;
        background-position: center;
    }

    .followers-chat-btn::before,
    .followers-chat-btn:hover::before {
        display: none !important;
    }
</style>
<script>
    $('body').on('click', '.closebtn',function (e) {
        $(this).parents('.alert').remove();
    });
</script>
@endpush
