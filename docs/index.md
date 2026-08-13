# Alto Code Highlight documentation

Alto Code Highlight is a server-side syntax highlighter for PHP 8.4 and later.
It parses source code in PHP and returns escaped, theme-ready HTML. It does not
require a browser-side highlighter.

## Documentation

- [Installation](installation.md) covers requirements, Composer, and a smoke
  test.
- [Getting started](getting-started.md) goes from source code to a complete HTML
  page.
- [Examples](examples.md) presents canonical examples and generated visual
  previews.

## Languages

- [Languages](languages/index.md) lists every accepted language identifier.
- [Embedded languages](languages/embedded.md) explains HTML, SVG, Markdown, and
  Twig delegation.

## Theming

- [Themes](theming/index.md) lists all built-in variants and constructors.
- [Theme adapters](theming/adapters.md) shows how to reuse local Highlight.js,
  Prism, or TextMate theme files.
- [Creating a theme](theming/creating.md) implements `ThemeInterface` from
  semantic scopes to CSS.

## API

- [Public API](api/index.md) defines the supported entry points, extension
  contracts, and compatibility boundary.

## Public API at a glance

`Alto\Code\Highlight\Highlighter` is the main entry point:

```php
use Alto\Code\Highlight\Highlighter;
use Alto\Code\Highlight\Theme\AltoTheme;

$highlighter = new Highlighter(new AltoTheme());
$html = $highlighter->highlight('<?php echo "Hello";', 'php');
$css = $highlighter->getTheme()->getStylesheet();
```

The returned HTML is a `<pre class="alto-highlight">` element containing a
`<code>` element and semantic `<span>` elements. Source text is HTML-escaped
during rendering. Add the selected theme's stylesheet once to the page, then
insert the returned HTML without escaping it again.
