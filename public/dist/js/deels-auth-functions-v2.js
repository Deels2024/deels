(function () {
  'use strict';

  function ready(fn) {
    if (document.readyState === 'loading') document.addEventListener('DOMContentLoaded', fn, { once: true });
    else fn();
  }

  function validEmail(value) {
    return /^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(String(value || '').trim());
  }

  function enhanceRegistration() {
    var form = document.querySelector('.sign__form');
    var email = document.querySelector('.emailField');
    var send = document.querySelector('.sendCodeBtn');
    var status = document.querySelector('.email-code-status');
    var code = document.querySelector('.emailCodeField');
    if (!form || !email || !send || !status || !code) return;

    function syncStatus() {
      var text = (status.textContent || '').trim().toLowerCase();
      status.classList.remove('is-loading', 'is-success', 'is-error');
      send.classList.remove('is-loading', 'is-success', 'is-error');
      if (!text) return;
      if (text.indexOf('отправляем') !== -1) {
        status.classList.add('is-loading'); send.classList.add('is-loading');
      } else if (text.indexOf('отправлено') !== -1 || text.indexOf('подтверждён') !== -1) {
        status.classList.add('is-success'); send.classList.add('is-success');
      } else if (text.indexOf('не удалось') !== -1 || text.indexOf('неверн') !== -1 || text.indexOf('просроч') !== -1 || text.indexOf('введите') !== -1) {
        status.classList.add('is-error'); send.classList.add('is-error');
      }
    }

    var observer = new MutationObserver(syncStatus);
    observer.observe(status, { childList: true, characterData: true, subtree: true, attributes: true });

    send.addEventListener('click', function (event) {
      if (!validEmail(email.value)) {
        event.preventDefault();
        event.stopImmediatePropagation();
        status.textContent = 'Сначала укажите корректный e-mail.';
        status.classList.add('email-code-status_error', 'is-error');
        email.focus();
      }
    }, true);

    code.addEventListener('input', function () {
      code.value = code.value.replace(/\D/g, '').slice(0, 6);
      code.setAttribute('aria-invalid', code.value.length > 0 && code.value.length !== 6 ? 'true' : 'false');
    });

    email.addEventListener('input', function () {
      email.setAttribute('aria-invalid', email.value && !validEmail(email.value) ? 'true' : 'false');
    });

    syncStatus();
  }

  ready(enhanceRegistration);
})();