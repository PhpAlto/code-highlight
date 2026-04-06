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
 * Adapter for using TextMate (.tmTheme) themes with Alto\Code\Highlight.
 *
 * Parses TextMate PList XML theme files and generates CSS classes
 * and a stylesheet compatible with Alto's highlighting engine.
 *
 * @example
 * ```php
 * $theme = TextMateThemeAdapter::fromFile('/path/to/monokai.tmTheme', isDark: true);
 * $highlighter = new Highlighter(theme: $theme);
 * echo $highlighter->highlight($code, 'php');
 * ```
 *
 * @author Simon André <smn.andre@gmail.com>
 */
final class TextMateThemeAdapter implements ThemeInterface
{
    use ReadsThemeFile;
    private string $themeName;

    private bool $isDark;

    /** @var array<string, string> Map of scope value to CSS class name */
    private array $cssClasses;

    private string $stylesheet;

    /**
     * @param string $themeXmlContent The content of the .tmTheme (PList XML) file
     * @param string $themeName       A human-readable name for this theme
     * @param bool   $isDark          Whether this is a dark theme
     */
    public function __construct(
        string $themeXmlContent,
        string $themeName = 'textmate',
        bool $isDark = false,
    ) {
        $this->themeName = $themeName;
        $this->isDark = $isDark;

        $scopeStyles = $this->parseThemeXml($themeXmlContent);
        $this->buildTheme($scopeStyles);
    }

    /**
     * Create a theme adapter from a local .tmTheme file.
     *
     * @param string $xmlPath Path to the .tmTheme file
     * @param bool   $isDark  Whether this is a dark theme
     */
    public static function fromFile(string $xmlPath, bool $isDark = false): self
    {
        $content = self::readThemeFile($xmlPath);
        $themeName = pathinfo($xmlPath, PATHINFO_FILENAME);

        return new self($content, $themeName, $isDark);
    }

    public function getCssClasses(): array
    {
        return $this->cssClasses;
    }

    public function getStylesheet(): string
    {
        return $this->stylesheet;
    }

    public function getName(): string
    {
        return $this->themeName;
    }

    public function isDark(): bool
    {
        return $this->isDark;
    }

    /**
     * Parse the TextMate theme XML and return scope-to-CSS mappings.
     *
     * @return array<string, string> Map of Scope value to inline CSS declarations
     */
    private function parseThemeXml(string $themeXmlContent): array
    {
        $xml = simplexml_load_string($themeXmlContent);
        if (false === $xml) {
            throw new \InvalidArgumentException('Failed to parse theme XML.');
        }

        $mainDict = $xml->dict[0] ?? null;
        if (null === $mainDict) {
            return [];
        }

        $parsed = $this->parseDict($mainDict);
        $settings = $parsed['settings'] ?? [];
        if (!is_array($settings) || empty($settings)) {
            return [];
        }

        $theme = [];

        foreach ($settings as $rule) {
            if (!is_array($rule)) {
                continue;
            }
            $scopeString = $rule['scope'] ?? null;
            $ruleSettings = $rule['settings'] ?? null;

            if (!is_string($scopeString) || !is_array($ruleSettings)) {
                continue;
            }

            /** @var array<string, mixed> $ruleSettings */
            $css = $this->buildCss($ruleSettings);
            $scopes = $this->mapScope($scopeString);

            foreach ($scopes as $scope) {
                $theme[$scope->value] = $css;
            }
        }

        return $theme;
    }

    /**
     * Build the CSS classes map and stylesheet from parsed scope styles.
     *
     * @param array<string, string> $scopeStyles Map of scope value to inline CSS
     */
    private function buildTheme(array $scopeStyles): void
    {
        $css = [];
        $classes = [];

        foreach (Scope::cases() as $scope) {
            $className = 'alto-tm-'.str_replace('.', '-', $scope->value);
            $classes[$scope->value] = $className;

            if (isset($scopeStyles[$scope->value])) {
                $css[] = '.'.$className.' { '.$scopeStyles[$scope->value].' }';
            }
        }

        $this->cssClasses = $classes;
        $this->stylesheet = implode("\n", $css);
    }

    /**
     * @param array<string, mixed> $settings
     */
    private function buildCss(array $settings): string
    {
        $css = [];
        if (isset($settings['foreground']) && is_string($settings['foreground'])) {
            $color = self::sanitizeCssColor($settings['foreground']);
            if (null !== $color) {
                $css[] = 'color: '.$color;
            }
        }
        if (isset($settings['background']) && is_string($settings['background'])) {
            $color = self::sanitizeCssColor($settings['background']);
            if (null !== $color) {
                $css[] = 'background-color: '.$color;
            }
        }
        if (isset($settings['fontStyle']) && is_string($settings['fontStyle'])) {
            $fontStyle = $settings['fontStyle'];
            if (str_contains($fontStyle, 'bold')) {
                $css[] = 'font-weight: bold';
            }
            if (str_contains($fontStyle, 'italic')) {
                $css[] = 'font-style: italic';
            }
            if (str_contains($fontStyle, 'underline')) {
                $css[] = 'text-decoration: underline';
            }
        }

        return implode('; ', $css).';';
    }

    /**
     * Validate and sanitize a CSS color value from the theme XML.
     *
     * Only allows hex colors (#rgb, #rrggbb, #rrggbbaa), rgb/rgba/hsl/hsla
     * function calls with numeric arguments, and CSS named colors.
     * Rejects values containing CSS-breaking characters like }, {, ;, or <.
     */
    private static function sanitizeCssColor(string $value): ?string
    {
        $value = trim($value);

        // Hex colors: #rgb, #rrggbb, #rrggbbaa
        if (preg_match('/^#[0-9a-fA-F]{3,8}$/', $value)) {
            return $value;
        }

        // rgb(), rgba(), hsl(), hsla() with safe arguments (digits, commas, dots, spaces, %)
        if (preg_match('/^(?:rgba?|hsla?)\([0-9.,\s%]+\)$/', $value)) {
            return $value;
        }

        // Named CSS colors (alphanumeric only, no special chars)
        if (preg_match('/^[a-zA-Z]{3,20}$/', $value)) {
            return $value;
        }

        return null;
    }

    /**
     * @return Scope[]
     */
    private function mapScope(string $tmScope): array
    {
        $tmScope = trim($tmScope);

        // More specific mappings first
        if (str_starts_with($tmScope, 'entity.name.function')) {
            return [Scope::FunctionDefinition];
        }
        if (str_starts_with($tmScope, 'entity.name.type') || str_starts_with($tmScope, 'entity.name.class')) {
            return [Scope::TypeDefinition];
        }
        if (str_starts_with($tmScope, 'variable.parameter')) {
            return [Scope::VariableParameter];
        }
        if (str_starts_with($tmScope, 'constant.numeric')) {
            return [Scope::Number];
        }

        // General mappings
        $map = [
            'comment' => Scope::Comment,
            'string' => Scope::String,
            'keyword' => Scope::Keyword,
            'constant' => Scope::Constant,
            'variable' => Scope::Variable,
            'entity' => Scope::TypeReference,
            'storage' => Scope::Keyword,
            'support.function' => Scope::SupportFunction,
            'support.type' => Scope::SupportType,
            'support.constant' => Scope::SupportConstant,
            'punctuation' => Scope::Punctuation,
            'operator' => Scope::Operator,
        ];

        foreach ($map as $key => $scope) {
            if (str_starts_with($tmScope, $key)) {
                return [$scope];
            }
        }

        return [];
    }

    private function parseNode(\SimpleXMLElement $node): mixed
    {
        switch ($node->getName()) {
            case 'dict':
                return $this->parseDict($node);
            case 'array':
                return $this->parseArray($node);
            case 'string':
                return (string) $node;
            case 'integer':
                return (int) (string) $node;
            case 'true':
                return true;
            case 'false':
                return false;
            default:
                return null;
        }
    }

    /**
     * @return array<string, mixed>
     */
    private function parseDict(\SimpleXMLElement $dict): array
    {
        $result = [];
        $children = $dict->children();
        for ($i = 0; $i < count($children); $i += 2) {
            $key = $children[$i];
            $valueNode = $children[$i + 1] ?? null;
            if ('key' !== $key->getName() || null === $valueNode) {
                continue;
            }
            $result[(string) $key] = $this->parseNode($valueNode);
        }

        return $result;
    }

    /**
     * @return array<int, mixed>
     */
    private function parseArray(\SimpleXMLElement $array): array
    {
        $result = [];
        foreach ($array->children() as $node) {
            $result[] = $this->parseNode($node);
        }

        return $result;
    }
}
