@extends('layouts.admin.app_neon')

@section('title') @if(! empty($title)) {{$title}} @endif  @parent @endsection

@section('content')

    <main class="admin-main">
        <div class="account-main__head">
            <div class="account-main__head-title">
                <h1 class="account-main__title">{{ $title }}</h1>
            </div>
        </div>
{{--        <button class="aside-nav-btn">Открыть меню</button>--}}
        <div class="admin-table">
            @if($withdraw_requests->count() > 0)
                <div class="admin-table">
                    <table>
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Пользователь</th>
                                <th>@lang('app.amount')</th>
                                <th>Статус</th>
                                <th>Контакты</th>
                                <th>@lang('app.date')</th>
                                <th>#</th>
                            </tr>
                        </thead>
                    <tbody>
                    @foreach($withdraw_requests as $withdraw)
                        <tr>
                            <td>
                                {{$withdraw->id}}
                            </td>
                            <td>
                                <div class="avatar avatar--sm mr-1">
                                    @if($withdraw->user_id)
                                        <img src="{{$withdraw->user->avatar()}}" class="magnific_image circle-img" data-image="{{$withdraw->user->avatar()}}" alt="{{$withdraw->user->fullname}}">
                                    @else
                                        <img src="{{avatar_by_email($withdraw->user->email)}}" class="magnific_image circle-img" data-image="{{avatar_by_email($withdraw->user->email)}}" alt="{{$withdraw->user->email}}">
                                    @endif
                                </div>
                                <p class="comment-email">{{$withdraw->user->username}}<br>{{$withdraw->user->email}}</p>
                            </td>
                            <td>
                                <span style="display: flex; align-items: center">{{$withdraw->withdrawal_amount}} ₽
                            </td>
                            <td>
                                <span class="badge badge-pill badge-{{$withdraw->getStatusColor()}}">{{$withdraw->getStatus()}}</span>
                            </td>
                            <td> {{$withdraw->user->contacts ?? '-'}}</td>
                            <td>{{$withdraw->created_at->format('d.m.Y')}}</td>
                            <td>
                                @if(Auth::user()->is_admin())

                                    @if($withdraw->status == 'pending')
                                        <table>
                                            <tr>
                                                <td><form action="" method="post"> @csrf
                                                        <input type="hidden" name="type" value="approved">
                                                        <input type="hidden" name="withdraw" value="{{$withdraw->id}}">
                                                        <button type="submit" class=" actions-link" style="background-image: url(/dist/images/admin_icons/icon-checkmark.svg)"></button>
                                                    </form>
                                                </td>
                                                <td>
                                                    <form action="" method="post"> @csrf
                                                        <input type="hidden" name="type" value="declined">
                                                        <input type="hidden" name="withdraw" value="{{$withdraw->id}}">
                                                        <button type="submit" class=" actions-link" style="background-image: url(/dist/images/admin_icons/icon-del.svg)"></button>
                                                    </form>
                                                </td>
                                            </tr>
                                        </table>
{{--                                    @elseif($withdraw->status == 'approved')--}}
{{--                                        <form action="" method="post"> @csrf--}}
{{--                                            <input type="hidden" name="type" value="declined">--}}
{{--                                            <input type="hidden" name="withdraw" value="{{$withdraw->id}}">--}}
{{--                                            <button type="submit" class=" actions-link" style="background-image: url(/dist/images/admin_icons/icon-del.svg)"></button>--}}
{{--                                        </form>--}}
{{--                                    @elseif($withdraw->status == 'declined')--}}
{{--                                        <form action="" method="post"> @csrf--}}
{{--                                            <input type="hidden" name="type" value="approved">--}}
{{--                                            <input type="hidden" name="withdraw" value="{{$withdraw->id}}">--}}
{{--                                            <button type="submit" class=" actions-link" style="background-image: url(/dist/images/admin_icons/icon-checkmark.svg)"></button>--}}
{{--                                        </form>--}}
                                    @endif
                                @endif
                            </td>
                        </tr>
                    @endforeach
                    </tbody>
                </table>

                    {{$withdraw_requests->withQueryString()->links()}}

            @else
                <div class="no-data-wrap text-center p-5 mt-5">
                    <i class="fa fa-frown-o"></i>
                    <h1>@lang('app.no_available_data')</h1>
                </div>
            @endif



            <div class="clearfix"></div>
        </div>

    </main>


@endsection