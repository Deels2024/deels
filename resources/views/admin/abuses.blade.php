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
                <h1 class="account-main__title">{{$title}}</h1>
            </div>
        </div>


        <div class="d-flex mb-4">
            <a href="{{route('abuses_list')}}" class="btn btn-sm mr-2" style="padding: 10px; font-size: 12px; line-height: 12px; height: auto; min-height: auto;">На модерации</a>
            <a href="{{route('abuses_list')}}?type=confirmed" class="btn btn-sm mr-2" style="padding: 10px; font-size: 12px; line-height: 12px; height: auto; min-height: auto;">Одобренные</a>
        </div>
        <div class="d-inline-flex mb-4">

            <form action="{{'abuses_list'}}" type="GET" class="d-flex mb-4">
                <input type="hidden" name="type" class="" value="{{$_GET['type'] ?? ''}}"/>
            <div class="">
                <input id="name" class="new__input" type="text" placeholder="Кто пожаловался ID" name="abuser_id" value="{{$_GET['abuser_id'] ?? ''}}">
            </div>
            <div class="ml-4">
                <input id="name" class="new__input" type="text" placeholder="На кого пожаловались ID" name="user_id" value="{{$_GET['user_id'] ?? ''}}">
            </div>
                <div class="ml-4">
                    <button type="submit" class="btn btn-small ml-4" style="padding: 5px; max-height: auto">Применить</button>
                </div>
            </form>
        </div>


            <div class="followers-block">
                @if(count($abuses))
                    <div class="admin-table">
                        <table>
                            <thead>
                            <tr>
                                <th>На кого пожаловались</th>
                                <th>Кто пожаловался</th>
                                <th>Причина</th>
                                <th>Контент заблокирован</th>
                                <th>Дата</th>
                                <th>Действие</th>
                            </tr>
                            </thead>
                            <tbody>
                            @foreach($abuses as $abuse)
                                <tr>
                                    <td style="vertical-align: middle;">
                                        <div class="bank__user">
                                            @if( $abuse->user)
                                            <img class="bank__img magnific_image circle-img" src="{!! $abuse->user->avatar() !!}" data-image="{!! $abuse->user->avatar() !!}"/>
                                            <div class="bank__user-text">{{$abuse->user->fullname}} (ID {{$abuse->user->id}})</div>
                                            @else
                                                <div class="bank__user-text">Пользователь удален</div>
                                            @endif
                                        </div>
                                    </td>
                                    <td style="vertical-align: middle;">
                                        <div class="bank__user">
                                            <img class="bank__img magnific_image circle-img" src="{!! $abuse->abuser->avatar() !!}" data-image="{!! $abuse->abuser->avatar() !!}"/>
                                            <div class="bank__user-text">{{$abuse->abuser->fullname}} (ID {{$abuse->abuser->id}})</div>
                                        </div>
                                    </td>
                                    <td style="vertical-align: middle;">
                                        <p>
                                            {{$abuse->abuse}}
                                        </p>
                                    </td>
                                    <td style="vertical-align: middle;">
                                        <p>
                                            {{$abuse->blocked ? 'Да' : 'Нет'}}
                                        </p>
                                    </td>
                                    <td style="vertical-align: middle;">
                                        <p>
                                            {{\Carbon\Carbon::parse($abuse->created_at)->format('d.m.Y H:i')}}
                                        </p>
                                    </td>
                                    <td style="vertical-align: middle;">
                                       <div class="d-flex">
                                           @if(!$abuse->confirmed)
                                           <form action="" method="post"> @csrf
                                               <input type="hidden" name="type" value="approved">
                                               <input type="hidden" name="abuse_id" value="{{$abuse->id}}">
                                               <button type="submit" class=" actions-link" style="background-image: url(/dist/images/admin_icons/icon-checkmark.svg)"></button>
                                           </form>
                                           @endif
                                           <form action="" method="post" class="ml-4"> @csrf
                                               <input type="hidden" name="type" value="declined">
                                               <input type="hidden" name="abuse_id" value="{{$abuse->id}}">
                                               <button type="submit" class=" actions-link" style="background-image: url(/dist/images/admin_icons/icon-del.svg)"></button>
                                           </form>
                                       </div>
                                    </td>
                                </tr>
                            @endforeach

                            </tbody>
                        </table>
                    </div>


                    {{$abuses->links()}}
                @else
                    Жалоб нет
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
                if(data.count == 0) {
                    $('.followers-block').html('Вы не подписаны');
                } else {
                    like_btn.parents('tr').remove();
                }
            }
        });
    });
</script>
<script>
    $('body').on('click', '.closebtn',function (e) {
        $(this).parents('.alert').remove();
    });
</script>
@endpush

