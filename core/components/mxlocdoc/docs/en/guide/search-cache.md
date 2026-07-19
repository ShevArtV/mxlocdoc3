---
title: Search and cache
order: 5
---
# Search and cache

Search scans Markdown files by title, path and plain text body.

The prepared search index is cached in:

```text
core/cache/mxlocdoc/
```

Cache files use the selected language root in their cache key, so each language has an independent index.

## Cache invalidation

The `mxLocDocCacheClear` plugin listens to the MODX `OnBeforeCacheUpdate` event. When a manager user clears the MODX cache, mxLocDoc removes its search cache too.

Set `mxlocdoc.cache_ttl` to `0` to disable search index caching.

