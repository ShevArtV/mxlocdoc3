# 04. Navigation

Status: done 2026-07-18.

## Цель

Сделать навигацию похожей на docs-сайт: основной источник структуры - manifest, fallback - файловое дерево.

## Что сделать

- Поддержать manifest `_sidebar.json`.
- Поддержать альтернативный manifest `mxlocdoc.json`.
- Использовать `README.md` как индекс раздела.
- Добавить fallback по `.md` файлам, если manifest отсутствует.
- Поддержать опциональный front matter `title`, `order`, `hidden`.
- Валидировать пути из manifest через secure filesystem layer.

## Куда именно

- Будущий сервис navigation:
  - `core/components/mxlocdoc/services/NavigationBuilder.php`
- Будущий repository:
  - `core/components/mxlocdoc/services/DocumentRepository.php`
- Будущий processor:
  - `core/components/mxlocdoc/processors/mgr/navigation/get.class.php`
- Manifest-файлы в пользовательской документации:
  - `<docs_path>/_sidebar.json`
  - `<docs_path>/mxlocdoc.json`

## Зачем

Manifest позволяет вручную задать порядок, названия и вложенность разделов. Fallback сохраняет работоспособность для папок с обычными Markdown-файлами без отдельной настройки.

## Пример manifest JSON

```json
{
  "title": "Project Docs",
  "items": [
    {
      "title": "Overview",
      "path": "README.md"
    },
    {
      "title": "Development",
      "items": [
        {
          "title": "Setup",
          "path": "development/setup.md"
        },
        {
          "title": "Deploy",
          "path": "development/deploy.md"
        }
      ]
    }
  ]
}
```

## Чеклист готовности

- Если есть manifest из `mxlocdoc.nav_file`, UI строит sidebar по нему.
- Если manifest отсутствует, sidebar строится по файлам и папкам.
- `README.md` отображается как индекс текущего раздела.
- `hidden: true` исключает страницу из sidebar, но не ломает прямое открытие, если файл разрешен.
- `order` сортирует элементы fallback-навигации.
- Все paths проходят через `PathResolver`.

## Риски и ограничения

- Некорректный JSON должен показывать понятную ошибку и, по решению v1, либо останавливать навигацию, либо переходить к fallback.
- Manifest не должен позволять ссылаться на файлы за пределами `docs_path`.
- Front matter нужен опционально; v1 не должен зависеть от сложного YAML-парсера, если это утяжеляет пакет.
