# 09. Release

Status: done for `0.1.0-pl` transport build on 2026-07-19.

## Цель

Описать финальный выпуск mxLocDoc как MODX 2 transport-пакета после содержательной реализации и проверки на стенде.

## Что сделать

- Поднять версию пакета.
- Обновить changelog.
- Собрать transport.
- Проверить zip на мусор.
- Сделать commit и push.
- После содержательной реализации обновить БЗ по `modx-modules/mxlocdoc`.

## Куда именно

- Версия:
  - `modxbuilder/mxlocdoc/build/config/config.inc.php`
- Changelog:
  - `core/components/mxlocdoc/docs/changelog.txt`
- Сборка:
  - `modxbuilder/mxlocdoc/build/build.package.php`
  - будущий transport в `core/packages/`
- Проверка архива:
  - `unzip -l <mxlocdoc-version>.transport.zip`
- БЗ после реализации:
  - `/home/shevartv/projects/knowledge-base/modx-modules/mxlocdoc/README.md`
  - `/home/shevartv/projects/knowledge-base/modx-modules/README.md`
  - `/home/shevartv/projects/knowledge-base/README.md`

## Зачем

Для MODX-пакета результатом правки является не только код, но и новая transport-версия. Иначе установка из старого zip может откатить фактическое состояние.

## Чеклист готовности

- Версия поднята по semver.
- `docs/changelog.txt` содержит запись новой версии.
- Transport собран на правильном стенде и правильным PHP.
- В zip нет `node_modules`, dev-инструментов, логов, `.git`, временных файлов и лишних архивов.
- Изменения закоммичены и запушены.
- БЗ обновлена после содержательной реализации, а не на этапе пустого roadmap.

## Риски и ограничения

- Нельзя перевыпускать уже опубликованную версию той же цифрой.
- Политика коммита zip должна быть проверена по проекту перед первым релизом.
- Если появятся runtime composer-зависимости, нужно отдельно решить, должен ли `vendor/` входить в transport.
