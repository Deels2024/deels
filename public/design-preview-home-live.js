(function () {
  'use strict';

  var scriptSource = document.currentScript && document.currentScript.src
    ? document.currentScript.src
    : '';

  function ready(callback) {
    if (document.readyState === 'loading') {
      document.addEventListener('DOMContentLoaded', callback, { once: true });
    } else {
      callback();
    }
  }

  function mediaUrl(fileName) {
    if (scriptSource) {
      return new URL('./home-media/' + fileName, scriptSource).href;
    }
    return './home-media/' + fileName;
  }

  ready(function () {
    var home = document.getElementById('screen-home');
    if (!home || home.dataset.liveHome === '1') return;
    home.dataset.liveHome = '1';

    var style = document.createElement('style');
    style.textContent = [
      '#screen-home .hero-grid{grid-template-columns:minmax(0,.88fr) minmax(440px,1.12fr);align-items:center}',
      '#screen-home .home-live-hero-visual{position:relative;min-width:0}',
      '#screen-home .home-live-hero-card{position:relative;display:block;min-height:510px;overflow:hidden;border-radius:38px;color:#fff;text-decoration:none;background:#3e155f;box-shadow:0 34px 90px rgba(54,20,84,.26),0 0 0 1px rgba(255,255,255,.75)}',
      '#screen-home .home-live-hero-card>img{position:absolute;inset:0;width:100%;height:100%;object-fit:cover;object-position:center;transform:scale(1.015);transition:transform .55s ease}',
      '#screen-home .home-live-hero-card:hover>img{transform:scale(1.045)}',
      '#screen-home .home-live-hero-card:after{content:"";position:absolute;inset:0;background:linear-gradient(180deg,rgba(18,7,26,.04) 24%,rgba(18,7,26,.18) 48%,rgba(18,7,26,.9) 100%)}',
      '#screen-home .home-live-pill{position:absolute;z-index:2;left:20px;top:20px;display:inline-flex;align-items:center;gap:8px;padding:9px 12px;border:1px solid rgba(255,255,255,.32);border-radius:999px;background:rgba(28,10,42,.36);backdrop-filter:blur(14px);font-size:12px;font-weight:900}',
      '#screen-home .home-live-pill i{width:8px;height:8px;border-radius:50%;background:#ff4fc8;box-shadow:0 0 0 5px rgba(255,79,200,.2);animation:homeLivePulse 1.8s infinite}',
      '#screen-home .home-live-hero-caption{position:absolute;z-index:2;left:24px;right:24px;bottom:24px}',
      '#screen-home .home-live-hero-caption small{display:block;margin-bottom:7px;font-size:13px;font-weight:850;color:rgba(255,255,255,.76)}',
      '#screen-home .home-live-hero-caption strong{display:block;max-width:520px;font-size:clamp(28px,3.4vw,46px);line-height:1.02;letter-spacing:-.045em}',
      '#screen-home .home-live-hero-caption span{display:inline-flex;margin-top:13px;padding:10px 13px;border-radius:13px;background:#fff;color:#5e2691;font-size:13px;font-weight:900}',
      '#screen-home .home-live-stat{position:absolute;z-index:3;right:18px;top:68px;min-width:156px;padding:13px 15px;border:1px solid rgba(255,255,255,.32);border-radius:18px;background:rgba(255,255,255,.86);backdrop-filter:blur(18px);color:#2b1b34;box-shadow:0 18px 44px rgba(25,7,37,.18)}',
      '#screen-home .home-live-stat strong{display:block;font-size:22px;letter-spacing:-.03em;color:#6b2bc1}',
      '#screen-home .home-live-stat span{font-size:11px;color:#74697d}',
      '#screen-home .home-live-pulse{padding:0 0 8px;background:#fff}',
      '#screen-home .home-live-pulse-grid{display:grid;grid-template-columns:1.25fr repeat(3,1fr);gap:12px;transform:translateY(-18px)}',
      '#screen-home .home-live-pulse-card{display:flex;align-items:center;gap:11px;min-height:74px;padding:14px 16px;border:1px solid #ece3f2;border-radius:20px;background:rgba(255,255,255,.96);box-shadow:0 16px 42px rgba(68,26,107,.09)}',
      '#screen-home .home-live-pulse-card b{display:grid;place-items:center;width:38px;height:38px;border-radius:13px;background:#f4edfa;color:#6b2bc1;font-size:17px}',
      '#screen-home .home-live-pulse-card strong{display:block;font-size:17px;line-height:1.1;color:#30253a}',
      '#screen-home .home-live-pulse-card span{display:block;margin-top:3px;color:#807488;font-size:11px}',
      '#screen-home .home-live-poster{position:relative;isolation:isolate;background:#35104f!important}',
      '#screen-home .home-live-poster:after{content:"";position:absolute;z-index:1;inset:0;background:linear-gradient(180deg,rgba(18,6,27,.03) 34%,rgba(18,6,27,.84) 100%);pointer-events:none}',
      '#screen-home .home-live-photo{position:absolute;z-index:0;inset:0;width:100%;height:100%;object-fit:cover;transition:transform .45s ease}',
      '#screen-home .video-card:hover .home-live-photo{transform:scale(1.045)}',
      '#screen-home .home-live-poster .poster-top,#screen-home .home-live-poster .poster-caption,#screen-home .home-live-poster .play-button{z-index:3}',
      '#screen-home .home-live-poster .poster-tag{border:1px solid rgba(255,255,255,.24);background:rgba(20,6,30,.42);backdrop-filter:blur(10px)}',
      '#screen-home .home-live-poster .round-action{display:grid;place-items:center;width:38px;height:38px;border-radius:50%;background:rgba(20,6,30,.38);backdrop-filter:blur(10px);cursor:pointer;transition:.2s ease}',
      '#screen-home .home-live-poster .round-action.active{background:linear-gradient(135deg,#ff4fc8,#8c45d5);transform:scale(1.05)}',
      '#screen-home .home-live-poster .poster-caption span{font-size:12px;color:rgba(255,255,255,.78)}',
      '#screen-home .home-live-poster .poster-caption strong{font-size:22px;line-height:1.08;text-shadow:0 2px 16px rgba(0,0,0,.28)}',
      '#screen-home .video-card{cursor:pointer}',
      '#screen-home .video-card .card-meta{background:#fff}',
      '#screen-home .home-live-story{position:relative;overflow:hidden;min-height:170px;padding-left:178px!important}',
      '#screen-home .home-live-story:before{content:"";position:absolute;z-index:1;inset:0;background:linear-gradient(90deg,rgba(18,6,27,.02) 25%,rgba(18,6,27,.76) 100%)}',
      '#screen-home .home-live-story-photo{position:absolute;inset:0;width:100%;height:100%;object-fit:cover}',
      '#screen-home .home-live-story>div{position:relative;z-index:2}',
      '#screen-home .home-live-story .story-icon{display:none}',
      '#screen-home .home-live-story h3{color:#fff;text-shadow:0 2px 14px rgba(0,0,0,.24)}',
      '#screen-home .home-live-story span{color:rgba(255,255,255,.82)}',
      '@keyframes homeLivePulse{0%,100%{transform:scale(1);opacity:1}50%{transform:scale(.75);opacity:.62}}',
      '@media(max-width:980px){#screen-home .hero-grid{grid-template-columns:1fr!important}#screen-home .home-live-hero-card{min-height:460px}#screen-home .home-live-pulse-grid{grid-template-columns:repeat(2,1fr)}}',
      '@media(max-width:620px){#screen-home .home-live-hero-card{min-height:390px;border-radius:28px}#screen-home .home-live-stat{right:12px;top:58px;min-width:134px;padding:10px 12px}#screen-home .home-live-stat strong{font-size:18px}#screen-home .home-live-hero-caption{left:17px;right:17px;bottom:17px}#screen-home .home-live-hero-caption strong{font-size:30px}#screen-home .home-live-pulse-grid{display:flex;overflow-x:auto;gap:10px;padding-bottom:5px;scroll-snap-type:x mandatory}#screen-home .home-live-pulse-card{min-width:230px;scroll-snap-align:start}#screen-home .home-live-story{min-height:190px;padding:118px 16px 16px!important}#screen-home .home-live-story:before{background:linear-gradient(180deg,transparent 28%,rgba(18,6,27,.88) 100%)}}',
      '@media(prefers-reduced-motion:reduce){#screen-home .home-live-hero-card>img,#screen-home .home-live-photo{transition:none!important}#screen-home .home-live-pill i{animation:none!important}}'
    ].join('');
    document.head.appendChild(style);

    var heroVisual = home.querySelector('.hero-visual');
    if (heroVisual) {
      heroVisual.classList.add('home-live-hero-visual');
      heroVisual.innerHTML = '' +
        '<a href="#feed" class="home-live-hero-card" aria-label="Открыть живую ленту Deels">' +
          '<img src="' + mediaUrl('hero-rooftop.webp') + '" alt="Создатели Deels снимают челлендж вместе" decoding="async" fetchpriority="high">' +
          '<span class="home-live-pill"><i></i> Сообщество сейчас онлайн</span>' +
          '<span class="home-live-stat"><strong>2 148</strong><span>новых роликов сегодня</span></span>' +
          '<div class="home-live-hero-caption"><small>@deels.community • прямо сейчас</small><strong>Идеи становятся движением, когда их подхватывают другие</strong><span>Смотреть живую ленту →</span></div>' +
        '</a>';
    }

    var heroSection = home.querySelector('.hero');
    if (heroSection && !home.querySelector('.home-live-pulse')) {
      heroSection.insertAdjacentHTML('afterend', '' +
        '<section class="home-live-pulse" aria-label="Активность Deels сегодня"><div class="container home-live-pulse-grid">' +
          '<div class="home-live-pulse-card"><b>●</b><div><strong>Deels живёт прямо сейчас</strong><span>Новые ответы, голоса и участники каждую минуту</span></div></div>' +
          '<div class="home-live-pulse-card"><b>▶</b><div><strong>2 148 роликов</strong><span>опубликовано сегодня</span></div></div>' +
          '<div class="home-live-pulse-card"><b>⚡</b><div><strong>384 челленджа</strong><span>открыты для участия</span></div></div>' +
          '<div class="home-live-pulse-card"><b>🏆</b><div><strong>12 финалов</strong><span>завершатся сегодня</span></div></div>' +
        '</div></section>');
    }

    var videos = [
      { file: 'video-dance.webp', alt: 'Участница снимает танцевальный челлендж', tag: 'Танцы', author: '@mila.sun', title: 'Повтори летний движ' },
      { file: 'video-friends.webp', alt: 'Подруги снимают совместный челлендж', tag: 'Вместе', author: '@friends.room', title: 'Сними это с другом' },
      { file: 'video-creator.webp', alt: 'Автор записывает вертикальный видеоответ', tag: 'Творчество', author: '@alina.move', title: 'Покажи свою реакцию' },
      { file: 'video-boy.webp', alt: 'Автор участвует в новом видео-челлендже', tag: 'В тренде', author: '@max.live', title: 'Твой жест — твой стиль' }
    ];

    var cards = home.querySelectorAll('.horizontal-cards .video-card');
    cards.forEach(function (card, index) {
      var item = videos[index % videos.length];
      var poster = card.querySelector('.poster');
      if (!poster) return;
      poster.classList.add('home-live-poster');
      var emoji = poster.querySelector('.poster-emoji');
      if (emoji) emoji.remove();

      var image = document.createElement('img');
      image.className = 'home-live-photo';
      image.src = mediaUrl(item.file);
      image.alt = item.alt;
      image.loading = index === 0 ? 'eager' : 'lazy';
      image.decoding = 'async';
      poster.insertBefore(image, poster.firstChild);

      var tag = poster.querySelector('.poster-tag');
      var author = poster.querySelector('.poster-caption span');
      var title = poster.querySelector('.poster-caption strong');
      if (tag) tag.textContent = item.tag;
      if (author) author.textContent = item.author;
      if (title) title.textContent = item.title;

      card.setAttribute('role', 'link');
      card.setAttribute('tabindex', '0');
      card.setAttribute('aria-label', 'Открыть челлендж «' + item.title + '»');
      card.addEventListener('click', function (event) {
        if (event.target.closest('.round-action')) return;
        location.hash = 'challenges';
      });
      card.addEventListener('keydown', function (event) {
        if (event.key === 'Enter' || event.key === ' ') {
          event.preventDefault();
          location.hash = 'challenges';
        }
      });

      var favorite = poster.querySelector('.round-action');
      if (favorite) {
        favorite.setAttribute('role', 'button');
        favorite.setAttribute('tabindex', '0');
        favorite.setAttribute('aria-label', 'Сохранить челлендж');
        var toggleFavorite = function (event) {
          event.preventDefault();
          event.stopPropagation();
          favorite.classList.toggle('active');
          favorite.textContent = favorite.classList.contains('active') ? '♥' : '♡';
          favorite.setAttribute('aria-pressed', favorite.classList.contains('active') ? 'true' : 'false');
        };
        favorite.addEventListener('click', toggleFavorite);
        favorite.addEventListener('keydown', function (event) {
          if (event.key === 'Enter' || event.key === ' ') toggleFavorite(event);
        });
      }
    });

    var storyMedia = ['video-friends.webp', 'video-creator.webp', 'video-boy.webp'];
    var storyCards = home.querySelectorAll('.stories-stack .story-card');
    storyCards.forEach(function (card, index) {
      card.classList.add('home-live-story');
      var image = document.createElement('img');
      image.className = 'home-live-story-photo';
      image.src = mediaUrl(storyMedia[index % storyMedia.length]);
      image.alt = '';
      image.loading = 'lazy';
      image.decoding = 'async';
      card.insertBefore(image, card.firstChild);
    });
  });
})();