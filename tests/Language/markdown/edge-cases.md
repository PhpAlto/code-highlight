# Edge Cases for Markdown Coverage

## Headings (all levels)

# H1
## H2
### H3
#### H4
##### H5
###### H6

Alternative H1
==============

Alternative H2
--------------

## Emphasis

*italic* _italic_
**bold** __bold__
***bold italic*** ___bold italic___
~~strikethrough~~

## Lists

Unordered:
- Item 1
- Item 2
  - Nested item
  - Another nested
- Item 3

* Alternative
* Bullet
* Style

+ Plus sign
+ Bullets
+ Too

Ordered:
1. First
2. Second
   1. Nested
   2. Items
3. Third

## Links

[inline link](https://example.com)
[link with title](https://example.com "Title")
[reference link][ref]
[implicit reference]

[ref]: https://example.com
[implicit reference]: https://example.com

<https://autolink.com>
<email@example.com>

## Images

![alt text](image.jpg)
![alt with title](image.jpg "Title")
![reference image][img]

[img]: image.jpg

## Code

Inline `code` with backticks

```
Fenced code block
no language
```

```javascript
// JavaScript code
const x = 42;
```

```python
# Python code
def hello():
    print("world")
```

```php
<?php
echo "PHP code";
```

    Indented
    code
    block

## Blockquotes

> Simple blockquote
> Multiple lines

> Nested
>> blockquotes
>>> go deep

> Blockquote with **formatting**
> and `code`

## Horizontal Rules

---

***

___

- - -

* * *

_ _ _

## Tables

| Header 1 | Header 2 | Header 3 |
|----------|----------|----------|
| Cell 1   | Cell 2   | Cell 3   |
| Data     | Data     | Data     |

| Left | Center | Right |
|:-----|:------:|------:|
| L    | C      | R     |

## HTML

<div>Raw HTML</div>
<span>Inline HTML</span>

<details>
<summary>Collapsible</summary>
Content here
</details>

## Escaping

\*not italic\*
\[not a link\]
\`not code\`

## Complex Nesting

1. Ordered list
   - With unordered nested
     ```javascript
     // With code block
     const x = 1;
     ```
   - Another item
     > With blockquote
     > - And list inside blockquote
2. Continue

## Task Lists

- [ ] Unchecked
- [x] Checked
- [ ] Another task

## Definition Lists

Term 1
: Definition 1

Term 2
: Definition 2a
: Definition 2b

## Footnotes

Here's a sentence with a footnote[^1].

[^1]: This is the footnote content.

## Abbreviations

The HTML specification is maintained by the W3C.

*[HTML]: Hyper Text Markup Language
*[W3C]: World Wide Web Consortium

## Math (if supported)

Inline math: $E = mc^2$

Block math:
$$
\int_{-\infty}^{\infty} e^{-x^2} dx = \sqrt{\pi}
$$

## Comments

[//]: # (This is a comment)
[comment]: <> (This is also a comment)

## Multiple Blank Lines



(Three blank lines above)

## Mixed Content

Here's a paragraph with **bold**, *italic*, `code`, [link](url), and ![image](img.jpg).

> Blockquote with:
> - Lists
> - **Bold text**
> - `inline code`
> - [links](https://example.com)
>
> ```javascript
> // code block
> const x = 1;
> ```

## Edge Case Combinations

1. **Bold in list**
2. *Italic in list*
3. `Code in list`
4. [Link in list](url)

- [x] **Bold in task**
- [ ] *Italic in task*
- [ ] `Code in task`

## Raw URLs

Visit https://example.com for more.
Email: user@example.com

## Backslash Escapes

\! \# \$ \% \& \' \( \) \* \+ \, \- \. \/ \: \; \< \= \> \? \@ \[ \\ \] \^ \_ \` \{ \| \} \~

## Emphasis Edge Cases

**bold *italic* bold**
*italic **bold** italic*
***all bold italic***
**bold _italic_ bold**
_italic __bold__ italic_

## Link Edge Cases

[](empty-url)
[empty-text]()
[spaces in url](url with spaces)
[url#with#hashes](url)
[url?with?queries](url?param=value)

## List Edge Cases

- Item with
  continuation on next line
- Another item

1) Alternative
2) Numbering
3) Style

* Mixed
- Bullet
+ Styles

## Code Fence Languages

```bash
echo "bash"
```

```rust
fn main() {}
```

```go
func main() {}
```

```ruby
puts "ruby"
```

```java
class Main {}
```

```csharp
class Program {}
```

```typescript
const x: number = 42;
```

```sql
SELECT * FROM users;
```

```json
{"key": "value"}
```

```yaml
key: value
```

```xml
<root></root>
```

```dockerfile
FROM ubuntu
```

```makefile
target: dependency
	command
```
