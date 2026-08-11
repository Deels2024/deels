(function () {
  'use strict';

  function ready(fn) {
    if (document.readyState === 'loading') {
      document.addEventListener('DOMContentLoaded', fn, { once: true });
    } else {
      fn();
    }
  }

  function route(path) {
    return window.location.pathname === path;
  }

  function ensureNavLink(label, href, beforeLabel) {
    var lists = document.querySelectorAll('.header__list ul, .header__menu-list');
    lists.forEach(function (list) {
      var links = Array.prototype.slice.call(list.querySelectorAll('a'));
      if (links.some(function (link) { return link.textContent.trim() === label; })) return;
      var li = document.createElement('li');
      var a = document.createElement('a');
      a.href = href;
      a.textContent = label;
      li.appendChild(a);
      var before = links.find(function (link) { return link.textContent.trim() === beforeLabel; });
      if (before && before.parentElement && before.parentElement.parentElement === list) {
        list.insertBefore(li, before.parentElement);
      } else {
        list.appendChild(li);
      }
    });
  }

  function upgradeHeader() {
    ensureNavLink('Баттлы', '/battles', 'Сторис');
    ensureNavLink('Копилки', '/campaign', 'Контакты');
    var header = document.querySelector('.header');
    if (header) header.classList.add('deels-v2-header');
  }

  function buildHomeHero() {
    if (!route('/') || document.querySelector('.deels-v2-home-hero')) return;
    var anchor = document.querySelector('header');
    if (!anchor) return;

    var section = document.createElement('section');
    section.className = 'deels-v2-home-hero';
    section.innerHTML = '' +
      '<div class="container deels-v2-home-hero__grid">' +
        '<div class="deels-v2-home-hero__copy">' +
          '<span class="deels-v2-eyebrow">Твоя площадка для движения</span>' +
          '<h1>Создавай. Участвуй. <em>Побеждай.</em></h1>' +
          '<p>Челленджи, баттлы, вертикальные видео и копилки в одном месте. Снимай ответы, голосуй, общайся и получай награды.</p>' +
          '<div class="deels-v2-home-hero__actions">' +
            '<a class="btn" href="/dashboard/challenges/create">Создать челлендж</a>' +
            '<a class="btn btn-grey" href="/stories">Смотреть ленту</a>' +
          '</div>' +
          '<div class="deels-v2-home-hero__proof"><span>● Живые челленджи</span><span>● Реальные голоса</span><span>● Награды и донаты</span></div>' +
        '</div>' +
        '<div class="deels-v2-home-phone" aria-hidden="true">' +
          '<div class="deels-v2-home-phone__screen">' +
            '<div class="deels-v2-home-phone__badge">В ТРЕНДЕ</div>' +
            '<div class="deels-v2-home-phone__emoji">🔥</div>' +
            '<div class="deels-v2-home-phone__copy"><small>@deels</small><strong>Покажи, на что ты способен</strong><span>Свайпай ленту и выбирай следующий челлендж</span></div>' +
            '<div class="deels-v2-home-phone__rail"><b>♡</b><b>◉</b><b>↗</b></div>' +
          '</div>' +
        '</div>' +
      '</div>';
    anchor.insertAdjacentElement('afterend', section);
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

  function upgradeCatalogCards() {
    var selectors = ['.challenge-item', '.story-item', '.finish-item', '.bank__item', '.campaign-card', '.challenge-card', '.copystories-item', '.tops-story'];
    document.querySelectorAll(selectors.join(',')).forEach(function (card) {
      card.classList.add('deels-v2-live-card');
    });
  }

  function upgradeHeadings() {
    document.querySelectorAll('h1, h2').forEach(function (heading) {
      if (!heading.closest('header, footer, .chat')) heading.classList.add('deels-v2-heading');
    });
  }

  function storyItems() {
    return Array.prototype.slice.call(document.querySelectorAll('.show_story[data-route]'));
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
    var index = items.findIndex(function (item) {
      return String(item.getAttribute('data-story') || '') === String(currentId || '');
    });
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

    nav.querySelector('.story-swipe-prev').addEventListener('click', function (event) {
      event.preventDefault();
      event.stopPropagation();
      storyNeighbor(-1);
    });
    nav.querySelector('.story-swipe-next').addEventListener('click', function (event) {
      event.preventDefault();
      event.stopPropagation();
      storyNeighbor(1);
    });

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
    }, true);
  }

  function observeDynamicContent() {
    if (!window.MutationObserver) return;
    var observer = new MutationObserver(function () {
      upgradeCatalogCards();
      enhanceStoryPopup();
    });
    observer.observe(document.body, { childList: true, subtree: true });
  }

  ready(function () {
    document.body.classList.add('deels-v2-enabled');
    upgradeHeader();
    buildHomeHero();
    labelPage();
    upgradeCatalogCards();
    upgradeHeadings();
    enhanceStoryPopup();
    observeDynamicContent();
  });
})();
