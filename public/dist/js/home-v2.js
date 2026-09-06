(function () {
    'use strict';

    var root = document.querySelector('[data-home-v2]');
    if (!root) {
        return;
    }

    var reduceMotion = window.matchMedia && window.matchMedia('(prefers-reduced-motion: reduce)').matches;
    var saveData = navigator.connection && navigator.connection.saveData;

    if (reduceMotion || saveData) {
        root.querySelectorAll('.hv2-live-card video[autoplay]').forEach(function (video) {
            video.removeAttribute('autoplay');
            video.pause();
        });
    }

    function railStep(rail) {
        var first = rail.querySelector(':scope > :not([hidden])');
        if (!first) {
            return Math.max(rail.clientWidth * 0.8, 260);
        }

        var styles = window.getComputedStyle(rail);
        var gap = parseFloat(styles.columnGap || styles.gap || 0);
        return first.getBoundingClientRect().width + gap;
    }

    function bindRail(panel) {
        var rail = panel.querySelector('[data-rail]');
        var previous = panel.querySelector('[data-rail-prev]');
        var next = panel.querySelector('[data-rail-next]');

        if (!rail || !previous || !next) {
            return;
        }

        function updateControls() {
            var max = Math.max(0, rail.scrollWidth - rail.clientWidth);
            var hasOverflow = max > 3;
            previous.disabled = !hasOverflow || rail.scrollLeft <= 3;
            next.disabled = !hasOverflow || rail.scrollLeft >= max - 3;
            previous.parentElement.hidden = !hasOverflow;
        }

        previous.addEventListener('click', function () {
            rail.scrollBy({left: -railStep(rail), behavior: reduceMotion ? 'auto' : 'smooth'});
        });

        next.addEventListener('click', function () {
            rail.scrollBy({left: railStep(rail), behavior: reduceMotion ? 'auto' : 'smooth'});
        });

        rail.addEventListener('scroll', updateControls, {passive: true});
        window.addEventListener('resize', updateControls, {passive: true});
        updateControls();
    }

    root.querySelectorAll('.hv2-panel').forEach(bindRail);

    var filters = root.querySelector('[data-challenge-filters]');
    if (filters) {
        var challengePanel = filters.closest('.hv2-panel');
        var challengeRail = challengePanel.querySelector('[data-rail]');
        var empty = challengePanel.querySelector('[data-filter-empty]');

        filters.addEventListener('click', function (event) {
            var button = event.target.closest('[data-filter]');
            if (!button) {
                return;
            }

            var filter = button.getAttribute('data-filter');
            var visible = 0;

            filters.querySelectorAll('[data-filter]').forEach(function (item) {
                var active = item === button;
                item.classList.toggle('is-active', active);
                item.setAttribute('aria-pressed', active ? 'true' : 'false');
            });

            challengeRail.querySelectorAll('[data-challenge-card]').forEach(function (card) {
                var matches = filter === 'all' ||
                    (filter === 'reward' && card.getAttribute('data-has-reward') === '1') ||
                    (filter === 'new' && card.getAttribute('data-is-new') === '1') ||
                    (filter === 'ending' && card.getAttribute('data-is-ending') === '1');

                card.hidden = !matches;
                if (matches) {
                    visible += 1;
                }
            });

            challengeRail.scrollLeft = 0;
            challengeRail.dispatchEvent(new Event('scroll'));
            empty.hidden = visible !== 0;
        });
    }

    function setBankStatus(message, offline) {
        var status = root.querySelector('[data-bank-status]');
        if (!status) {
            return;
        }

        status.textContent = message;
        status.classList.toggle('is-offline', Boolean(offline));
    }

    root.querySelectorAll('[data-home-tabs]').forEach(function (tabs) {
        var tabButtons = Array.from(tabs.querySelectorAll('[data-home-tab]'));
        function selectCollectionTab(button) {
            var key = button.getAttribute('data-home-tab');
            tabButtons.forEach(function (item) {
                item.setAttribute('aria-pressed', item === button ? 'true' : 'false');
            });
            tabs.querySelectorAll('[data-home-panel]').forEach(function (panel) {
                panel.hidden = panel.getAttribute('data-home-panel') !== key;
                if (panel.hidden) {
                    panel.querySelectorAll('video').forEach(function (video) { video.pause(); });
                } else {
                    var rail = panel.querySelector('[data-rail]');
                    if (rail) rail.dispatchEvent(new Event('scroll'));
                }
            });
        }
        if (tabButtons.length) {
            tabs.classList.add('is-enhanced');
            function selectLinkedCollection() {
                var target = document.getElementById(window.location.hash.slice(1));
                var panel = target && target.closest('[data-home-panel]');
                var button = panel && tabs.contains(panel) && tabButtons.find(function (item) {
                    return item.getAttribute('data-home-tab') === panel.getAttribute('data-home-panel');
                });
                if (button) selectCollectionTab(button);
                return Boolean(button);
            }
            if (!selectLinkedCollection()) selectCollectionTab(tabButtons[0]);
            window.addEventListener('hashchange', selectLinkedCollection);
            tabButtons.forEach(function (button, index) {
                button.addEventListener('click', function () { selectCollectionTab(button); });
                button.addEventListener('keydown', function (event) {
                    var nextIndex;
                    if (event.key === 'ArrowRight') nextIndex = (index + 1) % tabButtons.length;
                    else if (event.key === 'ArrowLeft') nextIndex = (index - 1 + tabButtons.length) % tabButtons.length;
                    else if (event.key === 'Home') nextIndex = 0;
                    else if (event.key === 'End') nextIndex = tabButtons.length - 1;
                    else return;
                    event.preventDefault();
                    tabButtons[nextIndex].focus();
                    selectCollectionTab(tabButtons[nextIndex]);
                });
            });
        }
    });

    function setBankValue(value) {
        var counter = root.querySelector('[data-bank-counter]');
        if (counter) {
            var formatted = new Intl.NumberFormat('ru-RU').format(value);
            counter.textContent = formatted;
            counter.setAttribute('aria-label', formatted + ' DEELS');
        }
    }

    var bankInFlight = false;
    var bankRetry = root.querySelector('[data-bank-retry]');
    function refreshBank() {
        var url = root.getAttribute('data-bank-url');
        if (!url || document.hidden || bankInFlight || root.getAttribute('data-preview') === 'true') return;

        bankInFlight = true;
        if (bankRetry) bankRetry.hidden = true;
        setBankStatus('Обновляем данные…', false);
        var controller = new AbortController();
        var timeout = window.setTimeout(function () { controller.abort(); }, 10000);
        fetch(url, {
            headers: {'Accept': 'application/json'},
            credentials: 'same-origin',
            signal: controller.signal
        })
            .then(function (response) {
                if (!response.ok) throw new Error('Bank request failed');
                return response.json();
            })
            .then(function (data) {
                var count = data && data.count;
                if ((typeof count !== 'number' && typeof count !== 'string') || String(count).trim() === '' ||
                    !Number.isSafeInteger(Number(count)) || Number(count) < 0) {
                    throw new Error('Invalid bank balance');
                }
                setBankValue(Number(count));
                setBankStatus('Данные обновлены', false);
            })
            .catch(function () {
                // Preserve the last server-rendered value; never invent a fallback balance.
                setBankStatus('Показано последнее значение', true);
                if (bankRetry) bankRetry.hidden = false;
            })
            .finally(function () {
                bankInFlight = false;
                window.clearTimeout(timeout);
            });
    }
    if (bankRetry) bankRetry.addEventListener('click', refreshBank);
    refreshBank();
    window.setInterval(refreshBank, 60000);

    if (!reduceMotion && !saveData) {
        root.querySelectorAll('.hv2-ratio video').forEach(function (video) {
            var card = video.closest('a, article');
            if (!card) {
                return;
            }

            function play() {
                var promise = video.play();
                if (promise && typeof promise.catch === 'function') {
                    promise.catch(function () {});
                }
            }

            function pause() {
                video.pause();
            }

            card.addEventListener('mouseenter', play);
            card.addEventListener('mouseleave', pause);
            card.addEventListener('focusin', play);
            card.addEventListener('focusout', pause);
        });
    }
}());
