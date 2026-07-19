---
title: Overview
order: 1
---
# mxLocDoc

mxLocDoc is a MODX Revolution 3 extra for reading local Markdown documentation inside the MODX manager.

It is intended for project documentation that should stay on the same server as the site and be available to manager users without publishing the files on the public web.

## What it does

- Reads Markdown files from a local folder configured in a system setting.
- Builds the left navigation from `_sidebar.json` or `mxlocdoc.json`.
- Falls back to a filesystem navigation tree when no manifest exists.
- Renders Markdown safely in the manager.
- Serves relative images and other allowed assets through a protected connector.
- Provides live search over Markdown files.
- Supports language folders such as `en/` and `ru/`.

## What it does not do

- It does not edit Markdown files.
- It does not expose documentation on the public frontend.
- It does not create database tables for docs or search.
- It does not require Node, Vite, Vue or a frontend build step.

## Recommended structure

```text
core/components/mydocs/
├── en/
│   ├── _sidebar.json
│   ├── README.md
│   └── guide/
└── ru/
    ├── _sidebar.json
    ├── README.md
    └── guide/
```

Set `mxlocdoc.docs_path` to the parent folder:

```text
[[+corePath]]components/mydocs/
```

When language folders are present, mxLocDoc shows a language selector in the manager.

