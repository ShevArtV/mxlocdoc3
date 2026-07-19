# 02. System Settings

Status: done, 2026-07-18.

## Цель

Зафиксировать системные настройки, через которые администратор MODX будет управлять источником документации, навигацией, поиском и ограничениями безопасности.

## Что сделать

- Добавить настройку `mxlocdoc.docs_path`.
- Добавить настройку `mxlocdoc.default_file`.
- Добавить настройку `mxlocdoc.nav_file`.
- Добавить настройку `mxlocdoc.search_enabled`.
- Добавить настройку `mxlocdoc.cache_ttl`.
- Добавить настройку `mxlocdoc.max_file_size`.
- Добавить настройку `mxlocdoc.allowed_asset_extensions`.

## Куда именно

- Будущий transport config:
  - `modxbuilder/mxlocdoc/build/data/transport.settings.php`
- Будущие лексиконы:
  - `core/components/mxlocdoc/lexicon/ru/setting.inc.php`
- Runtime-чтение настроек:
  - `core/components/mxlocdoc/model/mxlocdoc/mxlocdoc.class.php`
  - `core/components/mxlocdoc/services/PathResolver.php`
  - `core/components/mxlocdoc/services/DocumentRepository.php`
  - `core/components/mxlocdoc/services/AssetRepository.php`

## Зачем

Настройки отделяют пакет от конкретного проекта: один и тот же transport можно поставить на разные сайты и указать разные папки документации без правки кода.

## Чеклист готовности

- `mxlocdoc.docs_path` задает корневую папку `.md` документации.
- `mxlocdoc.default_file` определяет файл по умолчанию, например `README.md`.
- `mxlocdoc.nav_file` позволяет выбрать `_sidebar.json` или `mxlocdoc.json`.
- `mxlocdoc.search_enabled` включает или отключает поиск.
- `mxlocdoc.cache_ttl` управляет кешем navigation/search для v1.
- `mxlocdoc.max_file_size` ограничивает чтение больших файлов.
- `mxlocdoc.allowed_asset_extensions` содержит whitelist форматов изображений и ассетов.
- Лексиконы настроек вынесены в `core/components/mxlocdoc/lexicon/ru/setting.inc.php`.
- Runtime-сервис `mxLocDoc` читает настройки в единый `$config` для следующих этапов.

## Риски и ограничения

- `docs_path` нельзя трактовать как URL: это локальный filesystem path.
- Пустой или неверный `docs_path` должен давать понятную ошибку в manager UI.
- Слишком широкие `allowed_asset_extensions` повышают риск отдачи нежелательных файлов через connector.
- Проверка `realpath`, выхода за корень и размера файлов не входит в этот этап; это задача `03-secure-filesystem`.
