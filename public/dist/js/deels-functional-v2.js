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

  ready(function () {
    ensureWalletTools();
  });
})();