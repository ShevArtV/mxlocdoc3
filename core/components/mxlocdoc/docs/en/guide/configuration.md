---
title: Configuration
order: 2
---
# Configuration

mxLocDoc is configured through MODX system settings.

| Setting | Default | Purpose |
|---|---:|---|
| `mxlocdoc.docs_path` | `[[+corePath]]components/mxlocdoc/docs/` | Local Markdown documentation root. |
| `mxlocdoc.languages` | `en,ru` | Comma-separated language codes that enable multilingual mode. An empty value means single-language mode. See the "Languages" page. |
| `mxlocdoc.default_file` | `README.md` | File opened for a folder index. |
| `mxlocdoc.nav_file` | `_sidebar.json` | Primary navigation manifest name. |
| `mxlocdoc.search_enabled` | `Yes` | Enables live search. |
| `mxlocdoc.cache_ttl` | `300` | Search index cache lifetime in seconds. |
| `mxlocdoc.max_file_size` | `1048576` | Maximum readable Markdown or asset file size. |
| `mxlocdoc.allowed_asset_extensions` | `jpg,jpeg,png,gif,webp,svg` | Asset extensions allowed through the protected connector. |

## Path placeholders

Use `[[+corePath]]` for portable paths:

```text
[[+corePath]]components/project-docs/
```

Absolute paths are still resolved for compatibility, but they are not recommended for distributable package configuration.

