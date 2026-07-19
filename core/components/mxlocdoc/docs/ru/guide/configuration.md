---
title: Настройки
order: 2
---
# Настройки

mxLocDoc настраивается через системные настройки MODX.

| Настройка | По умолчанию | Назначение |
|---|---:|---|
| `mxlocdoc.docs_path` | `[[+corePath]]components/mxlocdoc/docs/` | Локальный корень Markdown-документации. |
| `mxlocdoc.languages` | `en,ru` | Список кодов языков через запятую, для которых включается мультиязычность. Пустое значение — моно-режим. См. раздел «Языки». |
| `mxlocdoc.default_file` | `README.md` | Файл, который открывается как index папки. |
| `mxlocdoc.nav_file` | `_sidebar.json` | Основной manifest навигации. |
| `mxlocdoc.search_enabled` | `Да` | Включает live-search. |
| `mxlocdoc.cache_ttl` | `300` | Время жизни кэша поискового индекса в секундах. |
| `mxlocdoc.max_file_size` | `1048576` | Максимальный размер Markdown-файла или ассета. |
| `mxlocdoc.allowed_asset_extensions` | `jpg,jpeg,png,gif,webp,svg` | Расширения ассетов, которые можно отдавать через protected connector. |

## Плейсхолдеры пути

Используйте `[[+corePath]]` для переносимых путей:

```text
[[+corePath]]components/project-docs/
```

Абсолютные пути всё ещё разрешаются для совместимости, но не рекомендуются для распространяемой конфигурации пакета.

