(function(){
  'use strict';
  function ready(fn){if(document.readyState==='loading')document.addEventListener('DOMContentLoaded',fn,{once:true});else fn();}
  ready(function(){
    var home=document.getElementById('screen-home');
    if(!home||home.querySelector('.preview-home-enhancements'))return;
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
  });
})();