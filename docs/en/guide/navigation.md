---
title: Navigation
order: 3
---
# Navigation

mxLocDoc has two independent navigations:

- the **left menu** — a list of all documents, defined by a manifest file (or built
  automatically);
- the **on-page navigation** — the "On this page" list on the right, built from the
  headings of the open document.

## Left menu

### Where it comes from

By default the `_sidebar.json` manifest is read (its name is set by the
`mxlocdoc.nav_file` setting). If it is missing, `mxlocdoc.json` is checked. If there
is no manifest at all, mxLocDoc builds the tree automatically from the Markdown
files (fallback, see below).

### Manifest example

```json
{
  "title": "Project Docs",
  "items": [
    {"title": "Overview", "path": "README.md"},
    {"title": "Guide", "path": "guide/README.md", "items": [
      {"title": "Setup", "path": "guide/setup.md"},
      {"title": "Configuration", "path": "guide/configuration.md"}
    ]}
  ]
}
```

Top level: `title` is the heading of the whole menu, `items` is the array of entries.

### Item fields

- **`title`** — the entry label in the menu. If omitted, the document's front matter
  `title` is used, and if that is missing too, the file name. The field is optional,
  but with it the label is predictable.
- **`path`** — path to a Markdown file relative to the documentation root (or the
  language subroot when languages are enabled). An entry with a `path` is a clickable
  page. A folder's `README.md` is commonly used as its section index.
- **`items`** — nested entries. This builds a tree of any depth.
- **`hidden`** — `true` to hide the entry (and its whole branch) from the menu.

### Section or page

- An entry with a `path` is a **page**: clicking it opens the document.
- An entry without a `path` but with `items` is a **section**: a group heading that
  loads nothing when clicked. To make a section clickable, give it a `path` (usually
  the section's `README.md`) and `items` for the nested pages.

### Order and front matter

The order of entries in the manifest is the order they are listed in `items`.

Each document may carry front matter — a header at the top of the file between `---`
lines:

```markdown
---
title: Setup
order: 1
hidden: false
---
```

- `title` — the label when it is not set in the manifest;
- `order` — used for sorting in fallback mode (no manifest);
- `hidden: true` — hides the document from both the manifest menu and the fallback.

### Fallback without a manifest

If no manifest is found, mxLocDoc builds the menu from the files themselves: folders
become sections, a folder's `README.md` becomes its index, and the order follows the
`order` from front matter. Hidden (`hidden: true`) documents are skipped.

Every path in the manifest goes through the same secure filesystem layer as regular
document requests — a request cannot leave the documentation root.

## On-page navigation

The "On this page" list on the right is built **automatically** from the headings of
the open document — the `#`, `##`, `###` levels (h1–h3). Nothing needs to be
configured: the cleaner the heading structure in Markdown, the clearer the outline.

- Nesting in the list mirrors the heading level: `##` nests under `#`, `###` under
  `##`.
- Clicking an entry smoothly scrolls the document to the heading (without changing
  the page address).
- If the document has no h1–h3 headings, the list is hidden.

Headings of level `####` and deeper do not appear in this outline.
