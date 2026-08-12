(function(){
  'use strict';
  function ready(fn){if(document.readyState==='loading')document.addEventListener('DOMContentLoaded',fn,{once:true});else fn();}
  ready(function(){
    var feed=document.getElementById('screen-feed');
    if(!feed)return;

    var items=[
      {tone:'violet',kind:'Ответ на челлендж',author:'mila.sun',title:'Мой ответ на «Повтори летний движ»',challenge:'Повтори летний движ',likes:'8,2K',comments:'462',votes:'12,4K',emoji:'🕺'},
      {tone:'blue',kind:'Ответ на челлендж',author:'urban.day',title:'Мой город за 15 секунд',challenge:'Мой город за 15 секунд',likes:'6,7K',comments:'318',votes:'9,8K',emoji:'🏙️'},
      {tone:'orange',kind:'История',author:'alina.move',title:'Как я решилась начать',challenge:null,likes:'4,3K',comments:'205',votes:null,emoji:'✨'},
      {tone:'pink',kind:'Ответ на челлендж',author:'voiceclub',title:'Твой голос — твоя сила',challenge:'Твой голос — твоя сила',likes:'7,9K',comments:'391',votes:'8,2K',emoji:'🎤'}
    ];
    var index=0, touchStart=null, wheelLock=false;
    var liked={};
    var voted={};

    feed.innerHTML=''+
      '<main class="ux-feed-page">'+
        '<div class="ux-feed-top"><div><span class="eyebrow">Лента Deels</span><h1>Смотри. Реагируй. Голосуй.</h1><p>Свайп вверх/вниз на телефоне или крути колесо мыши, чтобы перейти к следующему видео.</p></div><div class="ux-feed-counter" id="uxFeedCounter"></div></div>'+
        '<div class="ux-feed-stage">'+
          '<button class="ux-feed-arrow prev" type="button" aria-label="Предыдущее видео">↑</button>'+
          '<article class="ux-reel" id="uxReel" tabindex="0" aria-live="polite"></article>'+
          '<button class="ux-feed-arrow next" type="button" aria-label="Следующее видео">↓</button>'+
        '</div>'+
        '<div class="ux-swipe-hint">↑↓ Свайп / скролл — другие видео</div>'+
      '</main>';

    var reel=document.getElementById('uxReel');
    var counter=document.getElementById('uxFeedCounter');
    function render(){
      var item=items[index];
      var voteBlock=item.challenge ? '<button type="button" class="ux-vote '+(voted[index]?'active':'')+'" id="uxVote"><span>✓</span><div><small>Голос в челлендже</small><strong>'+(voted[index]?'Голос за @'+item.author+' учтён':'Голосовать за @'+item.author)+'</strong><em>'+item.challenge+'</em></div></button>' : '<div class="ux-story-label">Это история — здесь можно поставить лайк, но голосования нет</div>';
      reel.className='ux-reel poster-'+item.tone;
      reel.innerHTML=''+
        '<div class="ux-reel-badge">'+item.kind+'</div>'+
        '<div class="ux-reel-emoji">'+item.emoji+'</div>'+
        '<div class="ux-reel-actions">'+
          '<button type="button" id="uxLike" class="'+(liked[index]?'active':'')+'" aria-label="Поставить лайк видео @'+item.author+'">♡<small>'+(liked[index]?'✓ '+item.likes:item.likes)+'</small><span>Лайк видео</span></button>'+
          '<button type="button" data-toast="Комментарии к этому видео">◯<small>'+item.comments+'</small><span>Комментарии</span></button>'+
          '<button type="button" data-toast="Ссылка на это видео скопирована">↗<small>92</small><span>Поделиться</span></button>'+
        '</div>'+
        '<div class="ux-reel-caption"><strong>@'+item.author+'</strong><h2>'+item.title+'</h2><p>'+(item.challenge?'Видео участвует в челлендже «'+item.challenge+'». Голос относится именно к этому автору и этому ответу.':'Личная история автора. Лайк относится именно к этому видео.')+'</p>'+voteBlock+'</div>';
      counter.textContent=(index+1)+' / '+items.length;
      var like=document.getElementById('uxLike');
      if(like)like.addEventListener('click',function(){liked[index]=!liked[index];render();});
      var vote=document.getElementById('uxVote');
      if(vote)vote.addEventListener('click',function(){voted[index]=true;render();});
    }
    function move(step){index=(index+step+items.length)%items.length;render();}
    feed.querySelector('.ux-feed-arrow.prev').addEventListener('click',function(){move(-1);});
    feed.querySelector('.ux-feed-arrow.next').addEventListener('click',function(){move(1);});
    reel.addEventListener('touchstart',function(e){touchStart=e.changedTouches[0].clientY;},{passive:true});
    reel.addEventListener('touchend',function(e){if(touchStart===null)return;var delta=e.changedTouches[0].clientY-touchStart;if(Math.abs(delta)>45)move(delta<0?1:-1);touchStart=null;},{passive:true});
    reel.addEventListener('wheel',function(e){if(Math.abs(e.deltaY)<8||wheelLock)return;e.preventDefault();wheelLock=true;move(e.deltaY>0?1:-1);setTimeout(function(){wheelLock=false;},380);},{passive:false});
    reel.addEventListener('keydown',function(e){if(e.key==='ArrowDown'||e.key==='PageDown'){e.preventDefault();move(1);}if(e.key==='ArrowUp'||e.key==='PageUp'){e.preventDefault();move(-1);}});
    render();

    function swipeRows(){
      var rows=[
        document.querySelector('.horizontal-cards'),
        document.querySelector('.stories-stack'),
        document.querySelector('#screen-challenges .grid'),
        document.querySelector('#screen-battles .grid')
      ].filter(Boolean);
      rows.forEach(function(row){
        row.classList.add('ux-swipe-row');
        if(row.dataset.uxSwipe==='1')return;
        row.dataset.uxSwipe='1';
        var down=false,startX=0,startLeft=0;
        row.addEventListener('pointerdown',function(e){if(e.pointerType==='mouse'){down=true;startX=e.clientX;startLeft=row.scrollLeft;row.setPointerCapture(e.pointerId);row.classList.add('dragging');}});
        row.addEventListener('pointermove',function(e){if(!down)return;row.scrollLeft=startLeft-(e.clientX-startX);});
        function stop(){down=false;row.classList.remove('dragging');}
        row.addEventListener('pointerup',stop);row.addEventListener('pointercancel',stop);
        row.addEventListener('wheel',function(e){if(Math.abs(e.deltaY)>Math.abs(e.deltaX)){var max=row.scrollWidth-row.clientWidth;if(max>4){e.preventDefault();row.scrollLeft+=e.deltaY;}}},{passive:false});
      });
    }
    swipeRows();
    window.addEventListener('hashchange',function(){setTimeout(swipeRows,30);});
  });
})();