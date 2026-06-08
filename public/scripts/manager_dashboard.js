/**
 * manager_dashboard.js
 * Umieść w: public/js/manager_dashboard.js
 *
 * Funkcje:
 *  - Żywy zegar (sekundy)
 *  - Odliczanie do automatycznego odświeżenia (60 s)
 *  - Zakładki filtrowania (Wszystkie / Zajęte / Wolne)
 *  - Wyszukiwanie po nazwie pokoju lub e-mailu gościa
 *  - Stagger animation dla kart przy ładowaniu strony
 */

(function () {
    'use strict';

    /* ── LIVE CLOCK ─────────────────────────────────────────────── */
    const clockEl = document.getElementById('live-clock');
    const dateEl  = document.getElementById('live-date');

    const DAYS_PL = ['Niedziela', 'Poniedziałek', 'Wtorek', 'Środa', 'Czwartek', 'Piątek', 'Sobota'];
    const MONTHS_PL = [
        'stycznia','lutego','marca','kwietnia','maja','czerwca',
        'lipca','sierpnia','września','października','listopada','grudnia'
    ];

    function pad2(n) { return String(n).padStart(2, '0'); }

    function updateClock() {
        const now = new Date();
        if (clockEl) {
            clockEl.textContent = pad2(now.getHours()) + ':' + pad2(now.getMinutes()) + ':' + pad2(now.getSeconds());
        }
        if (dateEl) {
            const day   = DAYS_PL[now.getDay()];
            const d     = now.getDate();
            const month = MONTHS_PL[now.getMonth()];
            const year  = now.getFullYear();
            dateEl.textContent = day + ', ' + d + ' ' + month + ' ' + year;
        }
    }

    updateClock();
    setInterval(updateClock, 1000);

    /* ── AUTO-REFRESH COUNTDOWN ─────────────────────────────────── */
    const countdownEl = document.getElementById('refresh-countdown');
    let countdown = 60;

    function tick() {
        countdown -= 1;
        if (countdownEl) countdownEl.textContent = countdown;
        if (countdown <= 0) {
            window.location.reload();
        }
    }

    setInterval(tick, 1000);

    /** Ręczne odświeżenie — dostępne globalnie dla przycisku onclick */
    window.forceRefresh = function () {
        window.location.reload();
    };

    /* ── FILTER LOGIC ───────────────────────────────────────────── */
    let currentFilter = 'all';
    let currentSearch = '';

    function applyFilters() {
        const cards     = document.querySelectorAll('.room-card');
        const q         = currentSearch.toLowerCase().trim();

        cards.forEach(function (card) {
            const status    = card.dataset.status   || '';
            const roomName  = card.dataset.roomName || '';
            const guest     = card.dataset.guest    || '';

            const matchStatus = currentFilter === 'all' || status === currentFilter;
            const matchSearch = !q || roomName.includes(q) || guest.includes(q);

            card.style.display = (matchStatus && matchSearch) ? '' : 'none';
        });

        updateNoResultsMsg();
    }

    function updateNoResultsMsg() {
        const grid    = document.getElementById('rooms-grid');
        if (!grid) return;

        const visible = grid.querySelectorAll('.room-card:not([style*="display: none"])').length;
        let noMsg     = grid.querySelector('.no-results-msg');

        if (visible === 0) {
            if (!noMsg) {
                noMsg = document.createElement('p');
                noMsg.className = 'no-results-msg';
                noMsg.style.cssText = 'grid-column:1/-1;font-family:var(--font-mono);font-size:12px;color:var(--txt-3);padding:24px 0;text-align:center;';
                grid.appendChild(noMsg);
            }
            noMsg.textContent = 'Brak pokoi pasujących do wybranych filtrów.';
        } else if (noMsg) {
            noMsg.remove();
        }
    }

    /* Filter tabs */
    document.querySelectorAll('.ftab').forEach(function (tab) {
        tab.addEventListener('click', function () {
            document.querySelectorAll('.ftab').forEach(function (t) {
                t.classList.remove('ftab--active');
                t.setAttribute('aria-selected', 'false');
            });
            tab.classList.add('ftab--active');
            tab.setAttribute('aria-selected', 'true');

            currentFilter = tab.dataset.filter;
            applyFilters();
        });
    });

    /* Search input */
    var searchInput = document.getElementById('search-input');
    if (searchInput) {
        searchInput.addEventListener('input', function () {
            currentSearch = this.value;
            applyFilters();
        });

        /* Wyczyść wyszukiwanie klawiszem Escape */
        searchInput.addEventListener('keydown', function (e) {
            if (e.key === 'Escape') {
                this.value = '';
                currentSearch = '';
                applyFilters();
            }
        });
    }

    /* ── STAGGER ANIMATION ──────────────────────────────────────── */
    var cards = document.querySelectorAll('.room-card');
    cards.forEach(function (card, idx) {
        card.style.animationDelay = (idx * 0.05) + 's';
    });

    /* ── KEYBOARD SHORTCUT: / → focus search ────────────────────── */
    document.addEventListener('keydown', function (e) {
        if (e.key === '/' && document.activeElement !== searchInput) {
            e.preventDefault();
            if (searchInput) searchInput.focus();
        }
    });

}());