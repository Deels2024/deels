(function(){
'use strict';
var src=document.currentScript&&document.currentScript.src?document.currentScript.src:'';
function ready(fn){if(document.readyState==='loading')document.addEventListener('DOMContentLoaded',fn,{once:true});else fn();}
function asset(name){return src?new URL('./home-media/'+name,src).href:'./home-media/'+name;}
ready(function(){
var home=document.getElementById('screen-home');if(!home)return;
var style=document.createElement('style');style.textContent=''+
'#screen-home .hero-visual{position:relative!important;min-height:560px!important;display:flex!important;align-items:center!important;justify-content:center!important}'+
'#screen-home .home-hero-photo{position:absolute;inset:20px 0 20px 42px;overflow:hidden;border-radius:38px;box-shadow:0 30px 80px rgba(55,20,84,.20)}'+
'#screen-home .home-hero-photo img{width:100%;height:100%;object-fit:cover;object-position:center;display:block}'+
'#screen-home .home-hero-photo:after{content:"";position:absolute;inset:0;background:linear-gradient(120deg,rgba(49,16,77,.08),rgba(49,16,77,.04) 45%,rgba(49,16,77,.34))}'+
'#screen-home .hero-visual .phone-frame{position:relative!important;z-index:4!important;margin-left:auto!important;margin-right:26px!important;transform:rotate(2deg)!important;box-shadow:0 30px 70px rgba(18,7,26,.32)!important}'+
'#screen-home .hero-visual .floating-chip{z-index:6!important}'+
'#screen-home .hero-visual .orbit{z-index:1!important}'+
'#screen-home .phone-video{position:relative!important;overflow:hidden!important;background:#301044!important}'+
'#screen-home .phone-video .home-phone-photo{position:absolute;inset:0;width:100%;height:100%;object-fit:cover;z-index:0}'+
'#screen-home .phone-video:after{content:"";position:absolute;inset:0;z-index:1;background:linear-gradient(180deg,rgba(18,6,27,.03) 35%,rgba(18,6,27,.82) 100%)}'+
'#screen-home .phone-video .phone-live,#screen-home .phone-video .phone-side,#screen-home .phone-video .phone-caption{position:relative;z-index:3}'+
'#screen-home .home-live-poster{background:#2f163e!important}'+
'#screen-home .campaign-cover.home-photo-cover{position:relative;overflow:hidden;min-height:210px!important;display:block!important}'+
'#screen-home .campaign-cover.home-photo-cover>img{position:absolute;inset:0;width:100%;height:100%;object-fit:cover}'+
'#screen-home .campaign-cover.home-photo-cover:after{content:"";position:absolute;inset:0;background:linear-gradient(180deg,rgba(20,6,30,.02),rgba(20,6,30,.58))}'+
'#screen-home .campaign-cover.home-photo-cover .poster-tag{position:absolute;z-index:2;left:14px;top:14px;background:rgba(20,6,30,.45);color:#fff;backdrop-filter:blur(8px)}'+
'#screen-home .campaign-cover.home-photo-cover>span:first-of-type{display:none}'+
'#screen-home .avatar-stack .home-avatar{background-size:cover!important;background-position:center!important;color:transparent!important;border:2px solid #fff!important}'+
'@media(max-width:980px){#screen-home .hero-visual{min-height:520px!important}#screen-home .home-hero-photo{inset:18px 16px!important}#screen-home .hero-visual .phone-frame{margin-right:42px!important}}'+
'@media(max-width:620px){#screen-home .hero-visual{min-height:460px!important}#screen-home .home-hero-photo{inset:10px 8px 26px!important;border-radius:28px}#screen-home .hero-visual .phone-frame{transform:scale(.82) rotate(1.5deg)!important;transform-origin:center!important;margin:0!important}#screen-home .hero-visual .floating-chip{display:none!important}}';document.head.appendChild(style);
var hv=home.querySelector('.hero-visual');if(hv){
 hv.classList.remove('home-live-hero-visual');
 hv.innerHTML='<div class="home-hero-photo"><img src="'+asset('hero-rooftop.webp')+'" alt="Участники Deels снимают контент вместе"></div><div class="orbit orbit-one"></div><div class="orbit orbit-two"></div><div class="floating-chip chip-prize"><span>🏆</span><strong>50 000 ₽</strong><small>призовой фонд</small></div><div class="floating-chip chip-trend"><span>↗</span><strong>В тренде</strong><small>180K ответов</small></div><div class="phone-frame"><div class="phone-top"><a href="#home" class="brand"><span class="brand-mark">D</span><span class="brand-word">DEELS</span></a><span>◌</span></div><div class="phone-video"><img class="home-phone-photo" src="'+asset('video-dance.webp')+'" alt="Вертикальное видео участницы"><span class="phone-live">DEELS • LIVE</span><div class="phone-side"><span>♡<small>8,2K</small></span><span>◯<small>462</small></span><span>↗<small>92</small></span></div><div class="phone-caption"><small>@mila.sun</small><strong>Повтори летний движ</strong><span>#танцы #deels</span></div></div><div class="phone-nav"><span>⌂</span><span>⌕</span><b>+</b><span>◌</span><span>●</span></div></div>';
}
var files=['video-dance.webp','video-friends.webp','video-creator.webp','video-boy.webp'];
home.querySelectorAll('.horizontal-cards .video-card .poster').forEach(function(p,i){var old=p.querySelector('.home-live-photo');if(old)old.src=asset(files[i%files.length]);else{var img=document.createElement('img');img.className='home-live-photo';img.src=asset(files[i%files.length]);img.alt='Видео участника Deels';p.insertBefore(img,p.firstChild);}var emoji=p.querySelector('.poster-emoji');if(emoji)emoji.remove();p.classList.add('home-live-poster');});
var storyFiles=['video-friends.webp','video-creator.webp','video-boy.webp'];home.querySelectorAll('.stories-stack .story-card').forEach(function(c,i){var img=c.querySelector('.home-live-story-photo');if(!img){img=document.createElement('img');img.className='home-live-story-photo';img.alt='История участника Deels';c.insertBefore(img,c.firstChild);}img.src=asset(storyFiles[i%storyFiles.length]);c.classList.add('home-live-story');});
var campaignFiles=['video-creator.webp','video-friends.webp','video-dance.webp'];home.querySelectorAll('.campaign-grid .campaign-cover').forEach(function(c,i){c.classList.add('home-photo-cover');var img=c.querySelector('img');if(!img){img=document.createElement('img');img.alt='Участники проекта Deels';c.insertBefore(img,c.firstChild);}img.src=asset(campaignFiles[i%campaignFiles.length]);});
var avatars=home.querySelectorAll('.hero-proof .avatar-stack span');avatars.forEach(function(a,i){if(i>=3)return;a.classList.add('home-avatar');a.style.backgroundImage='url("'+asset(files[i%files.length])+'")';});
});
})();
