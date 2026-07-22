var MxLocDoc = window.MxLocDoc || {};

Ext.onReady(function () {
    var root = document.getElementById('mxlocdoc-app');

    if (!root || !MxLocDoc.config || !MxLocDoc.config.connector_url) {
        return;
    }

    var state = {
        nav: null,
        activePath: '',
        documents: {},
        flatItems: [],
        collapsedNav: loadCollapsedNav(),
        language: selectInitialLanguage()
    };
    var lexicon = MxLocDoc.config.lexicon || {};
    var defaultFile = MxLocDoc.config.default_file || 'README.md';
    var availableLanguages = MxLocDoc.config.languages || [];

    var ui = {
        shell: root.querySelector('[data-mxlocdoc-shell]'),
        sidebar: root.querySelector('[data-mxlocdoc-sidebar]'),
        sidebarOpen: root.querySelector('[data-mxlocdoc-sidebar-open]'),
        sidebarClose: root.querySelector('[data-mxlocdoc-sidebar-close]'),
        language: root.querySelector('[data-mxlocdoc-language]'),
        languageSelect: root.querySelector('[data-mxlocdoc-language-select]'),
        nav: root.querySelector('[data-mxlocdoc-nav]'),
        search: root.querySelector('[data-mxlocdoc-search]'),
        searchResults: root.querySelector('[data-mxlocdoc-search-results]'),
        state: root.querySelector('[data-mxlocdoc-state]'),
        documentPanel: root.querySelector('.mxlocdoc-document-panel'),
        breadcrumbs: root.querySelector('[data-mxlocdoc-breadcrumbs]'),
        article: root.querySelector('[data-mxlocdoc-article]'),
        warnings: root.querySelector('[data-mxlocdoc-warnings]'),
        toc: root.querySelector('[data-mxlocdoc-toc]'),
        tocList: root.querySelector('[data-mxlocdoc-toc-list]')
    };
    var searchTimer = null;
    var HEADING_SELECTOR = 'h1, h2, h3, h4, h5, h6';

    function request(action, params, callback) {
        var xhr = new XMLHttpRequest();
        var data = {action: action};
        var body;

        if (window.MODx && MODx.siteId) {
            data.HTTP_MODAUTH = MODx.siteId;
        }
        if (state.language) {
            data.language = state.language;
        }
        Object.keys(params || {}).forEach(function (key) {
            data[key] = params[key];
        });
        body = buildQuery(data);

        xhr.open('POST', MxLocDoc.config.connector_url, true);
        xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded; charset=UTF-8');
        xhr.onreadystatechange = function () {
            var response;

            if (xhr.readyState !== 4) {
                return;
            }

            try {
                response = JSON.parse(xhr.responseText || '{}');
            } catch (error) {
                callback({success: false, message: text('invalid_json', 'Invalid JSON response')});
                return;
            }

            callback(response);
        };
        xhr.send(body);
    }

    function text(key, fallback) {
        return lexicon[key] || fallback || '';
    }

    function hydrateLexicon() {
        hydrateAttribute('data-mxlocdoc-text', function (element, value) {
            element.textContent = value;
        });
        hydrateAttribute('data-mxlocdoc-title', function (element, value) {
            element.setAttribute('title', value);
        });
        hydrateAttribute('data-mxlocdoc-aria', function (element, value) {
            element.setAttribute('aria-label', value);
        });
        hydrateAttribute('data-mxlocdoc-placeholder', function (element, value) {
            element.setAttribute('placeholder', value);
        });
    }

    function hydrateAttribute(attribute, setter) {
        var elements = root.querySelectorAll('[' + attribute + ']');
        Array.prototype.forEach.call(elements, function (element) {
            var key = element.getAttribute(attribute);
            var value = text(key, '');

            if (value) {
                setter(element, value);
            }
        });
    }

    function buildQuery(data) {
        var parts = [];
        Object.keys(data).forEach(function (key) {
            if (data[key] !== undefined && data[key] !== null) {
                parts.push(encodeURIComponent(key) + '=' + encodeURIComponent(data[key]));
            }
        });
        return parts.join('&');
    }

    function escapeHtml(value) {
        return String(value || '')
            .replace(/&/g, '&amp;')
            .replace(/</g, '&lt;')
            .replace(/>/g, '&gt;')
            .replace(/"/g, '&quot;')
            .replace(/'/g, '&#039;');
    }

    function setState(message, type) {
        ui.state.textContent = message || '';
        ui.state.className = 'mxlocdoc-state' + (type ? ' mxlocdoc-state--' + type : '');
        ui.state.hidden = !message;
    }

    function flattenItems(items, level) {
        (items || []).forEach(function (item) {
            if (item.path) {
                state.flatItems.push({
                    title: item.title || item.path,
                    path: item.path,
                    level: level
                });
            }
            flattenItems(item.children || [], level + 1);
        });
    }

    function loadCollapsedNav() {
        var stored;

        try {
            stored = window.localStorage ? window.localStorage.getItem('mxlocdoc.collapsedNav') : '';
            return stored ? JSON.parse(stored) : {};
        } catch (error) {
            return {};
        }
    }

    function saveCollapsedNav() {
        try {
            if (window.localStorage) {
                window.localStorage.setItem('mxlocdoc.collapsedNav', JSON.stringify(state.collapsedNav));
            }
        } catch (error) {}
    }

    function selectInitialLanguage() {
        var stored = '';
        var languages = MxLocDoc.config && MxLocDoc.config.languages ? MxLocDoc.config.languages : [];
        var configured = MxLocDoc.config ? MxLocDoc.config.language || '' : '';

        try {
            stored = window.localStorage ? window.localStorage.getItem('mxlocdoc.language') || '' : '';
        } catch (error) {}

        return findLanguage(stored, languages) || findLanguage(configured, languages) || (languages[0] ? languages[0].code : '');
    }

    function findLanguage(language, languages) {
        var normalized = String(language || '').toLowerCase().replace('_', '-');
        var found = '';

        (languages || []).some(function (item) {
            if (item.code === normalized) {
                found = item.code;
                return true;
            }
            return false;
        });

        return found;
    }

    function navKey(item, level, index, parentKey) {
        var value = item.path || item.title || String(index);

        return [parentKey || 'root', level, value].join('|');
    }

    function setNavCollapsed(item, collapsed, persist) {
        var key = item.getAttribute('data-nav-key');
        var toggle = item.querySelector(':scope > .mxlocdoc-nav__toggle');

        item.classList.toggle('is-collapsed', collapsed);
        if (toggle) {
            toggle.setAttribute('aria-expanded', collapsed ? 'false' : 'true');
        }
        if (persist && key) {
            if (collapsed) {
                state.collapsedNav[key] = true;
            } else {
                delete state.collapsedNav[key];
            }
            saveCollapsedNav();
        }
    }

    function renderNavigation(items, level, parentKey) {
        var list = document.createElement('ul');
        list.className = level === 0 ? 'mxlocdoc-nav__list' : 'mxlocdoc-nav__children';

        (items || []).forEach(function (item, index) {
            var hasChildren = !!(item.children && item.children.length);
            var key = navKey(item, level, index, parentKey);
            var li = document.createElement('li');
            var node;
            var toggle;

            li.className = 'mxlocdoc-nav__item';
            li.classList.add('mxlocdoc-nav__item--level-' + Math.min(level, 4));
            li.setAttribute('data-nav-key', key);
            if (hasChildren) {
                li.classList.add('has-children');
                if (state.collapsedNav[key]) {
                    li.classList.add('is-collapsed');
                }
            }
            if (item.path) {
                node = document.createElement('button');
                node.type = 'button';
                node.className = 'mxlocdoc-nav__link';
                node.classList.add('mxlocdoc-nav__node--level-' + Math.min(level, 4));
                node.dataset.path = item.path;
                node.textContent = item.title || item.path;
                node.title = item.path;
                li.dataset.search = ((item.title || '') + ' ' + item.path).toLowerCase();
                node.addEventListener('click', function () {
                    loadDocument(item.path, true);
                    closeSidebar();
                });
            } else {
                node = document.createElement('div');
                node.className = 'mxlocdoc-nav__section';
                node.classList.add('mxlocdoc-nav__node--level-' + Math.min(level, 4));
                node.textContent = item.title || '';
                li.dataset.search = String(item.title || '').toLowerCase();
            }

            if (hasChildren) {
                toggle = document.createElement('button');
                toggle.type = 'button';
                toggle.className = 'mxlocdoc-nav__toggle';
                toggle.setAttribute('aria-label', 'Toggle section');
                toggle.setAttribute('aria-expanded', li.classList.contains('is-collapsed') ? 'false' : 'true');
                toggle.addEventListener('click', function (event) {
                    event.preventDefault();
                    event.stopPropagation();
                    setNavCollapsed(li, !li.classList.contains('is-collapsed'), true);
                });
                li.appendChild(toggle);
            }
            li.appendChild(node);
            if (hasChildren) {
                li.appendChild(renderNavigation(item.children, level + 1, key));
            }
            list.appendChild(li);
        });

        return list;
    }

    function updateActiveNav(path) {
        var links = ui.nav.querySelectorAll('[data-path]');
        Array.prototype.forEach.call(links, function (link) {
            link.classList.toggle('is-active', link.dataset.path === path);
        });

        var items = ui.nav.querySelectorAll('.mxlocdoc-nav__item');
        Array.prototype.forEach.call(items, function (item) {
            var isActiveBranch = !!item.querySelector('.mxlocdoc-nav__link.is-active');

            item.classList.toggle('is-active-branch', isActiveBranch);
            if (isActiveBranch && item.classList.contains('is-collapsed')) {
                setNavCollapsed(item, false, false);
            }
        });
    }

    function firstDocument(items) {
        var found = '';

        function walk(nodes) {
            (nodes || []).some(function (item) {
                if (item.path) {
                    found = item.path;
                    return true;
                }
                return walk(item.children || []);
            });
            return !!found;
        }

        walk(items);
        return found;
    }

    function loadNavigation() {
        setState(text('loading_navigation', 'Loading navigation...'), 'loading');
        request('mgr/navigation/get', {}, function (response) {
            var object = response.object || {};
            var items = object.items || [];
            var requestedPath = getHashPath() || state.activePath || firstDocument(items);
            var initialPath;

            if (!response.success) {
                setState(response.message || text('navigation_error', 'Could not load navigation.'), 'error');
                return;
            }

            state.nav = object;
            if (object.language) {
                state.language = object.language;
            }
            if (object.languages) {
                availableLanguages = object.languages;
                renderLanguageSelector();
            }
            state.flatItems = [];
            flattenItems(items, 0);
            initialPath = findDocumentPath(requestedPath) || firstDocument(items);
            ui.nav.innerHTML = '';
            ui.nav.appendChild(renderNavigation(items, 0, ''));

            if (!items.length) {
                setState(text('documents_empty', 'No documents found.'), 'empty');
                return;
            }

            if (initialPath) {
                loadDocument(initialPath, initialPath !== requestedPath);
            }
        });
    }

    function loadDocument(path, pushHash, anchor) {
        if (!path) {
            return;
        }

        state.activePath = path;
        updateActiveNav(path);
        setState(text('loading_document', 'Loading document...'), 'loading');
        ui.article.innerHTML = '';
        ui.warnings.innerHTML = '';
        ui.breadcrumbs.innerHTML = '';
        ui.tocList.innerHTML = '';
        ui.toc.hidden = true;

        request('mgr/document/get', {path: path}, function (response) {
            var object = response.object || {};

            if (!response.success) {
                setState(response.message || text('document_error', 'Could not load document.'), 'error');
                return;
            }

            state.documents[path] = object;
            if (pushHash) {
                setHashPath(path);
            }
            setState('', '');
            renderDocument(object);
            resetDocumentScroll();
            if (anchor) {
                scrollToAnchor(anchor);
            }
        });
    }

    function renderDocument(documentData) {
        renderBreadcrumbs(documentData.path || state.activePath);
        ui.article.innerHTML = documentData.html || '';
        authorizeAssetUrls();
        wireArticleLinks();
        prepareHeadings();
        renderWarnings(documentData.warnings || []);
    }

    function authorizeAssetUrls() {
        var auth = window.MODx && MODx.siteId ? MODx.siteId : '';
        if (!auth) {
            return;
        }

        var images = ui.article.querySelectorAll('img[data-mxlocdoc-asset-path]');
        Array.prototype.forEach.call(images, function (image) {
            var src = image.getAttribute('src') || '';
            if (src.indexOf('HTTP_MODAUTH=') !== -1) {
                if (!state.language || src.indexOf('language=') !== -1) {
                    return;
                }
            }

            if (src.indexOf('HTTP_MODAUTH=') === -1) {
                src += (src.indexOf('?') === -1 ? '?' : '&') + 'HTTP_MODAUTH=' + encodeURIComponent(auth);
            }
            if (state.language && src.indexOf('language=') === -1) {
                src += (src.indexOf('?') === -1 ? '?' : '&') + 'language=' + encodeURIComponent(state.language);
            }
            image.setAttribute('src', src);
        });
    }

    function renderBreadcrumbs(path) {
        var segments = String(path || '').split('/').filter(Boolean);
        var currentPath = String(path || '');
        var rootPath = findDocumentPath(defaultFile) || firstDocument(state.nav ? state.nav.items : []);

        ui.breadcrumbs.innerHTML = '';
        appendBreadcrumb(text('documentation', 'Documentation'), rootPath, currentPath === rootPath);

        if (currentPath === rootPath) {
            return;
        }

        segments.forEach(function (segment, index) {
            var isLast = index === segments.length - 1;
            var targetPath = isLast ? currentPath : findDirectoryIndexPath(segments.slice(0, index + 1).join('/'));

            appendBreadcrumbSeparator();
            appendBreadcrumb(segment, targetPath, isLast || !targetPath);
        });
    }

    function appendBreadcrumb(label, path, isCurrent) {
        var node;

        if (path && !isCurrent) {
            node = document.createElement('button');
            node.type = 'button';
            node.className = 'mxlocdoc-breadcrumbs__link';
            node.dataset.path = path;
            node.addEventListener('click', function () {
                loadDocument(path, true);
            });
        } else {
            node = document.createElement('span');
            node.className = 'mxlocdoc-breadcrumbs__current';
        }

        node.textContent = label;
        ui.breadcrumbs.appendChild(node);
    }

    function appendBreadcrumbSeparator() {
        var separator = document.createElement('span');

        separator.className = 'mxlocdoc-breadcrumbs__sep';
        separator.textContent = '/';
        ui.breadcrumbs.appendChild(separator);
    }

    function findDirectoryIndexPath(directory) {
        var normalized = String(directory || '').replace(/^\/+|\/+$/g, '');
        var candidate = normalized ? normalized + '/' + defaultFile : defaultFile;

        return findDocumentPath(candidate);
    }

    function findDocumentPath(path) {
        var normalized = String(path || '').replace(/^\/+/, '');
        var found = '';

        state.flatItems.some(function (item) {
            if (item.path === normalized) {
                found = item.path;
                return true;
            }
            return false;
        });

        return found;
    }

    function wireArticleLinks() {
        var links = ui.article.querySelectorAll('a[data-mxlocdoc-path]');
        Array.prototype.forEach.call(links, function (link) {
            link.addEventListener('click', function (event) {
                event.preventDefault();
                loadDocument(
                    link.getAttribute('data-mxlocdoc-path'),
                    true,
                    link.getAttribute('data-mxlocdoc-anchor')
                );
            });
        });

        // Якоря внутри текущего документа. Ссылки на другие документы рендерер тоже
        // отдаёт как href="#<path>", поэтому они исключены по data-mxlocdoc-path.
        var anchors = ui.article.querySelectorAll('a[href^="#"]:not([data-mxlocdoc-path])');
        Array.prototype.forEach.call(anchors, function (link) {
            link.addEventListener('click', function (event) {
                event.preventDefault();
                scrollToAnchor(link.getAttribute('href'));
            });
        });
    }

    function prepareHeadings() {
        var headings = ui.article.querySelectorAll(HEADING_SELECTOR);
        var tocHtml = [];
        var seen = {};

        Array.prototype.forEach.call(headings, function (heading, index) {
            var id = uniqueHeadingId(heading.id || makeHeadingId(heading.textContent, index), seen);
            heading.id = id;
            // id нужен всем уровням — на них могут ссылаться якоря; в оглавление
            // по-прежнему попадают только h1-h3.
            if (!/^h[123]$/i.test(heading.tagName)) {
                return;
            }
            tocHtml.push(
                '<button type="button" class="mxlocdoc-toc__link mxlocdoc-toc__link--' + heading.tagName.toLowerCase() +
                '" data-target="' + escapeHtml(id) + '">' + escapeHtml(heading.textContent) + '</button>'
            );
        });

        ui.tocList.innerHTML = tocHtml.join('');
        ui.toc.hidden = tocHtml.length === 0;
        wireTocLinks();
    }

    function wireTocLinks() {
        var links = ui.tocList.querySelectorAll('[data-target]');
        Array.prototype.forEach.call(links, function (link) {
            link.addEventListener('click', function (event) {
                var target = document.getElementById(link.getAttribute('data-target'));

                event.preventDefault();
                scrollDocumentTo(target);
            });
        });
    }

    function scrollDocumentTo(target) {
        if (!target) {
            return;
        }

        if (ui.documentPanel) {
            var panelBox = ui.documentPanel.getBoundingClientRect();
            var targetBox = target.getBoundingClientRect();
            ui.documentPanel.scrollTop += targetBox.top - panelBox.top - 12;
            return;
        }

        if (target.scrollIntoView) {
            target.scrollIntoView({block: 'start', inline: 'nearest'});
        }
    }

    function resetDocumentScroll() {
        if (ui.documentPanel) {
            ui.documentPanel.scrollTop = 0;
        }
    }

    // Якорь приходит либо из href (DOMDocument percent-кодирует кириллицу при
    // сохранении HTML), либо из data-mxlocdoc-anchor, где остаётся как есть.
    function decodeAnchor(anchor) {
        var value = String(anchor || '').replace(/^#+/, '');

        try {
            return decodeURIComponent(value);
        } catch (error) {
            return value;
        }
    }

    function findAnchorTarget(anchor) {
        var raw = decodeAnchor(anchor);
        var slug = slugifyHeading(raw);
        var headings = ui.article.querySelectorAll(HEADING_SELECTOR);
        var byId = null;
        var byText = null;

        if (!raw) {
            return null;
        }

        Array.prototype.forEach.call(headings, function (heading) {
            if (byId) {
                return;
            }
            if (heading.id === raw || (slug && heading.id === 'mxlocdoc-heading-' + slug)) {
                byId = heading;
                return;
            }
            if (!byText && slug && slugifyHeading(heading.textContent) === slug) {
                byText = heading;
            }
        });

        return byId || byText;
    }

    function scrollToAnchor(anchor) {
        scrollDocumentTo(findAnchorTarget(anchor));
    }
    function slugifyHeading(value) {
        return String(value || '')
            .toLowerCase()
            .replace(/[^a-z0-9а-яё]+/gi, '-')
            .replace(/^-+|-+$/g, '');
    }

    function makeHeadingId(text, index) {
        return 'mxlocdoc-heading-' + (slugifyHeading(text) || index);
    }

    function uniqueHeadingId(id, seen) {
        var base = id;
        var counter = 2;

        while (seen[id]) {
            id = base + '-' + counter;
            counter++;
        }
        seen[id] = true;

        return id;
    }

    function renderWarnings(warnings) {
        if (!warnings.length) {
            ui.warnings.innerHTML = '';
            return;
        }

        ui.warnings.innerHTML = warnings.map(function (warning) {
            return '<div class="mxlocdoc-warning">' +
                '<strong>' + escapeHtml(warning.type || 'warning') + '</strong>: ' +
                escapeHtml(warning.path || '') +
                (warning.code ? ' (' + escapeHtml(warning.code) + ')' : '') +
                '</div>';
        }).join('');
    }

    function getHashPath() {
        var hash = window.location.hash || '';
        try {
            return hash.indexOf('#doc=') === 0 ? decodeURIComponent(hash.substring(5)) : '';
        } catch (error) {
            return '';
        }
    }

    function setHashPath(path) {
        var next = '#doc=' + encodeURIComponent(path);
        if (window.location.hash !== next) {
            window.location.hash = next;
        }
    }

    function openSidebar() {
        ui.shell.classList.add('is-sidebar-open');
    }

    function closeSidebar() {
        ui.shell.classList.remove('is-sidebar-open');
    }

    if (ui.sidebarOpen) {
        ui.sidebarOpen.addEventListener('click', openSidebar);
    }
    if (ui.sidebarClose) {
        ui.sidebarClose.addEventListener('click', closeSidebar);
    }
    if (ui.search) {
        ui.search.addEventListener('input', handleSearchInput);
    }
    if (ui.languageSelect) {
        ui.languageSelect.addEventListener('change', function () {
            state.language = ui.languageSelect.value;
            try {
                if (window.localStorage) {
                    window.localStorage.setItem('mxlocdoc.language', state.language);
                }
            } catch (error) {}
            renderSearchResults([]);
            loadNavigation();
        });
    }
    window.addEventListener('hashchange', function () {
        var path = getHashPath();
        if (path && path !== state.activePath) {
            loadDocument(path, false);
        }
    });

    renderLanguageSelector();
    hydrateLexicon();
    root.classList.add('mxlocdoc-ready');
    loadNavigation();

    function renderLanguageSelector() {
        if (!ui.language || !ui.languageSelect) {
            return;
        }

        ui.language.hidden = availableLanguages.length <= 1;
        ui.languageSelect.innerHTML = '';
        availableLanguages.forEach(function (language) {
            var option = document.createElement('option');

            option.value = language.code;
            option.textContent = language.label || language.code.toUpperCase();
            option.selected = language.code === state.language;
            ui.languageSelect.appendChild(option);
        });
    }

    function handleSearchInput() {
        var query = String(ui.search.value || '').trim();

        if (searchTimer) {
            window.clearTimeout(searchTimer);
        }
        if (query.length < 2) {
            renderSearchResults([]);
            return;
        }

        searchTimer = window.setTimeout(function () {
            runSearch(query);
        }, 220);
    }

    function runSearch(query) {
        request('mgr/search', {query: query, limit: 12}, function (response) {
            var object = response.object || {};

            if (!response.success) {
                renderSearchError(response.message || text('search_error', 'Search failed.'));
                return;
            }

            renderSearchResults(object.items || []);
        });
    }

    function renderSearchResults(items) {
        if (!ui.searchResults) {
            return;
        }

        if (!items.length) {
            var query = ui.search ? String(ui.search.value || '').trim() : '';
            ui.searchResults.innerHTML = query.length >= 2
                ? '<div class="mxlocdoc-search-result mxlocdoc-search-result--empty">' + escapeHtml(text('search_empty', 'No results found.')) + '</div>'
                : '';
            ui.searchResults.hidden = query.length < 2;
            return;
        }

        ui.searchResults.innerHTML = items.map(function (item) {
            return '<button type="button" class="mxlocdoc-search-result" data-path="' + escapeHtml(item.path) + '">' +
                '<span class="mxlocdoc-search-result__title">' + escapeHtml(item.title || item.path) + '</span>' +
                '<span class="mxlocdoc-search-result__path">' + escapeHtml(item.path) + '</span>' +
                '<span class="mxlocdoc-search-result__snippet">' + escapeHtml(item.snippet || '') + '</span>' +
                '</button>';
        }).join('');
        ui.searchResults.hidden = false;

        Array.prototype.forEach.call(ui.searchResults.querySelectorAll('[data-path]'), function (button) {
            button.addEventListener('click', function () {
                loadDocument(button.getAttribute('data-path'), true);
                ui.searchResults.hidden = true;
                ui.search.value = '';
            });
        });
    }

    function renderSearchError(message) {
        if (!ui.searchResults) {
            return;
        }

        ui.searchResults.innerHTML = '<div class="mxlocdoc-search-result mxlocdoc-search-result--empty">' + escapeHtml(message) + '</div>';
        ui.searchResults.hidden = false;
    }
});
