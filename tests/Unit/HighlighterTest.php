<?php

declare(strict_types=1);

/*
 * This file is part of the ALTO library.
 *
 * © 2026-present Simon André
 *
 * For full copyright and license information, please see
 * the LICENSE file distributed with this source code.
 */

namespace Alto\Code\Highlight\Tests\Unit;

use Alto\Code\Highlight\Adapter\HighlightJsThemeAdapter;
use Alto\Code\Highlight\Embedded\EmbeddedLanguageRegistry;
use Alto\Code\Highlight\Embedded\EmbeddedTrigger;
use Alto\Code\Highlight\Exception\LanguageNotFoundException;
use Alto\Code\Highlight\Highlighter;
use Alto\Code\Highlight\Language\LanguageInterface;
use Alto\Code\Highlight\Parser\ParsedStream;
use Alto\Code\Highlight\Parser\ParsedToken;
use Alto\Code\Highlight\Scope;
use Alto\Code\Highlight\Tests\TestCase;
use Alto\Code\Highlight\Theme\AltoTheme;
use Alto\Code\Highlight\Theme\NoctisTheme;
use Alto\Code\Highlight\Theme\SolarTheme;
use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(Highlighter::class)]
final class HighlighterTest extends TestCase
{
    public function testCanBeInstantiatedWithTheme(): void
    {
        $theme = new SolarTheme();
        $highlighter = new Highlighter($theme);

        $this->assertInstanceOf(Highlighter::class, $highlighter);
    }

    public function testCanGetTheme(): void
    {
        $theme = new SolarTheme();
        $highlighter = new Highlighter($theme);

        $this->assertSame($theme, $highlighter->getTheme());
    }

    public function testRegistersPhpLanguageByDefault(): void
    {
        $theme = new SolarTheme();
        $highlighter = new Highlighter($theme);

        $html = $highlighter->highlight('<?php echo "test";', 'php');

        $this->assertStringContainsString('<pre class="alto-highlight language-php">', $html);
        $this->assertStringContainsString('</pre>', $html);
    }

    public function testCanHighlightSimplePhpCode(): void
    {
        $theme = new SolarTheme();
        $highlighter = new Highlighter($theme);

        $html = $highlighter->highlight('<?php echo "hello";', 'php');

        $this->assertStringContainsString('<pre class="alto-highlight language-php"><code class="language-php">', $html);
        $this->assertStringContainsString('</code></pre>', $html);
        $this->assertStringContainsString('hello', $html);
    }

    public function testNormalizesLanguageIdentifier(): void
    {
        $theme = new SolarTheme();
        $highlighter = new Highlighter($theme);

        $html1 = $highlighter->highlight('<?php $x = 1;', 'PHP');
        $html2 = $highlighter->highlight('<?php $x = 1;', '  php  ');

        $this->assertStringContainsString('<pre class="alto-highlight language-php">', $html1);
        $this->assertStringContainsString('<pre class="alto-highlight language-php">', $html2);
    }

    public function testHandlesPhpSnippetLanguage(): void
    {
        $theme = new SolarTheme();
        $highlighter = new Highlighter($theme);

        $html = $highlighter->highlight('$var = 42;', 'php-snippet');

        $this->assertStringContainsString('<pre class="alto-highlight language-php">', $html);
        $this->assertStringContainsString('$var', $html);
        $this->assertSame('$var = 42;', $this->extractCodeText($html));
        $this->assertStringNotContainsString('&lt;?php', $html);
    }

    public function testPhpLanguageParsesOneLineWithoutSyntheticContext(): void
    {
        $highlighter = new Highlighter(new AltoTheme());
        $code = '$page->getByRole("button")->click();';

        $html = $highlighter->highlight($code, 'php');

        self::assertSame($code, $this->extractCodeText($html));
        self::assertStringContainsString('<span class="alto-variable">$page</span>', $html);
        self::assertStringContainsString('<span class="alto-function">getByRole</span>', $html);
        self::assertStringNotContainsString('&lt;?php', $html);
        self::assertStringNotContainsString('alto-line-number', $html);
    }

    public function testOneLinePhpSupportsLineNumbersAndHighlighting(): void
    {
        $highlighter = new Highlighter(new AltoTheme());
        $code = '$page->getByRole("button")->click();';

        $html = $highlighter->highlight(
            $code,
            'php',
            lineNumbers: true,
            highlightLines: [1],
        );

        self::assertSame(1, substr_count($html, 'alto-line-number'));
        self::assertSame(1, substr_count($html, 'alto-highlighted'));
        self::assertStringContainsString('$page', $html);
        self::assertStringNotContainsString('&lt;?php', $html);
    }

    public function testThrowsExceptionForUnknownLanguage(): void
    {
        $theme = new SolarTheme();
        $highlighter = new Highlighter($theme);

        $this->expectException(LanguageNotFoundException::class);
        $highlighter->highlight('code', 'unknown-language');
    }

    public function testCanRegisterCustomLanguage(): void
    {
        $theme = new SolarTheme();
        $highlighter = new Highlighter($theme);

        $customLanguage = new class implements LanguageInterface {
            public function parse(string $code): ParsedStream
            {
                return new ParsedStream([
                    new ParsedToken($code, Scope::String),
                ]);
            }

            public function getIdentifier(): string
            {
                return 'custom';
            }
        };

        $highlighter->registerLanguage($customLanguage);
        $html = $highlighter->highlight('test', 'custom');

        $this->assertStringContainsString('<pre class="alto-highlight language-custom">', $html);
    }

    public function testCanHighlightWithLineNumbers(): void
    {
        $theme = new SolarTheme();
        $highlighter = new Highlighter($theme);

        $html = $highlighter->highlight('<?php echo "test";', 'php', true);

        $this->assertStringContainsString('alto-line-number', $html);
    }

    public function testCanHighlightSpecificLines(): void
    {
        $theme = new SolarTheme();
        $highlighter = new Highlighter($theme);

        $code = '<?php
$a = 1;
$b = 2;
$c = 3;';

        $html = $highlighter->highlight($code, 'php', true, [2, 3]);

        $this->assertStringContainsString('alto-highlighted', $html);
    }

    public function testEscapesHtmlEntities(): void
    {
        $theme = new SolarTheme();
        $highlighter = new Highlighter($theme);

        $html = $highlighter->highlight('<?php echo "<script>";', 'php');

        $this->assertStringContainsString('&lt;script&gt;', $html);
        $this->assertStringNotContainsString('<script>', $html);
    }

    public function testWorksWithNoctisTheme(): void
    {
        $theme = new NoctisTheme();
        $highlighter = new Highlighter($theme);

        $html = $highlighter->highlight('<?php $x = 1;', 'php');

        $this->assertStringContainsString('<pre class="alto-highlight language-php">', $html);
    }

    public function testHandlesEmptyCode(): void
    {
        $theme = new SolarTheme();
        $highlighter = new Highlighter($theme);

        $html = $highlighter->highlight('', 'php');

        $this->assertSame('<pre class="alto-highlight language-php"><code class="language-php"></code></pre>', $html);
    }

    public function testHandlesMultilineCode(): void
    {
        $theme = new SolarTheme();
        $highlighter = new Highlighter($theme);

        $code = '<?php
function test() {
    return 42;
}';

        $html = $highlighter->highlight($code, 'php');

        $this->assertStringContainsString('function', $html);
        $this->assertStringContainsString('return', $html);
        $this->assertStringContainsString('42', $html);
    }

    public function testHandlesCodeWithQuotes(): void
    {
        $theme = new SolarTheme();
        $highlighter = new Highlighter($theme);

        $html = $highlighter->highlight('<?php "test\'s quote";', 'php');

        $this->assertStringContainsString('&quot;', $html);
        $this->assertStringContainsString('&#039;', $html);
    }

    public function testPreservesWhitespaceInFormattedOutput(): void
    {
        $theme = new SolarTheme();
        $highlighter = new Highlighter($theme);

        $code = '<?php
$a = 1;

$b = 2;';

        $html = $highlighter->highlight($code, 'php');

        $this->assertStringContainsString("\n", $html);
    }

    public function testAppliesScopeClassesCorrectly(): void
    {
        $theme = new SolarTheme();
        $highlighter = new Highlighter($theme);

        $html = $highlighter->highlight('<?php function test() {}', 'php');

        $this->assertStringContainsString('<span class=', $html);
        $this->assertStringContainsString('>function</span>', $html);
        $this->assertStringContainsString('>test</span>', $html);
    }

    public function testHandlesLineNumbersWithoutHighlights(): void
    {
        $theme = new SolarTheme();
        $highlighter = new Highlighter($theme);

        $code = '<?php
$a = 1;
$b = 2;';

        $html = $highlighter->highlight($code, 'php', true, []);

        $this->assertStringContainsString('alto-line-number', $html);
        $this->assertStringNotContainsString('alto-highlighted', $html);
    }

    public function testHandlesCodeWithNewlinesInLineNumberMode(): void
    {
        $theme = new SolarTheme();
        $highlighter = new Highlighter($theme);

        $code = "<?php\n\$a = 1;\n\$b = 2;\n\$c = 3;";
        $html = $highlighter->highlight($code, 'php', true);

        $lineNumberCount = substr_count($html, 'alto-line-number');
        $this->assertGreaterThan(2, $lineNumberCount);
    }

    public function testFormatsLineNumbersCorrectly(): void
    {
        $theme = new SolarTheme();
        $highlighter = new Highlighter($theme);

        $html = $highlighter->highlight('<?php $x = 1;', 'php', true);

        $this->assertStringContainsString('alto-line-number', $html);
    }

    public function testHandlesHighlightedLinesCorrectly(): void
    {
        $theme = new SolarTheme();
        $highlighter = new Highlighter($theme);

        $code = "<?php\n\$a = 1;\n\$b = 2;";
        $html = $highlighter->highlight($code, 'php', true, [1, 2, 3]);

        $this->assertStringContainsString('alto-line-number', $html);
        $this->assertStringContainsString('$a', $html);
        $this->assertStringContainsString('$b', $html);
    }

    public function testCanProcessComplexPhpCode(): void
    {
        $theme = new SolarTheme();
        $highlighter = new Highlighter($theme);

        $code = '<?php
class MyClass {
    private $property;

    public function __construct($value) {
        $this->property = $value;
    }

    public function getValue() {
        return $this->property;
    }
}

$obj = new MyClass(42);
echo $obj->getValue();';

        $html = $highlighter->highlight($code, 'php');

        $this->assertStringContainsString('MyClass', $html);
        $this->assertStringContainsString('property', $html);
        $this->assertStringContainsString('getValue', $html);
    }

    public function testLanguageClassReflectsRequestedLanguage(): void
    {
        $theme = new SolarTheme();
        $highlighter = new Highlighter($theme);

        $html = $highlighter->highlight('[]', 'json');

        $this->assertStringContainsString('<pre class="alto-highlight language-json">', $html);
        $this->assertStringContainsString('<code class="language-json">', $html);
    }

    public function testHighlightAdapterAddsHljsClass(): void
    {
        $theme = new HighlightJsThemeAdapter('monokai', isDark: true);
        $highlighter = new Highlighter($theme);

        $html = $highlighter->highlight('<?php echo "hi";', 'php');

        $this->assertStringContainsString('<code class="language-php hljs">', $html);
    }

    public function testHandlesEmptyLanguageIdentifier(): void
    {
        $theme = new SolarTheme();
        $highlighter = new Highlighter($theme);

        $this->expectException(LanguageNotFoundException::class);
        $highlighter->highlight('code', '');
    }

    public function testGetEmbeddedRegistry(): void
    {
        $highlighter = new Highlighter(new SolarTheme());
        $this->assertNotNull($highlighter->getEmbeddedRegistry());
    }

    public function testCanInjectCustomEmbeddedRegistry(): void
    {
        $registry = new EmbeddedLanguageRegistry([
            [
                'host' => 'html',
                'triggers' => [],
            ],
        ]);

        $highlighter = new Highlighter(new AltoTheme(), $registry);

        $plan = $highlighter->getEmbeddedRegistry()->getPlan('html');

        self::assertNotNull($plan);
        self::assertCount(0, $plan->getTriggers());
    }

    public function testCanToggleEmbeddingForLanguagePair(): void
    {
        $highlighter = new Highlighter(new AltoTheme());
        $html = '<script>var x = 1;</script>';

        $output = $highlighter->highlight($html, 'html');
        self::assertStringContainsString('<span class="alto-keyword">var</span>', $output);

        $highlighter->setEmbeddingEnabled('html', 'javascript', false);

        $outputWithoutEmbedding = $highlighter->highlight($html, 'html');
        self::assertStringNotContainsString('<span class="alto-keyword">var</span>', $outputWithoutEmbedding);
        self::assertStringContainsString('var x = 1;', $outputWithoutEmbedding);
    }

    public function testMarkdownFencedCodeBlocksUseEmbeddedLanguages(): void
    {
        $highlighter = new Highlighter(new AltoTheme());

        $md = "```json\n{\"key\": \"value\"}\n```";
        $output = $highlighter->highlight($md, 'markdown');

        self::assertStringContainsString('alto-string', $output);
        self::assertStringContainsString('&quot;key&quot;', $output);
    }

    public function testTwigBlockEmbeddingUsesCustomRegistry(): void
    {
        $registry = new EmbeddedLanguageRegistry([
            [
                'host' => 'twig',
                'triggers' => [
                    EmbeddedTrigger::block('scripts', 'javascript'),
                ],
            ],
        ]);

        $highlighter = new Highlighter(new AltoTheme(), $registry);

        $twig = "{% block scripts %}\nvar x = 10;\n{% endblock %}";
        $output = $highlighter->highlight($twig, 'twig');

        self::assertStringContainsString('alto-keyword', $output);
    }

    public function testDetectsVendorContainerClass(): void
    {
        $highlighter = new Highlighter(new SolarTheme());
        $html = $highlighter->highlight('<?php echo "test";', 'php');

        // Should not add vendor container classes when none are available
        self::assertStringNotContainsString('hljs', $html);
    }

    public function testHighlightWithoutLineNumbersAndHighlights(): void
    {
        $highlighter = new Highlighter(new SolarTheme());
        $html = $highlighter->highlight('<?php echo "test";', 'php', false, []);

        self::assertStringNotContainsString('alto-line-number', $html);
        self::assertStringNotContainsString('alto-highlighted', $html);
    }

    public function testHighlightWithEmptyHighlightedLines(): void
    {
        $highlighter = new Highlighter(new SolarTheme());
        $code = "<?php\n\$a = 1;\n\$b = 2;";
        $html = $highlighter->highlight($code, 'php', true, []);

        self::assertStringContainsString('alto-line-number', $html);
        self::assertStringNotContainsString('alto-highlighted', $html);
    }

    public function testJoinsCssClassesCorrectly(): void
    {
        $highlighter = new Highlighter(new SolarTheme());
        $html = $highlighter->highlight('<?php function test() {}', 'php');

        // Verify that multiple CSS classes are properly joined
        self::assertStringContainsString('class="', $html);
        self::assertStringContainsString('</span>', $html);
    }

    public function testFormatsClassAttributeWithMultipleClasses(): void
    {
        $theme = new SolarTheme();
        $highlighter = new Highlighter($theme);

        $html = $highlighter->highlight('<?php $var = 42;', 'php');

        // Should have properly formatted class attributes
        self::assertStringContainsString('class="', $html);
        self::assertStringContainsString('solar-', $html);
    }

    public function testLineNumberFormattingWithHighlightedLines(): void
    {
        $highlighter = new Highlighter(new SolarTheme());
        $code = "<?php\n\$a = 1;\n\$b = 2;\n\$c = 3;";
        $html = $highlighter->highlight($code, 'php', true, [2]);

        self::assertStringContainsString('alto-line-number', $html);
        self::assertStringContainsString('alto-highlighted', $html);
    }

    public function testHandlesCodeWithOnlyWhitespace(): void
    {
        $highlighter = new Highlighter(new SolarTheme());
        $html = $highlighter->highlight('   ', 'php');

        self::assertStringContainsString('<pre class="alto-highlight language-php">', $html);
        self::assertStringContainsString('</pre>', $html);
    }

    public function testHighlightPreservesOriginalLanguageIdentifier(): void
    {
        $highlighter = new Highlighter(new SolarTheme());
        $html = $highlighter->highlight('{}', 'json');

        self::assertStringContainsString('language-json', $html);
        self::assertStringNotContainsString('language-JSON', $html);
    }

    public function testCanToggleMultipleEmbeddingPairs(): void
    {
        $highlighter = new Highlighter(new AltoTheme());

        $highlighter->setEmbeddingEnabled('html', 'javascript', false);
        $highlighter->setEmbeddingEnabled('html', 'css', false);

        $html = '<script>var x = 1;</script><style>body{}</style>';
        $output = $highlighter->highlight($html, 'html');

        // JavaScript keyword 'var' should NOT be highlighted when embedding is disabled
        self::assertStringNotContainsString('<span class="alto-keyword">var</span>', $output);
        // CSS 'body' selector should NOT be highlighted as CSS when embedding is disabled
        self::assertStringNotContainsString('alto-selector', $output);
        // But HTML tags should still be highlighted as they are the host language
        self::assertStringContainsString('alto-keyword', $output);
    }

    public function testReEnablingEmbeddingRestoresLanguage(): void
    {
        $highlighter = new Highlighter(new AltoTheme());
        $html = '<script>var x = 1;</script>';

        $highlighter->setEmbeddingEnabled('html', 'javascript', false);
        $outputDisabled = $highlighter->highlight($html, 'html');
        self::assertStringNotContainsString('<span class="alto-keyword">var</span>', $outputDisabled);

        $highlighter->setEmbeddingEnabled('html', 'javascript', true);
        $outputEnabled = $highlighter->highlight($html, 'html');
        self::assertStringContainsString('<span class="alto-keyword">var</span>', $outputEnabled);
    }

    public function testConstructorThrowsExceptionForInvalidLanguage(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('All languages must implement LanguageInterface.');

        new Highlighter(new AltoTheme(), null, [new \stdClass()]);
    }

    public function testSanitizesLanguageClassWithSpecialCharacters(): void
    {
        $highlighter = new Highlighter(new AltoTheme());
        // Create a mock language with special characters
        $mockLanguage = new class implements LanguageInterface {
            public function getIdentifier(): string
            {
                return 'my!!!special@@@lang###';
            }

            public function parse(string $code): ParsedStream
            {
                return new ParsedStream([new ParsedToken('test', Scope::MarkupText)]);
            }
        };

        $highlighter->registerLanguage($mockLanguage);
        $html = $highlighter->highlight('test', 'my!!!special@@@lang###');

        // Language class should be sanitized: special chars replaced with dashes, trimmed
        self::assertStringContainsString('language-my-special-lang', $html);
        self::assertStringNotContainsString('!!!', $html);
        self::assertStringNotContainsString('@@@', $html);
        self::assertStringNotContainsString('###', $html);
    }

    public function testEmbeddedPhpWithoutOpeningTag(): void
    {
        $highlighter = new Highlighter(new AltoTheme());

        // Markdown with embedded PHP lacking <?php
        $markdown = <<<'MD'
# Test

```php
echo "hello";
$var = 42;
```
MD;

        $html = $highlighter->highlight($markdown, 'markdown');

        // PHP code should be parsed correctly
        self::assertStringContainsString('echo', $html);
        self::assertStringContainsString('hello', $html);
        self::assertStringContainsString('$var', $html);
        // Should have PHP syntax highlighting
        self::assertStringContainsString('alto-keyword', $html);
        // The <?php tag should NOT appear in the output (it was added internally then removed)
        self::assertStringNotContainsString('&lt;?php', $html);
    }

    public function testEmbeddedPhpWithOpeningTagUnmodified(): void
    {
        $highlighter = new Highlighter(new AltoTheme());

        // Markdown with embedded PHP including <?php
        $markdown = <<<'MD'
# Test

```php
<?php
echo "world";
```
MD;

        $html = $highlighter->highlight($markdown, 'markdown');

        // PHP code should be parsed correctly
        self::assertStringContainsString('echo', $html);
        self::assertStringContainsString('world', $html);
        // The <?php tag SHOULD appear in the output (it was in the original code)
        self::assertStringContainsString('&lt;?php', $html);
        // Should have PHP syntax highlighting
        self::assertStringContainsString('alto-keyword', $html);
    }

    private function extractCodeText(string $html): string
    {
        self::assertSame(1, preg_match('/<code[^>]*>(.*)<\/code>/s', $html, $matches));

        return html_entity_decode(strip_tags($matches[1]), ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
    }
}
