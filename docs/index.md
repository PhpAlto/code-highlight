# Alto Code Highlight documentation

Alto Code Highlight is a server-side syntax highlighter for PHP 8.4 and later.
It parses source code in PHP and returns escaped, theme-ready HTML. It does not
require a browser-side highlighter.

## Start here

- [Installation](installation.md) covers requirements, Composer, and a smoke
  test.
- [Getting started](getting-started.md) goes from source code to a complete HTML
  page.
- [Languages](languages.md) lists every accepted language identifier.
- [Themes](themes.md) lists all built-in theme variants and their constructors.

## Guides

- [Embedded languages](embedded-languages.md) explains HTML, SVG, Markdown, and
  Twig delegation.
- [Theme adapters](theme-adapters.md) shows how to reuse local Highlight.js,
  Prism, or TextMate theme files.
- [Creating a theme](creating-a-theme.md) implements `ThemeInterface` from
  semantic scopes to CSS.
- [Public API](public-api.md) defines the supported entry points, extension
  contracts, and compatibility boundary.
- [Examples](examples.md) links to the canonical source samples and generated
  visual previews.

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
