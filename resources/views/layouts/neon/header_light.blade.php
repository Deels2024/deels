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
    <link rel="stylesheet" href="{{ext_asset('/dist/css/deels-v2-campaigns-auth.css')}}"/>
    <link rel="stylesheet" href="{{ext_asset('/dist/css/deelsweb-auth-source.css')}}"/>

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
		window.addEventListener('load', e => {
			registerSW();
		});

		async function registerSW() {
			if ('serviceWorker' in navigator) {
				try {
					await navigator.serviceWorker.register('/sw.js');
				} catch (e) {
					console.log('ServiceWorker registration failed. Sorry about that.');
				}
			} else {
				document.querySelector('.alert').removeAttribute('hidden');
			}
		}
    </script>
    @if(!env('DISABLE_JIVOSITE') && isset($show_jivo))
    <script src="//code-ya.jivosite.com/widget/Q7UeuOPwUP" defer></script>
    @endif

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
    <!-- /Yandex.Metrika counter -->

    <script>window.yaContextCb = window.yaContextCb || []</script>
    <script src="https://yandex.ru/ads/system/context.js" async></script>
    <!-- Yandex.RTB R-A-2121442-1 -->
    <script>
		window.setTimeout(() => {
			window.yaContextCb.push(() => {
				Ya.Context.AdvManager.render({
					type: 'fullscreen',
					platform: 'touch',
					blockId: 'R-A-2121442-1'
				})
			})
		}, 5000);
    </script>
    <!-- Top.Mail.Ru counter -->
    <script type="text/javascript">
        var _tmr = window._tmr || (window._tmr = []);
        _tmr.push({id: "3511033", type: "pageView", start: (new Date()).getTime()});
        (function (d, w, id) {
            if (d.getElementById(id)) return;
            var ts = document.createElement("script"); ts.type = "text/javascript"; ts.async = true; ts.id = id;
            ts.src = "https://top-fwz1.mail.ru/js/code.js";
            var f = function () {var s = d.getElementsByTagName("script")[0]; s.parentNode.insertBefore(ts, s);};
            if (w.opera == "[object Opera]") { d.addEventListener("DOMContentLoaded", f, false); } else { f(); }
        })(document, window, "tmr-code");
    </script>
    <noscript><div><img src="https://top-fwz1.mail.ru/counter?id=3511033;js=na" style="position:absolute;left:-9999px;" alt="Top.Mail.Ru" /></div></noscript>
    <!-- /Top.Mail.Ru counter -->
    <script type="text/javascript">!function(){var t=document.createElement("script");t.type="text/javascript",t.async=!0,t.src='https://vk.ru/js/api/openapi.js?173',t.onload=function(){VK.Retargeting.Init("VK-RTRG-1891647-73Ile"),VK.Retargeting.Hit()},document.head.appendChild(t)}();</script><noscript><img src="https://vk.ru/rtrg?p=VK-RTRG-1891647-73Ile" style="position:fixed; left:-999px;" alt=""/></noscript>
</head>

<body>
