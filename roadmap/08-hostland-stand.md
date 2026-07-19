# 08. Hostland Stand

## Цель

Проверить будущую реализацию mxLocDoc на MODX 2 стенде Hostland перед релизом transport-пакета.

## Что сделать

- Залить измененные файлы будущего пакета на стенд Hostland `mscdek2.art-sites.ru`.
- Использовать путь стенда `/home/host1860015/art-sites.ru/htdocs/mscdek2/`.
- Собрать transport через PHP 7.4, а не системный PHP.
- Установить transport на стенде.
- Подготовить тестовую папку docs с `.md`, manifest и картинками.
- Проверить CMP в браузере через Chrome MCP.
- Проверить MODX error log.

## Куда именно

- Стенд:
  - `mscdek2.art-sites.ru`
  - `/home/host1860015/art-sites.ru/htdocs/mscdek2/`
- Команда будущей сборки:
  - `/usr/local/php/php-7.4/bin/php modxbuilder/mxlocdoc/build/build.package.php`
- Тестовая документация:
  - `<docs_path>/README.md`
  - `<docs_path>/_sidebar.json`
  - `<docs_path>/guide/setup.md`
  - `<docs_path>/images/example.png`
- Проверка логов:
  - MODX error log стенда.

## Зачем

MODX 2 пакеты часто корректнее собирать и проверять на живом стенде, где есть реальный manager, настройки, namespace, connectors и package manager.

## Чеклист готовности

- Файлы залиты на стенд одной сгруппированной серией команд.
- Transport собран через `/usr/local/php/php-7.4/bin/php`.
- Transport установлен на стенде через package manager или CLI.
- `mxlocdoc.docs_path` указывает на тестовую папку.
- Manager CMP открывается.
- Markdown, manifest-навигация, fallback, картинки и поиск работают.
- MODX error log не содержит новых ошибок mxLocDoc.

## Риски и ограничения

- Не использовать системный `php`: на Hostland нужен `/usr/local/php/php-7.4/bin/php`.
- Не выполнять серверную инфраструктуру и destructive-команды.
- Не делать много отдельных SSH-подключений; команды группировать.
- Стендовая проверка не заменяет release checklist.
