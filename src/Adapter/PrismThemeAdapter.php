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

namespace Alto\Code\Highlight\Adapter;

use Alto\Code\Highlight\Scope;
use Alto\Code\Highlight\ThemeInterface;

/**
 * Adapter for using Prism.js themes with Alto\Code\Highlight.
 *
 * Prism.js has the simplest and most compatible CSS class structure,
 * making it ideal for theme compatibility. Supports 250+ themes.
 *
 * @example
 * ```php
 * $theme = new PrismThemeAdapter('tomorrow', isDark: true);
 * $highlighter = new Highlighter(theme: $theme);
 * echo $highlighter->highlight($code, 'php');
 * ```
 *
 * @author Simon André <smn.andre@gmail.com>
 */
final class PrismThemeAdapter implements ThemeInterface
{
    use ReadsThemeFile;

    private const PRISM_VERSION = '1.29.0';

    private const CDN_BASE = 'https://cdnjs.cloudflare.com/ajax/libs/prism';

    private string $themeName;

    private string $cssContent;

    private bool $isDark;

    /**
     * Mapping: Alto Scope → Prism.js token class.
     *
     * Prism uses a simple .token.{name} pattern, making it
     * the easiest library to integrate with.
     */
    private const SCOPE_MAP = [
        // Comments
        Scope::Comment->value => 'token comment',
        Scope::CommentDocblock->value => 'token comment',
        Scope::CommentTask->value => 'token comment',

        // Keywords & operators
        Scope::Keyword->value => 'token keyword',
        Scope::KeywordDeclaration->value => 'token keyword',
        Scope::KeywordOperator->value => 'token keyword',
        Scope::KeywordControl->value => 'token keyword',
        Scope::StorageModifier->value => 'token keyword',
        Scope::Operator->value => 'token operator',
        Scope::Punctuation->value => 'token punctuation',

        // Literals
        Scope::String->value => 'token string',
        Scope::StringInterpolated->value => 'token string',
        Scope::StringTemplateExpression->value => 'token interpolation',
        Scope::Number->value => 'token number',
        Scope::Boolean->value => 'token boolean',
        Scope::Null->value => 'token constant',
        Scope::RegExp->value => 'token regex',
        Scope::Constant->value => 'token constant',
        Scope::BuiltInConstant->value => 'token builtin',
        Scope::EnumCase->value => 'token constant',

        // Variables
        Scope::Variable->value => 'token variable',
        Scope::VariableParameter->value => 'token parameter',
        Scope::VariableProperty->value => 'token property',
        Scope::VariableThis->value => 'token keyword',

        // Namespaces & types
        Scope::Namespace->value => 'token namespace',
        Scope::TypeDefinition->value => 'token class-name',
        Scope::TypeReference->value => 'token class-name',
        Scope::BuiltInType->value => 'token builtin',

        // Functions
        Scope::FunctionDefinition->value => 'token function',
        Scope::FunctionCall->value => 'token function',
        Scope::FunctionBuiltin->value => 'token function',

        // Attributes & markup
        Scope::AttributeName->value => 'token attr-name',
        Scope::AttributeValue->value => 'token attr-value',
        Scope::TagName->value => 'token tag',
        Scope::TagAttributeName->value => 'token attr-name',
        Scope::TagAttributeValue->value => 'token attr-value',
        Scope::MarkupText->value => 'token plain',
        Scope::SectionName->value => 'token tag',

        // Diff / diagnostics
        Scope::DiffAdded->value => 'token inserted',
        Scope::DiffRemoved->value => 'token deleted',
        Scope::DiffChanged->value => 'token changed',
        Scope::Meta->value => 'token important',
        Scope::DiagnosticError->value => 'token important',
        Scope::DiagnosticWarning->value => 'token important',
        Scope::DiagnosticInfo->value => 'token important',

        // Support buckets
        Scope::SupportType->value => 'token class-name',
        Scope::SupportFunction->value => 'token function',
        Scope::SupportConstant->value => 'token constant',

        // Misc
        Scope::Whitespace->value => '',
    ];

    /**
     * Create a theme adapter from a local CSS file.
     *
     * @param string $cssPath Path to the Prism.js CSS file
     * @param bool   $isDark  Whether this is a dark theme
     */
    public static function fromFile(string $cssPath, bool $isDark = false): self
    {
        self::readThemeFile($cssPath);
        $themeName = pathinfo($cssPath, PATHINFO_FILENAME);

        return new self($themeName, $cssPath, $isDark);
    }

    public function __construct(
        string $themeName,
        ?string $cssPath = null,
        bool $isDark = false,
    ) {
        $this->themeName = $themeName;
        $this->isDark = $isDark;

        if (null !== $cssPath) {
            $realPath = realpath($cssPath);
            if (false !== $realPath && is_file($realPath) && is_readable($realPath)) {
                $css = @file_get_contents($realPath);
                $this->cssContent = false === $css ? $this->loadFromCdn($themeName) : $css;
            } else {
                $this->cssContent = $this->loadFromCdn($themeName);
            }
        } else {
            $this->cssContent = $this->loadFromCdn($themeName);
        }
    }

    public function getCssClasses(): array
    {
        $classes = [];

        foreach (self::SCOPE_MAP as $altoScope => $prismClass) {
            $classes[$altoScope] = $prismClass;
        }

        // Fallback for unmapped scopes
        foreach (Scope::cases() as $scope) {
            if (!isset($classes[$scope->value])) {
                $classes[$scope->value] = 'token plain';
            }
        }

        return $classes;
    }

    public function getStylesheet(): string
    {
        return $this->cssContent;
    }

    public function getName(): string
    {
        return "prism-{$this->themeName}";
    }

    public function isDark(): bool
    {
        return $this->isDark;
    }

    private function loadFromCdn(string $themeName): string
    {
        $cdnUrl = self::CDN_BASE.'/'.self::PRISM_VERSION.'/themes/prism-'.$themeName.'.min.css';
        $css = $this->fetchCss($cdnUrl);

        if (null !== $css) {
            return <<<CSS
/* Prism.js theme: {$themeName} */
/* Source: {$cdnUrl} */
{$css}
CSS;
        }

        return <<<CSS
/* Prism.js theme: {$themeName} */
/* Loaded from CDN: {$cdnUrl} */
@import url('{$cdnUrl}');

/* Alto\Code\Highlight container styles */
.alto-highlight {
    display: block;
    overflow-x: auto;
    padding: 1em;
}

.alto-highlight code {
    display: block;
}
CSS;
    }

    /**
     * Get list of popular Prism.js themes.
     *
     * @return array<string, array{name: string, dark: bool}>
     */
    public static function getPopularThemes(): array
    {
        return [
            'default' => ['name' => 'Prism Default', 'dark' => false],
            'dark' => ['name' => 'Prism Dark', 'dark' => true],
            'twilight' => ['name' => 'Twilight', 'dark' => true],
            'coy' => ['name' => 'Coy', 'dark' => false],
            'okaidia' => ['name' => 'Okaidia', 'dark' => true],
            'solarizedlight' => ['name' => 'Solarized Light', 'dark' => false],
            'tomorrow' => ['name' => 'Tomorrow Night', 'dark' => true],
            'funky' => ['name' => 'Funky', 'dark' => true],
        ];
    }

    private function fetchCss(string $url): ?string
    {
        $context = stream_context_create([
            'http' => ['timeout' => 3],
            'https' => ['timeout' => 3],
        ]);

        $css = @file_get_contents($url, false, $context);

        if (false === $css) {
            return null;
        }

        return '' === trim($css) ? null : $css;
    }
}
