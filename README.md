# ALTO \ Code Highlight

**Syntax highlighting for PHP projects**

Server-side highlighting for the full PHP stack: [27 languages](#languages)
covering PHP, HTML, Twig, JavaScript, CSS, YAML, and
more. [Zero dependencies](#install), [semantic PHP syntax](#features)
understanding definitions vs. calls, [embedded language support](#embeddings),
and [490+ compatible themes](#compatibility). Works
in [Twig templates](#integrations), Laravel Blade, Symfony controllers—anywhere
PHP runs.

[![Tests](https://img.shields.io/badge/tests-463%20passed-success)](https://github.com/phpalto/code-highlight)
[![PHPStan](https://img.shields.io/badge/PHPStan-level%2010-brightgreen)](https://github.com/phpalto/code-highlight)
[![PHP](https://img.shields.io/badge/PHP-8.4+-777BB4?logo=php&logoColor=white)](https://www.php.net)
[![License](https://img.shields.io/badge/license-MIT-blue.svg)](LICENSE)

## Features

- **Built for PHP ecosystems:** Highlight the full stack—PHP, HTML, Twig,
  JavaScript, CSS, SQL, YAML, and 20+ more languages in a single library.
- **Zero dependencies:** No Node.js, Python, or external processes. Just
  Composer. Works in any PHP 8.4+ environment.
- **Full PHP syntax:** Semantic parser that understands context—distinguishes
  `class User` (definition) from `new User()` (usage), `function greet()` (
  definition) from `greet()` (call).
- **Embedded languages:** Automatically switches parsers for `<style>` and
  `<script>` tags in HTML, fenced code blocks in Markdown, and `{% block css %}`
  in Twig.
- **Dark mode support:** Multiple dark themes included out of the box (
  CupertinoDark, Dracula, Noctis) plus compatibility with 490+ Highlight.js and
  Prism themes.

## Install

```bash
composer require alto/code-highlight
```

## Usage

```php
use Alto\Code\Highlight\Highlighter;
use Alto\Code\Highlight\Theme\GitHubTheme;

// 1. Initialize with a theme
$highlighter = new Highlighter(new GitHubTheme());

// 2. Output the theme's CSS (typically in your <head>)
echo "<style>" . $highlighter->getTheme()->getStylesheet() . "</style>";

// 3. Highlight your code
echo $highlighter->highlight($code, 'php');
```

## Languages

### PHP

Alto uses a semantic parser for PHP that goes beyond pattern matching to
understand code context. It correctly distinguishes between:

- **Definitions vs. usage:** `class User` vs. `new User()`, `function greet()`
  vs. `greet()`
- **Context-aware scoping:** Variables, function calls, class instantiation,
  method calls

### Embeddings

Four languages support automatic embedded language detection:

- **HTML** — CSS in `<style>` tags, JavaScript in `<script>` tags
- **SVG** — CSS in `<style>` tags, JavaScript in `<script>` tags
- **Markdown** — Any language in fenced code blocks (` ```language `)
- **Twig** — Languages via block names (`{% block css %}`,
  `{% block javascript %}`)

### Full list

| Category        | Languages                                                                  |
|-----------------|----------------------------------------------------------------------------|
| **Programming** | Bash, C#, Go, Java, JavaScript, PHP, Python, Ruby, Rust, Swift, TypeScript |
| **Markup**      | HTML, SVG, XML                                                             |
| **Data**        | Diff, DotEnv, HTTP, JSON, YAML                                             |
| **Prose**       | Markdown                                                                   |
| **Query**       | SQL                                                                        |
| **Stylesheet**  | CSS, SCSS                                                                  |
| **Template**    | Twig                                                                       |
| **Config**      | Dockerfile, INI, Makefile                                                  |

## Themes

### Built-in themes

Alto includes 7 built-in themes ready to use:

- **Light themes:** `Alto`, `GitHub`, `Polar`, `Solar`
- **Dark themes:** `CupertinoDark`, `Dracula`, `Noctis`

```php
use Alto\Code\Highlight\Theme\DraculaTheme;

$highlighter = new Highlighter(new DraculaTheme());
```

### Dark mode

Three dark themes are included out of the box:

- **CupertinoDark** — macOS-inspired dark theme
- **Dracula** — Popular dark theme with vibrant colors
- **Noctis** — Low-contrast dark theme for extended coding sessions

### Compatibility

Use existing CSS from the **Highlight.js** (240+ themes), **Prism** (250+
themes), or **TextMate** (.tmTheme) ecosystems:

```php
use Alto\Code\Highlight\Adapter\HighlightJsThemeAdapter;

$theme = HighlightJsThemeAdapter::fromFile('/path/to/github-dark.css');
$highlighter = new Highlighter($theme);
```

```php
use Alto\Code\Highlight\Adapter\TextMateThemeAdapter;

$theme = TextMateThemeAdapter::fromFile('/path/to/monokai.tmTheme', isDark: true);
$highlighter = new Highlighter($theme);
```

## Integrations

### Twig Extension

**[Twig Extension](https://github.com/phpalto/twig-code-highlight)** — Highlight
code directly in Twig templates using blocks or filters.

## Contributing

Contributions are welcome! Please feel free
to [submit issues](https://github.com/phpalto/code-highlight/issues)
or [pull requests](https://github.com/phpalto/code-highlight/pulls).

## License

Released by the [Alto project](https://github.com/phpalto) under the MIT
License.
See the [LICENSE](LICENSE) file for details.
