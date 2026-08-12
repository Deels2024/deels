(function () {
  'use strict';

  function ready(fn) {
    if (document.readyState === 'loading') {
      document.addEventListener('DOMContentLoaded', fn, { once: true });
    } else {
      fn();
    }
  }

  ready(function () {
    var copy = {
      feed: {
        eyebrow: 'Лента Deels',
        text: 'Свайпай вертикальные видео, поддерживай участников и голосуй за лучшие ответы.'
      },
      challenges: {
        eyebrow: 'Каталог',
        text: 'Находи интересные вызовы, присоединяйся к участникам и следи за результатами.'
      },
      battles: {
        eyebrow: 'Один на один',
        text: 'Принимай вызовы, публикуй свой ответ и побеждай благодаря голосам сообщества.'
      },
      profile: {
        eyebrow: 'Публичный профиль',
        text: 'Видео, челленджи, достижения, подписчики и копилки — всё в одном профиле.'
      },
      wallet: {
        eyebrow: 'Личный кабинет',
        text: 'Следи за балансом, пополнениями, выводами, донатами и автоплатежами.'
      },
      messages: {
        eyebrow: 'Общение',
        text: 'Личные сообщения, новые ответы и важные уведомления всегда под рукой.'
      },
      create: {
        eyebrow: 'Создание',
        text: 'Заполни детали, добавь обложку или видео и опубликуй новый челлендж.'
      },
      campaign: {
        eyebrow: 'Копилка',
        text: 'Поддерживай сбор, следи за прогрессом, новостями и благодарностями участникам.'
      },
      admin: {
        eyebrow: 'Модерация',
        text: 'Управляй контентом и обращениями в едином центре с доступами по ролям.'
      }
    };

    Object.keys(copy).forEach(function (name) {
      var screen = document.getElementById('screen-' + name);
      if (!screen) return;
      var eyebrow = screen.querySelector('.screen-head .eyebrow');
      var text = screen.querySelector('.screen-head p');
      if (eyebrow) eyebrow.textContent = copy[name].eyebrow;
      if (text) text.textContent = copy[name].text;
    });

    document.querySelectorAll('#screen-campaign .eyebrow').forEach(function (node) {
      if (node.textContent.trim() === 'Updates') node.textContent = 'Новости';
      if (node.textContent.trim() === 'FAQ') node.textContent = 'Вопросы';
    });

    var networkButton = document.getElementById('networkBtn');
    if (networkButton) networkButton.textContent = 'Проверить соединение';

    document.querySelectorAll('[data-toast]').forEach(function (node) {
      var text = node.getAttribute('data-toast') || '';
      if (/Laravel|preview/i.test(text)) {
        if (/участ/i.test(text)) text = 'Переходим к участию';
        else if (/ответ/i.test(text)) text = 'Открываем ваш ответ';
        else if (/форм/i.test(text)) text = 'Челлендж готов к публикации';
        else if (/сообщ/i.test(text)) text = 'Сообщение отправлено';
        else text = 'Готово';
        node.setAttribute('data-toast', text);
      }
    });

    var previewFooter = document.querySelector('.preview-footer');
    if (previewFooter) previewFooter.remove();

    var style = document.createElement('style');
    style.textContent = [
      '.deels-mobile-drawer{position:fixed;inset:0;z-index:300;display:none;background:rgba(28,13,38,.38);backdrop-filter:blur(10px)}',
      '.deels-mobile-drawer.open{display:block}',
      '.deels-mobile-panel{position:absolute;left:12px;right:12px;top:76px;padding:12px;border:1px solid rgba(107,43,193,.12);border-radius:22px;background:rgba(255,255,255,.98);box-shadow:0 24px 70px rgba(48,19,72,.2)}',
      '.deels-mobile-panel a{display:flex;align-items:center;min-height:48px;padding:0 14px;border-radius:14px;color:#352b40;text-decoration:none;font-weight:800}',
      '.deels-mobile-panel a.active{color:#6b2bc1;background:#f4edfa}',
      '.deels-mobile-panel .menu-sep{height:1px;margin:8px 4px;background:#eee6f3}',
      'body.deels-menu-open{overflow:hidden}',
      '@media(min-width:901px){.deels-mobile-drawer{display:none!important}}',
      '@media(max-width:620px){.site-header .header-inner{min-height:66px}.screen-wrap{padding-bottom:72px}.screen-head h1{letter-spacing:-.035em}.screen-head p{font-size:15px;line-height:1.45}.tabs{gap:7px}.tabs button,.tabs a{padding:9px 13px}.content-card{border-radius:22px}.poster{border-radius:22px}}'
    ].join('');
    document.head.appendChild(style);

    var header = document.querySelector('.site-header');
    var menuButton = header && header.querySelector('.mobile-menu-button');
    if (menuButton) {
      var drawer = document.createElement('div');
      drawer.className = 'deels-mobile-drawer';
      drawer.setAttribute('aria-hidden', 'true');
      drawer.innerHTML = '<nav class="deels-mobile-panel" aria-label="Мобильная навигация">' +
        '<a href="#home" data-mobile-screen="home">Главная</a>' +
        '<a href="#feed" data-mobile-screen="feed">Лента</a>' +
        '<a href="#challenges" data-mobile-screen="challenges">Челленджи</a>' +
        '<a href="#battles" data-mobile-screen="battles">Баттлы</a>' +
        '<a href="#campaign" data-mobile-screen="campaign">Копилки</a>' +
        '<div class="menu-sep"></div>' +
        '<a href="#profile" data-mobile-screen="profile">Профиль</a>' +
        '<a href="#wallet" data-mobile-screen="wallet">Кошелёк</a>' +
        '<a href="#messages" data-mobile-screen="messages">Сообщения</a>' +
        '</nav>';
      document.body.appendChild(drawer);

      function closeMenu() {
        drawer.classList.remove('open');
        drawer.setAttribute('aria-hidden', 'true');
        menuButton.setAttribute('aria-expanded', 'false');
        document.body.classList.remove('deels-menu-open');
      }

      function syncMobileActive() {
        var current = (location.hash || '#home').slice(1);
        drawer.querySelectorAll('[data-mobile-screen]').forEach(function (link) {
          link.classList.toggle('active', link.getAttribute('data-mobile-screen') === current);
        });
      }

      menuButton.setAttribute('aria-expanded', 'false');
      menuButton.addEventListener('click', function () {
        var open = !drawer.classList.contains('open');
        drawer.classList.toggle('open', open);
        drawer.setAttribute('aria-hidden', open ? 'false' : 'true');
        menuButton.setAttribute('aria-expanded', open ? 'true' : 'false');
        document.body.classList.toggle('deels-menu-open', open);
        syncMobileActive();
      });
      drawer.addEventListener('click', function (event) {
        if (event.target === drawer || event.target.closest('[data-mobile-screen]')) closeMenu();
      });
      document.addEventListener('keydown', function (event) {
        if (event.key === 'Escape') closeMenu();
      });
      window.addEventListener('hashchange', function () {
        syncMobileActive();
        closeMenu();
      });
      syncMobileActive();
    }

    document.querySelectorAll('.tabs').forEach(function (tabs) {
      tabs.addEventListener('click', function (event) {
        var button = event.target.closest('button');
        if (!button || !tabs.contains(button)) return;
        tabs.querySelectorAll('button').forEach(function (item) { item.classList.remove('active'); });
        button.classList.add('active');
      });
    });
  });
})();