---
title: Markdown syntax
order: 4
---
# Markdown syntax

Documents are written in **Markdown** — a simple markup where formatting is set
with plain characters right inside the text. mxLocDoc renders them through
Parsedown in safe mode. Below is what is supported, what is not, and a short
cheat sheet for anyone new to Markdown.

Full reference: [Markdown Guide](https://www.markdownguide.org/basic-syntax/)
(the "Basic Syntax" and "Extended Syntax" sections). Interactive tutorial —
[CommonMark](https://commonmark.org/help/).

## What is supported

### Headings

````markdown
# Level one heading
## Level two
### Level three
````

### Text emphasis

````markdown
**bold**, *italic*, ~~strikethrough~~
````

### Lists

````markdown
- item
- item
  - nested item

1. first
2. second
````

### Links and images

````markdown
[link text](guide/setup.md)
![image caption](images/diagram.svg)
````

Relative links to `.md` files open inside mxLocDoc, and relative images are served
through a protected connector: mxLocDoc checks the path, extension and file size
and never lets a request leave the documentation root. External links and images
stay as regular ones.

### Code

Inline code goes in single backticks: `` `example` ``. A block goes in triple
backticks and may name a language:

`````markdown
```php
echo 'hello';
```
`````

Code is shown in a monospace font. Syntax highlighting is not applied.

### Blockquotes and a horizontal rule

````markdown
> a quote

---
````

### Tables

````markdown
| Column | Value |
|--------|-------|
| A      | 1     |
| B      | 2     |
````

## What is not supported

- **Task lists** `- [ ] item` / `- [x] item` — they render as plain text with
  brackets, without checkboxes.
- **Footnotes** `[^1]`, **definition lists**, **abbreviations**, **inline
  attributes** `{.class}` — these are ParsedownExtra extensions and are not
  bundled.
- **Raw HTML** (`<div>`, `<iframe>`, `<details>`, and so on) — it is escaped on
  purpose and shown as text. This protects against unsafe content.
- **Syntax highlighting** in code blocks — code is monospace but not colored.
- Unsafe links (`javascript:` and similar) are stripped.

## Cheat sheet

On the left is what you type, on the right is how it looks once rendered.

| You want       | Type this             | Result                        |
|----------------|-----------------------|-------------------------------|
| Heading        | `## Title`            | a section heading             |
| Bold text      | `**text**`            | **text**                      |
| Italic         | `*text*`              | *text*                        |
| Strikethrough  | `~~text~~`            | ~~text~~                      |
| List           | `- item`              | a bulleted list               |
| Numbered list  | `1. item`             | a numbered list               |
| Link           | `[text](url)`         | [text](https://modx.com)      |
| Inline code    | `` `code` ``          | `code`                        |
| Quote          | `> text`              | a blockquote                  |

Tip: keep a blank line between paragraphs, headings and lists — without it the
blocks may merge into a single paragraph.
