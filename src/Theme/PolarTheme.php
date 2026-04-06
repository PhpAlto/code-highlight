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

/*
 * This theme's color palette is loosely based on the Nord color scheme.
 * Original color scheme © Arctic Ice Studio — MIT License
 * https://www.nordtheme.com
 */

namespace Alto\Code\Highlight\Theme;

use Alto\Code\Highlight\Scope;
use Alto\Code\Highlight\ThemeInterface;

/**
 * Polar — a cool, polar-inspired dark theme. Color palette loosely based on Nord.
 *
 * Original Nord color scheme © Arctic Ice Studio — MIT License
 *
 * @see https://www.nordtheme.com
 */
final class PolarTheme implements ThemeInterface
{
    public function getName(): string
    {
        return 'Polar';
    }

    public function isDark(): bool
    {
        return true;
    }

    public function getCssClasses(): array
    {
        return $this->prefixed([
            [Scope::Comment, 'comment'],
            [Scope::CommentDocblock, 'comment-doc'],
            [Scope::CommentTask, 'comment'],
            [Scope::Keyword, 'keyword'],
            [Scope::KeywordDeclaration, 'keyword-declaration'],
            [Scope::KeywordOperator, 'keyword-operator'],
            [Scope::KeywordControl, 'keyword-control'],
            [Scope::StorageModifier, 'keyword'],
            [Scope::Operator, 'operator'],
            [Scope::Punctuation, 'punctuation'],
            [Scope::String, 'string'],
            [Scope::StringInterpolated, 'string-interpolated'],
            [Scope::StringTemplateExpression, 'string'],
            [Scope::Number, 'number'],
            [Scope::Boolean, 'boolean'],
            [Scope::Null, 'null'],
            [Scope::RegExp, 'constant'],
            [Scope::Constant, 'constant'],
            [Scope::BuiltInConstant, 'constant-magic'],
            [Scope::EnumCase, 'enum-case'],
            [Scope::Variable, 'variable'],
            [Scope::VariableParameter, 'variable-parameter'],
            [Scope::VariableProperty, 'variable-property'],
            [Scope::VariableThis, 'variable-this'],
            [Scope::Namespace, 'class-definition'],
            [Scope::TypeDefinition, 'class-definition'],
            [Scope::TypeReference, 'class-name'],
            [Scope::BuiltInType, 'typehint'],
            [Scope::FunctionDefinition, 'function-definition'],
            [Scope::FunctionCall, 'function-call'],
            [Scope::FunctionBuiltin, 'function-call'],
            [Scope::AttributeName, 'attribute'],
            [Scope::AttributeValue, 'string'],
            [Scope::TagName, 'keyword'],
            [Scope::TagAttributeName, 'variable'],
            [Scope::TagAttributeValue, 'string'],
            [Scope::MarkupText, 'punctuation'],
            [Scope::SectionName, 'type'],
            [Scope::DiffAdded, 'string'],
            [Scope::DiffRemoved, 'constant'],
            [Scope::DiffChanged, 'number'],
            [Scope::Meta, 'constant-class'],
            [Scope::DiagnosticError, 'constant'],
            [Scope::DiagnosticWarning, 'constant'],
            [Scope::DiagnosticInfo, 'constant'],
            [Scope::SupportType, 'class-name'],
            [Scope::SupportFunction, 'function-call'],
            [Scope::SupportConstant, 'constant'],
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
            $classes[$scope->value] = '' === $suffix ? '' : 'polar-'.$suffix;
        }

        return $classes;
    }

    public function getStylesheet(): string
    {
        return <<<'CSS'
/* Polar Theme - Dark (Nord-ish) */
.alto-highlight {
    background-color: #2e3440;
    color: #d8dee9;
    padding: 1em;
    border-radius: 4px;
    overflow-x: auto;
}

.polar-comment,
.polar-comment-doc {
    color: #4c566a;
    font-style: italic;
}

.polar-punctuation {
    color: #d8dee9;
}

.polar-keyword,
.polar-keyword-declaration,
.polar-keyword-control {
    color: #81a1c1;
    font-weight: bold;
}

.polar-keyword-operator {
    color: #81a1c1;
}

.polar-operator {
    color: #88c0d0;
}

.polar-string,
.polar-string-interpolated,
.polar-string-heredoc {
    color: #a3be8c;
}

.polar-number {
    color: #b48ead;
}

.polar-boolean,
.polar-null {
    color: #d08770;
}

.polar-variable,
.polar-variable-parameter,
.polar-variable-property {
    color: #8fbcbb;
}

.polar-variable-this {
    color: #ebcb8b;
    font-weight: bold;
}

.polar-function-definition {
    color: #88c0d0;
    font-weight: bold;
}

.polar-function-call {
    color: #88c0d0;
}

.polar-class-definition,
.polar-interface-definition,
.polar-trait-definition,
.polar-enum-definition {
    color: #5e81ac;
    font-weight: bold;
}

.polar-class-name {
    color: #5e81ac;
}

.polar-enum-case {
    color: #b48ead;
}

.polar-constant {
    color: #ebcb8b;
}

.polar-constant-magic {
    color: #d08770;
}

.polar-constant-class {
    color: #ebcb8b;
}

.polar-attribute {
    color: #b48ead;
}

.polar-typehint,
.polar-typehint-class {
    color: #8fbcbb;
}
CSS;
    }
}
