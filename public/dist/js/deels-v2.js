(function () {
  'use strict';

  function ready(fn) {
    if (document.readyState === 'loading') document.addEventListener('DOMContentLoaded', fn, { once: true });
    else fn();
  }

  function ensureNavLink(label, href, beforeLabel) {
    var lists = document.querySelectorAll('.header__list ul, .header__menu-list');
    lists.forEach(function (list) {
      var links = Array.prototype.slice.call(list.querySelectorAll('a'));
      if (links.some(function (link) { return link.textContent.trim() === label; })) return;
      var li = document.createElement('li');
      li.className = 'deels-source-nav-link';
      var a = document.createElement('a');
      a.href = href;
      a.textContent = label;
      li.appendChild(a);
      var before = links.find(function (link) { return link.textContent.trim() === beforeLabel; });
      if (before && before.parentElement && before.parentElement.parentElement === list) list.insertBefore(li, before.parentElement);
      else list.appendChild(li);
    });
  }

  function normalizeNav() {
    ensureNavLink('Лента', '/stories?type=new', 'Челленджи');
    ensureNavLink('Баттлы', '/challenges?content=battles', 'Сторис');
    ensureNavLink('Копилки', '/campaign', 'Контакты');

    document.querySelectorAll('.header__list ul, .header__menu-list').forEach(function (list) {
      Array.prototype.slice.call(list.querySelectorAll('a')).forEach(function (link) {
        var text = link.textContent.trim();
        if (text === 'Челленджи') link.href = '/challenges?content=challenges';
        if (text === 'Сторис') { link.textContent = 'Истории'; link.href = '/stories?type=popular'; }
        if (text === 'Баттлы') link.href = '/challenges?content=battles';
        if (text === 'Копилки') link.href = '/campaign';
        if (['Главная', 'Контакты', 'О нас', 'Начать копить'].indexOf(text) !== -1 && link.parentElement) link.parentElement.classList.add('deels-source-hide');
      });
    });
  }

  function ensureCreateButton() {
    var actions = document.querySelector('.header__icons');
    if (!actions || actions.querySelector('.deels-source-create')) return;
    var create = document.createElement('a');
    create.className = 'deels-source-create';
    create.href = '/dashboard/challenges/create';
    create.innerHTML = '<span aria-hidden="true">+</span> Создать';
    var profile = actions.querySelector('.header_profile');
    if (profile) actions.insertBefore(create, profile);
    else actions.appendChild(create);
  }

  function upgradeHeader() {
    normalizeNav();
    ensureCreateButton();
    var header = document.querySelector('.header');
    if (header) header.classList.add('deels-source-header');
  }

  function labelPage() {
    var path = window.location.pathname;
    document.documentElement.dataset.deelsPath = path;
    if (path.indexOf('/challenges') === 0) document.body.classList.add('deels-v2-page-challenges');
    if (path.indexOf('/stories') === 0) document.body.classList.add('deels-v2-page-stories');
    if (path.indexOf('/battles') === 0) document.body.classList.add('deels-v2-page-battles');
    if (path.indexOf('/campaign') === 0 || path.indexOf('/campaigns') === 0) document.body.classList.add('deels-v2-page-campaigns');
    if (path.indexOf('/dashboard') === 0) document.body.classList.add('deels-v2-page-dashboard');
  }

  function functionalHubItems() {
    return [
      ['✦', 'Мои челленджи', 'Создание, редактирование и управление', '/dashboard/challenges'],
      ['▶', 'Мои сторис', 'Опубликованные видео и истории', '/dashboard/stories'],
      ['♡', 'Мои копилки', 'Сборы, редактирование и статусы', '/dashboard/my_campaigns'],
      ['₽', 'Кошелёк', 'Баланс, пополнение и вывод', '/dashboard/wallet'],
      ['✉', 'Сообщения', 'Диалоги с пользователями', '/dashboard/messages'],
      ['♥', 'Мои лайки', 'Сохранённая активность', '/dashboard/user_likes'],
      ['◎', 'Друзья', 'Взаимные подписки', '/dashboard/user_friends'],
      ['↑', 'Подписки', 'На кого вы подписаны', '/dashboard/user_followings'],
      ['↓', 'Подписчики', 'Кто подписан на вас', '/dashboard/user_followers'],
      ['↻', 'Автоплатежи', 'Управление регулярными платежами', '/dashboard/autopayments'],
      ['✓', 'Спасибо', 'Благодарности и отклики', '/dashboard/thanks']
    ];
  }

  function ensureFunctionalHub() {
    if (window.location.pathname.indexOf('/dashboard') !== 0) return;
    if (document.querySelector('.deels-functional-hub')) return;
    var account = document.querySelector('.account-main, .account__content .account-main, .dashboard-content');
    if (!account) return;

    var section = document.createElement('section');
    section.className = 'deels-functional-hub';
    var cards = functionalHubItems().map(function (item) {
      return '<a class="deels-functional-card" href="' + item[3] + '">' +
        '<span class="deels-functional-icon">' + item[0] + '</span>' +
        '<span class="deels-functional-copy"><strong>' + item[1] + '</strong><small>' + item[2] + '</small></span>' +
        '<span class="deels-functional-arrow">→</span>' +
      '</a>';
    }).join('');
    section.innerHTML = '<div class="deels-functional-head"><div><span class="deels-functional-eyebrow">Все возможности Deels</span><h2>Мои разделы</h2><p>Функции старого кабинета сохранены и доступны в новом интерфейсе.</p></div></div><div class="deels-functional-grid">' + cards + '</div>';

    var head = account.querySelector('.account-main__head, .dashboard-title');
    if (head && head.nextSibling) head.parentNode.insertBefore(section, head.nextSibling);
    else account.insertBefore(section, account.firstChild);
  }

  function campaignPublicPath() {
    var match = window.location.pathname.match(/^\/campaign\/([^/]+)\/?$/);
    return match ? match[1] : null;
  }

  function ensureCampaignPublicTools() {
    var slug = campaignPublicPath();
    if (!slug || document.querySelector('.deels-campaign-tools')) return;
    var anchor = document.querySelector('.main-content__switch, .main-content__info, .campaign-detail-grid, .campaign-detail');
    if (!anchor) return;
    var nav = document.createElement('nav');
    nav.className = 'deels-campaign-tools';
    nav.setAttribute('aria-label', 'Разделы копилки');
    nav.innerHTML = '' +
      '<a class="active" href="/campaign/' + slug + '">О сборе</a>' +
      '<a href="/campaign/backers/' + slug + '">Донатеры</a>' +
      '<a href="/campaign/updates/' + slug + '">Обновления</a>' +
      '<a href="/campaign/faqs/' + slug + '">FAQ</a>';
    anchor.parentNode.insertBefore(nav, anchor);
  }

  function campaignEditId() {
    var match = window.location.pathname.match(/^\/dashboard\/my_campaigns\/edit_campaign\/(\d+)/);
    return match ? match[1] : null;
  }

  function ensureCampaignManagementTools() {
    var id = campaignEditId();
    if (!id || document.querySelector('.deels-campaign-manage')) return;
    var account = document.querySelector('.account-main, .account__content .account-main');
    if (!account) return;
    var base = '/dashboard/my_campaigns/edit_campaign/' + id;
    var path = window.location.pathname;
    var tabs = [
      ['Основное', base],
      ['Награды', base + '/rewards'],
      ['Обновления', base + '/updates'],
      ['FAQ', base + '/faqs']
    ];
    var nav = document.createElement('nav');
    nav.className = 'deels-campaign-manage';
    nav.setAttribute('aria-label', 'Управление копилкой');
    nav.innerHTML = '<div><span>Управление копилкой</span><strong>Все функции старого кабинета</strong></div>' + tabs.map(function (item) {
      var active = path === item[1] || (item[1] !== base && path.indexOf(item[1]) === 0);
      return '<a class="' + (active ? 'active' : '') + '" href="' + item[1] + '">' + item[0] + '</a>';
    }).join('');
    var head = account.querySelector('.account-main__head');
    if (head && head.nextSibling) head.parentNode.insertBefore(nav, head.nextSibling);
    else account.insertBefore(nav, account.firstChild);
  }

  function upgradeCatalogCards() {
    var selectors = ['.challenge-item', '.story-item', '.finish-item', '.bank__item', '.campaign-card', '.challenge-card', '.copystories-item', '.tops-story'];
    document.querySelectorAll(selectors.join(',')).forEach(function (card) { card.classList.add('deels-v2-live-card'); });
  }

  function upgradeHeadings() {
    document.querySelectorAll('h1, h2').forEach(function (heading) {
      if (!heading.closest('header, footer, .chat, .deels-source-home, .source-catalog, .contest-overview, .deels-functional-hub, .deels-campaign-manage')) heading.classList.add('deels-v2-heading');
    });
  }

  function storyItems() {
    return Array.prototype.slice.call(document.querySelectorAll('.show_story[data-route]'));
  }

  function updateStoryProgress(popup) {
    if (!popup) return;
    var items = storyItems();
    var progress = popup.querySelector('.deels-v2-story-progress');
    if (!progress) {
      progress = document.createElement('div');
      progress.className = 'deels-v2-story-progress';
      popup.appendChild(progress);
    }
    progress.innerHTML = '';
    var currentId = String(popup.getAttribute('data-current-story') || '');
    var max = Math.min(items.length, 12);
    for (var i = 0; i < max; i++) {
      var segment = document.createElement('span');
      if (String(items[i].getAttribute('data-story') || '') === currentId) segment.className = 'active';
      progress.appendChild(segment);
    }
    if (!max) progress.style.display = 'none';
    else progress.style.display = 'flex';
  }

  function openStory(item) {
    if (!item) return;
    item.dispatchEvent(new MouseEvent('click', { bubbles: true, cancelable: true, view: window }));
  }

  function storyNeighbor(direction) {
    var popup = document.getElementById('story-popup');
    if (!popup) return;
    var currentId = popup.getAttribute('data-current-story');
    var items = storyItems();
    if (!items.length) return;
    var index = items.findIndex(function (item) { return String(item.getAttribute('data-story') || '') === String(currentId || ''); });
    if (index < 0) index = 0;
    var next = (index + direction + items.length) % items.length;
    if (window.jQuery && window.jQuery.magnificPopup) window.jQuery.magnificPopup.close();
    window.setTimeout(function () { openStory(items[next]); }, 80);
  }

  function enhanceStoryPopup() {
    var popup = document.getElementById('story-popup');
    if (!popup || popup.dataset.deelsV2Enhanced === '1') return;
    popup.dataset.deelsV2Enhanced = '1';

    var nav = document.createElement('div');
    nav.className = 'story-swipe-nav';
    nav.innerHTML = '<button type="button" class="story-swipe-prev" aria-label="Предыдущая сторис">‹</button><button type="button" class="story-swipe-next" aria-label="Следующая сторис">›</button>';
    popup.appendChild(nav);

    var hint = document.createElement('div');
    hint.className = 'story-swipe-hint';
    hint.textContent = 'Свайп ← →';
    popup.appendChild(hint);
    updateStoryProgress(popup);

    nav.querySelector('.story-swipe-prev').addEventListener('click', function (event) { event.preventDefault(); event.stopPropagation(); storyNeighbor(-1); });
    nav.querySelector('.story-swipe-next').addEventListener('click', function (event) { event.preventDefault(); event.stopPropagation(); storyNeighbor(1); });

    var startX = null;
    popup.addEventListener('touchstart', function (event) {
      if (event.touches && event.touches.length === 1) startX = event.touches[0].clientX;
    }, { passive: true });
    popup.addEventListener('touchend', function (event) {
      if (startX === null || !event.changedTouches || !event.changedTouches.length) return;
      var delta = event.changedTouches[0].clientX - startX;
      startX = null;
      if (Math.abs(delta) < 50) return;
      storyNeighbor(delta < 0 ? 1 : -1);
    }, { passive: true });

    document.addEventListener('keydown', function (event) {
      if (!document.querySelector('.mfp-wrap') || !document.querySelector('.mfp-content #story-popup')) return;
      if (event.key === 'ArrowLeft') storyNeighbor(-1);
      if (event.key === 'ArrowRight') storyNeighbor(1);
    });

    document.addEventListener('click', function (event) {
      var trigger = event.target.closest && event.target.closest('.show_story[data-story]');
      if (!trigger) return;
      popup.setAttribute('data-current-story', trigger.getAttribute('data-story') || '');
      updateStoryProgress(popup);
    }, true);
  }

  function observeDynamicContent() {
    if (!window.MutationObserver) return;
    var observer = new MutationObserver(function () {
      upgradeCatalogCards();
      enhanceStoryPopup();
      updateStoryProgress(document.getElementById('story-popup'));
      ensureFunctionalHub();
      ensureCampaignPublicTools();
      ensureCampaignManagementTools();
    });
    observer.observe(document.body, { childList: true, subtree: true });
  }

  ready(function () {
    document.body.classList.add('deels-v2-enabled');
    upgradeHeader();
    labelPage();
    ensureFunctionalHub();
    ensureCampaignPublicTools();
    ensureCampaignManagementTools();
    upgradeCatalogCards();
    upgradeHeadings();
    enhanceStoryPopup();
    observeDynamicContent();
  });
})();