<script>
    (function () {
        window.DeelsTelegramWebAppAuth = {
            status: 'init'
        };

        var webApp = window.Telegram && window.Telegram.WebApp;
        if (!webApp || !webApp.initData) {
            window.DeelsTelegramWebAppAuth.status = 'not_telegram_webapp';
            return;
        }

        webApp.expand();

        if (window.userId && Number(window.userId) > 0) {
            window.DeelsTelegramWebAppAuth.status = 'already_authenticated';
            webApp.ready();
            return;
        }

        var authKey = 'telegram_webapp_auth_' + (webApp.initDataUnsafe.query_id || webApp.initDataUnsafe.user && webApp.initDataUnsafe.user.id || 'current');
        if (sessionStorage.getItem(authKey) === 'done') {
            window.DeelsTelegramWebAppAuth.status = 'already_attempted';
            webApp.ready();
            return;
        }

        window.DeelsTelegramWebAppAuth.status = 'requesting';

        fetch('{{ route('telegram.webapp.auth') }}', {
            method: 'POST',
            credentials: 'same-origin',
            headers: {
                'Content-Type': 'application/json',
                'Accept': 'application/json',
                'X-Requested-With': 'XMLHttpRequest'
            },
            body: JSON.stringify({
                init_data: webApp.initData
            })
        })
            .then(function (response) {
                window.DeelsTelegramWebAppAuth.httpStatus = response.status;
                if (!response.ok) {
                    return response.json().then(function (data) {
                        throw new Error(data.message || 'Telegram WebApp auth failed');
                    });
                }
                return response.json();
            })
            .then(function (data) {
                window.DeelsTelegramWebAppAuth.response = data;
                if (data.success) {
                    window.DeelsTelegramWebAppAuth.status = 'authenticated';
                    sessionStorage.setItem(authKey, 'done');
                    window.location.reload();
                } else {
                    window.DeelsTelegramWebAppAuth.status = 'failed';
                }
            })
            .catch(function (error) {
                window.DeelsTelegramWebAppAuth.status = 'error';
                window.DeelsTelegramWebAppAuth.error = error.message;
                console.error(error);
            })
            .finally(function () {
                webApp.ready();
            });
    })();
</script>
