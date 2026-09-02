(function(){
'use strict';
var src=document.currentScript&&document.currentScript.src?document.currentScript.src:'';
function ready(fn){if(document.readyState==='loading')document.addEventListener('DOMContentLoaded',fn,{once:true});else fn();}
function asset(name){return src?new URL('./home-media/'+name,src).href:'./home-media/'+name;}
function addImage(box,file,cls,alt){if(!box)return null;var img=box.querySelector(':scope > img.'+cls);if(!img){img=document.createElement('img');img.className=cls;img.alt=alt||'';img.loading='lazy';img.decoding='async';box.insertBefore(img,box.firstChild);}img.src=asset(file);return img;}
function setDirectImage(box,file,alt){if(!box)return;var img=box.querySelector(':scope > img');if(!img){img=document.createElement('img');img.alt=alt||'';img.loading='lazy';img.decoding='async';box.insertBefore(img,box.firstChild);}img.src=asset(file);}
ready(function(){
var live=['video-dance.webp','video-creator.webp','video-boy.webp','hero-rooftop.webp'];
var style=document.createElement('style');style.textContent=''+
'.deels-media-fill{position:relative!important;overflow:hidden!important;isolation:isolate}.deels-media-img{position:absolute!important;inset:0!important;width:100%!important;height:100%!important;object-fit:cover!important;object-position:center!important;z-index:0!important}.deels-media-fill:after{content:"";position:absolute;z-index:1;inset:0;background:linear-gradient(180deg,rgba(18,7,27,.02) 32%,rgba(18,7,27,.72) 100%);pointer-events:none}.deels-media-fill>:not(.deels-media-img){position:relative;z-index:2}.deels-media-fill .chem,.deels-media-fill .emoji,.deels-media-fill>.campaign-mini-emoji,.deels-media-fill>.poster-emoji,.deels-media-fill>.ux-reel-emoji{display:none!important}'+
'#screen-home .home-story-cover{aspect-ratio:9/16!important;min-height:0!important;padding:0!important;border-radius:24px!important;background:#24132f!important}#screen-home .home-story-cover .home-story-photo{position:absolute!important;inset:0!important;width:100%!important;height:100%!important;object-fit:cover!important}#screen-home .home-story-cover:after{content:"";position:absolute;inset:0;z-index:1;background:linear-gradient(180deg,transparent 38%,rgba(18,7,27,.88) 100%)}#screen-home .home-story-cover .home-story-copy{position:absolute!important;z-index:2!important;left:15px!important;right:15px!important;bottom:15px!important;color:#fff!important}#screen-home .home-story-cover .home-story-copy h3{margin:5px 0!important;color:#fff!important;font-size:21px!important;line-height:1.12!important}'+
'#screen-challenges .chv.deels-media-fill,#screen-challenges .chvideo.deels-media-fill{aspect-ratio:9/16!important;background:#2d173d!important}#screen-challenges .chv.deels-media-fill:after,#screen-challenges .chvideo.deels-media-fill:after{background:linear-gradient(180deg,rgba(18,7,27,.02) 34%,rgba(18,7,27,.88) 100%)}'+
'#screen-battles .battle-side.deels-media-fill{aspect-ratio:9/16!important;background:#1c1125!important}#screen-battles .battle-card-visual .deels-battle-half{position:relative!important;overflow:hidden!important}#screen-battles .battle-card-visual .deels-battle-half img{position:absolute;inset:0;width:100%;height:100%;object-fit:cover}#screen-battles .battle-card-visual .deels-battle-half:after{content:"";position:absolute;inset:0;background:linear-gradient(180deg,transparent 50%,rgba(15,6,22,.44))}'+
'#screen-feed .ux-reel.deels-media-fill:after{z-index:2!important;background:linear-gradient(180deg,rgba(16,5,24,.03) 32%,rgba(16,5,24,.82) 100%)!important}#screen-feed .ux-reel.deels-media-fill>.deels-media-img{z-index:0!important}#screen-feed .ux-reel.deels-media-fill>*:not(.deels-media-img){z-index:4!important}#screen-feed .ux-reel.deels-media-fill>.ux-reel-progress,#screen-feed .ux-reel.deels-media-fill>.ux-reel-badge,#screen-feed .ux-reel.deels-media-fill>.ux-reel-actions,#screen-feed .ux-reel.deels-media-fill>.ux-reel-caption{position:absolute!important}'+
'#screen-campaign .campaign-mini-cover.deels-media-fill{min-height:190px!important;background:#2d173d!important}#screen-campaign .campaign-mini-cover.deels-media-fill:after{background:linear-gradient(180deg,rgba(18,7,27,.02),rgba(18,7,27,.58))}#screen-campaign .campaign-mini-cover.deels-media-fill .campaign-mini-tag,#screen-campaign .campaign-mini-cover.deels-media-fill .campaign-mini-verified{z-index:3!important}#screen-campaign .balance-grid .poster.deels-media-fill{min-height:310px!important}'+
'#screen-profile .poster.deels-media-fill{aspect-ratio:9/16!important;background:#2d173d!important}#screen-profile .avatar-lg.deels-profile-avatar{background-size:cover!important;background-position:center!important;color:transparent!important;border:3px solid rgba(255,255,255,.9)!important}'+
'@media(max-width:700px){#screen-home .home-story-cover{border-radius:20px!important}#screen-home .home-story-cover .home-story-copy h3{font-size:19px!important}#screen-campaign .balance-grid .poster.deels-media-fill{min-height:230px!important}}';document.head.appendChild(style);

/* Главная: все пользовательские карточки используют только проверенные живые фото. */
var home=document.getElementById('screen-home');
if(home){
  home.querySelectorAll('.home-story-cover').forEach(function(c,i){var img=c.querySelector('.home-story-photo');if(img)img.src=asset(live[(i+1)%live.length]);});
  home.querySelectorAll('.home-prod-card-poster').forEach(function(c,i){setDirectImage(c,live[i%live.length],'Обложка челленджа Deels');});
  var homeStoryFiles=['hero-rooftop.webp','video-creator.webp','video-boy.webp'];
  home.querySelectorAll('.home-prod-story').forEach(function(c,i){setDirectImage(c,homeStoryFiles[i%homeStoryFiles.length],'История участника Deels');});
  var homeCampaignFiles=['video-creator.webp','hero-rooftop.webp','video-dance.webp'];
  home.querySelectorAll('.home-prod-campaign-cover').forEach(function(c,i){setDirectImage(c,homeCampaignFiles[i%homeCampaignFiles.length],'Обложка копилки Deels');});
  home.querySelectorAll('.home-prod-avatar').forEach(function(a,i){a.style.backgroundImage='url("'+asset(live[i%live.length])+'")';});
}

/* Челленджи: featured + все карточки с реальными 9:16 превью. */
var challenges=document.getElementById('screen-challenges');
if(challenges){var featured=challenges.querySelector('.chv');if(featured){featured.classList.add('deels-media-fill');addImage(featured,live[0],'deels-media-img','Вертикальное видео челленджа');}challenges.querySelectorAll('.chvideo').forEach(function(box,i){box.classList.add('deels-media-fill');addImage(box,live[(i+1)%live.length],'deels-media-img','Обложка челленджа Deels');});}

/* Баттлы: оба участника и все быстрые дуэли заполнены фото. */
var battles=document.getElementById('screen-battles');
if(battles){var sideFiles=['video-dance.webp','video-boy.webp'];battles.querySelectorAll('.battle-side').forEach(function(box,i){box.classList.add('deels-media-fill');addImage(box,sideFiles[i%2],'deels-media-img','Участник баттла Deels');});var battleFiles=['video-creator.webp','video-boy.webp','video-dance.webp','hero-rooftop.webp'];battles.querySelectorAll('.battle-card-visual').forEach(function(vis,i){var halves=vis.querySelectorAll(':scope > div');halves.forEach(function(half,j){half.classList.add('deels-battle-half');half.textContent='';addImage(half,battleFiles[(i*2+j)%battleFiles.length],'deels-media-img','Участник баттла');});});}

/* Лента: фото-фрейм меняется вместе с роликом, UI остаётся поверх. */
var feed=document.getElementById('screen-feed');if(feed){function fillFeed(){var reel=feed.querySelector('#uxReel');if(!reel)return;var counter=feed.querySelector('#uxFeedCounter');var idx=0;if(counter){var m=(counter.textContent||'').match(/^(\d+)/);if(m)idx=Math.max(0,parseInt(m[1],10)-1);}reel.classList.add('deels-media-fill');addImage(reel,live[idx%live.length],'deels-media-img','Видео в ленте Deels');}fillFeed();var observer=new MutationObserver(function(){fillFeed();});observer.observe(feed,{subtree:true,childList:true,characterData:true});}

/* Копилки: фото есть и на главной карточке, и во всех мини-карточках. */
var campaign=document.getElementById('screen-campaign');if(campaign){var campaignHero=campaign.querySelector('.balance-grid .poster');if(campaignHero){campaignHero.classList.add('deels-media-fill');addImage(campaignHero,'hero-rooftop.webp','deels-media-img','История копилки Deels');}campaign.querySelectorAll('.campaign-mini-cover').forEach(function(box,i){box.classList.add('deels-media-fill');addImage(box,live[(i+1)%live.length],'deels-media-img','Обложка копилки Deels');});}

/* Профиль: аватар и вся медиасетка заполнены живыми кадрами. */
var profile=document.getElementById('screen-profile');if(profile){profile.querySelectorAll('.poster').forEach(function(box,i){box.classList.add('deels-media-fill');addImage(box,live[i%live.length],'deels-media-img','Видео пользователя Deels');});var av=profile.querySelector('.avatar-lg');if(av){av.classList.add('deels-profile-avatar');av.style.backgroundImage='url("'+asset('video-creator.webp')+'")';}}
});
})();