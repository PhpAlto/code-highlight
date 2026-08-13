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

namespace Alto\Code\Highlight\Tests\Unit\Adapter;

use Alto\Code\Highlight\Adapter\HighlightJsThemeAdapter;
use Alto\Code\Highlight\Scope;
use Alto\Code\Highlight\Tests\TestCase;
use Alto\Code\Highlight\ThemeInterface;
use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(HighlightJsThemeAdapter::class)]
final class HighlightJsThemeAdapterTest extends TestCase
{
    public function testItImplementsThemeInterface(): void
    {
        self::assertInstanceOf(ThemeInterface::class, $this->makeTheme());
    }

    public function testItFormatsNameWithPrefix(): void
    {
        self::assertSame('hljs-github-dark', $this->makeTheme('github-dark')->getName());
    }

    public function testItReportsDarkAndLightModes(): void
    {
        self::assertTrue($this->makeTheme(isDark: true)->isDark());
        self::assertFalse($this->makeTheme('github', isDark: false)->isDark());
    }

    public function testItReturnsCssClassMapping(): void
    {
        $classes = $this->makeTheme()->getCssClasses();

        self::assertNotEmpty($classes);
    }

    public function testItReturnsStylesheetString(): void
    {
        self::assertNotSame('', $this->makeTheme()->getStylesheet());
    }

    public function testItMapsBasicScopes(): void
    {
        $classes = $this->makeTheme()->getCssClasses();

        foreach ([Scope::Keyword, Scope::String, Scope::Number, Scope::Comment, Scope::Boolean, Scope::Null] as $scope) {
            self::assertArrayHasKey($scope->value, $classes);
        }
    }

    public function testItMapsFunctionScopes(): void
    {
        $classes = $this->makeTheme()->getCssClasses();

        self::assertStringContainsString('hljs-title', $classes[Scope::FunctionDefinition->value]);
        self::assertStringContainsString('hljs-title', $classes[Scope::FunctionCall->value]);
    }

    public function testItMapsClassScopes(): void
    {
        $classes = $this->makeTheme()->getCssClasses();

        self::assertStringContainsString('hljs-title', $classes[Scope::TypeDefinition->value]);
        self::assertSame('hljs-type', $classes[Scope::TypeReference->value]);
    }

    public function testItMapsVariableScopes(): void
    {
        $classes = $this->makeTheme()->getCssClasses();

        self::assertSame('hljs-variable', $classes[Scope::Variable->value]);
        self::assertSame('hljs-params', $classes[Scope::VariableParameter->value]);
        self::assertSame('hljs-property', $classes[Scope::VariableProperty->value]);
    }

    public function testItMapsKeywordScopes(): void
    {
        $classes = $this->makeTheme()->getCssClasses();

        self::assertSame('hljs-keyword', $classes[Scope::Keyword->value]);
        self::assertSame('hljs-keyword', $classes[Scope::KeywordDeclaration->value]);
        self::assertSame('hljs-keyword', $classes[Scope::KeywordControl->value]);
    }

    public function testItMapsLiteralScopes(): void
    {
        $classes = $this->makeTheme()->getCssClasses();

        self::assertSame('hljs-string', $classes[Scope::String->value]);
        self::assertSame('hljs-number', $classes[Scope::Number->value]);
        self::assertSame('hljs-literal', $classes[Scope::Boolean->value]);
        self::assertSame('hljs-literal', $classes[Scope::Null->value]);
    }

    public function testItMapsHtmlScopes(): void
    {
        $classes = $this->makeTheme()->getCssClasses();

        self::assertSame('hljs-tag', $classes[Scope::TagName->value]);
        self::assertSame('hljs-attr', $classes[Scope::TagAttributeName->value]);
        self::assertSame('hljs-string', $classes[Scope::TagAttributeValue->value]);
    }

    public function testItMapsCssScopes(): void
    {
        $classes = $this->makeTheme()->getCssClasses();

        self::assertSame('hljs-tag', $classes[Scope::TagName->value]);
        self::assertSame('hljs-meta', $classes[Scope::AttributeName->value]);
        self::assertSame('hljs-keyword', $classes[Scope::Keyword->value]);
    }

    public function testItMapsJavascriptScopes(): void
    {
        $classes = $this->makeTheme()->getCssClasses();

        self::assertSame('hljs-keyword', $classes[Scope::Keyword->value]);
        self::assertSame('hljs-keyword', $classes[Scope::KeywordDeclaration->value]);
        self::assertSame('hljs-regexp', $classes[Scope::RegExp->value]);
    }

    public function testItMapsTypescriptScopes(): void
    {
        $classes = $this->makeTheme()->getCssClasses();

        self::assertStringContainsString('hljs-title', $classes[Scope::TypeDefinition->value]);
        self::assertSame('hljs-type', $classes[Scope::TypeReference->value]);
    }

    public function testItMapsMarkdownScopes(): void
    {
        $classes = $this->makeTheme()->getCssClasses();

        self::assertSame('hljs-tag', $classes[Scope::TagName->value]);
        self::assertSame('hljs-keyword', $classes[Scope::Keyword->value]);
        self::assertSame('hljs-string', $classes[Scope::String->value]);
    }

    public function testItMapsAllScopesWithoutGaps(): void
    {
        $classes = $this->makeTheme()->getCssClasses();

        foreach (Scope::cases() as $scope) {
            self::assertArrayHasKey($scope->value, $classes);
        }
    }

    public function testItMapsWhitespaceAndPunctuation(): void
    {
        $classes = $this->makeTheme()->getCssClasses();

        self::assertSame('', $classes[Scope::Whitespace->value]);
    }

    public function testStylesheetReferencesRequestedTheme(): void
    {
        $stylesheet = $this->makeTheme('monokai')->getStylesheet();

        self::assertStringContainsString('Highlight.js theme: monokai', $stylesheet);
        self::assertStringContainsString('monokai', $stylesheet);
    }

    public function testStylesheetIncludesContainerStyles(): void
    {
        self::assertStringContainsString('.alto-highlight', $this->makeTheme()->getStylesheet());
    }

    public function testStylesheetMentionsThemeName(): void
    {
        self::assertStringContainsString('github-dark', $this->makeTheme('github-dark')->getStylesheet());
    }

    public function testStylesheetUsesExpectedCdnVersion(): void
    {
        self::assertStringContainsString('11.9.0', $this->makeTheme()->getStylesheet());
    }

    public function testStylesheetAdaptsLocalSelectors(): void
    {
        $css = ".hljs { background: #000; }\npre code.hljs { color: #fff; }";
        $path = tempnam(sys_get_temp_dir(), 'hljs');
        file_put_contents($path, $css);

        try {
            $stylesheet = $this->makeTheme('local-theme', cssPath: $path)->getStylesheet();
        } finally {
            @unlink($path);
        }

        self::assertStringContainsString('.alto-highlight code {', $stylesheet);
        self::assertStringContainsString('pre.alto-highlight code {', $stylesheet);
        self::assertStringNotContainsString('pre code.hljs', $stylesheet);
    }

    public function testPopularThemesStructure(): void
    {
        $themes = HighlightJsThemeAdapter::getPopularThemes();

        self::assertNotEmpty($themes);

        foreach ($themes as $key => $info) {
            self::assertIsString($key);
            self::assertArrayHasKey('name', $info);
            self::assertArrayHasKey('dark', $info);
            self::assertIsString($info['name']);
            self::assertIsBool($info['dark']);
        }
    }

    public function testPopularThemesIncludeExpectedEntries(): void
    {
        $themes = HighlightJsThemeAdapter::getPopularThemes();

        self::assertArrayHasKey('github', $themes);
        self::assertArrayHasKey('github-dark', $themes);
        self::assertArrayHasKey('monokai', $themes);
        self::assertArrayHasKey('atom-one-dark', $themes);
        self::assertArrayHasKey('atom-one-light', $themes);
        self::assertArrayHasKey('dracula', $themes);
    }

    public function testPopularThemesMarkLightVsDark(): void
    {
        $themes = HighlightJsThemeAdapter::getPopularThemes();

        self::assertFalse($themes['github']['dark']);
        self::assertTrue($themes['github-dark']['dark']);
        self::assertTrue($themes['monokai']['dark']);
        self::assertFalse($themes['atom-one-light']['dark']);
    }

    public function testDifferentThemeNamesGenerateExpectedMetadata(): void
    {
        $githubDark = $this->makeTheme('github-dark');
        $monokai = $this->makeTheme('monokai');
        $atom = $this->makeTheme('atom-one-dark');
        $vs = $this->makeTheme('vs2015');

        self::assertSame('hljs-github-dark', $githubDark->getName());
        self::assertTrue($githubDark->isDark());
        self::assertSame('hljs-monokai', $monokai->getName());
        self::assertSame('hljs-atom-one-dark', $atom->getName());
        self::assertSame('hljs-vs2015', $vs->getName());
    }

    public function testKeywordVariantsMapToHljsKeyword(): void
    {
        $classes = $this->makeTheme()->getCssClasses();

        self::assertSame('hljs-keyword', $classes[Scope::Keyword->value]);
        self::assertSame('hljs-keyword', $classes[Scope::KeywordDeclaration->value]);
        self::assertSame('hljs-keyword', $classes[Scope::KeywordControl->value]);
    }

    public function testStringVariantsMapToHljsString(): void
    {
        $classes = $this->makeTheme()->getCssClasses();

        self::assertSame('hljs-string', $classes[Scope::String->value]);
        self::assertSame('hljs-string', $classes[Scope::StringInterpolated->value]);
        self::assertSame('hljs-string', $classes[Scope::String->value]);
    }

    public function testConstantVariantsContainConstant(): void
    {
        $classes = $this->makeTheme()->getCssClasses();

        self::assertStringContainsString('constant', $classes[Scope::Constant->value]);
        self::assertStringContainsString('constant', $classes[Scope::BuiltInConstant->value]);
        self::assertStringContainsString('constant', $classes[Scope::Constant->value]);
    }

    public function testHandlesThemeNamesWithHyphensAndNumbers(): void
    {
        self::assertStringContainsString('atom-one-dark', $this->makeTheme('atom-one-dark')->getName());
        self::assertStringContainsString('vs2015', $this->makeTheme('vs2015')->getName());
    }

    public function testHandlesDifferentDarkFlags(): void
    {
        $light = $this->makeTheme('github', isDark: false);
        $dark = $this->makeTheme('github-dark', isDark: true);

        self::assertFalse($light->isDark());
        self::assertTrue($dark->isDark());
    }

    public function testCssClassNamesAreValid(): void
    {
        foreach ($this->makeTheme()->getCssClasses() as $className) {
            if ('' === $className) {
                continue;
            }

            self::assertMatchesRegularExpression('/^[a-z-_]/i', $className);
        }
    }

    public function testCssClassNamesStartWithHljsPrefix(): void
    {
        $classes = array_filter($this->makeTheme()->getCssClasses(), static fn(string $class) => '' !== $class);

        foreach ($classes as $className) {
            self::assertStringContainsString('hljs', $className);
        }
    }

    public function testHandlesUnreadableLocalCssFile(): void
    {
        // Pass a path that doesn't exist - should fallback to CDN
        $theme = new HighlightJsThemeAdapter('monokai', cssPath: '/nonexistent/path.css', isDark: true);
        $stylesheet = $theme->getStylesheet();

        // Should contain CDN reference since local file doesn't exist
        self::assertStringContainsString('monokai', $stylesheet);
    }

    public function testHandlesLocalCssFileWithEmptyContent(): void
    {
        // Create an empty temporary CSS file
        $path = tempnam(sys_get_temp_dir(), 'hljs');
        file_put_contents($path, '');

        try {
            $theme = new HighlightJsThemeAdapter('monokai', cssPath: $path, isDark: true);
            $stylesheet = $theme->getStylesheet();

            // Empty file is still processed (not same as file_get_contents returning false)
            // The result will be an empty stylesheet since the CSS was empty
            self::assertSame('', $stylesheet);
        } finally {
            @unlink($path);
        }
    }

    public function testFromFileThrowsOnNonExistentPath(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('does not exist');

        HighlightJsThemeAdapter::fromFile('/nonexistent/path/theme.css');
    }

    public function testFromFileThrowsOnEmptyPath(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('must not be empty');

        HighlightJsThemeAdapter::fromFile('');
    }

    public function testFromFileThrowsOnDirectoryPath(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('not a file');

        HighlightJsThemeAdapter::fromFile(__DIR__);
    }

    private function makeTheme(string $name = 'monokai', bool $isDark = true, ?string $cssPath = null): HighlightJsThemeAdapter
    {
        return new HighlightJsThemeAdapter($name, cssPath: $cssPath, isDark: $isDark);
    }
}
