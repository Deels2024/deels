(function () {
  'use strict';

  function ready(fn) {
    if (document.readyState === 'loading') document.addEventListener('DOMContentLoaded', fn, { once: true });
    else fn();
  }

  function ensureWalletTools() {
    if (window.location.pathname !== '/dashboard/wallet') return;
    if (document.querySelector('.deels-wallet-tools')) return;

    var account = document.querySelector('.account-main, .account__content .account-main, .dashboard-content');
    if (!account) return;

    var type = new URLSearchParams(window.location.search).get('type') || 'movements';
    var nav = document.createElement('nav');
    nav.className = 'deels-wallet-tools';
    nav.setAttribute('aria-label', 'Разделы кошелька');

    var items = [
      ['movements', 'Движения', '/dashboard/wallet', 'Все операции по кошельку'],
      ['billing', 'Биллинг', '/dashboard/wallet?type=billing', 'Пополнения и платежные операции'],
      ['donate', 'Донаты', '/dashboard/wallet?type=donate', 'История поддержки и донатов'],
      ['autopayments', 'Автоплатежи', '/dashboard/autopayments', 'Регулярные платежи']
    ];

    nav.innerHTML = items.map(function (item) {
      var active = item[0] === type;
      return '<a class="' + (active ? 'active' : '') + '" href="' + item[2] + '">' +
        '<strong>' + item[1] + '</strong><small>' + item[3] + '</small>' +
      '</a>';
    }).join('');

    var head = account.querySelector('.account-main__head, .dashboard-title');
    if (head && head.nextSibling) head.parentNode.insertBefore(nav, head.nextSibling);
    else account.insertBefore(nav, account.firstChild);
  }

  function ensureWalletGuidance() {
    if (window.location.pathname !== '/dashboard/wallet') return;
    var withdraw = document.getElementById('withdraw');
    if (!withdraw || withdraw.dataset.guidanceEnhanced === '1') return;
    withdraw.dataset.guidanceEnhanced = '1';

    var form = withdraw.querySelector('#walletWithdraw');
    if (!form) return;
    var amount = form.querySelector('[name="withdraw_input"]');
    var button = form.querySelector('.btn_withdraw');
    if (!amount || !button) return;

    var guide = document.createElement('div');
    guide.className = 'deels-wallet-guidance';
    guide.innerHTML = '' +
      '<strong>Условия вывода</strong>' +
      '<ul>' +
        '<li>Минимальная сумма — 500 ₽.</li>' +
        '<li>Комиссия проекта — 20%; к выплате поступает 80% запрошенной суммы.</li>' +
        '<li>Одновременно может обрабатываться только одна заявка.</li>' +
        '<li>Повторный вывод доступен не чаще одного раза в 30 дней.</li>' +
        '<li>Фактическая доступность и реквизиты повторно проверяются сервером.</li>' +
      '</ul>';
    form.insertBefore(guide, form.firstChild);

    var status = document.createElement('div');
    status.className = 'deels-wallet-client-status';
    status.setAttribute('role', 'status');
    status.setAttribute('aria-live', 'polite');
    button.parentNode.insertBefore(status, button);

    function validateAmount() {
      var value = Number(String(amount.value || '').replace(/[^0-9]/g, ''));
      if (!value) {
        status.textContent = '';
        button.removeAttribute('aria-disabled');
        return true;
      }
      if (value < 500) {
        status.textContent = 'Минимальная сумма для вывода — 500 ₽.';
        status.className = 'deels-wallet-client-status is-error';
        button.setAttribute('aria-disabled', 'true');
        return false;
      }
      status.textContent = 'Предварительно к выплате: ' + Math.floor(value * 0.8).toLocaleString('ru-RU') + ' ₽ после комиссии 20%.';
      status.className = 'deels-wallet-client-status is-ok';
      button.removeAttribute('aria-disabled');
      return true;
    }

    amount.addEventListener('input', validateAmount);
    button.addEventListener('click', function (event) {
      if (!validateAmount()) {
        event.preventDefault();
        event.stopImmediatePropagation();
        amount.focus();
      }
    }, true);
  }

  function ensureDepositGuidance() {
    if (window.location.pathname !== '/dashboard/wallet') return;
    var deposit = document.getElementById('deposit');
    if (!deposit || deposit.dataset.guidanceEnhanced === '1') return;
    deposit.dataset.guidanceEnhanced = '1';
    var form = deposit.querySelector('form');
    if (!form) return;
    var note = document.createElement('div');
    note.className = 'deels-wallet-guidance deels-wallet-guidance--compact';
    note.innerHTML = '<strong>Пополнение</strong><p>После подтверждения Deels перенаправит вас на защищённую платёжную страницу. Результат платежа вернётся в кошелёк автоматически.</p>';
    form.insertBefore(note, form.firstChild);
  }

  function ensureGuestContestPrompt() {
    if (Number(window.userId || 0) !== 0) return;
    if (!/^\/(challenges|battles)\/show\//.test(window.location.pathname)) return;
    if (document.querySelector('.deels-guest-contest-prompt')) return;

    var actions = document.querySelector('.contest-overview__actions');
    if (!actions) return;

    var disabledParticipation = actions.querySelector('.challenge-participation-actions button[disabled]');
    if (disabledParticipation) disabledParticipation.style.display = 'none';

    var prompt = document.createElement('div');
    prompt.className = 'deels-guest-contest-prompt';
    var next = encodeURIComponent(window.location.pathname + window.location.search);
    prompt.innerHTML = '' +
      '<div><strong>Хотите участвовать?</strong><span>Войдите — после этого Deels покажет доступное действие для этого челленджа или баттла.</span></div>' +
      '<a href="/login?next=' + next + '">Войти</a>';
    actions.insertBefore(prompt, actions.firstChild);
  }

  function enhanceContestState() {
    var actions = document.querySelector('.contest-overview__actions');
    if (!actions || actions.dataset.stateEnhanced === '1') return;
    actions.dataset.stateEnhanced = '1';

    var participation = actions.querySelector('.challenge-participation-actions');
    if (!participation) return;
    var text = (participation.textContent || '').replace(/\s+/g, ' ').trim().toLowerCase();
    var state = 'neutral';
    if (text.indexOf('принять') !== -1) state = 'invited';
    else if (text.indexOf('выйти из участия') !== -1) state = 'participating';
    else if (text.indexOf('участвовать') !== -1) state = 'available';
    else if (text.indexOf('заверш') !== -1) state = 'finished';
    else if (text.indexOf('закрыт') !== -1) state = 'closed';
    actions.setAttribute('data-participation-state', state);

    var labels = {
      invited: 'Вас пригласили',
      participating: 'Вы участвуете',
      available: 'Можно участвовать',
      finished: 'Завершено',
      closed: 'Набор закрыт'
    };
    if (labels[state] && !actions.querySelector('.deels-contest-state-chip')) {
      var chip = document.createElement('span');
      chip.className = 'deels-contest-state-chip deels-contest-state-chip--' + state;
      chip.textContent = labels[state];
      actions.insertBefore(chip, actions.firstChild);
    }
  }

  function observeFunctionalStates() {
    if (!window.MutationObserver) return;
    var observer = new MutationObserver(function () {
      ensureWalletTools();
      ensureWalletGuidance();
      ensureDepositGuidance();
      ensureGuestContestPrompt();
      enhanceContestState();
    });
    observer.observe(document.body, { childList: true, subtree: true });
  }

  ready(function () {
    ensureWalletTools();
    ensureWalletGuidance();
    ensureDepositGuidance();
    ensureGuestContestPrompt();
    enhanceContestState();
    observeFunctionalStates();
  });
})();