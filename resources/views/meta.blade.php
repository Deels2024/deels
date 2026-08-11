@if(\Illuminate\Support\Str::contains(Request::fullUrl(), 'about-us') && \Request::route()?->getName() == 'single_page')
{{--    <meta name="description" content="Узнать подробнее о краудфандинговой платформе и целях проекта">--}}
@elseif(\Illuminate\Support\Str::contains(Request::fullUrl(), 'contact-us') && \Request::route()?->getName() == 'single_page')
    <meta name="description" content="Узнать контакты и связаться с кураторами проекта">
@elseif(\Request::route()?->getName() == 'stories.catalog')
    <meta name="description" content="Смотреть все сторис">
@elseif(\Request::route()?->getName() == 'password.request')
    <meta name="description" content="Сбросить пароль своей учетной записи">
@elseif(\Request::route()?->getName() == 'login')
    <meta name="description" content="Авторизоваться на сервисе">
@elseif(Request::fullUrl()==='http://deels.ru/campaigns?type=fully_donated')
    <meta name="description" content="Посмотреть, какие мечты уже исполнились, какие копилки заполнены на 100%">
@elseif (request()->route()?->getName()!=='campaign_single' && \Request::route()?->getName() != 'campaigns.category')
{{--    <meta name="description" content="Откройте сбор средств на то, о чем давно мечтаете! Наша краудфандинговая площадка предоставит удобные финансовые инструменты и обеспечит безопасное взаимодействие между участниками процесса.{{isset($_GET['page']) && $_GET['page'] > 0 ? ' Страница '.$_GET['page'] : ''}}">--}}
@endif
