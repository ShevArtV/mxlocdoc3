# 06. Manager UI

Status: done locally on 2026-07-18. Live MODX manager browser check is deferred to `08-hostland-stand` because this session has no Chrome DevTools MCP tools.

## Цель

Сделать manager CMP похожим на внутренний docs-сайт: с навигацией, поиском, хлебными крошками, статьей и оглавлением.

## Что сделать

- Реализовать layout: sidebar, top search, breadcrumbs, article, headings/toc.
- Использовать vanilla JS/CSS без Vue, Node, Vite и отдельного build-step.
- Сделать responsive-поведение для узких экранов manager.
- Обработать длинные названия файлов, заголовков и путей без переполнений.
- Проверить UI в MODX manager через Chrome MCP/browser check.

## Куда именно

- Manager controller:
  - `core/components/mxlocdoc/controllers/index.class.php`
- Template:
  - `core/components/mxlocdoc/templates/home.tpl`
- Assets:
  - `assets/components/mxlocdoc/js/mgr/mxlocdoc.js`
  - `assets/components/mxlocdoc/css/mgr/main.css`
- Processors:
  - `core/components/mxlocdoc/processors/mgr/navigation/get.class.php`
  - `core/components/mxlocdoc/processors/mgr/document/get.class.php`
  - `core/components/mxlocdoc/processors/mgr/search.class.php` (next step)

## Зачем

Ценность пакета не в списке файлов, а в быстром чтении проектной документации из manager. Docs-like UI снижает трение для администраторов и разработчиков.

## Чеклист готовности

- [x] Sidebar показывает manifest/fallback-навигацию.
- [x] Top search доступен сразу после открытия CMP: в шаге 06 фильтрует навигацию, full-text идет в шаге 07.
- [x] Breadcrumbs отражают текущий путь документа.
- [x] Article area рассчитан на заголовки, списки, code blocks, таблицы и изображения из server-side Markdown renderer.
- [x] TOC строится по headings текущей статьи.
- [x] Длинные строки и названия не ломают layout за счет `min-width: 0`, `overflow-wrap: anywhere`, scroll для `pre`/`table`.
- [ ] UI проверен в manager через Chrome MCP/browser check: перенесено на `08-hostland-stand`.

## Риски и ограничения

- MODX 2 manager может иметь старые CSS/JS ограничения, поэтому UI должен быть простым и изолированным.
- Нельзя завязываться на npm build artifacts.
- Responsive нужен для manager viewport, но v1 не обязан быть полноценным публичным mobile docs-сайтом.
