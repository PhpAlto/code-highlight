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

/*
 * This theme's color palette is inspired by the Solarized color scheme.
 * Original color scheme © Ethan Schoonover — MIT License
 * https://ethanschoonover.com/solarized
 */

namespace Alto\Code\Highlight\Theme;

use Alto\Code\Highlight\Scope;
use Alto\Code\Highlight\ThemeInterface;

/**
 * Solar — color palette inspired by the Solarized color scheme.
 *
 * Original Solarized color scheme © Ethan Schoonover — MIT License
 *
 * @see https://ethanschoonover.com/solarized
 *
 * @author Simon André <smn.andre@gmail.com>
 */
final class SolarTheme implements ThemeInterface
{
    public function __construct(
        private readonly bool $dark = false,
    ) {}

    public function getName(): string
    {
        return $this->dark ? 'Solar Dark' : 'Solar Light';
    }

    public function isDark(): bool
    {
        return $this->dark;
    }

    public function getCssClasses(): array
    {
        $prefix = $this->dark ? 'solar-dark-' : 'solar-light-';

        $classes = [];
        foreach ($this->scopeMap() as [$scope, $suffix]) {
            $classes[$scope->value] = '' === $suffix ? '' : $prefix . $suffix;
        }

        return $classes;
    }

    /**
     * @return array<array{Scope, string}>
     */
    private function scopeMap(): array
    {
        return [
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
        ];
    }

    public function getStylesheet(): string
    {
        return $this->dark ? $this->darkStylesheet() : $this->lightStylesheet();
    }

    private function lightStylesheet(): string
    {
        return <<<'CSS'
/* Solar Light Theme (Solarized Light) */
.alto-highlight {
    background-color: #fdf6e3;
    color: #657b83;
    padding: 1em;
    border-radius: 4px;
    overflow-x: auto;
}

.solar-light-comment,
.solar-light-comment-doc {
    color: #93a1a1;
    font-style: italic;
}

.solar-light-punctuation {
    color: #657b83;
}

.solar-light-keyword,
.solar-light-keyword-declaration,
.solar-light-keyword-control {
    color: #859900;
    font-weight: bold;
}

.solar-light-keyword-operator {
    color: #268bd2;
}

.solar-light-operator {
    color: #657b83;
}

.solar-light-string,
.solar-light-string-interpolated {
    color: #2aa198;
}

.solar-light-number {
    color: #d33682;
}

.solar-light-boolean,
.solar-light-null {
    color: #268bd2;
}

.solar-light-variable,
.solar-light-variable-parameter,
.solar-light-variable-property {
    color: #268bd2;
}

.solar-light-variable-this {
    color: #6c71c4;
    font-weight: bold;
}

.solar-light-function-definition {
    color: #b58900;
    font-weight: bold;
}

.solar-light-function-call {
    color: #b58900;
}

.solar-light-class-definition {
    color: #cb4b16;
    font-weight: bold;
}

.solar-light-class-name {
    color: #cb4b16;
}

.solar-light-enum-case {
    color: #6c71c4;
}

.solar-light-constant {
    color: #6c71c4;
}

.solar-light-constant-magic {
    color: #d33682;
}

.solar-light-constant-class {
    color: #6c71c4;
}

.solar-light-attribute {
    color: #d33682;
}

.solar-light-typehint {
    color: #cb4b16;
}

.solar-light-type {
    color: #cb4b16;
}
CSS;
    }

    private function darkStylesheet(): string
    {
        return <<<'CSS'
/* Solar Dark Theme (Solarized Dark) */
.alto-highlight {
    background-color: #002b36;
    color: #839496;
    padding: 1em;
    border-radius: 4px;
    overflow-x: auto;
}

.solar-dark-comment,
.solar-dark-comment-doc {
    color: #586e75;
    font-style: italic;
}

.solar-dark-punctuation {
    color: #839496;
}

.solar-dark-keyword,
.solar-dark-keyword-declaration,
.solar-dark-keyword-control {
    color: #859900;
    font-weight: bold;
}

.solar-dark-keyword-operator {
    color: #268bd2;
}

.solar-dark-operator {
    color: #839496;
}

.solar-dark-string,
.solar-dark-string-interpolated {
    color: #2aa198;
}

.solar-dark-number {
    color: #d33682;
}

.solar-dark-boolean,
.solar-dark-null {
    color: #268bd2;
}

.solar-dark-variable,
.solar-dark-variable-parameter,
.solar-dark-variable-property {
    color: #268bd2;
}

.solar-dark-variable-this {
    color: #6c71c4;
    font-weight: bold;
}

.solar-dark-function-definition {
    color: #b58900;
    font-weight: bold;
}

.solar-dark-function-call {
    color: #b58900;
}

.solar-dark-class-definition {
    color: #cb4b16;
    font-weight: bold;
}

.solar-dark-class-name {
    color: #cb4b16;
}

.solar-dark-enum-case {
    color: #6c71c4;
}

.solar-dark-constant {
    color: #6c71c4;
}

.solar-dark-constant-magic {
    color: #d33682;
}

.solar-dark-constant-class {
    color: #6c71c4;
}

.solar-dark-attribute {
    color: #d33682;
}

.solar-dark-typehint {
    color: #cb4b16;
}

.solar-dark-type {
    color: #cb4b16;
}
CSS;
    }
}
