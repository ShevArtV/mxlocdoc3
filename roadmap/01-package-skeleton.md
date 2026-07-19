# 01. Package Skeleton

Status: done, 2026-07-18.

## Цель

Создать минимальный каркас MODX 2 transport-пакета mxLocDoc без бизнес-логики Markdown, navigation, assets и search.

## Что сделать

- Создать структуру `core/components/mxlocdoc/`.
- Создать структуру `assets/components/mxlocdoc/`.
- Создать структуру `modxbuilder/mxlocdoc/`.
- Добавить manager controller, service shell, connector, lexicon и package docs.
- Скопировать старый `modxbuilder` из Sendit и перенастроить его под `mxlocdoc`.

## Куда именно

- Core-код:
  - `core/components/mxlocdoc/controllers/index.class.php`
  - `core/components/mxlocdoc/processors/mgr/`
  - `core/components/mxlocdoc/model/mxlocdoc/mxlocdoc.class.php`
  - `core/components/mxlocdoc/services/`
  - `core/components/mxlocdoc/templates/home.tpl`
  - `core/components/mxlocdoc/lexicon/ru/`
  - `core/components/mxlocdoc/docs/readme.txt`
  - `core/components/mxlocdoc/docs/changelog.txt`
  - `core/components/mxlocdoc/docs/license.txt`
- Assets:
  - `assets/components/mxlocdoc/connector.php`
  - `assets/components/mxlocdoc/js/mgr/mxlocdoc.js`
  - `assets/components/mxlocdoc/css/mgr/main.css`
- Builder:
  - `modxbuilder/mxlocdoc/build/build.package.php`
  - `modxbuilder/mxlocdoc/build/build.schema.php`
  - `modxbuilder/mxlocdoc/build/build.models.php`
  - `modxbuilder/mxlocdoc/build/config/config.inc.php`
  - `modxbuilder/mxlocdoc/build/data/transport.*.php`
  - `modxbuilder/mxlocdoc/build/resolvers/resolvers.php`
  - `modxbuilder/tools/modxbuilder.class.php`
  - `modxbuilder/tools/xpdogenerator.class.php`

## Зачем

Transport-пакет должен ставиться штатно через MODX package manager, регистрировать namespace и menu, приносить manager UI и файлы core/assets без ручного копирования. Сборка идёт старым `modxbuilder` из Sendit и на живом стенде читает элементы/настройки/меню из MODX manager.

## Чеклист готовности

- Namespace `mxlocdoc` зарегистрирован на `{core_path}components/mxlocdoc/`.
- В manager menu есть пункт входа в CMP.
- Лексиконы вынесены в `lexicon/ru/`.
- `docs/readme.txt`, `docs/changelog.txt`, `docs/license.txt` попадают в package attributes.
- Builder собирает transport из `modxbuilder/mxlocdoc/build/`.
- `build/data/transport.*.php` адаптированы из Sendit и читают реальные объекты стенда по namespace/category.

## Риски и ограничения

- На этом этапе не нужна xPDO-схема, если v1 не хранит поисковый индекс в БД.
- Нельзя заводить Node/Vite pipeline: UI должен работать как обычные manager assets.
- Нельзя забыть `assets/components/mxlocdoc/connector.php`, потому что через него пойдут Markdown assets и AJAX-процессоры.
- Сборку надо проверять на живом MODX 2 стенде Hostland командой с `/usr/local/php/php-7.4/bin/php`; локально без MODX core доступна только проверка синтаксиса.
- Старый `modxbuilder` включает `build/data/transport.*.php` безусловно; файлы должны существовать, но их задача — забрать созданные в админке объекты, а не описывать несуществующие элементы вручную.
