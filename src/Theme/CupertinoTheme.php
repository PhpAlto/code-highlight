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

namespace Alto\Code\Highlight\Theme;

use Alto\Code\Highlight\Scope;
use Alto\Code\Highlight\ThemeInterface;

/**
 * Cupertino — a theme inspired by macOS developer tooling aesthetics.
 *
 * An original color palette in the spirit of native macOS code editors,
 * designed for comfort on both dark and light backgrounds.
 *
 * @author Simon André <smn.andre@gmail.com>
 */
final class CupertinoTheme implements ThemeInterface
{
    public function __construct(
        private readonly bool $dark = true,
    ) {}

    public function getName(): string
    {
        return $this->dark ? 'Cupertino Dark' : 'Cupertino Light';
    }

    public function isDark(): bool
    {
        return $this->dark;
    }

    public function getCssClasses(): array
    {
        $prefix = $this->dark ? 'cupertino-dark-' : 'cupertino-light-';

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
            [Scope::DiagnosticWarning, 'number'],
            [Scope::DiagnosticInfo, 'variable'],
            [Scope::SupportType, 'class-name'],
            [Scope::SupportFunction, 'function-call'],
            [Scope::SupportConstant, 'constant'],
            [Scope::Whitespace, ''],
        ];
    }

    public function getStylesheet(): string
    {
        return $this->dark ? $this->darkStylesheet() : $this->lightStylesheet();
    }

    private function darkStylesheet(): string
    {
        return <<<'CSS'
/* Cupertino Dark Theme */
.alto-highlight {
    background-color: #1a1c20;
    color: #d2d4d8;
    padding: 1em;
    border-radius: 8px;
    overflow-x: auto;
    font-family: ui-monospace, "SF Mono", Menlo, Consolas, monospace;
    font-size: 85%;
    line-height: 1.5;
}

.cupertino-dark-comment,
.cupertino-dark-comment-doc {
    color: #7a909e;
    font-style: italic;
}

.cupertino-dark-punctuation {
    color: #d2d4d8;
}

.cupertino-dark-keyword,
.cupertino-dark-keyword-declaration,
.cupertino-dark-keyword-control {
    color: #e8709f;
}

.cupertino-dark-keyword-operator {
    color: #e8709f;
}

.cupertino-dark-operator {
    color: #b8c0cc;
}

.cupertino-dark-string,
.cupertino-dark-string-interpolated {
    color: #e5c27a;
}

.cupertino-dark-number {
    color: #df9f58;
}

.cupertino-dark-boolean,
.cupertino-dark-null {
    color: #df9f58;
}

.cupertino-dark-variable,
.cupertino-dark-variable-parameter,
.cupertino-dark-variable-property {
    color: #63cce8;
}

.cupertino-dark-variable-this {
    color: #4ab8d8;
    font-weight: bold;
}

.cupertino-dark-function-definition {
    color: #52b8cf;
    font-weight: bold;
}

.cupertino-dark-function-call {
    color: #52b8cf;
}

.cupertino-dark-class-definition {
    color: #e8cf68;
    font-weight: bold;
}

.cupertino-dark-class-name {
    color: #e8cf68;
}

.cupertino-dark-enum-case {
    color: #e8a86e;
}

.cupertino-dark-constant {
    color: #e8709f;
}

.cupertino-dark-constant-magic {
    color: #c97eb8;
}

.cupertino-dark-constant-class {
    color: #e8cf68;
}

.cupertino-dark-attribute {
    color: #c97eb8;
}

.cupertino-dark-typehint {
    color: #63cce8;
}

.cupertino-dark-type {
    color: #e8cf68;
}
CSS;
    }

    private function lightStylesheet(): string
    {
        return <<<'CSS'
/* Cupertino Light Theme */
.alto-highlight {
    background-color: #ffffff;
    color: #1d1d1f;
    padding: 1em;
    border-radius: 8px;
    overflow-x: auto;
    font-family: ui-monospace, "SF Mono", Menlo, Consolas, monospace;
    font-size: 85%;
    line-height: 1.5;
}

.cupertino-light-comment,
.cupertino-light-comment-doc {
    color: #6e7781;
    font-style: italic;
}

.cupertino-light-punctuation {
    color: #1d1d1f;
}

.cupertino-light-keyword,
.cupertino-light-keyword-declaration,
.cupertino-light-keyword-control {
    color: #c7254e;
}

.cupertino-light-keyword-operator {
    color: #c7254e;
}

.cupertino-light-operator {
    color: #3d4751;
}

.cupertino-light-string,
.cupertino-light-string-interpolated {
    color: #b8860b;
}

.cupertino-light-number {
    color: #a25900;
}

.cupertino-light-boolean,
.cupertino-light-null {
    color: #a25900;
}

.cupertino-light-variable,
.cupertino-light-variable-parameter,
.cupertino-light-variable-property {
    color: #0969da;
}

.cupertino-light-variable-this {
    color: #0550ae;
    font-weight: bold;
}

.cupertino-light-function-definition {
    color: #0969da;
    font-weight: bold;
}

.cupertino-light-function-call {
    color: #0969da;
}

.cupertino-light-class-definition {
    color: #953800;
    font-weight: bold;
}

.cupertino-light-class-name {
    color: #953800;
}

.cupertino-light-enum-case {
    color: #a25900;
}

.cupertino-light-constant {
    color: #c7254e;
}

.cupertino-light-constant-magic {
    color: #8250df;
}

.cupertino-light-constant-class {
    color: #953800;
}

.cupertino-light-attribute {
    color: #8250df;
}

.cupertino-light-typehint {
    color: #0969da;
}

.cupertino-light-type {
    color: #953800;
}
CSS;
    }
}
