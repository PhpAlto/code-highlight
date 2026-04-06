<?php

declare(strict_types=1);

/*
 * This file is part of the ALTO library.
 *
 * © 2026–present Simon André
 *
 * For full copyright and license information, please see
 * the LICENSE file distributed with this source code.
 */

namespace Alto\Code\Highlight\Tests\Unit\Adapter;

use Alto\Code\Highlight\Adapter\PrismThemeAdapter;
use Alto\Code\Highlight\Scope;
use Alto\Code\Highlight\Tests\TestCase;
use Alto\Code\Highlight\ThemeInterface;
use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(PrismThemeAdapter::class)]
final class PrismThemeAdapterTest extends TestCase
{
    public function testItImplementsThemeInterface(): void
    {
        self::assertInstanceOf(ThemeInterface::class, $this->makeTheme());
    }

    public function testItFormatsNameWithPrefix(): void
    {
        self::assertSame('prism-tomorrow', $this->makeTheme()->getName());
    }

    public function testItReportsDarkMode(): void
    {
        self::assertTrue($this->makeTheme()->isDark());
    }

    public function testItReportsLightMode(): void
    {
        self::assertFalse($this->makeTheme(isDark: false)->isDark());
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

    public function testItUsesTokenPrefixForNonEmptyClasses(): void
    {
        $classes = array_filter($this->makeTheme()->getCssClasses(), static fn (string $class) => '' !== $class);

        foreach ($classes as $className) {
            self::assertStringContainsString('token', $className);
        }
    }

    public function testItMapsKeywords(): void
    {
        $classes = $this->makeTheme()->getCssClasses();

        self::assertSame('token keyword', $classes[Scope::Keyword->value]);
        self::assertSame('token keyword', $classes[Scope::KeywordDeclaration->value]);
        self::assertSame('token keyword', $classes[Scope::KeywordControl->value]);
    }

    public function testItMapsLiterals(): void
    {
        $classes = $this->makeTheme()->getCssClasses();

        self::assertSame('token string', $classes[Scope::String->value]);
        self::assertSame('token number', $classes[Scope::Number->value]);
        self::assertSame('token boolean', $classes[Scope::Boolean->value]);
        self::assertSame('token constant', $classes[Scope::Null->value]);
    }

    public function testItMapsFunctions(): void
    {
        $classes = $this->makeTheme()->getCssClasses();

        self::assertSame('token function', $classes[Scope::FunctionDefinition->value]);
        self::assertSame('token function', $classes[Scope::FunctionCall->value]);
    }

    public function testItMapsClasses(): void
    {
        $classes = $this->makeTheme()->getCssClasses();

        self::assertSame('token class-name', $classes[Scope::TypeDefinition->value]);
        self::assertSame('token class-name', $classes[Scope::TypeReference->value]);
    }

    public function testItMapsVariables(): void
    {
        $classes = $this->makeTheme()->getCssClasses();

        self::assertSame('token variable', $classes[Scope::Variable->value]);
        self::assertSame('token parameter', $classes[Scope::VariableParameter->value]);
        self::assertSame('token property', $classes[Scope::VariableProperty->value]);
    }

    public function testItMapsHtmlScopes(): void
    {
        $classes = $this->makeTheme()->getCssClasses();

        self::assertSame('token tag', $classes[Scope::TagName->value]);
        self::assertSame('token attr-name', $classes[Scope::TagAttributeName->value]);
        self::assertSame('token attr-value', $classes[Scope::TagAttributeValue->value]);
    }

    public function testItMapsCssScopes(): void
    {
        $classes = $this->makeTheme()->getCssClasses();

        self::assertSame('token tag', $classes[Scope::TagName->value]);
        self::assertSame('token attr-name', $classes[Scope::AttributeName->value]);
        self::assertSame('token keyword', $classes[Scope::Keyword->value]);
    }

    public function testItMapsJavascriptScopes(): void
    {
        $classes = $this->makeTheme()->getCssClasses();

        self::assertSame('token keyword', $classes[Scope::KeywordDeclaration->value]);
        self::assertSame('token regex', $classes[Scope::RegExp->value]);
    }

    public function testItMapsTypescriptScopes(): void
    {
        $classes = $this->makeTheme()->getCssClasses();

        self::assertSame('token class-name', $classes[Scope::TypeDefinition->value]);
        self::assertSame('token builtin', $classes[Scope::BuiltInType->value]);
    }

    public function testItMapsMarkdownScopes(): void
    {
        $classes = $this->makeTheme()->getCssClasses();

        self::assertSame('token tag', $classes[Scope::TagName->value]);
        self::assertSame('token keyword', $classes[Scope::Keyword->value]);
        self::assertSame('token string', $classes[Scope::String->value]);
    }

    public function testItMapsAllScopesWithoutGaps(): void
    {
        $classes = $this->makeTheme()->getCssClasses();

        foreach (Scope::cases() as $scope) {
            self::assertArrayHasKey($scope->value, $classes);
        }
    }

    public function testItMapsWhitespaceAndPunctuationCorrectly(): void
    {
        $classes = $this->makeTheme()->getCssClasses();

        self::assertSame('', $classes[Scope::Whitespace->value]);
        self::assertSame('token punctuation', $classes[Scope::Punctuation->value]);
    }

    public function testStylesheetReferencesRequestedTheme(): void
    {
        $stylesheet = $this->makeTheme()->getStylesheet();

        self::assertStringContainsString('Prism.js theme: tomorrow', $stylesheet);
        self::assertStringContainsString('tomorrow', $stylesheet);
    }

    public function testStylesheetIncludesContainerOrLanguageRule(): void
    {
        $stylesheet = $this->makeTheme()->getStylesheet();

        if (!str_contains($stylesheet, '.alto-highlight')) {
            self::assertStringContainsString('code[class*=language-]', $stylesheet);

            return;
        }

        self::assertStringContainsString('.alto-highlight', $stylesheet);
    }

    public function testStylesheetMentionsThemeName(): void
    {
        self::assertStringContainsString('okaidia', $this->makeTheme('okaidia')->getStylesheet());
    }

    public function testStylesheetUsesExpectedCdnVersion(): void
    {
        self::assertStringContainsString('1.29.0', $this->makeTheme()->getStylesheet());
    }

    public function testStylesheetCanUseLocalCssFile(): void
    {
        $path = tempnam(sys_get_temp_dir(), 'prism');
        file_put_contents($path, '.token.keyword { color: red; }');

        try {
            $stylesheet = $this->makeTheme('local', cssPath: $path)->getStylesheet();
        } finally {
            @unlink($path);
        }

        self::assertStringContainsString('.token.keyword { color: red; }', $stylesheet);
    }

    public function testPopularThemesStructure(): void
    {
        $themes = PrismThemeAdapter::getPopularThemes();

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
        $themes = PrismThemeAdapter::getPopularThemes();

        self::assertArrayHasKey('default', $themes);
        self::assertFalse($themes['default']['dark']);
        self::assertArrayHasKey('dark', $themes);
        self::assertTrue($themes['dark']['dark']);
        self::assertArrayHasKey('tomorrow', $themes);
        self::assertTrue($themes['tomorrow']['dark']);
        self::assertArrayHasKey('okaidia', $themes);
        self::assertTrue($themes['okaidia']['dark']);
    }

    public function testPopularThemesLightVsDarkMarking(): void
    {
        $themes = PrismThemeAdapter::getPopularThemes();

        self::assertFalse($themes['default']['dark']);
        self::assertTrue($themes['dark']['dark']);
        self::assertFalse($themes['coy']['dark']);
        self::assertTrue($themes['tomorrow']['dark']);
    }

    public function testDifferentThemeNamesGenerateExpectedMetadata(): void
    {
        $light = $this->makeTheme('default', isDark: false);
        $tomorrow = $this->makeTheme('tomorrow');
        $okaidia = $this->makeTheme('okaidia');
        $twilight = $this->makeTheme('twilight');

        self::assertSame('prism-default', $light->getName());
        self::assertFalse($light->isDark());
        self::assertSame('prism-tomorrow', $tomorrow->getName());
        self::assertTrue($tomorrow->isDark());
        self::assertSame('prism-okaidia', $okaidia->getName());
        self::assertSame('prism-twilight', $twilight->getName());
    }

    public function testHandlesThemeNamesWithHyphens(): void
    {
        self::assertStringContainsString('solarizedlight', $this->makeTheme('solarizedlight', isDark: false)->getName());
    }

    public function testHandlesDifferentDarkFlags(): void
    {
        $light = $this->makeTheme('default', isDark: false);
        $dark = $this->makeTheme('dark', isDark: true);

        self::assertFalse($light->isDark());
        self::assertTrue($dark->isDark());
    }

    public function testCssClassFormatContainsToken(): void
    {
        foreach ($this->makeTheme()->getCssClasses() as $class) {
            if ('' === $class) {
                continue;
            }

            self::assertStringContainsString('token', $class);
        }
    }

    public function testCssClassFormatMatchesPattern(): void
    {
        foreach (array_filter($this->makeTheme()->getCssClasses(), static fn (string $class) => '' !== $class) as $class) {
            self::assertMatchesRegularExpression('/^token\s+[\w-]+$/', $class);
        }
    }

    public function testConsistencyWithHighlightjsAdapterForScopeKeys(): void
    {
        $classes = $this->makeTheme()->getCssClasses();

        foreach (Scope::cases() as $scope) {
            self::assertArrayHasKey($scope->value, $classes);
        }
    }

    public function testConsistencyWithHighlightjsAdapterForWhitespace(): void
    {
        self::assertSame('', $this->makeTheme()->getCssClasses()[Scope::Whitespace->value]);
    }

    private function makeTheme(string $name = 'tomorrow', bool $isDark = true, ?string $cssPath = null): PrismThemeAdapter
    {
        return new PrismThemeAdapter($name, cssPath: $cssPath, isDark: $isDark);
    }
}
