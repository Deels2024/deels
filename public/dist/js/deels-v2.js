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
    var selectors = ['.challenge-item', '.story-item', '.finish-item', '.bank__item', '.campaign-card'];
    document.querySelectorAll(selectors.join(',')).forEach(function (card) {
      card.classList.add('deels-v2-live-card');
    });
  }

  function upgradeHeadings() {
    document.querySelectorAll('h1, h2').forEach(function (heading) {
      if (!heading.closest('header, footer, .chat')) heading.classList.add('deels-v2-heading');
    });
  }

  ready(function () {
    document.body.classList.add('deels-v2-enabled');
    upgradeHeader();
    buildHomeHero();
    labelPage();
    upgradeCatalogCards();
    upgradeHeadings();
  });
})();
