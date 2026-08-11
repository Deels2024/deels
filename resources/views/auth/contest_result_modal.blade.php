@auth
    @php
        $contestResultEvents = auth()->user()
            ->events()
            ->pending()
            ->oldest()
            ->get()
            ->map(fn ($event) => [
                'id' => $event->id,
                'result' => $event->result,
                'data' => $event->data,
            ])
            ->values();
    @endphp

    @if($contestResultEvents->isNotEmpty())
        <style>
            .contest-result-modal {
                position: fixed;
                inset: 0;
                z-index: 10050;
                display: grid;
                place-items: center;
                padding: 20px;
                background: rgba(0,0,0,0.4);
                opacity: 0;
                visibility: hidden;
                transition: opacity .25s ease, visibility .25s ease;
            }

            .contest-result-modal.is-visible {
                opacity: 1;
                visibility: visible;
            }

            .contest-result-modal__fireworks {
                position: absolute;
                inset: 0;
                pointer-events: none;
                background: url('/images/fireworks.gif') center / cover no-repeat;
            }

            .contest-result-modal__dialog {
                position: relative;
                width: min(640px, 100%);
                overflow: hidden;
                background: #0d102c;
                box-shadow: 0 28px 80px rgba(0, 0, 0, .48);
                color: #fff;
                transform: translateY(18px) scale(.97);
                transition: transform .32s cubic-bezier(.2, .8, .2, 1);
            }

            .contest-result-modal.is-visible .contest-result-modal__dialog {
                transform: translateY(0) scale(1);
            }

            .contest-result-modal__content {
                position: relative;
                z-index: 1;
                padding: 64px 48px 48px;
                text-align: center;
            }

            .contest-result-modal__close {
                position: absolute;
                z-index: 2;
                top: 16px;
                right: 16px;
                width: 44px;
                height: 44px;
                border: transparent;
                border-radius: 50%;
                background: transparent;
                color: #fff;
                font: 400 32px/36px Arial, sans-serif;
                cursor: pointer;
                transition: background .2s ease, transform .2s ease;
            }

            .contest-result-modal__close:hover,
            .contest-result-modal__close:focus {

                color: #b224ef;

            }

            .contest-result-modal__close:active {
                transform: scale(.94);
            }

            .contest-result-modal__title {
                margin: 0 0 18px;
                text-transform: uppercase;
                color: #fff;
                font-weight: 800;
                font-size: 35px;
                line-height: 42px;
            }

            .contest-result-modal__message,
            .contest-result-modal__reward {
                margin: 0 auto;
                max-width: 440px;
                color: rgba(255, 255, 255, .88);
                font-size: 19px;
                line-height: 1.55;
            }

            .contest-result-modal__message a {
                color: #text-accent;
                font-weight: 700;
                display: inline !important;
                max-width: 100%;
                white-space: normal !important;
                overflow-wrap: break-word;
                word-break: break-word;
                text-decoration: underline;
                text-decoration-thickness: 1px;
                text-underline-offset: 3px;
            }

            .contest-result-modal__reward {
                width: fit-content;
                margin-top: 24px;
                padding: 10px 18px;
                border-radius: 999px;
                font-weight: 700;
            }

            body.contest-result-modal-open {
                overflow: hidden;
            }

            @media (max-width: 575px) {
                .contest-result-modal {
                    padding: 12px;
                }

                .contest-result-modal__dialog {
                    border-radius: 18px;
                }

                .contest-result-modal__content {
                    padding: 66px 24px 38px;
                }

                .contest-result-modal__message,
                .contest-result-modal__reward {
                    font-size: 16px;
                }
            }

            @media (prefers-reduced-motion: reduce) {
                .contest-result-modal,
                .contest-result-modal__dialog {
                    transition: none;
                }

                .contest-result-modal__fireworks {
                    display: none;
                }
            }
        </style>

        <div class="contest-result-modal"
             id="contest-result-modal"
             role="dialog"
             aria-modal="true"
             aria-labelledby="contest-result-modal-title"
             aria-describedby="contest-result-modal-message">
            <div class="contest-result-modal__fireworks" aria-hidden="true"></div>
            <div class="contest-result-modal__dialog">
                <button class="contest-result-modal__close"
                        type="button"
                        aria-label="Закрыть поздравление">&times;</button>
                <div class="contest-result-modal__content">
                    <h2 class="contest-result-modal__title" id="contest-result-modal-title"></h2>
                    <div class="contest-result-modal__message" id="contest-result-modal-message"></div>
                    <div class="contest-result-modal__reward ch-block__btn--xl ch-block__btn--outline" hidden></div>
                </div>
            </div>
        </div>

        <script>
            document.addEventListener('DOMContentLoaded', function () {
                const events = @json($contestResultEvents);
                const modal = document.getElementById('contest-result-modal');
                if (!modal || !events.length) return;

                const title = modal.querySelector('.contest-result-modal__title');
                const message = modal.querySelector('.contest-result-modal__message');
                const reward = modal.querySelector('.contest-result-modal__reward');
                const close = modal.querySelector('.contest-result-modal__close');
                let current = 0;

                function showEvent() {
                    const event = events[current];
                    const data = event.data || {};
                    title.textContent = data.title || (event.result === 'battle_draw' ? 'Ничья!' : 'Победа!');
                    message.innerHTML = data.message || '';
                    reward.textContent = data.reward_text || '';
                    reward.hidden = !data.reward_text;
                    modal.classList.add('is-visible');
                    document.body.classList.add('contest-result-modal-open');
                    close.focus();
                }

                function dismissCurrent() {
                    const event = events[current];
                    modal.classList.remove('is-visible');
                    document.body.classList.remove('contest-result-modal-open');

                    fetch('/api/user/events/' + event.id + '/dismiss', {
                        method: 'POST',
                        credentials: 'same-origin',
                        headers: {
                            'Accept': 'application/json',
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content || ''
                        }
                    }).catch(function () {});

                    current++;
                    if (events[current]) setTimeout(showEvent, 280);
                }

                close.addEventListener('click', dismissCurrent);
                document.addEventListener('keydown', function (event) {
                    if (event.key === 'Escape' && modal.classList.contains('is-visible')) dismissCurrent();
                });
                showEvent();
            });
        </script>
    @endif
@endauth
