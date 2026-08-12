(function () {
  'use strict';

  function ready(fn) {
    if (document.readyState === 'loading') document.addEventListener('DOMContentLoaded', fn, { once: true });
    else fn();
  }

  function cleanLabel(text) {
    return String(text || '')
      .replace(/[🕹👍👎️]+/g, '')
      .replace(/\s+/g, ' ')
      .trim();
  }

  function iconFor(label) {
    var value = label.toLowerCase();
    if (value.indexOf('сторис') !== -1 || value.indexOf('ответ') !== -1) return '▶';
    if (value.indexOf('челлендж') !== -1) return '✦';
    if (value.indexOf('бат') !== -1) return '⚡';
    if (value.indexOf('копил') !== -1 || value.indexOf('плат') !== -1 || value.indexOf('транзак') !== -1 || value.indexOf('вывод') !== -1) return '₽';
    if (value.indexOf('польз') !== -1 || value.indexOf('аккаун') !== -1) return '◎';
    if (value.indexOf('жалоб') !== -1) return '!';
    if (value.indexOf('стат') !== -1) return '↗';
    if (value.indexOf('игр') !== -1) return '◇';
    if (value.indexOf('рассыл') !== -1) return '✉';
    if (value.indexOf('настрой') !== -1) return '⚙';
    if (value.indexOf('лог') !== -1) return '≡';
    if (value.indexOf('тег') !== -1) return '#';
    return '•';
  }

  function collectAdminLinks(menu) {
    var seen = Object.create(null);
    return Array.prototype.slice.call(menu.querySelectorAll('a[href]')).map(function (link) {
      var href = link.getAttribute('href') || '';
      var label = cleanLabel(link.textContent);
      if (!href || href === '#' || !label || seen[href]) return null;
      seen[href] = true;
      return { href: href, label: label };
    }).filter(Boolean);
  }

  function ensureAdminHub() {
    var menu = document.querySelector('.admin_menu');
    if (!menu || document.querySelector('.deels-admin-hub')) return;
    var content = document.querySelector('.account__content');
    if (!content) return;

    document.body.classList.add('deels-v2-admin');
    var links = collectAdminLinks(menu);
    if (!links.length) return;

    var section = document.createElement('section');
    section.className = 'deels-admin-hub';
    section.innerHTML = '' +
      '<div class="deels-admin-head">' +
        '<div><span>Управление Deels</span><h1>Админ-центр</h1><p>Модерация, финансы, пользователи и системные инструменты в одном интерфейсе.</p></div>' +
        '<button type="button" class="deels-admin-toggle" aria-expanded="true">Свернуть</button>' +
      '</div>' +
      '<div class="deels-admin-grid">' + links.map(function (item) {
        return '<a href="' + item.href + '" class="deels-admin-card"><b>' + iconFor(item.label) + '</b><span>' + item.label + '</span><i>→</i></a>';
      }).join('') + '</div>';

    content.insertBefore(section, content.firstChild);
    var button = section.querySelector('.deels-admin-toggle');
    var grid = section.querySelector('.deels-admin-grid');
    button.addEventListener('click', function () {
      var expanded = button.getAttribute('aria-expanded') === 'true';
      button.setAttribute('aria-expanded', expanded ? 'false' : 'true');
      button.textContent = expanded ? 'Показать' : 'Свернуть';
      grid.hidden = expanded;
    });
  }

  function ensureAdminDataStates() {
    var main = document.querySelector('.admin-main');
    if (!main || main.dataset.statesEnhanced === '1') return;
    main.dataset.statesEnhanced = '1';

    var filterForm = main.querySelector('form.form--admin, form.d-flex.mb-4');
    if (filterForm && !main.querySelector('.deels-admin-filter-note')) {
      var note = document.createElement('div');
      note.className = 'deels-admin-filter-note';
      note.textContent = 'Список обновляется по выбранным фильтрам. Все действия выполняются через существующую систему прав Deels.';
      filterForm.parentNode.insertBefore(note, filterForm);
    }

    Array.prototype.forEach.call(main.querySelectorAll('.admin-table table, table.admin-table, .followers-block table'), function (table) {
      var tbody = table.querySelector('tbody');
      if (!tbody || tbody.querySelector('tr')) return;
      var wrap = table.closest('.admin-table, .followers-block') || table.parentNode;
      if (!wrap || wrap.querySelector('.deels-admin-empty')) return;
      table.style.display = 'none';
      var empty = document.createElement('div');
      empty.className = 'deels-admin-empty';
      empty.innerHTML = '<strong>Нет данных</strong><span>По текущим фильтрам записей не найдено.</span>';
      wrap.appendChild(empty);
    });
  }

  ready(function () {
    ensureAdminHub();
    ensureAdminDataStates();
  });
})();