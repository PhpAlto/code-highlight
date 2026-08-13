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

namespace Alto\Code\Highlight\Adapter;

use Alto\Code\Highlight\Scope;
use Alto\Code\Highlight\ThemeInterface;

/**
 * Adapter for using Highlight.js themes with Alto\Code\Highlight.
 *
 * Enables compatibility with 240+ Highlight.js themes by mapping Alto's
 * semantic scopes to Highlight.js CSS class names.
 *
 * @example
 * ```php
 * // Use GitHub Dark theme from Highlight.js
 * $theme = new HighlightJsThemeAdapter('github-dark', isDark: true);
 * $highlighter = new Highlighter(theme: $theme);
 * echo $highlighter->highlight($code, 'php');
 * ```
 * @example
 * ```php
 * // Use local CSS file instead of CDN
 * $theme = new HighlightJsThemeAdapter(
 *     themeName: 'monokai',
 *     cssPath: __DIR__ . '/themes/monokai.css',
 *     isDark: true
 * );
 * ```
 *
 * @author Simon André <smn.andre@gmail.com>
 */
final class HighlightJsThemeAdapter implements ThemeInterface
{
    use ReadsThemeFile;

    private const string HLJS_VERSION = '11.9.0';

    private const string CDN_BASE = 'https://cdnjs.cloudflare.com/ajax/libs/highlight.js';

    private string $themeName;

    private string $cssContent;

    private bool $isDark;

    /**
     * Comprehensive mapping: Alto Scope → Highlight.js class.
     *
     * Note: Highlight.js has ~20 scope classes, while Alto has 80+.
     * This mapping intelligently groups Alto's detailed scopes into
     * Highlight.js's simpler class structure.
     */
    private const SCOPE_MAP = [
        // Comments
        Scope::Comment->value => 'hljs-comment',
        Scope::CommentDocblock->value => 'hljs-doctag',
        Scope::CommentTask->value => 'hljs-comment',

        // Keywords & operators
        Scope::Keyword->value => 'hljs-keyword',
        Scope::KeywordDeclaration->value => 'hljs-keyword',
        Scope::KeywordOperator->value => 'hljs-keyword',
        Scope::KeywordControl->value => 'hljs-keyword',
        Scope::StorageModifier->value => 'hljs-keyword',
        Scope::Operator->value => 'hljs-operator',
        Scope::Punctuation->value => 'hljs-punctuation',

        // Literals
        Scope::String->value => 'hljs-string',
        Scope::StringInterpolated->value => 'hljs-string',
        Scope::StringTemplateExpression->value => 'hljs-string',
        Scope::Number->value => 'hljs-number',
        Scope::Boolean->value => 'hljs-literal',
        Scope::Null->value => 'hljs-literal',
        Scope::RegExp->value => 'hljs-regexp',
        Scope::Constant->value => 'hljs-variable constant_',
        Scope::BuiltInConstant->value => 'hljs-variable constant_',
        Scope::EnumCase->value => 'hljs-variable constant_',

        // Variables
        Scope::Variable->value => 'hljs-variable',
        Scope::VariableParameter->value => 'hljs-params',
        Scope::VariableProperty->value => 'hljs-property',
        Scope::VariableThis->value => 'hljs-variable language_',

        // Namespaces & types
        Scope::Namespace->value => 'hljs-title class_',
        Scope::TypeDefinition->value => 'hljs-title class_',
        Scope::TypeReference->value => 'hljs-type',
        Scope::BuiltInType->value => 'hljs-type',

        // Functions
        Scope::FunctionDefinition->value => 'hljs-title function_',
        Scope::FunctionCall->value => 'hljs-title function_ invoke__',
        Scope::FunctionBuiltin->value => 'hljs-built_in',

        // Attributes & markup
        Scope::AttributeName->value => 'hljs-meta',
        Scope::AttributeValue->value => 'hljs-string',
        Scope::TagName->value => 'hljs-tag',
        Scope::TagAttributeName->value => 'hljs-attr',
        Scope::TagAttributeValue->value => 'hljs-string',
        Scope::MarkupText->value => 'hljs-text',
        Scope::SectionName->value => 'hljs-section',

        // Diff & diagnostics
        Scope::DiffAdded->value => 'hljs-addition',
        Scope::DiffRemoved->value => 'hljs-deletion',
        Scope::DiffChanged->value => 'hljs-meta',
        Scope::Meta->value => 'hljs-meta',
        Scope::DiagnosticError->value => 'hljs-meta',
        Scope::DiagnosticWarning->value => 'hljs-meta',
        Scope::DiagnosticInfo->value => 'hljs-meta',

        // Support buckets
        Scope::SupportType->value => 'hljs-type',
        Scope::SupportFunction->value => 'hljs-built_in',
        Scope::SupportConstant->value => 'hljs-variable constant_',

        // Misc
        Scope::Whitespace->value => '',
    ];

    /**
     * Create a theme adapter from a local CSS file.
     *
     * @param string $cssPath Path to the Highlight.js CSS file
     * @param bool   $isDark  Whether this is a dark theme
     */
    public static function fromFile(string $cssPath, bool $isDark = false): self
    {
        // Validate the file eagerly — fromFile() should throw, not fall back to CDN
        self::readThemeFile($cssPath);
        $themeName = pathinfo($cssPath, PATHINFO_FILENAME);

        return new self($themeName, $cssPath, $isDark);
    }

    /**
     * @param string      $themeName Highlight.js theme name (e.g., 'github', 'monokai')
     * @param string|null $cssPath   Path to local CSS file (optional, uses CDN if null)
     * @param bool        $isDark    Whether this is a dark theme
     */
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
                $this->cssContent = false === $css
                    ? $this->loadFromCdn($themeName)
                    : $this->adaptCssForAlto($css);
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

        // Map all defined scopes
        foreach (self::SCOPE_MAP as $altoScope => $hljsClass) {
            $classes[$altoScope] = $hljsClass;
        }

        // Fallback for any unmapped scopes
        foreach (Scope::cases() as $scope) {
            if (!isset($classes[$scope->value])) {
                $classes[$scope->value] = 'hljs-meta';
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
        return "hljs-{$this->themeName}";
    }

    public function isDark(): bool
    {
        return $this->isDark;
    }

    /**
     * Adapt Highlight.js CSS to work with Alto's HTML structure.
     *
     * Highlight.js structure: <pre><code class="hljs"><span class="hljs-keyword">
     * Alto structure:         <pre class="alto-highlight"><code><span class="hljs-keyword">
     */
    private function adaptCssForAlto(string $css): string
    {
        // Replace standalone .hljs selectors
        $css = (string) preg_replace(
            '/(?<![\w.-])\.hljs(?![-\w])/',
            '.alto-highlight code',
            $css,
        );

        // Replace pre code.hljs selectors
        $css = (string) preg_replace(
            '/pre\s+code\.hljs/',
            'pre.alto-highlight code',
            $css,
        );

        // Ensure all .hljs-* classes are properly scoped
        $css = (string) preg_replace(
            '/(?<![\w.-])(\.hljs-[\w-]+)(?!\s*\{)/',
            '.alto-highlight code $1',
            $css,
        );

        return $css;
    }

    /**
     * Load theme CSS from CDN.
     */
    private function loadFromCdn(string $themeName): string
    {
        $cdnUrl = self::CDN_BASE . '/' . self::HLJS_VERSION . '/styles/' . $themeName . '.min.css';
        $css = $this->fetchCss($cdnUrl);

        if (null !== $css) {
            $adapted = $this->adaptCssForAlto($css);

            return <<<CSS
/* Highlight.js theme: {$themeName} */
/* Source: {$cdnUrl} */
{$adapted}
CSS;
        }

        return <<<CSS
/* Highlight.js theme: {$themeName} */
/* Loaded from CDN: {$cdnUrl} */
/* For production, download the CSS file locally */
@import url('{$cdnUrl}');

/* Alto\Code\Highlight container styles */
.alto-highlight {
    display: block;
    overflow-x: auto;
    padding: 0.5em;
}

.alto-highlight code {
    display: block;
}
CSS;
    }

    /**
     * Get list of popular Highlight.js themes.
     *
     * @return array<string, array{name: string, dark: bool}>
     */
    public static function getPopularThemes(): array
    {
        return [
            'github' => ['name' => 'GitHub', 'dark' => false],
            'github-dark' => ['name' => 'GitHub Dark', 'dark' => true],
            'monokai' => ['name' => 'Monokai', 'dark' => true],
            'atom-one-dark' => ['name' => 'Atom One Dark', 'dark' => true],
            'atom-one-light' => ['name' => 'Atom One Light', 'dark' => false],
            'dracula' => ['name' => 'Dracula', 'dark' => true],
            'nord' => ['name' => 'Nord', 'dark' => true],
            'vs2015' => ['name' => 'Visual Studio 2015', 'dark' => true],
            'vs' => ['name' => 'Visual Studio', 'dark' => false],
            'tomorrow-night-blue' => ['name' => 'Tomorrow Night Blue', 'dark' => true],
            'solarized-dark' => ['name' => 'Solarized Dark', 'dark' => true],
            'solarized-light' => ['name' => 'Solarized Light', 'dark' => false],
            'rainbow' => ['name' => 'Rainbow', 'dark' => true],
            'stackoverflow-dark' => ['name' => 'Stack Overflow Dark', 'dark' => true],
            'stackoverflow-light' => ['name' => 'Stack Overflow Light', 'dark' => false],
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
