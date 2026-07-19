# 03. Secure Filesystem

Status: done, 2026-07-18.

## Цель

Спроектировать безопасный слой доступа к локальной документации, чтобы manager UI не мог читать файлы за пределами `mxlocdoc.docs_path`.

## Что сделать

- Реализован `PathResolver` для нормализации и проверки путей.
- Реализован `DocumentRepository` для чтения `.md` файлов.
- Реализован `AssetRepository` для отдачи картинок и разрешенных ассетов.
- Используется `realpath` для корня документации и каждого запрошенного файла.
- Запрещен выход за `docs_path`.
- Ограничены расширения `.md` и asset-файлов whitelist-ом.
- Проверяется max size до чтения файла.
- Возвращаются понятные ошибки для manager UI.

## Куда именно

- Сервисы:
  - `core/components/mxlocdoc/services/pathresolver.class.php`
  - `core/components/mxlocdoc/services/documentrepository.class.php`
  - `core/components/mxlocdoc/services/assetrepository.class.php`
- Processors:
  - `core/components/mxlocdoc/processors/mgr/document/get.class.php`
  - `core/components/mxlocdoc/processors/mgr/asset/get.class.php`
- HTTP entrypoint:
  - `assets/components/mxlocdoc/connector.php`

## Зачем

Пакет работает с локальной файловой системой из manager. Без строгой нормализации путей он может превратиться в произвольное чтение файлов сайта.

## Чеклист готовности

- [x] Любой относительный путь приводится к `realpath`.
- [x] Итоговый путь обязан начинаться с `realpath(mxlocdoc.docs_path)`.
- [x] `.md` читаются только через `DocumentRepository`.
- [x] Картинки и ассеты читаются только через `AssetRepository`.
- [x] Файлы больше `mxlocdoc.max_file_size` не читаются.
- [x] Ошибки различают: не задан `docs_path`, файл не найден, запрещенное расширение, выход за корень, файл слишком большой.
- [x] Добавлены processors `mgr/document/get` и `mgr/asset/get`.
- [x] Проверены `php -l` и CLI smoke со stub MODX.

## Риски и ограничения

- Симлинки внутри документации допустимы только если после `realpath` остаются внутри `docs_path`.
- Нельзя доверять путям из query string или manifest.
- Ошибки не должны раскрывать лишние системные пути за пределами разрешенной папки.
