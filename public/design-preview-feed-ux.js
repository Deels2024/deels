(function(){
  'use strict';
  function ready(fn){if(document.readyState==='loading')document.addEventListener('DOMContentLoaded',fn,{once:true});else fn();}
  ready(function(){
    var feed=document.getElementById('screen-feed');
    if(!feed)return;
    var style=document.createElement('style');
    style.textContent=''+
      '.ux-feed-page{width:min(1180px,calc(100% - 32px));margin:0 auto;padding:34px 0 76px}.ux-feed-top{display:flex;justify-content:space-between;align-items:end;gap:28px;margin-bottom:18px}.ux-feed-top h1{margin:6px 0 7px;font-size:clamp(32px,4vw,54px);line-height:1}.ux-feed-top p{margin:0;color:var(--deels-muted,#756b80);max-width:700px}.ux-feed-counter{padding:8px 12px;border-radius:999px;background:#f3ebf9;color:#6b2bc1;font-weight:900}.ux-feed-stage{display:grid;grid-template-columns:54px minmax(0,560px) 54px;gap:14px;align-items:center;justify-content:center}.ux-feed-arrow{width:48px;height:48px;border:1px solid #e7dfec;border-radius:50%;background:#fff;color:#6b2bc1;font-size:22px;font-weight:900;cursor:pointer;box-shadow:0 8px 24px rgba(68,26,107,.08)}.ux-reel{position:relative;min-height:min(72vh,720px);border-radius:32px;overflow:hidden;display:flex;align-items:center;justify-content:center;color:#fff;box-shadow:0 26px 70px rgba(46,15,73,.22);touch-action:pan-x}.ux-reel:after{content:"";position:absolute;inset:0;background:linear-gradient(180deg,rgba(16,5,24,.08) 28%,rgba(16,5,24,.82) 100%);pointer-events:none}.ux-reel-badge{position:absolute;z-index:3;left:18px;top:18px;padding:7px 10px;border-radius:999px;background:rgba(24,8,35,.58);backdrop-filter:blur(10px);font-size:11px;font-weight:900}.ux-reel-emoji{position:relative;z-index:1;font-size:116px;filter:drop-shadow(0 20px 32px rgba(20,5,31,.25))}.ux-reel-actions{position:absolute;z-index:4;right:14px;bottom:148px;display:grid;gap:13px}.ux-reel-actions button{width:72px;border:0;background:rgba(24,8,35,.28);backdrop-filter:blur(8px);border-radius:18px;padding:9px 5px;color:#fff;display:grid;justify-items:center;gap:2px;font-size:24px;cursor:pointer}.ux-reel-actions button.active{background:rgba(255,79,200,.9)}.ux-reel-actions small{font-size:10px;font-weight:900}.ux-reel-actions span{font-size:8px;opacity:.85}.ux-reel-caption{position:absolute;z-index:4;left:20px;right:100px;bottom:24px}.ux-reel-caption>strong{font-size:14px}.ux-reel-caption h2{margin:5px 0 7px;font-size:clamp(25px,3vw,38px);line-height:1.03}.ux-reel-caption>p{margin:0 0 12px;font-size:13px;line-height:1.45;opacity:.9}.ux-vote{width:100%;border:1px solid rgba(255,255,255,.34);border-radius:16px;background:rgba(255,255,255,.13);backdrop-filter:blur(12px);color:#fff;padding:11px 13px;display:flex;gap:10px;align-items:center;text-align:left;cursor:pointer}.ux-vote>span{width:32px;height:32px;border-radius:50%;display:grid;place-items:center;background:#fff;color:#6b2bc1;font-weight:900}.ux-vote div{display:grid}.ux-vote small{font-size:9px;opacity:.8}.ux-vote strong{font-size:13px}.ux-vote em{font-size:10px;font-style:normal;opacity:.72}.ux-vote.active{background:rgba(76,198,135,.25);border-color:rgba(126,244,183,.55)}.ux-story-label{padding:10px 12px;border-radius:14px;background:rgba(255,255,255,.11);font-size:11px}.ux-swipe-hint{text-align:center;color:#756b80;font-size:12px;font-weight:800;margin-top:12px}.ux-swipe-row{overflow-x:auto!important;scroll-snap-type:x mandatory;overscroll-behavior-inline:contain;-webkit-overflow-scrolling:touch;scrollbar-width:none;cursor:grab}.ux-swipe-row::-webkit-scrollbar{display:none}.ux-swipe-row>*{scroll-snap-align:start}.ux-swipe-row.dragging{cursor:grabbing;user-select:none}.stories-stack.ux-swipe-row{display:flex!important;gap:14px!important}.stories-stack.ux-swipe-row>*{min-width:min(86vw,430px)}'+
      '@media(max-width:700px){.ux-feed-page{width:100%;padding:12px 0 84px}.ux-feed-top{padding:0 16px;align-items:start}.ux-feed-top h1{font-size:28px}.ux-feed-top p{font-size:12px}.ux-feed-stage{display:block}.ux-feed-arrow{display:none}.ux-reel{min-height:calc(100svh - 190px);border-radius:0}.ux-reel-caption{left:16px;right:88px;bottom:20px}.ux-reel-actions{right:9px;bottom:148px}.ux-reel-actions button{width:64px}.ux-swipe-hint{margin:9px 0}.ux-swipe-row{display:flex!important;grid-template-columns:none!important;gap:14px!important;padding-bottom:6px}.ux-swipe-row>*{min-width:min(84vw,340px);flex:0 0 min(84vw,340px)}}';
    document.head.appendChild(style);

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