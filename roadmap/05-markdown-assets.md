# 05. Markdown Assets

Status: done 2026-07-18.

## Цель

Безопасно отрисовать Markdown-документацию и корректно показать относительные картинки внутри MODX manager.

## Что сделать

- Выбран vendored Parsedown `1.8.0` как single-file renderer для PHP 7.4 / MODX 2 без Node/composer runtime.
- Raw HTML отключён через `Parsedown::setSafeMode(true)`.
- Поддержаны относительные ссылки между `.md` файлами через `data-mxlocdoc-path`.
- Изображения вида `![alt](./images/a.png)` переписываются на protected connector URL.
- Whitelist расширений asset-файлов остаётся в `AssetRepository` и настройке `mxlocdoc.allowed_asset_extensions`.
- Ошибки отсутствующих изображений не валят статью: processor возвращает `warnings`, HTML получает marker-класс, MODX log получает warning без абсолютного пути.

## Куда именно

- Renderer:
  - `core/components/mxlocdoc/services/markdownrenderer.class.php`
  - `core/components/mxlocdoc/vendor/parsedown/Parsedown.php`
- Asset service:
  - `core/components/mxlocdoc/services/assetrepository.class.php`
- Processor/connector:
  - `core/components/mxlocdoc/processors/mgr/document/get.class.php`
  - `core/components/mxlocdoc/processors/mgr/asset/get.class.php`
  - `assets/components/mxlocdoc/connector.php`
- Manager templates/assets остаются задачей `06-manager-ui`:
  - `core/components/mxlocdoc/templates/home.tpl`
  - `assets/components/mxlocdoc/js/mgr/mxlocdoc.js`
  - `assets/components/mxlocdoc/css/mgr/main.css`

## Зачем

Markdown должен быть удобен для разработчиков, но manager UI не должен выполнять произвольный HTML или отдавать произвольные файлы с диска.

## Чеклист готовности

- `.md` файл рендерится в HTML статьи.
- Raw HTML отключён через safe mode.
- Ссылки на другие `.md` размечаются для открытия внутри CMP без перезагрузки manager.
- Картинки отдаются через connector после проверки пути и расширения.
- Поддерживаются только расширения из `mxlocdoc.allowed_asset_extensions`, например `png,jpg,jpeg,gif,svg,webp`.
- Ошибки asset-загрузки возвращаются в `warnings`, размечаются в HTML и логируются без раскрытия абсолютных путей.

## Риски и ограничения

- SVG может содержать активный контент; если whitelist включает `svg`, нужна отдельная политика санитизации или строгая отдача как файл без inline-вставки.
- Markdown renderer не требует Node или frontend build.
- Ссылки на внешние URL отличаются от локальных путей и не прокидываются через filesystem layer.
- Parsedown safe mode не заменяет полноценную CSP/HTML sanitization policy для будущих расширений; в v1 raw HTML не включается.
