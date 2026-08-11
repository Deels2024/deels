@extends('layouts.admin.app_neon')

@section('title')
    @if(! empty($title))
        {!!$title!!}
    @endif  @parent
@endsection

@section('content')
    <main class="admin-main">
        <div class="account-main__head">
            <div class="account-main__head-side">
                <h1 class="account-main__title">Благодарности</h1><span>Статистика ваших благодарностей</span>
            </div>
        </div>
{{--        <button class="aside-nav-btn">Открыть меню</button>--}}

        <div class="admin-table">
            <table>
                <thead>
                <tr>
                    <th>Идентификатор копилки</th>
                    <th>Автор копилки</th>
                    <th>Сумма</th>
                    <th>Донатер</th>
                    <th>Дата</th>
                    <th>Благодарность</th>
                    @if(auth()->user()->is_admin())
                        <th>Действия</th>
                    @endif
                </tr>
                </thead>
                <tbody align="center">
                @if($t->count() > 0)
                    @foreach($t as $thank)
                        <tr>
                            <td>
                                <p><a href="{!!route('campaign_single', $thank->payment->campaign_id)!!}">
                                        {{--                                        @if ($payment->user_id)--}}
                                        {{--                                            {{\App\Models\User::find($payment->user_id)->email}}--}}
                                        {{--                                        @else--}}
                                        {{--                                            {{$payment->email}}--}}
                                        {{--                                        @endif--}}
                                        {{$thank->payment->campaign_id}}
                                    </a></p>
                            </td>
                            <td>
                                <p>
                                    {{$thank->payment->campaign->user->username ?? 'Пользователь не найден'}}
                                </p>
                            </td>
                            <td>
                                <p>
                                    {!!get_amount($thank->payment->amount)!!}
                                </p>
                            </td>
                            <td>
                                <p>
                                    {{$thank->payment->user->username ?? 'Пользователь не найден'}}
                                </p>
                            </td>

                            <td>
                                <p>{!!$thank->created_at->format('d.m.Y')!!}</p>
                            </td>
                            <td>
                                @if ($thank->data['type']==='comment')
                                    {{$thank->data['payload']}}
                                @elseif ($thank->data['type']==='audio')
                                    <audio controls src="{{$thank->data['payload']}}"></audio>
                                @else
                                    <img width="320" height="240" alt="" src="{{$thank->data['payload']}}" />
                                @endif
                            </td>
                            @if(auth()->user()->is_admin())
                                <td>
                                    <div class="actions">
                                        <a class="actions-link comment_action" data-action="trash"
                                           data-id="{{$thank->id}}" href="javascript:;"
                                           style="background-image: url(/dist/images/admin_icons/icon-del.svg)"></a>
                                        @if ($thank->approved !== 1)
                                            <a class="actions-link comment_action" data-action="approve"
                                               data-id="{{$thank->id}}" href="javascript:;"
                                               style="background-image: url(/dist/images/admin_icons/icon-checkmark.svg)"></a>
                                        @endif
                                    </div>
                                </td>
                            @endif
                        </tr>
                    @endforeach
                @endif
                </tbody>
            </table>
        </div>
    </main>
@endsection



@section('page-js')
    <script>
        $(document).ready(function () {
            $('body').on('click', '.comment_action', function (e) {
                e.preventDefault();

                var $that = $(this);
                if ($that.attr('data-action') === 'trash') {
                    if (!confirm("<?php echo trans('app.are_you_sure'); ?>")) {
                        return false;
                    }
                }

                var action = $that.data('action');

                $.ajax({
                    type: 'POST',
                    url: '{{ route('moderate_thank') }}',
                    data: {thank_id: $that.data('id'), action: action, _token: '{{ csrf_token() }}'},
                    success: function (data) {
                        if (data.success === 1) {
                            window.location.reload()
                        }
                    }
                });
            });
        });
    </script>

@endsection
