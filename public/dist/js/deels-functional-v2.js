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
      ensureGuestContestPrompt();
      enhanceContestState();
    });
    observer.observe(document.body, { childList: true, subtree: true });
  }

  ready(function () {
    ensureWalletTools();
    ensureGuestContestPrompt();
    enhanceContestState();
    observeFunctionalStates();
  });
})();