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

namespace Alto\Code\Highlight\Tests\Unit\Language;

use Alto\Code\Highlight\Embedded\EmbeddedLanguageRegistry;
use Alto\Code\Highlight\Language\CssLanguage;
use Alto\Code\Highlight\Language\EmbeddedLanguageContext;
use Alto\Code\Highlight\Language\HtmlLanguage;
use Alto\Code\Highlight\Language\JavaScriptLanguage;
use Alto\Code\Highlight\Language\LanguageInterface;
use Alto\Code\Highlight\Language\SvgLanguage;
use Alto\Code\Highlight\Parser\ParsedToken;
use Alto\Code\Highlight\Tests\TestCase;
use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(HtmlLanguage::class)]
#[CoversClass(SvgLanguage::class)]
#[CoversClass(CssLanguage::class)]
#[CoversClass(JavaScriptLanguage::class)]
#[CoversClass(EmbeddedLanguageRegistry::class)]
final class EmbeddedLanguageTest extends TestCase
{
    private HtmlLanguage $htmlLanguage;

    private SvgLanguage $svgLanguage;

    private CssLanguage $cssLanguage;

    private JavaScriptLanguage $jsLanguage;

    private EmbeddedLanguageRegistry $registry;

    /**
     * @var array<string, LanguageInterface>
     */
    private array $languages;

    protected function setUp(): void
    {
        parent::setUp();

        $this->htmlLanguage = new HtmlLanguage();
        $this->svgLanguage = new SvgLanguage();
        $this->cssLanguage = new CssLanguage();
        $this->jsLanguage = new JavaScriptLanguage();
        $this->registry = new EmbeddedLanguageRegistry();

        $this->languages = [
            'css' => $this->cssLanguage,
            'javascript' => $this->jsLanguage,
            'html' => $this->htmlLanguage,
            'svg' => $this->svgLanguage,
        ];
    }

    public function testHtmlStyleTagHighlightsCssIdenticallyToStandalone(): void
    {
        $cssCode = <<<'CSS'
body {
    background-color: #f0f0f0;
    font-family: Arial, sans-serif;
}
.container {
    max-width: 1200px;
}
CSS;

        $htmlWithCss = <<<'HTML'
<style>
body {
    background-color: #f0f0f0;
    font-family: Arial, sans-serif;
}
.container {
    max-width: 1200px;
}
</style>
HTML;

        // Parse standalone CSS
        $standaloneCssStream = $this->cssLanguage->parse($cssCode);

        // Parse HTML with embedded CSS
        $context = $this->createEmbeddingContext('html');
        $htmlStream = $this->htmlLanguage->parseWithEmbedding($htmlWithCss, $context);

        // Extract just the CSS tokens from the HTML stream (between <style> and </style>)
        $embeddedCssTokens = $this->extractEmbeddedTokens($htmlStream->getTokens(), 'style');

        // Compare the CSS tokens
        $standaloneCssTokens = $standaloneCssStream->getTokens();

        self::assertEmbeddedTokensMatchStandalone($standaloneCssTokens, $embeddedCssTokens, 'CSS in HTML <style>');
    }

    public function testHtmlScriptTagHighlightsJavascriptIdenticallyToStandalone(): void
    {
        $jsCode = <<<'JS'
function greet(name) {
    const message = `Hello, ${name}!`;
    return message;
}
JS;

        $htmlWithJs = <<<'HTML'
<script>
function greet(name) {
    const message = `Hello, ${name}!`;
    return message;
}
</script>
HTML;

        // Parse standalone JavaScript
        $standaloneJsStream = $this->jsLanguage->parse($jsCode);

        // Parse HTML with embedded JavaScript
        $context = $this->createEmbeddingContext('html');
        $htmlStream = $this->htmlLanguage->parseWithEmbedding($htmlWithJs, $context);

        // Extract just the JavaScript tokens from the HTML stream
        $embeddedJsTokens = $this->extractEmbeddedTokens($htmlStream->getTokens(), 'script');

        // Compare the JavaScript tokens
        $standaloneJsTokens = $standaloneJsStream->getTokens();

        self::assertEmbeddedTokensMatchStandalone($standaloneJsTokens, $embeddedJsTokens, 'JavaScript in HTML <script>');
    }

    public function testSvgStyleTagHighlightsCssIdenticallyToStandalone(): void
    {
        $cssCode = <<<'CSS'
.cls-1 {
    fill: #ff0000;
    stroke: #000000;
}
CSS;

        $svgWithCss = <<<'SVG'
<svg>
<style>
.cls-1 {
    fill: #ff0000;
    stroke: #000000;
}
</style>
</svg>
SVG;

        // Parse standalone CSS
        $standaloneCssStream = $this->cssLanguage->parse($cssCode);

        // Parse SVG with embedded CSS
        $context = $this->createEmbeddingContext('svg');
        $svgStream = $this->svgLanguage->parseWithEmbedding($svgWithCss, $context);

        // Extract just the CSS tokens from the SVG stream
        $embeddedCssTokens = $this->extractEmbeddedTokens($svgStream->getTokens(), 'style');

        // Compare the CSS tokens
        $standaloneCssTokens = $standaloneCssStream->getTokens();

        self::assertEmbeddedTokensMatchStandalone($standaloneCssTokens, $embeddedCssTokens, 'CSS in SVG <style>');
    }

    public function testHtmlWithMultipleEmbeddedLanguages(): void
    {
        $html = <<<'HTML'
<!DOCTYPE html>
<html>
<head>
    <style>
        body { margin: 0; }
    </style>
</head>
<body>
    <script>
        console.log("test");
    </script>
</body>
</html>
HTML;

        $context = $this->createEmbeddingContext('html');
        $stream = $this->htmlLanguage->parseWithEmbedding($html, $context);

        // Should contain CSS tokens
        $tokens = $stream->getTokens();
        $tokenTexts = array_map(fn(ParsedToken $t) => $t->getText(), $tokens);
        $fullText = implode('', $tokenTexts);

        self::assertStringContainsString('body', $fullText);
        self::assertStringContainsString('margin', $fullText);
        self::assertStringContainsString('console.log', $fullText);
    }

    public function testNestedStyleTagsDoNotBreakParsing(): void
    {
        $html = <<<'HTML'
<div>
    <style>
        .outer { color: red; }
    </style>
    <div>
        <style>
            .inner { color: blue; }
        </style>
    </div>
</div>
HTML;

        $context = $this->createEmbeddingContext('html');
        $stream = $this->htmlLanguage->parseWithEmbedding($html, $context);

        self::assertGreaterThan(0, $stream->count());
    }

    /**
     * Create an embedding context that can resolve embedded languages.
     */
    private function createEmbeddingContext(string $hostLanguage): EmbeddedLanguageContext
    {
        $plan = $this->registry->getPlan($hostLanguage);

        return EmbeddedLanguageContext::fromResolver(
            fn(string $languageIdentifier, string $code) => $this->languages[strtolower($languageIdentifier)]->parse($code),
            $plan,
        );
    }

    /**
     * Extract tokens that belong to embedded content between opening and closing tags.
     *
     * @param list<ParsedToken> $tokens
     *
     * @return list<ParsedToken>
     */
    private function extractEmbeddedTokens(array $tokens, string $tagName): array
    {
        $embeddedTokens = [];
        $state = 'outside'; // outside, in_open_tag, inside, in_close_tag
        $seenTagName = false;

        for ($i = 0; $i < count($tokens); ++$i) {
            $token = $tokens[$i];
            $text = $token->getText();

            switch ($state) {
                case 'outside':
                    // Look for opening <
                    if ('<' === $text) {
                        // Check if next token is our tag name
                        if ($i + 1 < count($tokens) && $tokens[$i + 1]->getText() === $tagName) {
                            $state = 'in_open_tag';
                            $seenTagName = false;
                        }
                    }
                    break;

                case 'in_open_tag':
                    // We're inside the opening tag, wait for the closing >
                    if ('>' === $text) {
                        $state = 'inside';
                    }
                    break;

                case 'inside':
                    // Check for closing tag start
                    if ('</' === $text || '<' === $text) {
                        // Look ahead to see if this is our closing tag
                        if ($i + 1 < count($tokens)) {
                            $nextText = $tokens[$i + 1]->getText();
                            if ('</' === $text || ('<' === $text && $i + 1 < count($tokens) && '/' === $tokens[$i + 1]->getText())) {
                                // Check if the tag name follows
                                $checkIdx = '</' === $text ? $i + 1 : $i + 2;
                                if ($checkIdx < count($tokens) && $tokens[$checkIdx]->getText() === $tagName) {
                                    // This is our closing tag
                                    $state = 'outside';
                                    break;
                                }
                            }
                        }
                    }
                    // Collect this token as part of embedded content
                    $embeddedTokens[] = $token;
                    break;
            }
        }

        return $embeddedTokens;
    }

    /**
     * Assert that embedded tokens match standalone tokens.
     *
     * @param list<ParsedToken> $standaloneTokens
     * @param list<ParsedToken> $embeddedTokens
     */
    private static function assertEmbeddedTokensMatchStandalone(array $standaloneTokens, array $embeddedTokens, string $message): void
    {
        // Filter out leading/trailing whitespace for comparison
        $standaloneTokens = self::trimWhitespaceTokens($standaloneTokens);
        $embeddedTokens = self::trimWhitespaceTokens($embeddedTokens);

        self::assertCount(
            count($standaloneTokens),
            $embeddedTokens,
            sprintf('%s: Token count mismatch. Standalone: %d, Embedded: %d', $message, count($standaloneTokens), count($embeddedTokens)),
        );

        foreach ($standaloneTokens as $index => $standaloneToken) {
            $embeddedToken = $embeddedTokens[$index];

            self::assertSame(
                $standaloneToken->getText(),
                $embeddedToken->getText(),
                sprintf('%s: Token text mismatch at index %d', $message, $index),
            );

            self::assertSame(
                $standaloneToken->getScope(),
                $embeddedToken->getScope(),
                sprintf(
                    '%s: Scope mismatch at index %d for text "%s". Standalone: %s, Embedded: %s',
                    $message,
                    $index,
                    $standaloneToken->getText(),
                    $standaloneToken->getScope()->value,
                    $embeddedToken->getScope()->value,
                ),
            );
        }
    }

    /**
     * Trim leading and trailing whitespace tokens.
     *
     * @param list<ParsedToken> $tokens
     *
     * @return list<ParsedToken>
     */
    private static function trimWhitespaceTokens(array $tokens): array
    {
        // Remove leading whitespace
        while (!empty($tokens) && preg_match('/^\s+$/', $tokens[0]->getText())) {
            array_shift($tokens);
        }

        // Remove trailing whitespace
        while (!empty($tokens) && preg_match('/^\s+$/', $tokens[count($tokens) - 1]->getText())) {
            array_pop($tokens);
        }

        return $tokens;
    }
}
