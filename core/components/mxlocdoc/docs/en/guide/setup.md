---
title: Setup
order: 1
---
# Setup

Install mxLocDoc as a MODX 2 package, then open it from the manager menu.

The package creates a namespace, menu entry, system settings and manager processors. The documentation files themselves remain ordinary Markdown files on disk.

## Minimum setup

1. Create a local documentation folder.
2. Add `README.md`.
3. Set `mxlocdoc.docs_path` to that folder.
4. Clear the MODX manager cache.
5. Open mxLocDoc in the manager.

## Default docs path

The default value is:

```text
[[+corePath]]components/mxlocdoc/docs/
```

The `[[+corePath]]` placeholder is resolved to the MODX core path at runtime. A plain relative path is also treated as relative to the MODX core folder.

