function initEmailPrompt() {
    const modal = document.getElementById('select-email-modal');
    if (!modal) return;

    const email = document.getElementById('select-email');
    const status = document.getElementById('select-email-status');
    const submit = document.getElementById('select-email-submit');
    const verify = document.getElementById('select-email-verify');
    const codeBlock = document.getElementById('select-email-code-block');
    const code = document.getElementById('select-email-code');
    const resend = document.getElementById('select-email-resend');
    const later = document.getElementById('select-email-later');
    const csrfToken = modal.dataset.csrfToken;
    let timerId = null;

    const setStatus = (message, success = false, error = false) => {
        status.textContent = message;
        status.classList.toggle('success', success);
        status.classList.toggle('error', error);
    };

    if (email.dataset.verificationPending === '1') {
        resend.disabled = false;
        resend.textContent = 'Получить новый код';
    }

    const startTimer = (seconds = 120) => {
        clearInterval(timerId);
        resend.disabled = true;
        const render = () => {
            const minutes = String(Math.floor(seconds / 60)).padStart(2, '0');
            const rest = String(seconds % 60).padStart(2, '0');
            resend.textContent = `Отправить повторно ${minutes}:${rest}`;
            if (seconds-- <= 0) {
                clearInterval(timerId);
                timerId = null;
                resend.disabled = false;
                resend.textContent = 'Получить новый код';
            }
        };
        render();
        timerId = setInterval(render, 1000);
    };

    const sendCode = async () => {
        setStatus('Отправляем письмо…');
        submit.disabled = true;
        resend.disabled = true;
        try {
            const response = await fetch(modal.dataset.emailStoreUrl, {
                method: 'POST',
                headers: {'Content-Type': 'application/json', 'Accept': 'application/json', 'X-CSRF-TOKEN': csrfToken},
                body: JSON.stringify({email: email.value.trim()})
            });
            const data = await response.json();
            if (data.retry_after) startTimer(data.retry_after);
            if (!response.ok || !data.success) throw new Error(data.error || 'Не удалось отправить код.');
            email.readOnly = true;
            codeBlock.hidden = false;
            submit.hidden = true;
            verify.hidden = false;
            setStatus('Письмо с кодом отправлено.', true);
            code.focus();
            startTimer(120);
        } catch (error) {
            setStatus(error.message, false, true);
            if (!timerId) resend.disabled = false;
        } finally {
            submit.disabled = false;
        }
    };

    later.addEventListener('click', function () {
        const postponeUrl = modal.dataset.emailPostponeUrl;
        modal.style.display = 'none';
        document.body.classList.remove('overflow');

        fetch(postponeUrl, {
            method: 'POST',
            headers: {'Accept': 'application/json', 'X-CSRF-TOKEN': csrfToken},
            keepalive: true
        }).catch(function () {});
    });
    submit.addEventListener('click', sendCode);
    resend.addEventListener('click', sendCode);
    verify.addEventListener('click', async function () {
        if (!/^\d{6}$/.test(code.value.trim())) {
            setStatus('Введите шестизначный код.', false, true);
            return;
        }
        verify.disabled = true;
        try {
            const response = await fetch(modal.dataset.emailVerifyUrl, {
                method: 'POST',
                headers: {'Content-Type': 'application/json', 'Accept': 'application/json', 'X-CSRF-TOKEN': csrfToken},
                body: JSON.stringify({code: code.value.trim()})
            });
            const data = await response.json();
            if (!response.ok || !data.success) throw new Error(data.error || 'Не удалось подтвердить код.');
            modal.remove();
        } catch (error) {
            setStatus(error.message, false, true);
        } finally {
            verify.disabled = false;
        }
    });
}

if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', initEmailPrompt);
} else {
    initEmailPrompt();
}

(function ($) {
    if (!$) return;

    function showSuspiciousRestriction(data) {
        if (!data || (!data.shouldShowEmailPrompt && !data.shouldShowPhonePrompt && !data.need_actions)) {
            return false;
        }

        if ($.magnificPopup && $.magnificPopup.instance && $.magnificPopup.instance.isOpen) {
            $.magnificPopup.close();
        }

        var modal = null;
        if (data.shouldShowEmailPrompt) {
            modal = document.getElementById('select-email-modal');
        } else if (data.shouldShowPhonePrompt) {
            modal = document.getElementById('select-phone-modal');
        } else {
            modal = document.getElementById('suspicious-activity-modal');
            var message = document.getElementById('suspicious-activity-message');
            if (message) message.textContent = data.message || data.error || '';
        }

        if (!modal) {
            modal = document.createElement('div');
            modal.className = 'popup__wrape email-prompt-popup';
            modal.innerHTML = '<div class="popup__modal">'
                + '<div class="popup__title">Действие недоступно</div>'
                + '<p class="email-prompt-popup__text"></p>'
                + '<div class="email-prompt-popup__actions">'
                + '<button type="button" class="btn btn_fill suspicious-fallback-close">Понятно</button>'
                + '</div></div>';
            modal.querySelector('.email-prompt-popup__text').textContent = data.message || data.error || 'Действие временно недоступно';
            document.body.appendChild(modal);
        }
        if (data.shouldShowPhonePrompt && $.fn.mask) {
            $(modal).find('#select-phone').mask('+7 (999) 999-99-99');
        }
        modal.style.display = 'flex';
        document.body.classList.add('overflow');
        window.setTimeout(function () {
            var fields = modal.querySelectorAll(
                '#select-email-code, #select-phone-code, #select-email:not([readonly]), #select-phone:not([readonly]), button'
            );
            for (var index = 0; index < fields.length; index += 1) {
                if (fields[index].offsetParent !== null && !fields[index].disabled) {
                    fields[index].focus();
                    break;
                }
            }
        }, 100);
        return true;
    }

    window.showSuspiciousRestriction = showSuspiciousRestriction;

    $(document).ajaxError(function (_event, xhr) {
        if (xhr.status === 403) {
            showSuspiciousRestriction(xhr.responseJSON || {});
        }
    });

    $(document).on('click', '#suspicious-activity-close', function () {
        $('#suspicious-activity-modal').hide();
        document.body.classList.remove('overflow');
    });

    $(document).on('click', '.suspicious-fallback-close', function () {
        $(this).closest('.popup__wrape').remove();
        document.body.classList.remove('overflow');
    });
})(window.jQuery);
