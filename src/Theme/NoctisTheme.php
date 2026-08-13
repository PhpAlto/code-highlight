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
 * This theme's color palette is inspired by the Noctis theme.
 * Original color scheme © Liviu Schera — MIT License
 * https://github.com/liviuschera/noctis
 */

namespace Alto\Code\Highlight\Theme;

use Alto\Code\Highlight\Scope;
use Alto\Code\Highlight\ThemeInterface;

/**
 * Noctis — color palette inspired by the Noctis theme.
 *
 * Original Noctis color scheme © Liviu Schera — MIT License
 *
 * @see https://github.com/liviuschera/noctis
 *
 * @author Simon André <smn.andre@gmail.com>
 */
final class NoctisTheme implements ThemeInterface
{
    public function __construct(
        private readonly bool $dark = true,
    ) {}

    public function getName(): string
    {
        return $this->dark ? 'Noctis Dark' : 'Noctis Light';
    }

    public function isDark(): bool
    {
        return $this->dark;
    }

    public function getCssClasses(): array
    {
        $prefix = $this->dark ? 'noctis-dark-' : 'noctis-light-';

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

    private function darkStylesheet(): string
    {
        return <<<'CSS'
/* Noctis Dark Theme */
.alto-highlight {
    background-color: #1e1e1e;
    color: #d4d4d4;
    padding: 1em;
    border-radius: 4px;
    overflow-x: auto;
}

.noctis-dark-comment,
.noctis-dark-comment-doc {
    color: #6a9955;
    font-style: italic;
}

.noctis-dark-punctuation {
    color: #d4d4d4;
}

.noctis-dark-keyword,
.noctis-dark-keyword-declaration,
.noctis-dark-keyword-control {
    color: #c586c0;
    font-weight: bold;
}

.noctis-dark-keyword-operator {
    color: #569cd6;
}

.noctis-dark-operator {
    color: #d4d4d4;
}

.noctis-dark-string,
.noctis-dark-string-interpolated {
    color: #ce9178;
}

.noctis-dark-number {
    color: #b5cea8;
}

.noctis-dark-boolean,
.noctis-dark-null {
    color: #569cd6;
}

.noctis-dark-variable,
.noctis-dark-variable-parameter,
.noctis-dark-variable-property {
    color: #9cdcfe;
}

.noctis-dark-variable-this {
    color: #569cd6;
    font-weight: bold;
}

.noctis-dark-function-definition {
    color: #dcdcaa;
    font-weight: bold;
}

.noctis-dark-function-call {
    color: #dcdcaa;
}

.noctis-dark-class-definition {
    color: #4ec9b0;
    font-weight: bold;
}

.noctis-dark-class-name {
    color: #4ec9b0;
}

.noctis-dark-enum-case {
    color: #4fc1ff;
}

.noctis-dark-constant {
    color: #4fc1ff;
}

.noctis-dark-constant-magic {
    color: #c586c0;
}

.noctis-dark-constant-class {
    color: #4fc1ff;
}

.noctis-dark-attribute {
    color: #c586c0;
}

.noctis-dark-typehint {
    color: #4ec9b0;
}

.noctis-dark-type {
    color: #4ec9b0;
}
CSS;
    }

    private function lightStylesheet(): string
    {
        return <<<'CSS'
/* Noctis Light Theme */
.alto-highlight {
    background-color: #faf9f5;
    color: #3b4256;
    padding: 1em;
    border-radius: 4px;
    overflow-x: auto;
}

.noctis-light-comment,
.noctis-light-comment-doc {
    color: #5c7e3e;
    font-style: italic;
}

.noctis-light-punctuation {
    color: #3b4256;
}

.noctis-light-keyword,
.noctis-light-keyword-declaration,
.noctis-light-keyword-control {
    color: #a354a3;
    font-weight: bold;
}

.noctis-light-keyword-operator {
    color: #3865c4;
}

.noctis-light-operator {
    color: #3b4256;
}

.noctis-light-string,
.noctis-light-string-interpolated {
    color: #b84e3a;
}

.noctis-light-number {
    color: #5c945c;
}

.noctis-light-boolean,
.noctis-light-null {
    color: #3865c4;
}

.noctis-light-variable,
.noctis-light-variable-parameter,
.noctis-light-variable-property {
    color: #007fd4;
}

.noctis-light-variable-this {
    color: #3865c4;
    font-weight: bold;
}

.noctis-light-function-definition {
    color: #836a00;
    font-weight: bold;
}

.noctis-light-function-call {
    color: #836a00;
}

.noctis-light-class-definition {
    color: #008b5c;
    font-weight: bold;
}

.noctis-light-class-name {
    color: #008b5c;
}

.noctis-light-enum-case {
    color: #007fd4;
}

.noctis-light-constant {
    color: #007fd4;
}

.noctis-light-constant-magic {
    color: #a354a3;
}

.noctis-light-constant-class {
    color: #007fd4;
}

.noctis-light-attribute {
    color: #a354a3;
}

.noctis-light-typehint {
    color: #008b5c;
}

.noctis-light-type {
    color: #008b5c;
}
CSS;
    }
}
