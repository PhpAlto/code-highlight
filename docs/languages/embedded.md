# Embedded languages

Some host languages delegate parts of their source to another registered
parser. The outer highlighter still returns one escaped HTML block and uses one
theme.

## Built-in behavior

| Host identifier | Embedded content | Target identifier |
|---|---|---|
| `html` | Content of `<style>` tags | `css` |
| `html` | Content of `<script>` tags | `javascript` |
| `svg` | Content of `<style>` tags | `css` |
| `svg` | Content of `<script>` tags | `javascript` |
| `markdown` | Content of a fenced block whose info string is an identifier | Dynamic |
| `twig` | Template text outside Twig delimiters | `html` |

HTML and SVG use declarative tag triggers from the default
`EmbeddedLanguageRegistry`. Markdown resolves the fenced identifier
dynamically. Twig parses its expressions and tags itself, while delegating
ordinary template text to HTML.

The default Twig plan has no named block mappings. Add them explicitly when a
Twig block contains CSS, JavaScript, or another language.

## HTML and SVG tags

No extra configuration is required:

```php
$source = <<<'HTML'
<style>
    .notice { color: rebeccapurple; }
</style>
<script>
    const notice = document.querySelector('.notice');
</script>
HTML;

$html = $highlighter->highlight($source, 'html');
```

The tag and attributes use the host parser. Text up to the matching closing tag
uses the `css` or `javascript` parser.

## Markdown fences

Use an exact registered identifier in the fence:

````markdown
```php
echo "Embedded PHP";
```
````

Embedded PHP may omit its opening tag. The highlighter adds one for parsing and
removes the synthetic tag from the rendered result.

If the fence has no identifier, its content is rendered as code text. If the
identifier is unknown or an embedded parser fails, the host highlight still
succeeds and keeps that content visible with the generic string scope.

## Named Twig blocks

Create a registry that replaces the empty default Twig plan while preserving
the other defaults:

```php
use Alto\Code\Highlight\Embedded\EmbeddedLanguagePlan;
use Alto\Code\Highlight\Embedded\EmbeddedLanguageRegistry;
use Alto\Code\Highlight\Embedded\EmbeddedTrigger;
use Alto\Code\Highlight\Highlighter;
use Alto\Code\Highlight\Theme\AltoTheme;

$plans = EmbeddedLanguageRegistry::getDefaultPlans();
$plans[] = EmbeddedLanguagePlan::forHost('twig', [
    EmbeddedTrigger::block('css', 'css'),
    EmbeddedTrigger::block('javascript', 'javascript'),
]);

$registry = new EmbeddedLanguageRegistry($plans);
$highlighter = new Highlighter(new AltoTheme(), $registry);

$html = $highlighter->highlight(
    '{% block javascript %}const ready = true;{% endblock %}',
    'twig',
);
```

Block names and target identifiers are normalized to lowercase.

Passing a custom registry to `Highlighter` does not merge plans automatically.
The example starts with `getDefaultPlans()` so HTML, SVG, and Markdown keep
their built-in behavior. A later plan for the same host replaces the earlier
one.

## Customize tag triggers

Attribute constraints can route a tag to a different parser. Put a constrained
trigger before a generic trigger for the same tag:

```php
$registry = new EmbeddedLanguageRegistry([
    EmbeddedLanguagePlan::forHost('html', [
        EmbeddedTrigger::tag('style', 'css'),
        EmbeddedTrigger::tag('script', 'typescript', [
            'type' => ['text/typescript', 'application/typescript'],
        ]),
        EmbeddedTrigger::tag('script', 'javascript'),
    ]),
]);
```

Constraint names and values are compared case-insensitively. A `null`
constraint requires only that the attribute be present.

## Toggle a host/target pair

Configured tag or block triggers are enabled by default:

```php
$highlighter->setEmbeddingEnabled('html', 'javascript', false);
$plainScript = $highlighter->highlight($source, 'html');

$highlighter->setEmbeddingEnabled('html', 'javascript', true);
$parsedScript = $highlighter->highlight($source, 'html');
```

The toggle is stored per highlighter instance and per normalized
`host:target` pair. Disabling a pair keeps the embedded source visible, but the
host parser treats it as markup text instead of delegating it.

Dynamic Markdown fences are not declarative triggers, so
`setEmbeddingEnabled()` does not toggle them.
