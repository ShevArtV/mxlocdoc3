---
title: Troubleshooting
order: 7
---
# Troubleshooting

## Documentation folder is not found

Check `mxlocdoc.docs_path`. Prefer a portable path:

```text
[[+corePath]]components/project-docs/
```

The final resolved folder must exist and be readable by PHP.

## Images are not visible

Check that the image path is relative to the current Markdown file and that the extension is listed in `mxlocdoc.allowed_asset_extensions`.

## Search does not update

Clear the MODX manager cache. mxLocDoc clears its own search cache through the `mxLocDocCacheClear` plugin.

## A language is missing from the selector

The language folder must be a direct child of `mxlocdoc.docs_path`, for example `en/` or `ru/`, and it must contain Markdown documentation.

