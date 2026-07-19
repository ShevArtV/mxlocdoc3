# mxLocDoc Roadmap

mxLocDoc - легкий MODX 2 extra-пакет для просмотра локальной проектной документации в MODX manager. Документация хранится в Markdown-файлах на диске, путь к корню документации задается системной настройкой, а manager UI должен ощущаться как docs-сайт внутри админки, а не как файловый браузер.

## Ключевые решения

- Платформа: MODX 2; следующий отдельный этап — порт на MODX 3.
- Сборщик будущего пакета: `modxbuilder`.
- Интерфейс: lightweight manager CMP на vanilla JS/CSS, без VitePress, Node, Vue, Vite и отдельного фронтенд-билда.
- Источник документации: локальная папка из системной настройки `mxlocdoc.docs_path`; если внутри есть языковые подпапки (`en`, `ru`), manager UI показывает переключатель языка.
- Навигация: manifest `_sidebar.json` или `mxlocdoc.json` как основной способ, fallback по файлам.
- Контент: Markdown-рендер с безопасной обработкой HTML.
- Картинки: относительные изображения из `.md` отдаются через защищенный connector.
- Поиск: processor по `.md` с кешем в `core/cache/mxlocdoc` для v1; очистка через MODX cache clear.

## Этапы

- [00. Preparation](00-preparation.md) - done.
- [01. Package Skeleton](01-package-skeleton.md) - done.
- [02. System Settings](02-system-settings.md) - done.
- [03. Secure Filesystem](03-secure-filesystem.md) - done.
- [04. Navigation](04-navigation.md) - done.
- [05. Markdown Assets](05-markdown-assets.md) - done.
- [06. Manager UI](06-manager-ui.md) - done.
- [07. Search](07-search.md) - done.
- [08. Hostland Stand](08-hostland-stand.md) - done.
- [09. Release](09-release.md) - done; `0.1.0-pl` transport built.
- [10. MODX 3 Port](10-modx3-port.md) - done; `2.0.0-pl` transport built and verified on a MODX 3 stand.

## MVP-граница

В v1 должны войти:

- системные настройки пакета;
- manifest-навигация;
- fallback-навигация по файлам;
- Markdown-рендер;
- картинки из Markdown через защищенный connector;
- поиск по документации;
- manager UI в стиле docs-сайта;
- transport-сборка через `modxbuilder`.

## Out of Scope v1

В v1 не входят:

- редактирование Markdown из MODX manager;
- DB-индекс поиска;
- публичный фронтовый вывод документации;
- VitePress, Node, Vue, Vite или отдельный frontend build pipeline.
