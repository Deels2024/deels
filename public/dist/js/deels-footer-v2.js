(function(){
  'use strict';
  function ready(fn){if(document.readyState==='loading')document.addEventListener('DOMContentLoaded',fn,{once:true});else fn();}
  ready(function(){
    var footer=document.querySelector('footer.footer');
    if(!footer||footer.dataset.deelsSourceFooter==='1')return;
    footer.dataset.deelsSourceFooter='1';
    footer.className='site-footer';
    footer.innerHTML=''+
      '<div class="container footer-grid">'+
        '<div>'+ 
          '<a href="/" class="brand" aria-label="Deels — главная"><span class="brand-mark">D</span><span class="brand-word">DEELS</span></a>'+ 
          '<p>Новая развлекательная соцсеть, где идеи превращаются в движение.</p>'+ 
          '<div class="socials"><a href="https://vk.com/deels" target="_blank" rel="noopener">VK</a><a href="https://t.me/deels_ru" target="_blank" rel="noopener">TG</a></div>'+ 
        '</div>'+ 
        '<div><h4>Смотреть</h4><a href="/stories">Лента</a><a href="/challenges">Челленджи</a><a href="/battles">Баттлы</a><a href="/campaign">Копилки</a></div>'+ 
        '<div><h4>Deels</h4><a href="/">О проекте</a><a href="/contact-us">Контакты</a><a href="/offer">Документы</a><a href="/docs/privacy_policy.docx">Конфиденциальность</a></div>'+ 
        '<div><h4>Будь в движении</h4><p>Скачивай приложение и участвуй первым.</p><div class="store-buttons"><a href="https://apps.apple.com/us/app/deels/id6480409656" target="_blank" rel="noopener">App Store</a><a href="https://play.google.com/store/apps/details?id=com.kts.kopiberi_application" target="_blank" rel="noopener">Google Play</a></div></div>'+ 
      '</div>'+ 
      '<div class="container footer-bottom"><span>© 2026 Deels</span><span>Сделано для настоящих идей</span></div>';
  });
})();