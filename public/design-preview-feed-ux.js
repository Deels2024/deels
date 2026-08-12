(function(){
  'use strict';
  function ready(fn){if(document.readyState==='loading')document.addEventListener('DOMContentLoaded',fn,{once:true});else fn();}
  ready(function(){
    var feed=document.getElementById('screen-feed');
    if(!feed)return;
    var style=document.createElement('style');
    style.textContent=''+
      '.ux-feed-page{width:min(1180px,calc(100% - 32px));margin:0 auto;padding:34px 0 76px}.ux-feed-top{display:flex;justify-content:space-between;align-items:end;gap:28px;margin-bottom:18px}.ux-feed-top h1{margin:6px 0 7px;font-size:clamp(32px,4vw,54px);line-height:1}.ux-feed-top p{margin:0;color:var(--deels-muted,#756b80);max-width:700px}.ux-feed-counter{padding:8px 12px;border-radius:999px;background:#f3ebf9;color:#6b2bc1;font-weight:900}.ux-feed-stage{display:grid;grid-template-columns:54px minmax(0,560px) 54px;gap:14px;align-items:center;justify-content:center}.ux-feed-arrow{width:48px;height:48px;border:1px solid #e7dfec;border-radius:50%;background:#fff;color:#6b2bc1;font-size:22px;font-weight:900;cursor:pointer;box-shadow:0 8px 24px rgba(68,26,107,.08)}.ux-reel{position:relative;min-height:min(72vh,720px);border-radius:32px;overflow:hidden;display:flex;align-items:center;justify-content:center;color:#fff;box-shadow:0 26px 70px rgba(46,15,73,.22);touch-action:pan-x}.ux-reel:after{content:"";position:absolute;inset:0;background:linear-gradient(180deg,rgba(16,5,24,.08) 28%,rgba(16,5,24,.82) 100%);pointer-events:none}.ux-reel-badge{position:absolute;z-index:3;left:18px;top:18px;padding:7px 10px;border-radius:999px;background:rgba(24,8,35,.58);backdrop-filter:blur(10px);font-size:11px;font-weight:900}.ux-reel-emoji{position:relative;z-index:1;font-size:116px;filter:drop-shadow(0 20px 32px rgba(20,5,31,.25))}.ux-reel-actions{position:absolute;z-index:4;right:14px;bottom:148px;display:grid;gap:13px}.ux-reel-actions button{width:72px;border:0;background:rgba(24,8,35,.28);backdrop-filter:blur(8px);border-radius:18px;padding:9px 5px;color:#fff;display:grid;justify-items:center;gap:2px;font-size:24px;cursor:pointer}.ux-reel-actions button.active{background:rgba(255,79,200,.9)}.ux-reel-actions small{font-size:10px;font-weight:900}.ux-reel-actions span{font-size:8px;opacity:.85}.ux-reel-caption{position:absolute;z-index:4;left:20px;right:100px;bottom:24px}.ux-reel-caption>strong{font-size:14px}.ux-reel-caption h2{margin:5px 0 7px;font-size:clamp(25px,3vw,38px);line-height:1.03}.ux-reel-caption>p{margin:0 0 12px;font-size:13px;line-height:1.45;opacity:.9}.ux-vote{width:100%;border:1px solid rgba(255,255,255,.34);border-radius:16px;background:rgba(255,255,255,.13);backdrop-filter:blur(12px);color:#fff;padding:11px 13px;display:flex;gap:10px;align-items:center;text-align:left;cursor:pointer}.ux-vote>span{width:32px;height:32px;border-radius:50%;display:grid;place-items:center;background:#fff;color:#6b2bc1;font-weight:900}.ux-vote div{display:grid}.ux-vote small{font-size:9px;opacity:.8}.ux-vote strong{font-size:13px}.ux-vote em{font-size:10px;font-style:normal;opacity:.72}.ux-vote.active{background:rgba(76,198,135,.25);border-color:rgba(126,244,183,.55)}.ux-story-label{padding:10px 12px;border-radius:14px;background:rgba(255,255,255,.11);font-size:11px}.ux-swipe-hint{text-align:center;color:#756b80;font-size:12px;font-weight:800;margin-top:12px}.ux-swipe-row{overflow-x:auto!important;scroll-snap-type:x mandatory;overscroll-behavior-inline:contain;-webkit-overflow-scrolling:touch;scrollbar-width:none;cursor:grab}.ux-swipe-row::-webkit-scrollbar{display:none}.ux-swipe-row>*{scroll-snap-align:start}.ux-swipe-row.dragging{cursor:grabbing;user-select:none}'+
      '.ux-comments-layer{position:fixed;inset:0;z-index:400;display:none;background:rgba(18,8,25,.52);backdrop-filter:blur(3px)}.ux-comments-layer.open{display:block}.ux-comments-sheet{position:absolute;right:0;top:0;bottom:0;width:min(460px,100%);display:flex;flex-direction:column;background:#fff;color:#231a2d;box-shadow:-24px 0 70px rgba(22,8,33,.2)}.ux-comments-head{padding:18px 18px 14px;border-bottom:1px solid #eee6f2;display:flex;align-items:center;gap:12px}.ux-comments-head>div{min-width:0;flex:1}.ux-comments-head small{display:block;color:#82768c}.ux-comments-head strong{display:block;white-space:nowrap;overflow:hidden;text-overflow:ellipsis}.ux-comments-close{width:40px;height:40px;border:0;border-radius:50%;background:#f4eef8;color:#6b2bc1;font-size:23px;cursor:pointer}.ux-comments-list{flex:1;overflow:auto;padding:8px 18px 18px}.ux-comment{display:grid;grid-template-columns:38px 1fr;gap:10px;padding:14px 0;border-bottom:1px solid #f2edf5}.ux-comment-avatar{width:38px;height:38px;border-radius:50%;display:grid;place-items:center;color:#fff;background:linear-gradient(135deg,#6b2bc1,#ff4fc8);font-size:10px;font-weight:900}.ux-comment-body strong{font-size:13px}.ux-comment-body p{margin:4px 0 5px;font-size:14px;line-height:1.4}.ux-comment-body small{color:#92869c;font-size:10px}.ux-comments-empty{padding:32px 0;text-align:center;color:#81758b}.ux-comment-form{padding:12px 14px calc(12px + env(safe-area-inset-bottom));border-top:1px solid #eee6f2;display:flex;gap:8px;background:#fff}.ux-comment-form input{flex:1;min-width:0;height:46px;border:1px solid #ddd2e8;border-radius:14px;padding:0 13px;outline:none}.ux-comment-form input:focus{border-color:#8c55c5;box-shadow:0 0 0 3px rgba(107,43,193,.1)}.ux-comment-form button{width:46px;height:46px;border:0;border-radius:14px;color:#fff;background:#6b2bc1;font-size:18px;cursor:pointer}body.ux-comments-open{overflow:hidden}'+
      '@media(max-width:700px){.ux-feed-page{width:100%;padding:12px 0 84px}.ux-feed-top{padding:0 16px;align-items:start}.ux-feed-top h1{font-size:28px}.ux-feed-top p{font-size:12px}.ux-feed-stage{display:block}.ux-feed-arrow{display:none}.ux-reel{min-height:calc(100svh - 190px);border-radius:0}.ux-reel-caption{left:16px;right:88px;bottom:20px}.ux-reel-actions{right:9px;bottom:148px}.ux-reel-actions button{width:64px}.ux-swipe-hint{margin:9px 0}.ux-swipe-row{display:flex!important;grid-template-columns:none!important;gap:14px!important;padding-bottom:6px}.ux-swipe-row>*{min-width:min(84vw,340px);flex:0 0 min(84vw,340px)}.ux-comments-layer{align-items:flex-end}.ux-comments-layer.open{display:flex}.ux-comments-sheet{position:relative;top:auto;right:auto;bottom:auto;width:100%;height:min(72svh,680px);margin-top:auto;border-radius:24px 24px 0 0;box-shadow:0 -24px 70px rgba(22,8,33,.24)}.ux-comments-head{padding-top:13px}.ux-comments-head:before{content:"";position:absolute;top:7px;left:50%;width:42px;height:4px;border-radius:5px;background:#ded3e6;transform:translateX(-50%)}}';
    document.head.appendChild(style);

    var items=[
      {tone:'violet',kind:'Ответ на челлендж',author:'mila.sun',title:'Мой ответ на «Повтори летний движ»',challenge:'Повтори летний движ',likes:'8,2K',comments:'462',votes:'12,4K',emoji:'🕺'},
      {tone:'blue',kind:'Ответ на челлендж',author:'urban.day',title:'Мой город за 15 секунд',challenge:'Мой город за 15 секунд',likes:'6,7K',comments:'318',votes:'9,8K',emoji:'🏙️'},
      {tone:'orange',kind:'История',author:'alina.move',title:'Как я решилась начать',challenge:null,likes:'4,3K',comments:'205',votes:null,emoji:'✨'},
      {tone:'pink',kind:'Ответ на челлендж',author:'voiceclub',title:'Твой голос — твоя сила',challenge:'Твой голос — твоя сила',likes:'7,9K',comments:'391',votes:'8,2K',emoji:'🎤'}
    ];
    var commentsByIndex={
      0:[{a:'АК',u:'anna.k',t:'Очень крутой ответ 🔥',time:'2 мин'},{a:'МС',u:'misha.s',t:'Движение получилось идеально!',time:'8 мин'},{a:'ОЛ',u:'olya_live',t:'Голосую за тебя 💜',time:'14 мин'}],
      1:[{a:'ИВ',u:'igor.view',t:'Очень красивый город и монтаж.',time:'4 мин'},{a:'ЛН',u:'lena.n',t:'Хочу увидеть продолжение!',time:'19 мин'}],
      2:[{a:'КМ',u:'katya.m',t:'Спасибо за эту историю ❤️',time:'5 мин'}],
      3:[{a:'АС',u:'artem.sound',t:'Голос отличный!',time:'7 мин'},{a:'МК',u:'maria.k',t:'Удачи в челлендже 🙌',time:'21 мин'}]
    };
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
      '</main>'+
      '<div class="ux-comments-layer" id="uxCommentsLayer" role="dialog" aria-modal="true" aria-label="Комментарии">'+
        '<section class="ux-comments-sheet">'+
          '<header class="ux-comments-head"><div><small id="uxCommentsAuthor"></small><strong id="uxCommentsTitle"></strong></div><button class="ux-comments-close" id="uxCommentsClose" type="button" aria-label="Закрыть комментарии">×</button></header>'+
          '<div class="ux-comments-list" id="uxCommentsList"></div>'+
          '<form class="ux-comment-form" id="uxCommentForm"><input id="uxCommentInput" maxlength="500" placeholder="Написать комментарий" aria-label="Написать комментарий"><button type="submit" aria-label="Отправить комментарий">→</button></form>'+
        '</section>'+
      '</div>';

    var reel=document.getElementById('uxReel');
    var counter=document.getElementById('uxFeedCounter');
    var commentsLayer=document.getElementById('uxCommentsLayer');
    var commentsList=document.getElementById('uxCommentsList');
    var commentsTitle=document.getElementById('uxCommentsTitle');
    var commentsAuthor=document.getElementById('uxCommentsAuthor');
    var commentInput=document.getElementById('uxCommentInput');
    var commentForm=document.getElementById('uxCommentForm');
    var commentsClose=document.getElementById('uxCommentsClose');

    function commentsOpen(){return commentsLayer.classList.contains('open');}
    function renderComments(){
      var item=items[index];
      var rows=commentsByIndex[index]||[];
      commentsAuthor.textContent='Комментарии к видео @'+item.author;
      commentsTitle.textContent=item.title;
      commentsList.innerHTML=rows.length?rows.map(function(c){return '<article class="ux-comment"><div class="ux-comment-avatar">'+c.a+'</div><div class="ux-comment-body"><strong>@'+c.u+'</strong><p>'+c.t+'</p><small>'+c.time+'</small></div></article>';}).join(''):'<div class="ux-comments-empty">Комментариев пока нет. Будьте первым.</div>';
    }
    function openComments(){renderComments();commentsLayer.classList.add('open');document.body.classList.add('ux-comments-open');setTimeout(function(){commentInput.focus();},100);}
    function closeComments(){commentsLayer.classList.remove('open');document.body.classList.remove('ux-comments-open');commentInput.value='';reel.focus();}
    commentsClose.addEventListener('click',closeComments);
    commentsLayer.addEventListener('click',function(e){if(e.target===commentsLayer)closeComments();});
    document.addEventListener('keydown',function(e){if(e.key==='Escape'&&commentsOpen())closeComments();});
    commentForm.addEventListener('submit',function(e){e.preventDefault();var text=commentInput.value.trim();if(!text)return;(commentsByIndex[index]||(commentsByIndex[index]=[])).push({a:'ВЫ',u:'you',t:text,time:'сейчас'});commentInput.value='';renderComments();commentsList.scrollTop=commentsList.scrollHeight;});

    function render(){
      var item=items[index];
      var voteBlock=item.challenge ? '<button type="button" class="ux-vote '+(voted[index]?'active':'')+'" id="uxVote"><span>✓</span><div><small>Голос в челлендже</small><strong>'+(voted[index]?'Голос за @'+item.author+' учтён':'Голосовать за @'+item.author)+'</strong><em>'+item.challenge+'</em></div></button>' : '<div class="ux-story-label">Это история — здесь можно поставить лайк, но голосования нет</div>';
      reel.className='ux-reel poster-'+item.tone;
      reel.innerHTML=''+
        '<div class="ux-reel-badge">'+item.kind+'</div>'+
        '<div class="ux-reel-emoji">'+item.emoji+'</div>'+
        '<div class="ux-reel-actions">'+
          '<button type="button" id="uxLike" class="'+(liked[index]?'active':'')+'" aria-label="Поставить лайк видео @'+item.author+'">♡<small>'+(liked[index]?'✓ '+item.likes:item.likes)+'</small><span>Лайк видео</span></button>'+
          '<button type="button" id="uxComments" aria-label="Открыть комментарии к видео @'+item.author+'">◯<small>'+item.comments+'</small><span>Комментарии</span></button>'+
          '<button type="button" data-toast="Ссылка на это видео скопирована">↗<small>92</small><span>Поделиться</span></button>'+
        '</div>'+
        '<div class="ux-reel-caption"><strong>@'+item.author+'</strong><h2>'+item.title+'</h2><p>'+(item.challenge?'Видео участвует в челлендже «'+item.challenge+'». Голос относится именно к этому автору и этому ответу.':'Личная история автора. Лайк относится именно к этому видео.')+'</p>'+voteBlock+'</div>';
      counter.textContent=(index+1)+' / '+items.length;
      var like=document.getElementById('uxLike');
      if(like)like.addEventListener('click',function(){liked[index]=!liked[index];render();});
      var comments=document.getElementById('uxComments');
      if(comments)comments.addEventListener('click',openComments);
      var vote=document.getElementById('uxVote');
      if(vote)vote.addEventListener('click',function(){voted[index]=true;render();});
    }
    function move(step){if(commentsOpen())return;index=(index+step+items.length)%items.length;render();}
    feed.querySelector('.ux-feed-arrow.prev').addEventListener('click',function(){move(-1);});
    feed.querySelector('.ux-feed-arrow.next').addEventListener('click',function(){move(1);});
    reel.addEventListener('touchstart',function(e){if(commentsOpen())return;touchStart=e.changedTouches[0].clientY;},{passive:true});
    reel.addEventListener('touchend',function(e){if(commentsOpen()||touchStart===null)return;var delta=e.changedTouches[0].clientY-touchStart;if(Math.abs(delta)>45)move(delta<0?1:-1);touchStart=null;},{passive:true});
    reel.addEventListener('wheel',function(e){if(commentsOpen()||Math.abs(e.deltaY)<8||wheelLock)return;e.preventDefault();wheelLock=true;move(e.deltaY>0?1:-1);setTimeout(function(){wheelLock=false;},380);},{passive:false});
    reel.addEventListener('keydown',function(e){if(commentsOpen())return;if(e.key==='ArrowDown'||e.key==='PageDown'){e.preventDefault();move(1);}if(e.key==='ArrowUp'||e.key==='PageUp'){e.preventDefault();move(-1);}});
    render();

    function swipeRows(){
      var rows=[
        document.querySelector('.horizontal-cards'),
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