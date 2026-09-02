<!doctype html>
<html lang="ru">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="robots" content="noindex,follow">
    <title>@yield('title', 'Ошибка') — Deels</title>
    <link rel="stylesheet" href="{{ ext_asset('/dist/css/font/stylesheet.css') }}">
    <style>
        *{box-sizing:border-box}body{margin:0;font-family:"Gilroy","Segoe UI",Arial,sans-serif;background:radial-gradient(circle at 12% 14%,rgba(255,79,200,.12),transparent 28%),radial-gradient(circle at 88% 18%,rgba(52,215,233,.12),transparent 30%),#faf8fc;color:#231a2d}.error-page{min-height:100svh;display:grid;place-items:center;padding:40px 20px}.error-card{width:min(620px,100%);padding:42px;border:1px solid #ebe4f1;border-radius:30px;background:rgba(255,255,255,.94);box-shadow:0 24px 70px rgba(68,26,107,.12);text-align:center}.error-mark{width:70px;height:70px;margin:0 auto 20px;display:grid;place-items:center;border-radius:24px;color:#fff;background:linear-gradient(135deg,#6b2bc1,#ff4fc8);font-size:22px;font-weight:900}.error-code{display:block;margin-bottom:9px;color:#6b2bc1;font-size:12px;font-weight:900;letter-spacing:.12em}.error-card h1{margin:0 0 12px;font-size:clamp(36px,7vw,58px);line-height:1;letter-spacing:-.045em}.error-card p{max-width:470px;margin:0 auto;color:#756b80;font-size:16px;line-height:1.6}.error-actions{margin-top:26px;display:flex;justify-content:center;gap:10px;flex-wrap:wrap}.error-btn{min-height:46px;padding:0 18px;display:inline-flex;align-items:center;justify-content:center;border-radius:16px;color:#fff;background:#6b2bc1;font-weight:800;text-decoration:none}.error-btn.secondary{color:#4b1788;background:#f4eff8}@media(max-width:520px){.error-card{padding:32px 22px;border-radius:24px}.error-card h1{font-size:40px}}
    </style>
</head>
<body>
<main class="error-page">
    <section class="error-card">
        <div class="error-mark">D</div>
        <span class="error-code">@yield('code')</span>
        <h1>@yield('heading')</h1>
        <p>@yield('message')</p>
        <div class="error-actions">
            <a class="error-btn" href="{{ route('home') }}">На главную</a>
            <a class="error-btn secondary" href="{{ route('challenges.catalog') }}">Смотреть челленджи</a>
        </div>
    </section>
</main>
</body>
</html>
