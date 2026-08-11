<div class="alert-container" style="position: relative;top: 0;left: 0;width: 100%;height: auto;z-index: 999999;">
    @if (!request()->routeIs('register') && isset($errors) && $errors->any())
        @foreach ($errors->all() as $error)
            <div class="alert danger">
                <span class="closebtn">&times;</span>
                <strong>Ошибка!</strong> {{ $error }}
            </div>
        @endforeach

    @endif

    @if( session('error'))
        <div class="alert danger">
            <span class="closebtn">&times;</span>
            <strong>Ошибка!</strong> {!! session('error') !!}
        </div>
    @endif

    @if(session('success'))
        <div class="alert success">
            <span class="closebtn">&times;</span>
            <strong>Успех!</strong> {!! session('success') !!}
        </div>
    @endif

    @if(session('info'))
        <div class="alert info">
            <span class="closebtn">&times;</span>
            <strong>Info!</strong> {!! session('info') !!}
        </div>
    @endif

    @if(session('warning'))
        <div class="alert warning">
            <span class="closebtn">&times;</span>
            <strong>Warning!</strong> {!! session('warning') !!}
        </div>
    @endif
</div>
@push('after_scripts')
    <script>
        function hideAlertWithAnimation($alert) {
            if (!$alert || !$alert.length) {
                return;
            }

            if ($alert.data('hiding')) {
                return;
            }

            $alert.data('hiding', true);
            $alert.css('opacity', '0');

            setTimeout(function () {
                $alert.remove();
            }, 600);
        }

        function scheduleAlertAutoClose($scope) {
            var $alerts = $scope.find('.alert.danger, .alert.success').addBack('.alert.danger, .alert.success');

            $alerts.each(function () {
                var $alert = $(this);

                if ($alert.data('autocloseScheduled')) {
                    return;
                }

                $alert.data('autocloseScheduled', true);

                setTimeout(function () {
                    hideAlertWithAnimation($alert);
                }, 5000);
            });
        }

        $(function () {
            scheduleAlertAutoClose($('.alert-container'));

            var alertContainer = document.querySelector('.alert-container');
            if (!alertContainer || typeof MutationObserver === 'undefined') {
                return;
            }

            var observer = new MutationObserver(function (mutations) {
                mutations.forEach(function (mutation) {
                    mutation.addedNodes.forEach(function (node) {
                        if (node.nodeType !== 1) {
                            return;
                        }

                        scheduleAlertAutoClose($(node));
                    });
                });
            });

            observer.observe(alertContainer, { childList: true, subtree: true });
        });

        $('body').on('click', '.closebtn',function (e) {
            hideAlertWithAnimation($(this).parents('.alert'));
        });

        $('body').on('click', '.closebtn-persistent', function (e) {
            hideAlertWithAnimation($(this).parents('.alert'));
        });
    </script>
@endpush
