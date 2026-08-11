<script src="https://securepay.tinkoff.ru/html/payForm/js/tinkoff_v2.js"></script>
<form name="TinkoffPayForm" action="" class="form deposit__form form-horizontal TinkoffPayForm" method="post" enctype="multipart/form-data"> @csrf
    <input class="tinkoffPayRow" type="hidden" name="terminalkey" value="1619081031059">
    <input class="tinkoffPayRow" type="hidden" name="frame" value="true">
    <input class="tinkoffPayRow" type="hidden" name="language" value="ru">
    <input class="tinkoffPayRow" type="hidden" name="reccurentPayment" value="{{request()->has('auto') ? 'true' : 'false'}}">
    <input class="tinkoffPayRow" type="hidden" name="customerKey" value="{{Auth::id() ?? 'anon_'.time()}}">
    <input class="tinkoffPayRow receiptTinkoff" type="hidden" name="receipt" value=''>

    <div class="deposit__name">
        <label for="name">Полное имя*</label>
        <input type="text" name="name" id="name" value="@if(Auth::check()){!!auth()->user()->fullname!!}@else{!! old('full_name') !!}@endif" placeholder="Артем">
        <div class="deposit__name-hide">Заполните это поле</div>
    </div>
    <label for="email">Email</label>
    <input type="text" value="@if(Auth::check()){!!auth()->user()->email!!}@else{!! old('email') !!}@endif" name="email" id="email" placeholder="1234@gmail.com">

    <div class="modal-foure__summ">Общая сумма</div>
    <div class="modal-foure__cat">
        <div class="modal-foure__cat-item"><span class="action_campaign_name"></span> - <span class="action_campaign_price"></span>₽</div>
        <div class="modal-foure__cat-item">Итог - <span class="action_campaign_price"></span>₽</div>
    </div>
    <input style="    margin-bottom: 20px;" type="submit" class="deposit__btn btn btn_fill" value="Внести донат"/>
    <div class="modal-foure__end">
        Вы также признаете и соглашаетесь с Условиями использования и политикой конфиденциальности.
    </div>
    <input class="tinkoffPayRow tinkoffPayRowLast" type="hidden" placeholder="Сумма заказа" value="" name="amount" required>
    <input class="tinkoffPayRow tinkoffPayRowLast" type="hidden" placeholder="Номер заказа" name="order" value="">
    <input class="tinkoffPayRow tinkoffPayRowLast" type="hidden" placeholder="Описание заказа" value="" name="description">
</form>
