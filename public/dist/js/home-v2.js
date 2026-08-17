(function () {
    'use strict';

    var root = document.querySelector('[data-home-v2]');
    if (!root) {
        return;
    }

    var reduceMotion = window.matchMedia && window.matchMedia('(prefers-reduced-motion: reduce)').matches;

    function railStep(rail) {
        var first = rail.firstElementChild;
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
            empty.hidden = visible !== 0;
        });
    }

    function setBankValue(value) {
        var digits = String(Math.max(0, parseInt(value, 10) || 0)).padStart(8, '0').slice(-8).split('');
        var nodes = root.querySelectorAll('[data-bank-counter] .hv2-counter__digit');

        nodes.forEach(function (node, index) {
            if (node.textContent === digits[index]) {
                return;
            }

            node.textContent = digits[index];
            node.classList.remove('is-changing');
            void node.offsetWidth;
            node.classList.add('is-changing');
        });

        var counter = root.querySelector('[data-bank-counter]');
        if (counter) {
            counter.setAttribute('aria-label', new Intl.NumberFormat('ru-RU').format(parseInt(value, 10) || 0) + ' DEELS');
        }
    }

    function refreshBank() {
        var url = root.getAttribute('data-bank-url');
        if (!url || document.hidden) {
            return;
        }

        fetch(url, {
            headers: {'Accept': 'application/json'},
            credentials: 'same-origin'
        })
            .then(function (response) {
                if (!response.ok) {
                    throw new Error('Bank request failed');
                }
                return response.json();
            })
            .then(function (data) {
                if (data && typeof data.count !== 'undefined') {
                    setBankValue(data.count);
                }
            })
            .catch(function () {
                // The server-rendered value stays visible when live refresh is unavailable.
            });
    }

    window.setInterval(refreshBank, 10000);

    if (!reduceMotion && !(navigator.connection && navigator.connection.saveData)) {
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
