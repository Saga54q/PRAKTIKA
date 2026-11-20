/**
 * Логика каталога услуг: поиск, фильтрация по категориям и модальное окно подробностей.
 */
(function () {
    'use strict';

    const modal = document.getElementById('service-detail-modal');
    const grid = document.querySelector('[data-services-grid]');
    const form = document.querySelector('[data-catalog-form]');
    const searchInput = document.querySelector('[data-catalog-search]');
    const categoryInput = document.querySelector('[data-category-input]');
    const filterContainer = document.querySelector('[data-category-filters]');
    const countEl = document.querySelector('[data-catalog-count]');
    const resetLinks = document.querySelectorAll('[data-reset-filters]');

    let lastFocusedElement = null;
    let debounceTimer = null;
    let activeAbortController = null;

    // -------------------------------------------------------------------------
    // Модальное окно подробного описания услуги (работает и на главной, и в каталоге)
    // -------------------------------------------------------------------------
    function openModalFromCard(card) {
        if (!modal || !card) {
            return;
        }

        const titleEl = modal.querySelector('[data-modal-title]');
        const descEl = modal.querySelector('[data-modal-description]');
        const catEl = modal.querySelector('[data-modal-category]');
        const codeEl = modal.querySelector('[data-modal-code]');
        const imgEl = modal.querySelector('[data-modal-image]');
        const reqLink = modal.querySelector('[data-modal-request-link]');

        const id = card.getAttribute('data-service-id') || '';
        const title = card.getAttribute('data-service-title') || '';
        const fullDesc = card.getAttribute('data-service-full') || '';
        const category = card.getAttribute('data-service-category') || '';
        const code = card.getAttribute('data-service-code') || '';
        const image = card.getAttribute('data-service-image') || '';

        if (titleEl) titleEl.textContent = title;
        if (descEl) descEl.textContent = fullDesc;
        if (catEl) catEl.textContent = category;
        if (codeEl) codeEl.textContent = code;
        if (imgEl) {
            imgEl.setAttribute('src', image);
            imgEl.setAttribute('alt', title);
        }
        if (reqLink) {
            reqLink.setAttribute('href', 'request.php?service_id=' + encodeURIComponent(id));
        }

        lastFocusedElement = document.activeElement;
        modal.hidden = false;
        document.body.style.overflow = 'hidden';

        const closeBtn = modal.querySelector('[data-close-modal]');
        if (closeBtn) {
            closeBtn.focus();
        }
    }

    function closeModal() {
        if (!modal || modal.hidden) {
            return;
        }

        modal.hidden = true;
        document.body.style.overflow = '';
        if (lastFocusedElement && typeof lastFocusedElement.focus === 'function') {
            lastFocusedElement.focus();
        }
    }

    document.addEventListener('click', function (event) {
        const openBtn = event.target.closest('[data-open-service-modal]');
        if (openBtn) {
            const card = openBtn.closest('[data-service-card]');
            openModalFromCard(card);
            return;
        }

        if (modal && !modal.hidden) {
            if (event.target.closest('[data-close-modal]') || event.target === modal) {
                closeModal();
            }
        }
    });

    document.addEventListener('keydown', function (event) {
        if (event.key === 'Escape' && modal && !modal.hidden) {
            closeModal();
        }
    });

    // -------------------------------------------------------------------------
    // Поиск и фильтрация в каталоге
    // -------------------------------------------------------------------------
    if (!grid || !form || !searchInput || !categoryInput || !filterContainer) {
        return;
    }

    function buildServiceCardElement(service) {
        const article = document.createElement('article');
        article.className = 'service-card';
        article.id = 'service-' + service.id;
        article.setAttribute('data-service-card', '');
        article.setAttribute('data-service-id', String(service.id));
        article.setAttribute('data-service-code', service.code || '');
        article.setAttribute('data-service-category', service.category_name || '');
        article.setAttribute('data-service-title', service.title || '');
        article.setAttribute('data-service-full', service.full_description || '');
        article.setAttribute('data-service-image', service.image_path || '');

        const media = document.createElement('div');
        media.className = 'service-card-media';
        const img = document.createElement('img');
        img.src = service.image_path || '';
        img.alt = service.title || '';
        img.loading = 'lazy';
        img.width = 480;
        img.height = 280;
        media.appendChild(img);

        const body = document.createElement('div');
        body.className = 'service-card-body';

        const meta = document.createElement('div');
        meta.className = 'service-card-meta';

        const catBadge = document.createElement('span');
        catBadge.className = 'service-category-badge';
        catBadge.textContent = service.category_name || '';

        const codeSpan = document.createElement('span');
        codeSpan.className = 'service-code';
        codeSpan.textContent = service.code || '';

        meta.appendChild(catBadge);
        meta.appendChild(codeSpan);

        const title = document.createElement('h2');
        title.className = 'service-card-title';
        title.textContent = service.title || '';

        const desc = document.createElement('p');
        desc.className = 'service-card-desc';
        desc.textContent = service.short_description || '';

        body.appendChild(meta);
        body.appendChild(title);
        body.appendChild(desc);

        const footer = document.createElement('div');
        footer.className = 'service-card-footer';

        const detailsBtn = document.createElement('button');
        detailsBtn.type = 'button';
        detailsBtn.className = 'btn btn-outline btn-sm';
        detailsBtn.setAttribute('data-open-service-modal', '');
        detailsBtn.setAttribute('data-service-id', String(service.id));
        detailsBtn.textContent = 'Подробнее';

        const requestLink = document.createElement('a');
        requestLink.href = 'request.php?service_id=' + encodeURIComponent(String(service.id));
        requestLink.className = 'btn btn-primary btn-sm';
        requestLink.textContent = 'Оставить заявку';

        footer.appendChild(detailsBtn);
        footer.appendChild(requestLink);

        article.appendChild(media);
        article.appendChild(body);
        article.appendChild(footer);

        return article;
    }

    function buildEmptyStateElement() {
        const box = document.createElement('div');
        box.className = 'empty-state';
        box.setAttribute('data-empty-state', '');

        const title = document.createElement('h2');
        title.className = 'empty-state-title';
        title.textContent = 'Услуги по заданным критериям не найдены';

        const text = document.createElement('p');
        text.className = 'empty-state-text';
        text.textContent = 'Измените поисковый запрос или выберите другое направление оборудования, чтобы просмотреть доступные услуги.';

        const resetBtn = document.createElement('a');
        resetBtn.href = 'catalog.php';
        resetBtn.className = 'btn btn-secondary';
        resetBtn.textContent = 'Показать весь каталог';
        resetBtn.addEventListener('click', function (e) {
            e.preventDefault();
            resetAllFilters();
        });

        box.appendChild(title);
        box.appendChild(text);
        box.appendChild(resetBtn);
        return box;
    }

    function updateUiState(category, query, total) {
        categoryInput.value = category;

        const pills = filterContainer.querySelectorAll('[data-category-btn]');
        pills.forEach(function (pill) {
            const pillCat = pill.getAttribute('data-category-btn');
            const isActive = pillCat === category;
            pill.classList.toggle('is-active', isActive);
            pill.setAttribute('aria-selected', isActive ? 'true' : 'false');
        });

        if (countEl) {
            countEl.textContent = '';
            countEl.appendChild(document.createTextNode('Найдено услуг: '));
            const strong = document.createElement('strong');
            strong.textContent = String(total);
            countEl.appendChild(strong);
        }

        const hasActiveFilter = category !== 'all' || query !== '';
        resetLinks.forEach(function (link) {
            link.classList.toggle('is-hidden', !hasActiveFilter);
        });
        if (searchClearBtn) {
            searchClearBtn.classList.toggle('is-hidden', query === '');
        }

        const params = new URLSearchParams();
        if (category !== 'all') {
            params.set('category', category);
        }
        if (query !== '') {
            params.set('q', query);
        }
        const qs = params.toString();
        const nextUrl = qs ? 'catalog.php?' + qs : 'catalog.php';
        window.history.replaceState({}, '', nextUrl);
    }

    function fetchFilteredServices(category, query) {
        if (activeAbortController) {
            activeAbortController.abort();
        }
        activeAbortController = new AbortController();

        const params = new URLSearchParams();
        params.set('format', 'json');
        if (category !== 'all') {
            params.set('category', category);
        }
        if (query !== '') {
            params.set('q', query);
        }

        fetch('catalog.php?' + params.toString(), {
            headers: { Accept: 'application/json' },
            signal: activeAbortController.signal,
        })
            .then(function (response) {
                if (!response.ok) {
                    throw new Error('HTTP ' + response.status);
                }
                return response.json();
            })
            .then(function (payload) {
                if (!payload || payload.status !== 'ok') {
                    return;
                }

                grid.textContent = '';
                if (!Array.isArray(payload.services) || payload.services.length === 0) {
                    grid.appendChild(buildEmptyStateElement());
                } else {
                    const fragment = document.createDocumentFragment();
                    payload.services.forEach(function (service) {
                        fragment.appendChild(buildServiceCardElement(service));
                    });
                    grid.appendChild(fragment);
                }

                updateUiState(payload.activeCategory || 'all', payload.searchQuery || '', payload.total || 0);
            })
            .catch(function (error) {
                if (error && error.name === 'AbortError') {
                    return;
                }
                form.submit();
            });
    }

    function resetAllFilters() {
        searchInput.value = '';
        fetchFilteredServices('all', '');
    }

    filterContainer.addEventListener('click', function (event) {
        const pill = event.target.closest('[data-category-btn]');
        if (!pill) {
            return;
        }
        event.preventDefault();
        const category = pill.getAttribute('data-category-btn') || 'all';
        const query = searchInput.value.trim();
        fetchFilteredServices(category, query);
    });

    form.addEventListener('submit', function (event) {
        event.preventDefault();
        const category = categoryInput.value || 'all';
        const query = searchInput.value.trim();
        fetchFilteredServices(category, query);
    });

    searchInput.addEventListener('input', function () {
        clearTimeout(debounceTimer);
        debounceTimer = setTimeout(function () {
            const category = categoryInput.value || 'all';
            const query = searchInput.value.trim();
            fetchFilteredServices(category, query);
        }, 250);
    });

    const searchClearBtn = document.querySelector('[data-search-clear]');
    if (searchClearBtn) {
        searchClearBtn.addEventListener('click', function () {
            searchInput.value = '';
            fetchFilteredServices(categoryInput.value || 'all', '');
            searchInput.focus();
        });
    }

    resetLinks.forEach(function (link) {
        link.addEventListener('click', function (event) {
            event.preventDefault();
            resetAllFilters();
        });
    });
})();
