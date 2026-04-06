# Full Coverage Test

## Horizontal rules with leading whitespace

   ---
  ***
 ___

## Code blocks

Empty code block:
```

```

Unclosed code block (should consume to end):
```javascript
const x = 1;
// No closing fence

## Inline formatting edge cases

**bold text** and __also bold__
*italic text* and _also italic_
~~strikethrough~~
`inline code`

Unclosed special characters that become plain text:
This has a lone * asterisk
And a lone _ underscore
A single ~ tilde
A lone ` backtick

## Links and images edge cases

Normal link: [text](https://example.com)
Unclosed link [text](incomplete

Normal image: ![alt text](image.png)
Unclosed image ![alt](incomplete

## Tables

| Column 1 | Column 2 | Column 3 |
|----------|----------|----------|
| data | | empty cell |
||also empty||

## Lists with varied markers

- Dash list
* Asterisk list
+ Plus list
1. Numbered
2. List