/**
 * Recipe App — client-side behaviour.
 * Plain JS, no frameworks. Every check here is a UX enhancement only.
 * The server remains responsible for validation, searching and sorting
 * because JavaScript can be disabled or bypassed.
 */

document.addEventListener('DOMContentLoaded', function () {
    setupNavToggle();
    setupPasswordMatchValidation(
        'register-form',
        'password',
        'confirm_password'
    );
    setupPasswordMatchValidation(
        'password-form',
        'new_password',
        'confirm_password'
    );
    setupSearchEnhancements();
});

/**
 * Mobile hamburger menu. The nav is visible by default via CSS on
 * wide screens; on narrow screens it is hidden until toggled open.
 */
function setupNavToggle() {
    const toggle = document.getElementById('nav-toggle');
    const nav = document.getElementById('site-nav');

    if (!toggle || !nav) {
        return;
    }

    toggle.addEventListener('click', function () {
        const isOpen = nav.classList.toggle('is-open');

        toggle.setAttribute(
            'aria-expanded',
            isOpen ? 'true' : 'false'
        );
    });
}

/**
 * Live-checks that two password fields match, showing an inline
 * error and blocking submission until they do.
 */
function setupPasswordMatchValidation(
    formId,
    passwordFieldId,
    confirmFieldId
) {
    const form = document.getElementById(formId);

    if (!form) {
        return;
    }

    const passwordField =
        form.querySelector('#' + passwordFieldId);

    const confirmField =
        form.querySelector('#' + confirmFieldId);

    const errorMessage =
        form.querySelector('#confirm-password-error');

    if (!passwordField || !confirmField) {
        return;
    }

    function checkMatch() {
        const matches =
            passwordField.value === confirmField.value;

        confirmField.setCustomValidity(
            matches ? '' : 'Passwords do not match'
        );

        if (errorMessage) {
            errorMessage.hidden =
                matches || confirmField.value === '';
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
 * Progressive enhancement for the search page.
 *
 * The main text search filters the recipe cards already returned by
 * the server while the user types. Structured filters automatically
 * submit the complete form when changed, allowing PHP/MySQL to apply
 * all selected criteria together.
 *
 * Before an automatic submission, both the page scroll position and
 * the filter form's own scroll position are saved. When the refreshed
 * page loads, both positions are restored so the user stays where
 * they were.
 *
 * The Search button remains available so the form still works when
 * JavaScript is unavailable.
 */
function setupSearchEnhancements() {
    const form = document.getElementById('search-form');
    const grid = document.getElementById('recipe-grid');
    const resultsCount = document.getElementById('results-count');

    if (!form) {
        return;
    }

    const searchInput = form.querySelector('#search');

    /*
     * The sort control is visually outside the form but associated
     * with it using the HTML form="search-form" attribute.
     */
    const sortSelect = document.getElementById('sort');

    const difficultySelect =
        form.querySelector('#difficulty');

    const maxTimeInput =
        form.querySelector('#max_time');

    const minRatingSelect =
        form.querySelector('#min_rating');

    const categoryCheckboxes = form.querySelectorAll(
        'input[name="categories[]"]'
    );

    const allergenCheckboxes = form.querySelectorAll(
        'input[name="exclude_allergens[]"]'
    );

    /*
     * Two positions are stored:
     *
     * 1. window.scrollY keeps the overall page in place.
     *    This is especially useful on mobile.
     *
     * 2. form.scrollTop keeps the independently scrollable filter
     *    panel in place on desktop.
     */
    const pageScrollStorageKey =
        'recipeSearchPageScrollPosition';

    const filterScrollStorageKey =
        'recipeSearchFilterScrollPosition';

    let debounceTimer = null;

    /**
     * Restore the page and filter-panel positions after an automatic
     * filter submission caused the search page to reload.
     */
    const savedPageScroll =
        sessionStorage.getItem(pageScrollStorageKey);

    const savedFilterScroll =
        sessionStorage.getItem(filterScrollStorageKey);

    /*
     * Remove the saved values immediately so they only apply to the
     * reload caused by the automatic search submission.
     */
    sessionStorage.removeItem(pageScrollStorageKey);
    sessionStorage.removeItem(filterScrollStorageKey);

    const pageScrollPosition =
        Number.parseInt(savedPageScroll, 10);

    const filterScrollPosition =
        Number.parseInt(savedFilterScroll, 10);

    /*
     * Wait until the browser has created the refreshed layout before
     * restoring the previous positions.
     */
    requestAnimationFrame(function () {
        if (!Number.isNaN(pageScrollPosition)) {
            window.scrollTo({
                top: pageScrollPosition,
                left: 0,
                behavior: 'auto'
            });
        }

        if (!Number.isNaN(filterScrollPosition)) {
            form.scrollTop = filterScrollPosition;
        }
    });

    /**
     * Return all recipe cards currently rendered on the page.
     */
    function getCards() {
        if (!grid) {
            return [];
        }

        return Array.from(
            grid.querySelectorAll('.recipe-card')
        );
    }

    /**
     * Produces a simple alternative for plural searches.
     * This mirrors the lightweight behaviour used by PHP.
     */
    function getSearchTerms(value) {
        const trimmedValue =
            value.trim().toLowerCase();

        if (trimmedValue === '') {
            return [];
        }

        const terms = [trimmedValue];

        if (
            trimmedValue.length > 3 &&
            trimmedValue.endsWith('s')
        ) {
            terms.push(
                trimmedValue.slice(0, -1)
            );
        }

        return [...new Set(terms)];
    }

    /**
     * Filters the cards currently available on the page.
     *
     * Both the recipe title and description are checked so that the
     * live behaviour is consistent with the main PHP text search.
     */
    function applyLiveSearch() {
        if (!searchInput || !grid) {
            return;
        }

        const terms =
            getSearchTerms(searchInput.value);

        const cards = getCards();

        cards.forEach(function (card) {
            const title =
                (card.dataset.title || '').toLowerCase();

            const description =
                (card.dataset.description || '').toLowerCase();

            const searchableText =
                title + ' ' + description;

            const matches =
                terms.length === 0 ||
                terms.some(function (term) {
                    return searchableText.includes(term);
                });

            card.hidden = !matches;
        });

        const visibleCards = cards.filter(function (card) {
            return !card.hidden;
        });

        if (resultsCount) {
            const count = visibleCards.length;

            resultsCount.textContent =
                count +
                (count === 1
                    ? ' recipe found'
                    : ' recipes found');
        }
    }

    /**
     * Automatically submits the complete search form.
     *
     * Save both scroll positions first so that the refreshed page can
     * return the user to the same place.
     */
    function submitSearchForm() {
        sessionStorage.setItem(
            pageScrollStorageKey,
            String(window.scrollY)
        );

        sessionStorage.setItem(
            filterScrollStorageKey,
            String(form.scrollTop)
        );

        form.requestSubmit();
    }

    /*
     * Main title/description search remains live so typing does not
     * trigger a full page reload after every character.
     */
    if (searchInput) {
        searchInput.addEventListener('input', function () {
            clearTimeout(debounceTimer);

            debounceTimer = setTimeout(
                applyLiveSearch,
                150
            );
        });
    }

    /*
     * Select controls represent complete choices, so apply them
     * immediately when the user chooses a new value.
     */
    [
        sortSelect,
        difficultySelect,
        minRatingSelect
    ].forEach(function (control) {
        if (control) {
            control.addEventListener(
                'change',
                submitSearchForm
            );
        }
    });

    /*
     * Maximum time is a number field. Using change rather than input
     * waits until the user has finished editing before submitting.
     */
    if (maxTimeInput) {
        maxTimeInput.addEventListener(
            'change',
            submitSearchForm
        );
    }

    /*
     * Category selections narrow the search immediately. The complete
     * form is submitted, so all previously entered criteria are kept.
     */
    categoryCheckboxes.forEach(function (checkbox) {
        checkbox.addEventListener(
            'change',
            submitSearchForm
        );
    });

    /*
     * Allergen exclusions behave in the same way as category filters.
     */
    allergenCheckboxes.forEach(function (checkbox) {
        checkbox.addEventListener(
            'change',
            submitSearchForm
        );
    });
}