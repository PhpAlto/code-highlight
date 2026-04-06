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

namespace Alto\Code\Highlight\Theme;

use Alto\Code\Highlight\Scope;
use Alto\Code\Highlight\ThemeInterface;

/**
 * Alto Theme - A vibrant, high-contrast theme with Dark and Light modes.
 *
 * Designed with Apple-like aesthetics using OKLCH color space logic for
 * consistent perceptual lightness and chroma across hues.
 *
 * @author Simon André <smn.andre@gmail.com>
 */
final class AltoTheme implements ThemeInterface
{
    public function __construct(
        private readonly bool $dark = true,
    ) {
    }

    public function getName(): string
    {
        return $this->dark ? 'Alto Dark' : 'Alto Light';
    }

    public function isDark(): bool
    {
        return $this->dark;
    }

    public function getCssClasses(): array
    {
        return $this->prefixed([
            // Comments
            [Scope::Comment, 'comment'],
            [Scope::CommentDocblock, 'comment'],
            [Scope::CommentTask, 'comment'],

            // Keywords & modifiers
            [Scope::Keyword, 'keyword'],
            [Scope::KeywordDeclaration, 'keyword'],
            [Scope::KeywordOperator, 'keyword'],
            [Scope::KeywordControl, 'keyword'],
            [Scope::StorageModifier, 'keyword'],
            [Scope::Operator, 'operator'],
            [Scope::Punctuation, 'punctuation'],

            // Literals
            [Scope::String, 'string'],
            [Scope::StringInterpolated, 'string'],
            [Scope::StringTemplateExpression, 'string'],
            [Scope::Number, 'number'],
            [Scope::Boolean, 'number'],
            [Scope::Null, 'number'],
            [Scope::RegExp, 'number'],
            [Scope::Constant, 'constant'],
            [Scope::BuiltInConstant, 'constant'],
            [Scope::EnumCase, 'constant'],

            // Variables
            [Scope::Variable, 'variable'],
            [Scope::VariableParameter, 'variable'],
            [Scope::VariableProperty, 'variable'],
            [Scope::VariableThis, 'variable-special'],

            // Namespaces & types
            [Scope::Namespace, 'type'],
            [Scope::TypeDefinition, 'type'],
            [Scope::TypeReference, 'type'],
            [Scope::BuiltInType, 'type'],

            // Functions
            [Scope::FunctionDefinition, 'function'],
            [Scope::FunctionCall, 'function'],
            [Scope::FunctionBuiltin, 'function'],

            // Attributes & markup
            [Scope::AttributeName, 'variable'],
            [Scope::AttributeValue, 'string'],
            [Scope::TagName, 'keyword'],
            [Scope::TagAttributeName, 'variable'],
            [Scope::TagAttributeValue, 'string'],
            [Scope::MarkupText, 'punctuation'],
            [Scope::SectionName, 'type'],

            // Diff / meta / diagnostics
            [Scope::DiffAdded, 'string'],
            [Scope::DiffRemoved, 'constant'],
            [Scope::DiffChanged, 'number'],
            [Scope::Meta, 'constant'],
            [Scope::DiagnosticError, 'constant'],
            [Scope::DiagnosticWarning, 'constant'],
            [Scope::DiagnosticInfo, 'constant'],

            // Support buckets
            [Scope::SupportType, 'type'],
            [Scope::SupportFunction, 'function'],
            [Scope::SupportConstant, 'constant'],

            // Misc
            [Scope::Whitespace, 'punctuation'],
        ]);
    }

    /**
     * @param array<array{Scope, string}> $map
     *
     * @return array<string, string>
     */
    private function prefixed(array $map): array
    {
        $classes = [];
        foreach ($map as [$scope, $suffix]) {
            $classes[$scope->value] = '' === $suffix ? '' : 'alto-'.$suffix;
        }

        return $classes;
    }

    public function getStylesheet(): string
    {
        // Palette Definitions (OKLCH derived)
        if ($this->dark) {
            // Dark Mode (White-ish on Black)
            $bg = '#0a0a0a'; // Very dark gray/black
            $fg = '#f5f5f5'; // White-ish
            $comment = '#6b7280'; // Muted Gray
            $keyword = '#c084fc'; // Purple (Hue 270)
            $function = '#60a5fa'; // Blue (Hue 250)
            $type = '#818cf8'; // Indigo (Hue 230) - 3rd Blue
            $variable = '#22d3ee'; // Cyan (Hue 190)
            $string = '#f87171'; // Coral (Hue 20)
            $number = '#d1d5db'; // Light Gray
            $constant = '#d1d5db'; // Light Gray
            $operator = '#c084fc'; // Purple
            $punctuation = '#a3a3a3'; // Dimmed Gray
        } else {
            // Light Mode (Black-ish on White)
            $bg = '#ffffff'; // White
            $fg = '#1a1a1a'; // Black-ish
            $comment = '#9ca3af'; // Light Gray
            $keyword = '#9333ea'; // Purple
            $function = '#2563eb'; // Blue
            $type = '#4f46e5'; // Indigo
            $variable = '#0891b2'; // Cyan
            $string = '#dc2626'; // Coral
            $number = '#4b5563'; // Dark Gray
            $constant = '#4b5563'; // Dark Gray
            $operator = '#9333ea'; // Purple
            $punctuation = '#737373'; // Gray
        }

        return <<<CSS
/* Alto Theme ({$this->getName()}) */
.alto-highlight {
    background-color: {$bg};
    color: {$fg};
    padding: 1em;
    border-radius: 6px;
    overflow-x: auto;
    font-family: 'JetBrains Mono', 'Fira Code', Consolas, monospace;
    font-weight: 200; /* Base weight: Extra Light */
    line-height: 1.5;
}

.alto-comment { color: {$comment}; font-weight: 100; font-style: italic; } /* Thin */
.alto-keyword { color: {$keyword}; font-weight: 300; } /* Light (Bold-ish relative to 200) */
.alto-function { color: {$function}; }
.alto-type { color: {$type}; font-weight: 300; }
.alto-variable { color: {$variable}; }
.alto-variable-special { color: {$variable}; font-style: italic; }
.alto-string { color: {$string}; }
.alto-number { color: {$number}; }
.alto-constant { color: {$constant}; font-weight: 300; }
.alto-operator { color: {$operator}; }
.alto-punctuation { color: {$punctuation}; font-weight: 200; }
CSS;
    }
}
