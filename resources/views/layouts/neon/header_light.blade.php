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
    @if(request()->cookie('web_app'))
        <script src="https://telegram.org/js/telegram-web-app.js"></script>
    @endif
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
    <link rel="stylesheet" href=" {{ext_asset('/dist/css/owl.carousel.min.css')}}"/>
    <link rel="stylesheet" href=" {{ext_asset('/dist/css/owl.theme.default.min.css')}}"/>
    <link rel="stylesheet" href=" {{ext_asset('/dist/css/font/stylesheet.css')}}"/>
    <link rel="stylesheet" href=" {{ext_asset('/dist/css/magnific-popup.css')}}"/>
    <link rel="stylesheet" href=" {{ext_asset('/dist/css/admin_style.css')}}"/>
    <link rel="stylesheet" href=" {{ext_asset('/dist/css/kpromo_slider.css')}}"/>
    <link rel="stylesheet" href=" {{ext_asset('/js/libs/fancybox/jquery.fancybox.min.css')}}"/>
    <link rel="stylesheet" href=" {{ext_asset('/dist/css/admin-top.css')}}"/>
    <link rel="stylesheet" href=" {{ext_asset('/dist/css/app.css')}}"/>
    <link rel="stylesheet" href=" {{ext_asset('/dist/css/main.css')}}"/>
    <link rel="stylesheet" href="{{ext_asset('/dist/css/deels-v2.css')}}"/>
    <link rel="stylesheet" href="{{ext_asset('/dist/css/deels-v2-wallet-auth.css')}}"/>

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

    <script src="https://cdn.jsdelivr.net/npm/lozad/dist/lozad.min.js"></script>
    <script defer src="https://securepay.tinkoff.ru/html/payForm/js/tinkoff_v2.js"></script>

    <script>
        window.addEventListener('load', e => { registerSW(); });
        async function registerSW() {
            if ('serviceWorker' in navigator) {
                try { await navigator.serviceWorker.register('/sw.js'); }
                catch (e) { console.log('ServiceWorker registration failed. Sorry about that.'); }
            } else if (document.querySelector('.alert')) {
                document.querySelector('.alert').removeAttribute('hidden');
            }
        }
    </script>
    @if(!env('DISABLE_JIVOSITE') && isset($show_jivo))
    <script src="//code-ya.jivosite.com/widget/Q7UeuOPwUP" defer></script>
    @endif

    <!-- Existing analytics integrations retained unchanged in production source; visual migration is isolated from auth/business logic. -->
</head>

<body>
