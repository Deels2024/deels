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
                <h1 class="account-main__title">Мои конкурсы</h1>
            </div>
        </div>
{{--        <button class="aside-nav-btn">Открыть меню</button>--}}
        <div class="admin-table">
            <table>
                <thead>
                <tr>
                    <td>Название конкурса</td>
                    <td>Мои <img src="/images/action-top-banner/health.svg" alt="" /></td>
                    <td>Дата проведения</td>
                    <td>Статус конкурса</td>
                </tr>
                </thead>
                <tbody>
                @if($payments->count() > 0)
                    @foreach($payments as $payment)
                        <tr>
                            <td>
                                {{$payment->category_name}}
                            </td>
                            <td>
                               {{$payment->count}} <img src="/images/action-top-banner/health.svg" alt="" style="height: auto;width: auto;" />
                            </td>
                            <td>
                                <p>27.06 по 11.07</p>
                            </td>
                            <td>
                                <img src="/images/action-top-banner/strong.svg" alt="" style="height: auto;width: auto;" />
                            </td>
                        </tr>
                    @endforeach
                @endif
                </tbody>
            </table>
        </div>
    </main>
@endsection
