# 10. MODX 3 Port

## Цель

Портировать mxLocDoc с MODX 2 на MODX 3 после проверки MODX 2 transport-пакета.

## Что сделать

- Разобрать отличия MODX 3 package layout, namespace, menu, connector/controller и processor API.
- Выбрать структуру исходников MODX 3 версии: отдельная ветка, отдельная папка сборки или общий core с адаптерами.
- Перенести runtime-сервисы без изменения пользовательского контракта `docs_path`, manifest, Markdown rendering, protected assets, search cache и multilingual roots.
- Перенести manager UI, сохранив текущий vanilla JS/CSS подход без отдельного frontend build.
- Собрать MODX 3 transport через актуальный builder и проверить установку на MODX 3 стенде.

## Куда именно

- MODX 2 baseline: текущий `core/components/mxlocdoc/`, `assets/components/mxlocdoc/`, `modxbuilder/mxlocdoc/`.
- MODX 3 reference/build tooling: выбрать перед реализацией по БЗ и существующим MODX 3 пакетам.
- Документация: `docs/` и пакетная docs-папка MODX 3 должны описывать общий пользовательский контракт.

## Зачем

mxLocDoc должен быть доступен для MODX 3 без потери поведения, уже проверенного в MODX 2: локальная Markdown-документация в manager, безопасная выдача ассетов, поиск, кэш и мультиязычность.

## Чеклист готовности

- Решена стратегия code sharing между MODX 2 и MODX 3.
- MODX 3 package skeleton собирается.
- Manager menu/CMP открывается в MODX 3.
- Processors/document/assets/search работают на MODX 3.
- Browser smoke подтверждает navigation/document/search/language selector.
- Transport zip проверен на мусор.

## Риски и ограничения

- Не переносить механически MODX 2 manager/controller API без проверки по MODX 3 core.
- До выбора лицензии не публиковать production-релиз публично.
