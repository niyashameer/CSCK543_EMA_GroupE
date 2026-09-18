/**
 * Recipe App — client-side behaviour.
 * Plain JS, no frameworks. Every check here is a UX nicety only —
 * the server re-validates everything in PHP, since JS can be
 * disabled or bypassed.
 */

document.addEventListener('DOMContentLoaded', function () {
    setupNavToggle();
    setupPasswordMatchValidation('register-form', 'password', 'confirm_password');
    setupPasswordMatchValidation('password-form', 'new_password', 'confirm_password');
    setupLiveSearch();
});

/**
 * Mobile hamburger menu. The nav is visible by default via CSS on
 * wide screens; on narrow screens it's hidden until toggled open.
 */
function setupNavToggle() {
    const toggle = document.getElementById('nav-toggle');
    const nav = document.getElementById('site-nav');
    if (!toggle || !nav) {
        return;
    }

    toggle.addEventListener('click', function () {
        const isOpen = nav.classList.toggle('is-open');
        toggle.setAttribute('aria-expanded', isOpen ? 'true' : 'false');
    });
}

/**
 * Live-checks that two password fields match, showing an inline
 * error and blocking submission until they do.
 */
function setupPasswordMatchValidation(formId, passwordFieldId, confirmFieldId) {
    const form = document.getElementById(formId);
    if (!form) {
        return;
    }

    const passwordField = form.querySelector('#' + passwordFieldId);
    const confirmField = form.querySelector('#' + confirmFieldId);
    const errorMessage = form.querySelector('#confirm-password-error');

    if (!passwordField || !confirmField) {
        return;
    }

    function checkMatch() {
        const matches = passwordField.value === confirmField.value;
        confirmField.setCustomValidity(matches ? '' : 'Passwords do not match');
        if (errorMessage) {
            errorMessage.hidden = matches || confirmField.value === '';
        }
        return matches;
    }

    passwordField.addEventListener('input', checkMatch);
    confirmField.addEventListener('input', checkMatch);

    form.addEventListener('submit', function (event) {
        if (!checkMatch()) {
            event.preventDefault();
            confirmField.focus();
        }
    });
}

/**
 * Progressive enhancement for the search page: the title search box
 * and sort dropdown re-filter/re-order the already-loaded cards
 * instantly, without a full page reload. Category/difficulty/time/
 * rating/allergen filters still submit the form normally, since
 * those need the server (real SQL) to change the result set.
 */
function setupLiveSearch() {
    const form = document.getElementById('search-form');
    const grid = document.getElementById('recipe-grid');
    const resultsCount = document.getElementById('results-count');
    if (!form || !grid) {
        return;
    }

    const searchInput = form.querySelector('#search');
    const sortSelect = form.querySelector('#sort');
    let debounceTimer = null;

    function getCards() {
        return Array.from(grid.querySelectorAll('.recipe-card'));
    }

    function applyLiveSearchAndSort() {
        const query = (searchInput.value || '').trim().toLowerCase();
        const sort = sortSelect.value;
        const cards = getCards();

        cards.forEach(function (card) {
            const matches = query === '' || card.dataset.title.indexOf(query) !== -1;
            card.hidden = !matches;
        });

        const visibleCards = cards.filter(function (card) {
            return !card.hidden;
        });

        visibleCards.sort(function (a, b) {
            switch (sort) {
                case 'time_asc':
                    return Number(a.dataset.time) - Number(b.dataset.time);
                case 'rating_desc':
                    return Number(b.dataset.rating) - Number(a.dataset.rating);
                default:
                    return a.dataset.title.localeCompare(b.dataset.title);
            }
        });

        visibleCards.forEach(function (card) {
            grid.appendChild(card);
        });

        if (resultsCount) {
            const count = visibleCards.length;
            resultsCount.textContent = count + (count === 1 ? ' recipe found' : ' recipes found');
        }
    }

    if (searchInput) {
        searchInput.addEventListener('input', function () {
            clearTimeout(debounceTimer);
            debounceTimer = setTimeout(applyLiveSearchAndSort, 150);
        });
    }

    if (sortSelect) {
        sortSelect.addEventListener('change', applyLiveSearchAndSort);
    }
}
