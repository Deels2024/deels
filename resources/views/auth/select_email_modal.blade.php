@php
    $emailPromptUser = Auth::user();
    $emailVerificationPending = $emailPromptUser
        ? (!empty($emailPromptUser->email) && $emailPromptUser->emailVerificationPending())
        : false;
    $emailPromptAutoShow = $emailPromptUser && $emailPromptUser->shouldShowEmailPrompt();
@endphp
@if($emailPromptUser)
    <div class="popup__wrape email-prompt-popup" id="select-email-modal" role="dialog" aria-modal="true" aria-labelledby="select-email-modal-title"
         @if(!$emailPromptAutoShow) style="display: none" @endif
         data-email-store-url="{{ route('user.email.store') }}"
         data-email-verify-url="{{ route('user.email.verify') }}"
         data-email-postpone-url="{{ route('user.email-prompt.postpone') }}"
         data-csrf-token="{{ csrf_token() }}">
        <div class="popup__modal">
            <div class="popup__title" id="select-email-modal-title">{{ $emailVerificationPending ? 'Подтвердите почту' : 'Укажите почту' }}</div>
            <p class="email-prompt-popup__text">Укажите вашу почту, чтобы иметь возможность восстановить доступ к аккаунту и получать важные уведомления</p>
            <input class="popup__input" id="select-email" type="email" inputmode="email" autocomplete="email" placeholder="Ваш e-mail" value="{{ Auth::user()->email }}" data-verification-pending="{{ $emailVerificationPending ? '1' : '0' }}" {{ $emailVerificationPending ? 'readonly' : '' }}>
            <div class="email-prompt-popup__code" id="select-email-code-block" {{ $emailVerificationPending ? '' : 'hidden' }}>
                <input class="popup__input" id="select-email-code" type="text" maxlength="6" inputmode="numeric" autocomplete="one-time-code" placeholder="Код подтверждения почты">
                <button type="button" class="email-prompt-popup__resend" id="select-email-resend" disabled>Отправить повторно 02:00</button>
            </div>
            <div class="email-prompt-popup__status" id="select-email-status" aria-live="polite"></div>
            <div class="email-prompt-popup__actions">
                <button type="button" class="btn email-prompt-popup__later" id="select-email-later">Позже</button>
                <button type="button" class="btn btn_fill email-prompt-popup__submit" id="select-email-submit" {{ $emailVerificationPending ? 'hidden' : '' }}>Получить код</button>
                <button type="button" class="btn btn_fill email-prompt-popup__verify" id="select-email-verify" {{ $emailVerificationPending ? '' : 'hidden' }}>Готово</button>
            </div>
        </div>
    </div>
    <script>
        (function () {
            const modal = document.getElementById('select-email-modal');
            if (!modal || modal.dataset.inlineHandlersReady === '1') return;
            modal.dataset.inlineHandlersReady = '1';

            const email = modal.querySelector('#select-email');
            const code = modal.querySelector('#select-email-code');
            const codeBlock = modal.querySelector('#select-email-code-block');
            const status = modal.querySelector('#select-email-status');
            const submit = modal.querySelector('#select-email-submit');
            const verify = modal.querySelector('#select-email-verify');
            const resend = modal.querySelector('#select-email-resend');
            const later = modal.querySelector('#select-email-later');
            const headers = {
                'Content-Type': 'application/json',
                'Accept': 'application/json',
                'X-CSRF-TOKEN': modal.dataset.csrfToken
            };
            let timerId = null;

            // Clicking the backdrop must not postpone or close the prompt.
            modal.addEventListener('click', function (event) {
                if (event.target === modal) {
                    event.preventDefault();
                    event.stopImmediatePropagation();
                }
            }, true);

            if (email.dataset.verificationPending === '1') {
                resend.disabled = false;
                resend.textContent = 'Получить новый код';
            }

            const setStatus = function (message, isError) {
                status.textContent = message || '';
                status.classList.toggle('error', Boolean(isError));
                status.classList.toggle('success', !isError && Boolean(message));
            };

            const startTimer = function (seconds) {
                clearInterval(timerId);
                seconds = Math.max(1, Number(seconds) || 120);
                resend.disabled = true;

                const render = function () {
                    const minutes = String(Math.floor(seconds / 60)).padStart(2, '0');
                    const rest = String(seconds % 60).padStart(2, '0');
                    resend.textContent = `Отправить повторно ${minutes}:${rest}`;

                    if (seconds <= 0) {
                        clearInterval(timerId);
                        timerId = null;
                        resend.disabled = false;
                        resend.textContent = 'Получить новый код';
                        return;
                    }
                    seconds -= 1;
                };

                render();
                timerId = setInterval(render, 1000);
            };

            const request = async function (url, body) {
                const response = await fetch(url, {
                    method: 'POST',
                    headers: headers,
                    body: JSON.stringify(body || {})
                });
                const data = await response.json();
                if (!response.ok || !data.success) {
                    const error = new Error(data.error || data.message || 'Не удалось выполнить запрос.');
                    error.retryAfter = data.retry_after || 0;
                    throw error;
                }
                return data;
            };

            const sendCode = async function () {
                if (!email.value.trim()) {
                    setStatus('Укажите почту.', true);
                    email.focus();
                    return;
                }

                submit.disabled = true;
                resend.disabled = true;
                setStatus('Отправляем письмо…', false);
                try {
                    const data = await request(modal.dataset.emailStoreUrl, {email: email.value.trim()});
                    email.readOnly = true;
                    codeBlock.hidden = false;
                    submit.hidden = true;
                    verify.hidden = false;
                    setStatus('Письмо с кодом отправлено.', false);
                    startTimer(data.retry_after || 120);
                    code.focus();
                } catch (error) {
                    setStatus(error.message, true);
                    if (error.retryAfter) {
                        startTimer(error.retryAfter);
                    } else {
                        resend.disabled = false;
                    }
                } finally {
                    submit.disabled = false;
                }
            };

            modal.addEventListener('click', async function (event) {
                const button = event.target.closest('button');
                if (!button || !modal.contains(button)) return;

                if (![submit, resend, verify, later].includes(button)) return;
                event.preventDefault();
                event.stopImmediatePropagation();

                if (button === later) {
                    modal.style.display = 'none';
                    document.body.classList.remove('overflow');
                    fetch(modal.dataset.emailPostponeUrl, {
                        method: 'POST',
                        headers: {'Accept': 'application/json', 'X-CSRF-TOKEN': modal.dataset.csrfToken}
                    }).catch(function () {});
                    return;
                }

                if (button === submit || button === resend) {
                    await sendCode();
                    return;
                }

                if (!/^\d{6}$/.test(code.value.trim())) {
                    setStatus('Введите шестизначный код.', true);
                    code.focus();
                    return;
                }

                verify.disabled = true;
                try {
                    await request(modal.dataset.emailVerifyUrl, {code: code.value.trim()});
                    clearInterval(timerId);
                    modal.style.display = 'none';
                    document.body.classList.remove('overflow');
                } catch (error) {
                    setStatus(error.message, true);
                } finally {
                    verify.disabled = false;
                }
            }, true);
        })();
    </script>
@endif
