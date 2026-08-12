(function(){
  'use strict';
  function ready(fn){if(document.readyState==='loading')document.addEventListener('DOMContentLoaded',fn,{once:true});else fn();}
  ready(function(){
    var home=document.getElementById('screen-home');
    if(!home||home.querySelector('.preview-home-enhancements'))return;
    home.classList.add('deels-source-home','deels-v2-enabled','light_theme','light_there');

    var hero=home.querySelector('.hero-preview');
    var firstWrap=home.querySelector('.screen-wrap');
    if(!hero||!firstWrap)return;

    var ecosystem=document.createElement('section');
    ecosystem.className='source-ecosystem preview-home-enhancements';
    ecosystem.innerHTML='<div class="container"><div class="source-section-head" style="margin-bottom:28px"><div><span class="eyebrow">✦ Больше, чем лента</span><h2>Всё, что можно делать в Deels</h2><p>Один профиль объединяет творчество, соревнования, поддержку авторов и общение.</p></div></div><div class="source-ecosystem-grid">'+
      '<a class="source-ecosystem-card" href="#challenges"><span class="source-ecosystem-icon">✦</span><strong>Челленджи</strong><span>Запускай идеи, отвечай видео и собирай голоса.</span></a>'+
      '<a class="source-ecosystem-card" href="#battles"><span class="source-ecosystem-icon">⚡</span><strong>Баттлы</strong><span>Вызывай соперников и соревнуйся один на один.</span></a>'+
      '<a class="source-ecosystem-card" href="#feed"><span class="source-ecosystem-icon">▶</span><strong>Истории</strong><span>Публикуй вертикальные видео и находи аудиторию.</span></a>'+
      '<a class="source-ecosystem-card" href="#campaign"><span class="source-ecosystem-icon">💜</span><strong>Копилки</strong><span>Собирай поддержку на мечты, проекты и добрые дела.</span></a>'+
      '<a class="source-ecosystem-card" href="#wallet"><span class="source-ecosystem-icon">₽</span><strong>Кошелёк</strong><span>Баланс, пополнения, донаты и история операций.</span></a>'+
      '<a class="source-ecosystem-card" href="#messages"><span class="source-ecosystem-icon">✉</span><strong>Общение</strong><span>Подписки, авторы и прямые сообщения.</span></a>'+
    '</div></div>';
    hero.insertAdjacentElement('afterend',ecosystem);

    var stats=document.createElement('section');
    stats.className='source-live-stats preview-home-enhancements';
    stats.innerHTML='<div class="container"><div class="source-live-stats-card"><div class="source-live-stats-head"><div><span class="eyebrow">✦ Deels прямо сейчас</span><h2>Это уже действующая экосистема</h2></div><p>В рабочем Laravel эти карточки используют реальные счётчики платформы и обновляются автоматически.</p></div><div class="source-live-stats-grid">'+
      '<div class="source-live-stat"><strong>60K+</strong><span>пользователей</span></div>'+
      '<div class="source-live-stat"><strong>100K+</strong><span>просмотров контента</span></div>'+
      '<div class="source-live-stat"><strong>40K+</strong><span>созданных копилок</span></div>'+
      '<div class="source-live-stat"><strong>₽</strong><span>реальные суммы из Laravel</span></div>'+
    '</div></div></div>';
    firstWrap.insertAdjacentElement('afterend',stats);

    var economy=document.createElement('section');
    economy.className='source-economy preview-home-enhancements';
    economy.innerHTML='<div class="container source-economy-grid"><div class="source-economy-copy"><span class="eyebrow">✦ Творчество может приносить больше</span><h2>Создавай. Получай поддержку. Расти.</h2><p>Контент и инструменты монетизации собраны в одном профиле: донаты, платный доступ, кошелёк и копилки.</p><div class="source-economy-actions"><a href="#feed" class="button button-primary">Смотреть контент →</a><a href="#wallet" class="button button-glass">Открыть кошелёк</a></div></div><div class="source-economy-list">'+
      '<article class="source-economy-item"><b>♡</b><strong>Донаты авторам</strong><p>Поддержка контента остаётся внутри Deels.</p></article>'+
      '<article class="source-economy-item"><b>▶</b><strong>Контент с доступом</strong><p>Платные сторис и поддержка уже предусмотрены backend.</p></article>'+
      '<article class="source-economy-item"><b>₽</b><strong>Единый кошелёк</strong><p>Пополнения, донаты, движения и вывод средств.</p></article>'+
      '<article class="source-economy-item"><b>💜</b><strong>Копилки</strong><p>Сбор поддержки на мечты, проекты и инициативы.</p></article>'+
    '</div></div>';
    home.appendChild(economy);

    var footer=document.createElement('footer');
    footer.className='footer preview-home-enhancements';
    footer.innerHTML='<div class="container footer__menu"><a href="#home" class="footer__logo"><span style="font-size:28px;font-weight:900;color:#6b2bc1">DEELS</span></a><div class="footer__list"><ul><li><a href="#home">Главная</a></li><li><a href="#challenges">Челленджи</a></li><li><a href="#feed">Истории</a></li><li><a href="#campaign">Копилки</a></li><li><a href="#profile">Профиль</a></li><li><a href="#wallet">Кошелёк</a></li></ul></div><div class="footer__icons"><a href="#" aria-label="Telegram">TG</a><a href="#" aria-label="VK">VK</a></div></div><div class="container deels-footer-directory"><div class="deels-footer-col"><strong>Смотреть</strong><a href="#feed">Лента и истории</a><a href="#challenges">Челленджи</a><a href="#campaign">Копилки</a></div><div class="deels-footer-col"><strong>Создавать</strong><a href="#create">Создать челлендж</a><a href="#create">Создать сторис</a><a href="#campaign">Создать копилку</a></div><div class="deels-footer-col"><strong>Поддержка</strong><a href="#">Контакты</a><a href="#">Правила и оферты</a><a href="#">Конфиденциальность</a></div><div class="deels-footer-col"><strong>Deels</strong><a href="#home">О платформе</a><a href="#">Telegram</a><a href="#">VK</a></div></div><div class="container footer__items"><div class="footer__item"><span>⌖</span><a href="#">Санкт-Петербург, пр. Ветеранов 166, лит. А</a></div><div class="footer__item"><span>☎</span><a href="#">+7 (812) 507-98-08</a></div><div class="footer__item"><span>✉</span><a href="#">info@deels.ru</a></div></div><div class="container footer__href"><a href="#">Политика конфиденциальности</a><p>Реклама на Deels размещается по закону (erid)</p></div><div class="container footer__items gap-3"><a href="#" class="button button-soft">Google Play</a><a href="#" class="button button-soft">App Store</a></div>';
    home.appendChild(footer);
  });
})();