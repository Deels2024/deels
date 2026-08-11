<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="utf-8">
    <title>@yield('title') {{isset($_GET['page']) && $_GET['page'] > 0 ? '- страница '.$_GET['page'] : ''}} @show</title>
    @yield('meta-data')
    @stack('meta-data')
    @include('meta')
    <meta charset="UTF-8"/>
    <meta http-equiv="X-UA-Compatible" content="IE=edge"/>
    <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
    <meta name="csrf-token" content="{{csrf_token()}}">
    <link rel="shortcut icon" href="/dist/images/icons/favicon.ico" type="image/x-icon"/>
    @if(isset($_GET) && !empty($_GET))
        <link rel="canonical" href="{{request()->url()}}">
    @endif
    <link rel="apple-touch-icon" sizes="180x180" href="/images/favicons/apple-touch-icon.png">
    <link rel="icon" type="image/png" sizes="32x32" href="/images/favicons/favicon-32x32.png">
    <link rel="icon" type="image/png" sizes="16x16" href="/images/favicons/favicon-16x16.png">
    <link rel="manifest" href="/images/favicons/site.webmanifest">
    <meta name="msapplication-TileColor" content="#da532c">
    <meta name="theme-color" content="#ffffff">
    <link rel="manifest" href="/manifest.json">
    <link rel="stylesheet" href="{{ext_asset('/dist/css/app.min.css')}}"/>
    <link rel="stylesheet" href="{{ext_asset('/dist/css/deels-v2.css')}}"/>
    <link rel="stylesheet" href="{{ext_asset('/dist/css/deels-v2-compat.css')}}"/>
    <link rel="stylesheet" href="{{ext_asset('/dist/css/deels-v2-create.css')}}"/>
    <link rel="stylesheet" href="{{ext_asset('/dist/css/deels-v2-social.css')}}"/>
    <link rel="stylesheet" href="{{ext_asset('/dist/css/deels-v2-account.css')}}"/>
    <script defer src="{{ext_asset('/dist/js/deels-v2.js')}}"></script>
    @yield('page-css')
    <script>
        window.Laravel = {!! json_encode([
            'csrfToken' => csrf_token(),
        ]) !!};

        window.userId = {{Auth::id() ?? 0}}
    </script>
    @if(request()->cookie('web_app'))
        @include('partials.telegram_webapp_auth')
    @endif
    @include('layouts/neon/partials/counters')
</head>

<body>
@include('layouts/neon/partials/header_modal')
@include('layouts.neon.main_menu')
