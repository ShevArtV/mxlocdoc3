---
title: Languages
order: 6
---
# Languages

mxLocDoc supports multilingual documentation, but languages are declared
**explicitly** through the `mxlocdoc.languages` setting. There is no automatic
detection of language folders.

## How to enable

1. List the language codes in the `mxlocdoc.languages` setting, comma-separated,
   for example `en,ru` (the default value).
2. Inside `mxlocdoc.docs_path`, create a subfolder for each code with Markdown
   documents:

```text
docs/
├── en/
│   ├── _sidebar.json
│   └── README.md
└── ru/
    ├── _sidebar.json
    └── README.md
```

A code from the setting becomes an active language **only if** such a subfolder
exists and contains Markdown files. Codes without a folder are simply ignored.

## Single-language mode

When `mxlocdoc.languages` is empty, mxLocDoc runs in single-language mode:
`mxlocdoc.docs_path` itself is the root, no language subfolders are used, and the
language selector is hidden.

Note: a plain two-letter section folder (such as `ui`, `js`, `go`) is not treated
as a language — a language is enabled only through the `mxlocdoc.languages` setting.

## Selector and default language

- The language selector is shown only when there is more than one active language.
- The default language is the first match of: the requested language (if any) →
  the manager interface language → the site culture key → the first available code
  alphabetically.
- The user's choice is persisted and passed to every request: navigation,
  document, search and assets.
