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
 * This theme's color palette is based on the GitHub Primer color system.
 * Color palette © GitHub, Inc. — MIT License
 * https://github.com/primer/primitives
 */

namespace Alto\Code\Highlight\Theme;

use Alto\Code\Highlight\Scope;
use Alto\Code\Highlight\ThemeInterface;

/**
 * GitHub — dark and light variants based on the GitHub Primer color system.
 *
 * Color palette © GitHub, Inc. — MIT License
 *
 * @see https://github.com/primer/primitives
 *
 * @author Simon André <smn.andre@gmail.com>
 */
final class GitHubTheme implements ThemeInterface
{
    public function __construct(
        private readonly bool $dark = true,
    ) {}

    public function getName(): string
    {
        return $this->dark ? 'GitHub Dark' : 'GitHub Light';
    }

    public function isDark(): bool
    {
        return $this->dark;
    }

    public function getCssClasses(): array
    {
        $prefix = $this->dark ? 'github-dark-' : 'github-light-';

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
            [Scope::CommentDocblock, 'comment'],
            [Scope::CommentTask, 'comment'],
            [Scope::Keyword, 'keyword'],
            [Scope::KeywordDeclaration, 'keyword'],
            [Scope::KeywordOperator, 'keyword'],
            [Scope::KeywordControl, 'keyword'],
            [Scope::StorageModifier, 'keyword'],
            [Scope::Operator, 'operator'],
            [Scope::Punctuation, 'punctuation'],
            [Scope::String, 'string'],
            [Scope::StringInterpolated, 'string'],
            [Scope::StringTemplateExpression, 'string'],
            [Scope::Number, 'number'],
            [Scope::Boolean, 'constant'],
            [Scope::Null, 'constant'],
            [Scope::RegExp, 'string'],
            [Scope::Constant, 'constant'],
            [Scope::BuiltInConstant, 'constant'],
            [Scope::EnumCase, 'constant'],
            [Scope::Variable, 'variable'],
            [Scope::VariableParameter, 'variable'],
            [Scope::VariableProperty, 'variable'],
            [Scope::VariableThis, 'keyword'],
            [Scope::Namespace, 'type'],
            [Scope::TypeDefinition, 'type'],
            [Scope::TypeReference, 'type'],
            [Scope::BuiltInType, 'keyword'],
            [Scope::FunctionDefinition, 'function'],
            [Scope::FunctionCall, 'function'],
            [Scope::FunctionBuiltin, 'function'],
            [Scope::AttributeName, 'attribute'],
            [Scope::AttributeValue, 'string'],
            [Scope::TagName, 'tag'],
            [Scope::TagAttributeName, 'attribute'],
            [Scope::TagAttributeValue, 'string'],
            [Scope::MarkupText, 'punctuation'],
            [Scope::SectionName, 'type'],
            [Scope::DiffAdded, 'diff-added'],
            [Scope::DiffRemoved, 'diff-removed'],
            [Scope::DiffChanged, 'diff-changed'],
            [Scope::Meta, 'constant'],
            [Scope::DiagnosticError, 'diff-removed'],
            [Scope::DiagnosticWarning, 'number'],
            [Scope::DiagnosticInfo, 'constant'],
            [Scope::SupportType, 'type'],
            [Scope::SupportFunction, 'function'],
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
/* GitHub Dark Theme */
.alto-highlight {
    background-color: #0d1117;
    color: #e6edf3;
    padding: 1em;
    border-radius: 6px;
    overflow-x: auto;
    font-family: ui-monospace, SFMono-Regular, "SF Mono", Menlo, Consolas, "Liberation Mono", monospace;
    font-size: 85%;
    line-height: 1.45;
}

.github-dark-comment {
    color: #8b949e;
    font-style: italic;
}

.github-dark-keyword {
    color: #ff7b72;
}

.github-dark-operator {
    color: #ff7b72;
}

.github-dark-punctuation {
    color: #e6edf3;
}

.github-dark-string {
    color: #a5d6ff;
}

.github-dark-number {
    color: #79c0ff;
}

.github-dark-constant {
    color: #79c0ff;
}

.github-dark-variable {
    color: #ffa657;
}

.github-dark-function {
    color: #d2a8ff;
}

.github-dark-type {
    color: #ffa657;
}

.github-dark-tag {
    color: #7ee787;
}

.github-dark-attribute {
    color: #79c0ff;
}

.github-dark-diff-added {
    color: #3fb950;
    background-color: rgba(63, 185, 80, 0.1);
}

.github-dark-diff-removed {
    color: #f85149;
    background-color: rgba(248, 81, 73, 0.1);
}

.github-dark-diff-changed {
    color: #d29922;
    background-color: rgba(210, 153, 34, 0.1);
}
CSS;
    }

    private function lightStylesheet(): string
    {
        return <<<'CSS'
/* GitHub Light Theme */
.alto-highlight {
    background-color: #ffffff;
    color: #24292e;
    padding: 1em;
    border-radius: 6px;
    overflow-x: auto;
    font-family: ui-monospace, SFMono-Regular, "SF Mono", Menlo, Consolas, "Liberation Mono", monospace;
    font-size: 85%;
    line-height: 1.45;
}

.github-light-comment {
    color: #6e7781;
    font-style: italic;
}

.github-light-keyword {
    color: #cf222e;
}

.github-light-operator {
    color: #cf222e;
}

.github-light-punctuation {
    color: #24292e;
}

.github-light-string {
    color: #0a3069;
}

.github-light-number {
    color: #0550ae;
}

.github-light-constant {
    color: #0550ae;
}

.github-light-variable {
    color: #953800;
}

.github-light-function {
    color: #8250df;
}

.github-light-type {
    color: #953800;
}

.github-light-tag {
    color: #116329;
}

.github-light-attribute {
    color: #0550ae;
}

.github-light-diff-added {
    color: #116329;
    background-color: #dafbe1;
}

.github-light-diff-removed {
    color: #82071e;
    background-color: #ffebe9;
}

.github-light-diff-changed {
    color: #953800;
    background-color: #fdf8e1;
}
CSS;
    }
}
