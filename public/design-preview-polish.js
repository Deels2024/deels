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
      feed: { eyebrow: 'Лента Deels', text: 'Свайпай вертикальные видео, поддерживай участников и голосуй за лучшие ответы.' },
      challenges: { eyebrow: 'Каталог', text: 'Находи интересные вызовы, присоединяйся к участникам и следи за результатами.' },
      battles: { eyebrow: 'Один на один', text: 'Принимай вызовы, публикуй свой ответ и побеждай благодаря голосам сообщества.' },
      profile: { eyebrow: 'Публичный профиль', text: 'Видео, челленджи, достижения, подписчики и копилки — всё в одном профиле.' },
      wallet: { eyebrow: 'Личный кабинет', text: 'Следи за балансом, пополнениями, выводами, донатами и автоплатежами.' },
      messages: { eyebrow: 'Общение', text: 'Личные сообщения, новые ответы и важные уведомления всегда под рукой.' },
      create: { eyebrow: 'Создание', text: 'Заполни детали, добавь обложку или видео и опубликуй новый челлендж.' },
      campaign: { eyebrow: 'Копилка', text: 'Поддерживай сбор, следи за прогрессом, новостями и благодарностями участникам.' },
      admin: { eyebrow: 'Модерация', text: 'Управляй контентом и обращениями в едином центре с доступами по ролям.' }
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
      '.campaign-more{margin-top:64px}.campaign-more-head{display:flex;align-items:flex-end;justify-content:space-between;gap:24px;margin-bottom:26px}.campaign-more-head h2{margin:7px 0 5px;font-size:clamp(30px,4vw,48px);letter-spacing:-.04em}.campaign-more-head p{margin:0;color:var(--muted);max-width:620px}.campaign-count{padding:9px 13px;border-radius:999px;background:#f3ebf9;color:var(--purple);font-size:12px;font-weight:900}.campaign-gallery{display:grid;grid-template-columns:repeat(3,minmax(0,1fr));gap:18px}.campaign-mini{overflow:hidden;border:1px solid var(--line);border-radius:24px;background:#fff;box-shadow:0 12px 34px rgba(68,26,107,.07);transition:transform .2s ease,box-shadow .2s ease}.campaign-mini:hover{transform:translateY(-4px);box-shadow:0 20px 48px rgba(68,26,107,.12)}.campaign-mini-cover{position:relative;min-height:178px;padding:16px;display:flex;align-items:center;justify-content:center;color:#fff}.campaign-mini-cover.violet{background:linear-gradient(145deg,#421078,#a841d9)}.campaign-mini-cover.blue{background:linear-gradient(145deg,#145f94,#34d7e9)}.campaign-mini-cover.coral{background:linear-gradient(145deg,#a83e4f,#ff8f71)}.campaign-mini-cover.orange{background:linear-gradient(145deg,#b74f27,#ffad5d)}.campaign-mini-cover.pink{background:linear-gradient(145deg,#8d2480,#ff4fc8)}.campaign-mini-emoji{font-size:72px;filter:drop-shadow(0 16px 22px rgba(20,5,31,.25))}.campaign-mini-tag{position:absolute;left:14px;top:14px;padding:7px 9px;border-radius:999px;background:rgba(27,9,39,.48);backdrop-filter:blur(8px);font-size:10px;font-weight:900}.campaign-mini-verified{position:absolute;right:14px;top:14px;width:30px;height:30px;display:grid;place-items:center;border-radius:50%;background:rgba(255,255,255,.9);color:var(--purple);font-weight:900}.campaign-mini-body{padding:17px}.campaign-mini-body h3{min-height:48px;margin:0 0 13px;font-size:18px;line-height:1.25}.campaign-mini-progress{height:7px;border-radius:999px;background:#eee7f2;overflow:hidden}.campaign-mini-progress span{display:block;height:100%;border-radius:inherit;background:linear-gradient(90deg,var(--purple),var(--pink))}.campaign-mini-meta{display:flex;justify-content:space-between;gap:10px;margin:9px 0 15px}.campaign-mini-meta strong{font-size:13px}.campaign-mini-meta span{color:var(--muted);font-size:11px}.campaign-mini-bottom{display:flex;align-items:center;justify-content:space-between;gap:10px}.campaign-mini-percent{font-size:12px;font-weight:900;color:var(--purple)}.campaign-mini .pbtn{min-height:40px;padding:0 14px;border-radius:12px;font-size:12px}',
      '@media(min-width:901px){.deels-mobile-drawer{display:none!important}}',
      '@media(max-width:900px){.campaign-gallery{grid-template-columns:repeat(2,minmax(0,1fr))}}',
      '@media(max-width:620px){.site-header .header-inner{min-height:66px}.screen-wrap{padding-bottom:72px}.screen-head h1{letter-spacing:-.035em}.screen-head p{font-size:15px;line-height:1.45}.tabs{gap:7px}.tabs button,.tabs a{padding:9px 13px}.content-card{border-radius:22px}.poster{border-radius:22px}.campaign-more{margin-top:44px}.campaign-more-head{align-items:flex-start;flex-direction:column}.campaign-gallery{grid-template-columns:1fr}.campaign-mini{display:grid;grid-template-columns:126px minmax(0,1fr);border-radius:20px}.campaign-mini-cover{min-height:176px}.campaign-mini-emoji{font-size:50px}.campaign-mini-body h3{min-height:auto;font-size:16px}.campaign-mini-meta{display:grid;gap:2px}.campaign-mini-bottom{align-items:flex-end}}'
    ].join('');
    document.head.appendChild(style);

    var header = document.querySelector('.site-header');
    var menuButton = header && header.querySelector('.mobile-menu-button');
    if (menuButton) {
      var drawer = document.createElement('div');
      drawer.className = 'deels-mobile-drawer';
      drawer.setAttribute('aria-hidden', 'true');
      drawer.innerHTML = '<nav class="deels-mobile-panel" aria-label="Мобильная навигация"><a href="#home" data-mobile-screen="home">Главная</a><a href="#feed" data-mobile-screen="feed">Лента</a><a href="#challenges" data-mobile-screen="challenges">Челленджи</a><a href="#battles" data-mobile-screen="battles">Баттлы</a><a href="#campaign" data-mobile-screen="campaign">Копилки</a><div class="menu-sep"></div><a href="#profile" data-mobile-screen="profile">Профиль</a><a href="#wallet" data-mobile-screen="wallet">Кошелёк</a><a href="#messages" data-mobile-screen="messages">Сообщения</a></nav>';
      document.body.appendChild(drawer);
      function closeMenu() { drawer.classList.remove('open'); drawer.setAttribute('aria-hidden', 'true'); menuButton.setAttribute('aria-expanded', 'false'); document.body.classList.remove('deels-menu-open'); }
      function syncMobileActive() { var current=(location.hash||'#home').slice(1); drawer.querySelectorAll('[data-mobile-screen]').forEach(function(link){link.classList.toggle('active',link.getAttribute('data-mobile-screen')===current);}); }
      menuButton.setAttribute('aria-expanded','false');
      menuButton.addEventListener('click',function(){var open=!drawer.classList.contains('open');drawer.classList.toggle('open',open);drawer.setAttribute('aria-hidden',open?'false':'true');menuButton.setAttribute('aria-expanded',open?'true':'false');document.body.classList.toggle('deels-menu-open',open);syncMobileActive();});
      drawer.addEventListener('click',function(event){if(event.target===drawer||event.target.closest('[data-mobile-screen]'))closeMenu();});
      document.addEventListener('keydown',function(event){if(event.key==='Escape')closeMenu();});
      window.addEventListener('hashchange',function(){syncMobileActive();closeMenu();});
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

    var campaignScreen = document.getElementById('screen-campaign');
    var campaignWrap = campaignScreen && campaignScreen.querySelector('.screen-wrap');
    if (campaignWrap && !campaignWrap.querySelector('.campaign-more')) {
      var campaigns = [
        ['🐾','Тёплый дом для 40 хвостов',91,'819 000 ₽','900 000 ₽','Животные','blue'],['🎨','Творческий двор для подростков',64,'641 200 ₽','1 000 000 ₽','Дети и творчество','coral'],['🦿','Шаг вперёд для Ильи',72,'1 080 000 ₽','1 500 000 ₽','Здоровье','violet'],['📚','Библиотека в маленьком посёлке',47,'235 000 ₽','500 000 ₽','Образование','blue'],['🏀','Новая площадка для района',58,'464 000 ₽','800 000 ₽','Спорт','orange'],['🐶','Операция для Бруно',83,'249 000 ₽','300 000 ₽','Животные','pink'],['🎭','Спектакль молодых артистов',39,'156 000 ₽','400 000 ₽','Творчество','violet'],['🚑','Оборудование для волонтёров',68,'544 000 ₽','800 000 ₽','Помощь','coral'],['🌳','Вернём парк соседям',76,'380 000 ₽','500 000 ₽','Город','blue'],['🎓','Учёба для талантливой Алисы',55,'330 000 ₽','600 000 ₽','Образование','pink'],['🏊','Поездка команды на финал',62,'186 000 ₽','300 000 ₽','Спорт','violet'],['🧸','Комната отдыха в детской больнице',81,'972 000 ₽','1 200 000 ₽','Дети','orange'],['🎸','Первый альбом школьной группы',44,'132 000 ₽','300 000 ₽','Музыка','pink'],['🐈','Корм на зиму для приюта',69,'207 000 ₽','300 000 ₽','Животные','coral'],['🧑‍🦽','Доступная мастерская',52,'624 000 ₽','1 200 000 ₽','Социальный проект','blue'],['🌊','Очистим берег вместе',88,'352 000 ₽','400 000 ₽','Экология','violet'],['📷','Фотолаборатория для подростков',36,'144 000 ₽','400 000 ₽','Творчество','orange'],['🏠','Ремонт дома семьи после пожара',74,'1 110 000 ₽','1 500 000 ₽','Помощь семье','pink']
      ];
      var section=document.createElement('section');
      section.className='campaign-more';
      section.innerHTML='<div class="campaign-more-head"><div><span class="eyebrow">Ещё собирают сейчас</span><h2>Копилки сообщества</h2><p>Поддержите людей и проекты, которым сейчас особенно важна помощь.</p></div><span class="campaign-count">18 активных</span></div><div class="campaign-gallery">'+campaigns.map(function(c){return '<article class="campaign-mini"><div class="campaign-mini-cover '+c[6]+'"><span class="campaign-mini-tag">'+c[5]+'</span><span class="campaign-mini-verified">✓</span><span class="campaign-mini-emoji">'+c[0]+'</span></div><div class="campaign-mini-body"><h3>'+c[1]+'</h3><div class="campaign-mini-progress"><span style="width:'+c[2]+'%"></span></div><div class="campaign-mini-meta"><strong>'+c[3]+'</strong><span>из '+c[4]+'</span></div><div class="campaign-mini-bottom"><span class="campaign-mini-percent">'+c[2]+'% собрано</span><button class="pbtn soft" type="button" data-toast="Открывается выбранная копилка">Поддержать</button></div></div></article>';}).join('')+'</div>';
      campaignWrap.appendChild(section);
    }
  });
})();