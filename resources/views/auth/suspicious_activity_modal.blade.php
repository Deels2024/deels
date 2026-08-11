@php
    $suspiciousActivityUser = Auth::user();
@endphp
@if($suspiciousActivityUser)
    <div class="popup__wrape email-prompt-popup" id="suspicious-activity-modal" role="dialog" aria-modal="true"
         aria-labelledby="suspicious-activity-modal-title" style="display: none">
        <div class="popup__modal">
            <div class="popup__title" id="suspicious-activity-modal-title">Действие временно недоступно</div>
            <p class="email-prompt-popup__text" id="suspicious-activity-message"></p>
            <div class="email-prompt-popup__actions">
                <button type="button" class="btn btn_fill" id="suspicious-activity-close">Понятно</button>
            </div>
        </div>
    </div>
    <script>
        (function () {
            const modal = document.getElementById('suspicious-activity-modal');
            if (!modal || modal.dataset.closeHandlerReady === '1') return;
            modal.dataset.closeHandlerReady = '1';

            const close = function () {
                modal.style.display = 'none';
                document.body.classList.remove('overflow');
            };

            modal.addEventListener('click', function (event) {
                if (event.target === modal || event.target.closest('#suspicious-activity-close')) {
                    event.preventDefault();
                    event.stopImmediatePropagation();
                    close();
                }
            }, true);
        })();
    </script>
@endif
