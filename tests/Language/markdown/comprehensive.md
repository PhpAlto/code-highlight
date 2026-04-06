# Comprehensive Markdown Test

## All Features Covered

### Headings

# Heading 1
## Heading 2
### Heading 3
#### Heading 4
##### Heading 5
###### Heading 6

### Text Formatting

This is **bold text** and this is __also bold__.

This is *italic text* and this is _also italic_.

This is ***bold and italic*** text.

This is ~~strikethrough~~ text (GFM).

### Inline Code

Use `inline code` for commands like `npm install` or variables like `$variable`.

### Code Blocks

Fenced with language:
```php
<?php
function hello($name) {
    return "Hello, " . $name;
}
```

Fenced without language:
```
Plain text code block
No syntax highlighting
```

### Links

[Inline link](https://example.com)

[Link with title](https://example.com "Example Website")

[Reference link][ref]

[Another reference][1]

Autolink: <https://example.com>

Email: <email@example.com>

[ref]: https://reference.com
[1]: https://numbered.com

### Images

![Alt text](image.png)

![Image with title](photo.jpg "Photo Title")

![Reference image][img-ref]

[img-ref]: https://example.com/image.png

### Lists

#### Unordered

- Item 1
- Item 2
  - Nested item 2.1
  - Nested item 2.2
    - Deep nested 2.2.1
- Item 3

* Asterisk item
* Another asterisk

+ Plus item
+ Another plus

#### Ordered

1. First item
2. Second item
   1. Nested 2.1
   2. Nested 2.2
3. Third item

#### Mixed

1. Ordered
   - Unordered nested
   - Another unordered
2. Back to ordered

#### Task Lists (GFM)

- [x] Completed task
- [ ] Incomplete task
- [x] Another completed
  - [ ] Nested task
  - [x] Nested completed

### Blockquotes

> Single line blockquote

> Multi-line blockquote
> continues here
> and here

> Nested blockquote
>> Deeper level
>>> Even deeper

> Blockquote with **formatting**
> and `code` and [links](https://example.com)

### Horizontal Rules

---

***

___

- - -

* * *

_ _ _

### Tables (GFM)

| Header 1 | Header 2 | Header 3 |
|----------|----------|----------|
| Cell 1   | Cell 2   | Cell 3   |
| Cell 4   | Cell 5   | Cell 6   |

With alignment:

| Left | Center | Right |
|:-----|:------:|------:|
| L1   |   C1   |    R1 |
| L2   |   C2   |    R2 |

Complex table:

| Feature | Supported | Notes |
|---------|:---------:|-------|
| **Bold** | ✓ | `Works` |
| *Italic* | ✓ | [Link](url) |

### Escaping

\* Not italic \*

\[ Not a link \]

\# Not a heading

### HTML in Markdown

<div class="custom">
  <p>HTML is allowed</p>
</div>

<strong>Bold HTML</strong>

<!-- HTML comment -->

### Definition Lists (Some implementations)

Term 1
: Definition 1

Term 2
: Definition 2a
: Definition 2b

### Footnotes (Some implementations)

Here's a sentence with a footnote[^1].

[^1]: This is the footnote.

### Abbreviations (Some implementations)

The HTML specification is maintained by the W3C.

*[HTML]: Hyper Text Markup Language
*[W3C]: World Wide Web Consortium

### Combined Examples

**Bold with *nested italic* text**

*Italic with **nested bold** text*

`Code with **bold attempt**` (should not work)

[Link with **bold text**](https://example.com)

> Blockquote with:
> - List item 1
> - List item 2
>
> ```javascript
> console.log("code in blockquote");
> ```

### Edge Cases

Empty lines between:

Paragraphs


With multiple


Empty lines

Inline `code with **bold**` attempt

**Bold with `code` inside**

Links in [bold **text**](https://example.com)

### Special Characters

Emoji: 🚀 ✓ ⚠️ 💻

Math: E = mc²

Arrows: → ← ↑ ↓

Symbols: © ® ™ § ¶

### Line Breaks

Line 1
Line 2 (two spaces)

Line 3\
Line 4 (backslash)

### Inline HTML and Markdown Mix

<div>

Regular **markdown** works here.

- List item
- Another item

</div>

### Very Long Lines

This is a very long line that should wrap naturally in most markdown renderers and demonstrates how the parser handles extended content without line breaks in the source which is a common occurrence in markdown files especially when people paste content from other sources or write long paragraphs without manual line breaks.

### Unicode

Chinese: 你好世界
Japanese: こんにちは世界
Korean: 안녕하세요 세계
Arabic: مرحبا بالعالم
Hebrew: שלום עולם
Russian: Привет мир
