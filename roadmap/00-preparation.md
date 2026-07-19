# 00. Preparation

> Status: done 2026-07-18.

## Цель

Подготовить фактическую базу для реализации mxLocDoc как MODX 2 extra-пакета и не начинать код без сверки с рабочими локальными примерами.

## Что сделать

- Изучить MODX 2 package-референсы `apps/mxlogger` и `apps/ms_cdek2`.
- Сверить рецепты БЗ `shared/recipes/modx2-package-development.md` и `shared/recipes/package-release-workflow.md`.
- Проверить доступность и фактическое состояние стенда Hostland `mscdek2.art-sites.ru`.
- Зафиксировать правило одного SSH-подключения на серию команд при работе со стендом.

## Куда именно

- Локальные референсы:
  - `/home/shevartv/projects/apps/mxlogger/`
  - `/home/shevartv/projects/apps/ms_cdek2/`
- Рецепты БЗ:
  - `/home/shevartv/projects/knowledge-base/shared/recipes/modx2-package-development.md`
  - `/home/shevartv/projects/knowledge-base/shared/recipes/package-release-workflow.md`
- Будущий стенд:
  - `mscdek2.art-sites.ru`
  - `/home/host1860015/art-sites.ru/htdocs/mscdek2/`

## Зачем

mxLocDoc должен использовать уже проверенный для MODX 2 подход к структуре, сборке и проверке пакетов. Сверка заранее снижает риск собрать skeleton в стиле, который потом придется переделывать.

## Чеклист готовности

- [x] Понята структура `core/components/*`, `assets/components/*`, builder-папок и manager CMP в референсах.
- [x] Подтверждено, какой PHP использовать на Hostland: `/usr/local/php/php-7.4/bin/php`.
- [x] Зафиксировано, что серверные команды выполняются сериями через одно SSH-подключение.
- [x] Понятно, какие части roadmap относятся к MVP, а какие отложены.

## Результат сверки 2026-07-18

### Референсы

- `apps/mxlogger` — основной референс для lightweight manager CMP:
  - manager controller: `core/components/mxlogger/controllers/index.class.php`;
  - connector: `assets/components/mxlogger/connector.php`;
  - processors: `core/components/mxlogger/processors/mgr/*`;
  - ассеты менеджера: `assets/components/mxlogger/js/mgr/*`, `assets/components/mxlogger/css/mgr/main.css`;
  - шаблон CMP фактически минимальный: controller отдаёт `<div id="mxlogger-panel-home"></div>` через `getContent()`;
  - controller грузит сервис через `getService()`, прокидывает `connector_url/assets_url` в JS и использует `?v=filemtime` для cache-busting.
- `apps/mxlogger/modxbuilder/mxlogger` — основной референс для `modxbuilder`-каркаса mxLocDoc:
  - `build/config/config.inc.php`;
  - `build/build.package.php`;
  - `build/data/transport.settings.php`;
  - `build/data/transport.menu.php`;
  - пустые/минимальные transport-файлы для элементов, которые пакет не ставит;
  - `build/resolvers/resolvers.php` как агрегатор.
- `apps/ms_cdek2` — референс для текущего состояния стенда `mscdek2` и MODX 2 package layout с `_build/build.php`, но для mxLocDoc не копировать CDEK-специфику: encrypted vehicle, miniShop2 hooks, web modules, CDEK services.

### Стенд Hostland

Read-only проверка одним SSH-подключением подтвердила:

- SSH alias: `hostland`;
- пользователь: `host1860015`;
- домашний каталог: `/home/host1860015`;
- PHP для сборки: `/usr/local/php/php-7.4/bin/php`, фактически `PHP 7.4.33`;
- стенд существует: `/home/host1860015/art-sites.ru/htdocs/mscdek2/`;
- на стенде есть `config.core.php`, `manager/`, `_build/build.php`.

Для будущих серверных команд правило остаётся прежним: группировать чтение/проверки в один SSH-заход, не делать серию отдельных подключений.

### Выводы для 01-package-skeleton

- Делать MODX 2 layout:
  - `core/components/mxlocdoc/`;
  - `assets/components/mxlocdoc/`;
  - `assets/components/mxlocdoc/connector.php`;
  - `core/components/mxlocdoc/controllers/index.class.php`;
  - `core/components/mxlocdoc/processors/mgr/`;
  - `core/components/mxlocdoc/lexicon/{ru,en}/`;
  - `core/components/mxlocdoc/docs/`.
- Для v1 не нужна xPDO-модель и таблицы: документация читается с диска, поиск можно кешировать файлово/в MODX cache. `model/schema` можно оставить пустым или не вводить до появления БД-сущностей.
- Builder лучше начинать по `mxlogger/modxbuilder/mxlogger`, а не по `_build` из `ms_cdek2`: mxLogger ближе по типу пакета и не содержит доменной логики CDEK.
- Системные настройки в builder должны быть самодостаточными, по аналогии с `transport.settings.php` mxLogger. Базовые настройки следующего этапа: `mxlocdoc.docs_path`, `mxlocdoc.default_file`, `mxlocdoc.allow_html`, `mxlocdoc.cache_ttl`.
- Manager menu: пункт в `components`, namespace `mxlocdoc`, action `index`.
- Для CMP использовать vanilla JS/CSS поверх MODX manager controller, без Node/Vite/Vue. ExtJS можно использовать только как контейнер/точку входа, если это проще для MODX 2 manager.

## Риски и ограничения

- `mxlogger` и `ms_cdek2` могут использовать разные варианты билдера, поэтому нельзя механически копировать структуру без адаптации.
- Hostland shared-хостинг не должен использоваться для экспериментов с серверной инфраструктурой.
- Серия отдельных SSH-подключений может привести к fail2ban; команды нужно группировать.
- В `ms_cdek2/_build/build.php` есть специфичные для закрытого пакета механики (`encryptedVehicle`, запрос к modstore API, CDEK/miniShop2-логика). Их не переносить в mxLocDoc skeleton.
