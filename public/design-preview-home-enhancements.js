(function(){
  'use strict';
  function ready(fn){if(document.readyState==='loading')document.addEventListener('DOMContentLoaded',fn,{once:true});else fn();}
  ready(function(){
    var home=document.getElementById('screen-home');
    var previewTop=document.querySelector('.preview-top');
    if(!home||!previewTop)return;

    /* One shared source-faithful Deels header for every preview route. */
    previewTop.className='site-header';
    previewTop.innerHTML=''+
      '<div class="container header-inner">'+
        '<button type="button" class="icon-button mobile-menu-button" aria-label="Открыть меню">≡</button>'+
        '<a href="#home" class="brand" data-screen="home"><span class="brand-mark">D</span><span class="brand-word">DEELS</span></a>'+
        '<nav class="desktop-nav" id="previewNav" aria-label="Основная навигация">'+
          '<a href="#home" data-screen="home">Главная</a>'+
          '<a href="#feed" data-screen="feed">Лента</a>'+
          '<a href="#challenges" data-screen="challenges">Челленджи</a>'+
          '<a href="#battles" data-screen="battles">Баттлы</a>'+
          '<a href="#campaign" data-screen="campaign">Копилки</a>'+
        '</nav>'+
        '<div class="header-actions">'+
          '<a href="#create" data-screen="create" class="button button-primary button-small">+ Создать</a>'+
          '<a href="#profile" data-screen="profile" class="avatar avatar-small desktop-only">СС</a>'+
        '</div>'+
      '</div>';

    if(home.dataset.sourceHome!=='1'){
      home.dataset.sourceHome='1';
      home.innerHTML=''+
        '<div class="deels-app light_theme light_there">'+
          '<main>'+ 
            '<section class="hero theme-gradient"><div class="container hero-grid">'+
              '<div class="hero-copy"><span class="eyebrow">✦ Здесь начинается движение</span><h1>Твоя идея<br>может стать <em>движением</em></h1><p>Создавай челленджи, снимай ответы, участвуй в баттлах и поддерживай истории, которые хочется разделить.</p><div class="hero-actions"><a href="#create" class="button button-primary">Создать челлендж →</a><a href="#feed" class="button button-glass">▶ Смотреть ленту</a></div><div class="hero-proof"><div class="avatar-stack"><span>АК</span><span>МС</span><span>ОЛ</span><span>+12,4K</span></div><p><strong>12,4K+</strong><br>уже создают в Deels</p></div></div>'+ 
              '<div class="hero-visual"><div class="orbit orbit-one"></div><div class="orbit orbit-two"></div><div class="floating-chip chip-prize"><span>🏆</span><strong>50 000 ₽</strong><small>призовой фонд</small></div><div class="floating-chip chip-trend"><span>↗</span><strong>В тренде</strong><small>180K ответов</small></div><div class="phone-frame"><div class="phone-top"><a href="#home" class="brand"><span class="brand-mark">D</span><span class="brand-word">DEELS</span></a><span>◌</span></div><div class="phone-video poster-violet"><span class="phone-live">DEELS • LIVE</span><span class="poster-emoji">🕺</span><div class="phone-side"><span>♡<small>8,2K</small></span><span>◯<small>462</small></span><span>↗<small>92</small></span></div><div class="phone-caption"><small>@mila.sun</small><strong>Повтори летний движ</strong><span>#танцы #deels</span></div></div><div class="phone-nav"><span>⌂</span><span>⌕</span><b>+</b><span>◌</span><span>●</span></div></div></div>'+ 
            '</div></section>'+ 
            '<section class="section"><div class="container"><div class="section-head"><div><span class="eyebrow">✦ Горячее сейчас</span><h2>Челленджи, о которых говорят</h2><p>Выбирай идею, снимай свой ответ и забирай внимание аудитории.</p></div><a href="#challenges" class="text-link">Смотреть все →</a></div><div class="horizontal-cards">'+
              '<article class="video-card"><div class="poster poster-violet"><div class="poster-top"><span class="poster-tag">Танцы</span><span class="round-action">♡</span></div><span class="poster-emoji">🕺</span><div class="poster-caption"><span>@deels.team</span><strong>Повтори летний движ</strong></div><span class="play-button">▶</span></div><div class="card-meta"><div><strong>50 000 ₽</strong><span>призовой фонд</span></div><div><strong>1,2K</strong><span>участников</span></div></div></article>'+
              '<article class="video-card"><div class="poster poster-coral"><div class="poster-top"><span class="poster-tag">Добро</span><span class="round-action">♡</span></div><span class="poster-emoji">🤍</span><div class="poster-caption"><span>@mila.sun</span><strong>Цепочка добрых дел</strong></div><span class="play-button">▶</span></div><div class="card-meta"><div><strong>25 000 ₽</strong><span>призовой фонд</span></div><div><strong>846</strong><span>участников</span></div></div></article>'+
              '<article class="video-card"><div class="poster poster-blue"><div class="poster-top"><span class="poster-tag">Творчество</span><span class="round-action">♡</span></div><span class="poster-emoji">🏙️</span><div class="poster-caption"><span>@urban.day</span><strong>Мой город за 15 секунд</strong></div><span class="play-button">▶</span></div><div class="card-meta"><div><strong>30 000 ₽</strong><span>призовой фонд</span></div><div><strong>734</strong><span>участника</span></div></div></article>'+
              '<article class="video-card"><div class="poster poster-pink"><div class="poster-top"><span class="poster-tag">Музыка</span><span class="round-action">♡</span></div><span class="poster-emoji">🎤</span><div class="poster-caption"><span>@voiceclub</span><strong>Твой голос — твоя сила</strong></div><span class="play-button">▶</span></div><div class="card-meta"><div><strong>15 000 ₽</strong><span>призовой фонд</span></div><div><strong>590</strong><span>участников</span></div></div></article>'+
            '</div></div></section>'+ 
            '<section class="section section-dark theme-dark-card"><div class="container"><div class="section-head"><div><span class="eyebrow">✦ Простая механика</span><h2>От идеи до победы — три шага</h2><p>Никаких сложных правил. Только ты, камера и желание попробовать.</p></div></div><div class="steps-grid"><article><span>01</span><div class="step-icon">✦</div><h3>Найди свой вызов</h3><p>Выбери челлендж, который тебя цепляет.</p></article><article><span>02</span><div class="step-icon">▶</div><h3>Сними ответ</h3><p>Покажи свой вариант в коротком вертикальном видео.</p></article><article><span>03</span><div class="step-icon">🏆</div><h3>Собери голоса</h3><p>Делись, получай поддержку и выходи в топ.</p></article></div></div></section>'+ 
            '<section class="section"><div class="container split-feature"><div><div class="section-head"><div><span class="eyebrow">✦ Истории Deels</span><h2>Не просто видео. Настоящие истории</h2><p>Люди рассказывают о шагах, которые изменили их жизнь. Иногда достаточно одного честного ролика, чтобы вдохновить тысячи.</p></div></div><a href="#feed" class="button button-dark">Смотреть истории →</a></div><div class="stories-stack"><a href="#feed" class="story-card poster-pink"><div class="story-icon">✨</div><div><span>2 мин • @Алина М.</span><h3>Танец, который вернул уверенность</h3><span class="story-more">Смотреть историю →</span></div></a><a href="#feed" class="story-card poster-blue"><div class="story-icon">🎭</div><div><span>4 мин • @Илья С.</span><h3>Как я впервые вышел на сцену</h3><span class="story-more">Смотреть историю →</span></div></a><a href="#feed" class="story-card poster-orange"><div class="story-icon">🛹</div><div><span>3 мин • @Саша К.</span><h3>Новый город и 100 новых друзей</h3><span class="story-more">Смотреть историю →</span></div></a></div></div></section>'+ 
            '<section class="section section-tint"><div class="container"><div class="section-head"><div><span class="eyebrow">✦ Делись добром</span><h2>Копилки, которые меняют жизнь</h2><p>Поддерживай проверенные сборы и следи за результатом вместе с сообществом.</p></div><a href="#campaign" class="text-link">Смотреть все →</a></div><div class="campaign-grid"><article class="campaign-card"><a href="#campaign" class="campaign-cover poster-violet"><span>💜</span><span class="poster-tag">Проверенная копилка</span></a><div class="campaign-body"><h3>Поможем Маше снова танцевать</h3><div class="progress-line"><span style="width:78%"></span></div><div class="progress-meta"><strong>1 564 300 ₽</strong><span>из 2 000 000 ₽</span></div><a href="#campaign" class="button button-soft">Поддержать</a></div></article><article class="campaign-card"><a href="#campaign" class="campaign-cover poster-coral"><span>🎨</span><span class="poster-tag">Проверенная копилка</span></a><div class="campaign-body"><h3>Творческий двор для подростков</h3><div class="progress-line"><span style="width:64%"></span></div><div class="progress-meta"><strong>641 200 ₽</strong><span>из 1 000 000 ₽</span></div><a href="#campaign" class="button button-soft">Поддержать</a></div></article><article class="campaign-card"><a href="#campaign" class="campaign-cover poster-blue"><span>🐾</span><span class="poster-tag">Проверенная копилка</span></a><div class="campaign-body"><h3>Тёплый дом для 40 хвостов</h3><div class="progress-line"><span style="width:91%"></span></div><div class="progress-meta"><strong>819 000 ₽</strong><span>из 900 000 ₽</span></div><a href="#campaign" class="button button-soft">Поддержать</a></div></article></div></div></section>'+ 
            '<section class="section"><div class="container cta-card theme-dark-card"><div><span class="eyebrow">✦ Твой ход</span><h2>Готов создать то,<br>что подхватят другие?</h2><p>Начни с первого челленджа. Это займёт меньше пяти минут.</p></div><a href="#create" class="button button-white">Создать в Deels →</a></div></section>'+ 
          '</main>'+ 
          '<footer class="site-footer"><div class="container footer-grid"><div><a href="#home" class="brand"><span class="brand-mark">D</span><span class="brand-word">DEELS</span></a><p>Новая развлекательная соцсеть, где идеи превращаются в движение.</p><div class="socials"><span>VK</span><span>TG</span><span>YT</span></div></div><div><h4>Смотреть</h4><a href="#feed">Лента</a><a href="#challenges">Челленджи</a><a href="#feed">Истории</a><a href="#campaign">Копилки</a></div><div><h4>Deels</h4><a href="#home">О проекте</a><a href="#home">Контакты</a><a href="#home">Документы</a></div><div><h4>Будь в движении</h4><p>Скачивай приложение и участвуй первым.</p><div class="store-buttons"><span>App Store</span></div></div></div><div class="container footer-bottom"><span>© 2026 Deels</span><span>Сделано для настоящих идей</span></div></footer>'+ 
        '</div>';
    }

    /* Keep active item in the one shared header in sync with hash routing. */
    function syncHeader(){
      var name=(location.hash||'#home').slice(1);
      previewTop.querySelectorAll('[data-screen]').forEach(function(a){a.classList.toggle('active',a.dataset.screen===name);});
    }
    syncHeader();
    window.addEventListener('hashchange',syncHeader);
  });
})();