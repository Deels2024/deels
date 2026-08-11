@php
    $phonePromptUser = Auth::user();
    $phoneVerificationPending = $phonePromptUser ? $phonePromptUser->phoneVerificationPending() : false;
    $phoneActivation = $phonePromptUser ? $phonePromptUser->phoneVerify()->first() : null;
    $phoneAttemptsExhausted = $phoneActivation
        && ($phoneActivation->verify_phone_data['attempts_date'] ?? null) === now()->toDateString()
        && (int) ($phoneActivation->verify_phone_data['attempts'] ?? 0) >= 3;
    $phonePromptAutoShow = $phonePromptUser
        && !empty($phonePromptUser->email)
        && !$phonePromptUser->emailVerificationPending()
        && $phonePromptUser->shouldShowPhonePrompt();
@endphp
@if($phonePromptUser)
    <div class="popup__wrape email-prompt-popup" id="select-phone-modal" role="dialog" aria-modal="true" aria-labelledby="select-phone-modal-title"
         @if(!$phonePromptAutoShow) style="display: none" @endif
         data-phone-store-url="{{ route('user.phone.store') }}"
         data-phone-resend-url="{{ route('user.phone.resend') }}"
         data-phone-verify-url="{{ route('user.phone.verify') }}"
         data-phone-postpone-url="{{ route('user.phone-prompt.postpone') }}"
         data-csrf-token="{{ csrf_token() }}">
        <div class="popup__modal">
            <div class="popup__title" id="select-phone-modal-title">{{ $phoneVerificationPending ? 'Подтвердите телефон' : 'Укажите телефон' }}</div>
            <p class="email-prompt-popup__text">Укажите ваш телефон, чтобы иметь возможность восстановить доступ к аккаунту и получать важные уведомления</p>
            <input class="popup__input phone-mask" id="select-phone" type="tel" inputmode="tel" autocomplete="tel" placeholder="Ваш телефон" value="{{ Auth::user()->phone }}" {{ $phoneVerificationPending ? 'readonly' : '' }}>
            <div class="email-prompt-popup__code" id="select-phone-code-block" {{ $phoneVerificationPending ? '' : 'hidden' }}>
                <input class="popup__input" id="select-phone-code" type="text" maxlength="4" inputmode="numeric" autocomplete="one-time-code" placeholder="Код подтверждения телефона">
                <button type="button" class="email-prompt-popup__resend" id="select-phone-sms">Получить SMS</button>
                <button type="button" class="email-prompt-popup__resend" id="select-phone-call">Запросить звонок</button>
                <div id="select-phone-attempts-exhausted" @if(!$phoneAttemptsExhausted) hidden @endif>Закончились попытки, попробуйте снова завтра или обратитесь в поддержку</div>
            </div>
            <div class="email-prompt-popup__status" id="select-phone-status" aria-live="polite"></div>
            <div class="email-prompt-popup__actions">
                <button type="button" class="btn email-prompt-popup__later" id="select-phone-later">Позже</button>
                <button type="button" class="btn btn_fill email-prompt-popup__submit" id="select-phone-submit" {{ $phoneVerificationPending ? 'hidden' : '' }}>Получить код</button>
                <button type="button" class="btn btn_fill email-prompt-popup__verify" id="select-phone-verify" {{ $phoneVerificationPending ? '' : 'hidden' }}>Готово</button>
            </div>
        </div>
    </div>
    <script>
        (function initPhonePrompt() {
            const modal = document.getElementById('select-phone-modal');
            if (!modal) return;

            const phone = document.getElementById('select-phone');
            const code = document.getElementById('select-phone-code');
            const codeBlock = document.getElementById('select-phone-code-block');
            const status = document.getElementById('select-phone-status');
            const submit = document.getElementById('select-phone-submit');
            const verify = document.getElementById('select-phone-verify');
            const call = document.getElementById('select-phone-call');
            const sms = document.getElementById('select-phone-sms');
            const exhausted = document.getElementById('select-phone-attempts-exhausted');
            const later = document.getElementById('select-phone-later');
            const headers = {'Content-Type': 'application/json', 'Accept': 'application/json', 'X-CSRF-TOKEN': modal.dataset.csrfToken};
            let timerId = null;

            // Clicking the backdrop must not postpone or close the prompt.
            modal.addEventListener('click', function (event) {
                if (event.target === modal) {
                    event.preventDefault();
                    event.stopImmediatePropagation();
                }
            }, true);

            const setStatus = (message, success = false, error = false) => {
                status.textContent = message;
                status.classList.toggle('success', success);
                status.classList.toggle('error', error);
            };
            const startTimer = (seconds = 60) => {
                clearInterval(timerId);
                call.disabled = sms.disabled = true;
                const render = () => {
                    const minutes = String(Math.floor(seconds / 60)).padStart(2, '0');
                    const rest = String(seconds % 60).padStart(2, '0');
                    call.textContent = `Повторный запрос через ${minutes}:${rest}`;
                    sms.textContent = `SMS через ${minutes}:${rest}`;
                    if (seconds-- <= 0) {
                        clearInterval(timerId);
                        call.disabled = sms.disabled = false;
                        call.textContent = 'Запросить звонок';
                        sms.textContent = 'Получить SMS';
                    }
                };
                render();
                timerId = setInterval(render, 1000);
            };
            const showAttemptsExhausted = () => {
                clearInterval(timerId);
                call.hidden = sms.hidden = true;
                exhausted.hidden = false;
            };
            const request = async (url, body) => {
                const response = await fetch(url, {method: 'POST', headers, body: JSON.stringify(body)});
                const data = await response.json();
                if (!response.ok || !data.success) {
                    const error = new Error(data.error || data.message || 'Не удалось отправить код.');
                    error.limitReached = Boolean(data.limit_reached);
                    throw error;
                }
                return data;
            };
            const resend = async (type) => {
                setStatus(type === 'sms' ? 'Отправляем SMS…' : 'Запрашиваем звонок…');
                call.disabled = sms.disabled = true;
                try {
                    const data = await request(modal.dataset.phoneResendUrl, {type});
                    setStatus(data.message || 'Код отправлен.', true);
                    if (data.attempts_left === 0) showAttemptsExhausted();
                    else startTimer(data.retry_after || 60);
                } catch (error) {
                    setStatus(error.message, false, true);
                    if (error.limitReached) showAttemptsExhausted();
                    else call.disabled = sms.disabled = false;
                }
            };

            submit.addEventListener('click', async function () {
                submit.disabled = true;
                setStatus('Отправляем SMS…');
                try {
                    const data = await request(modal.dataset.phoneStoreUrl, {phone: phone.value.trim()});
                    phone.readOnly = true;
                    codeBlock.hidden = false;
                    submit.hidden = true;
                    verify.hidden = false;
                    setStatus(data.message || 'SMS отправлено.', true);
                    code.focus();
                    if (data.attempts_left === 0) showAttemptsExhausted();
                    else startTimer(data.retry_after || 60);
                } catch (error) {
                    setStatus(error.message, false, true);
                    if (error.limitReached) showAttemptsExhausted();
                } finally {
                    submit.disabled = false;
                }
            });
            call.addEventListener('click', () => resend('phone'));
            sms.addEventListener('click', () => resend('sms'));
            verify.addEventListener('click', async function () {
                if (!/^\d{4}$/.test(code.value.trim())) {
                    setStatus('Введите четырёхзначный код.', false, true);
                    return;
                }
                verify.disabled = true;
                try {
                    await request(modal.dataset.phoneVerifyUrl, {code: code.value.trim()});
                    modal.remove();
                } catch (error) {
                    setStatus(error.message, false, true);
                } finally {
                    verify.disabled = false;
                }
            });
            later.addEventListener('click', async function () {
                try {
                    await fetch(modal.dataset.phonePostponeUrl, {method: 'POST', headers: {'Accept': 'application/json', 'X-CSRF-TOKEN': modal.dataset.csrfToken}});
                } finally {
                    modal.style.display = 'none';
                    document.body.classList.remove('overflow');
                }
            });
            if (!exhausted.hidden) showAttemptsExhausted();
        })();
    </script>
@endif
