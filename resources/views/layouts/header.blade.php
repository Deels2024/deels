<!DOCTYPE html>
<html lang="{{ config('app.locale') }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <meta name="description" content="">
    <meta name="author" content="">
    @if(request()->cookie('web_app'))
        <script src="https://telegram.org/js/telegram-web-app.js"></script>
    @endif
    <link rel="icon" href="{{asset('assets/images/favicon.ico')}}">

    <!-- CSRF Token -->
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title> @yield('title') @show </title>

    @yield('meta-data')

    <link href="{{ asset('assets/css/bootstrap.min.css') }}" rel="stylesheet">
    <!-- Font awesome 4.4.0 -->
    <link rel="stylesheet" href="{{ asset('assets/font-awesome-4.4.0/css/font-awesome.min.css') }}">
    <!-- load page specific css -->

    <!-- main select2.css -->
    <link rel="stylesheet" href="{{ asset('assets/plugins/toastr/toastr.min.css') }}">
    <!-- Conditional page load script -->
    @if(request()->segment(1) === 'dashboard')
        <link rel="stylesheet" href="{{ asset('assets/css/admin.css') }}">
        <link rel="stylesheet" href="{{ asset('assets/plugins/metisMenu/dist/metisMenu.min.css') }}">
    @endif
<!-- main style.css -->
    <link href="http://fonts.googleapis.com/css?family=PT+Sans:regular,italic,bold,bolditalic"
          rel="stylesheet" type="text/css"/>
    <link href="{{ asset('assets/css/style.css') }}" rel="stylesheet">
    @yield('page-css')

    @if(get_option('additional_css'))
        <style type="text/css">
            {{ get_option('additional_css') }}
        </style>
    @endif

    <script>
		window.Laravel = {!! json_encode([
            'csrfToken' => csrf_token(),
        ]) !!};
        window.userId = {{Auth::id() ?? 0}};
    </script>
    @if(request()->cookie('web_app'))
        @include('partials.telegram_webapp_auth')
    @endif

    <meta name="apple-mobile-web-app-status-bar" content="#FFE1C4">
    <meta name="theme-color" content="#FFE1C4">
    <link rel="manifest" href="/manifest.json">
    <!-- Yandex.Metrika counter -->
    <script type="text/javascript" >
        (function(m,e,t,r,i,k,a){m[i]=m[i]||function(){(m[i].a=m[i].a||[]).push(arguments)};
            m[i].l=1*new Date();
            for (var j = 0; j < document.scripts.length; j++) {if (document.scripts[j].src === r) { return; }}
            k=e.createElement(t),a=e.getElementsByTagName(t)[0],k.async=1,k.src=r,a.parentNode.insertBefore(k,a)})
        (window, document, "script", "https://mc.yandex.ru/metrika/tag.js", "ym");

        ym(96537947, "init", {
            clickmap:true,
            trackLinks:true,
            accurateTrackBounce:true,
            webvisor:true
        });
    </script>
    <noscript><div><img src="https://mc.yandex.ru/watch/96537947" style="position:absolute; left:-9999px;" alt="" /></div></noscript>
    <script type="text/javascript">!function(){var t=document.createElement("script");t.type="text/javascript",t.async=!0,t.src='https://vk.ru/js/api/openapi.js?173',t.onload=function(){VK.Retargeting.Init("VK-RTRG-1891647-73Ile"),VK.Retargeting.Hit()},document.head.appendChild(t)}();</script><noscript><img src="https://vk.ru/rtrg?p=VK-RTRG-1891647-73Ile" style="position:fixed; left:-999px;" alt=""/></noscript>
    <!-- /Yandex.Metrika counter -->
</head>
<body>

@include('layouts.main_menu')
