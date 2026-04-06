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
 * This theme's color palette is inspired by the Dracula Theme.
 * Original color scheme © Zeno Rocha — MIT License
 * https://draculatheme.com
 */

namespace Alto\Code\Highlight\Theme;

use Alto\Code\Highlight\Scope;
use Alto\Code\Highlight\ThemeInterface;

/**
 * Dracula — color palette inspired by the Dracula Theme.
 *
 * Original Dracula color scheme © Zeno Rocha — MIT License
 *
 * @see https://draculatheme.com
 */
final class DraculaTheme implements ThemeInterface
{
    public function getName(): string
    {
        return 'Dracula';
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
            $classes[$scope->value] = '' === $suffix ? '' : 'dracula-'.$suffix;
        }

        return $classes;
    }

    public function getStylesheet(): string
    {
        return <<<'CSS'
/* Dracula Theme - Dark */
.alto-highlight {
    background-color: #282a36;
    color: #f8f8f2;
    padding: 1em;
    border-radius: 4px;
    overflow-x: auto;
}

.dracula-comment,
.dracula-comment-doc {
    color: #6272a4;
    font-style: italic;
}

.dracula-punctuation {
    color: #f8f8f2;
}

.dracula-keyword,
.dracula-keyword-declaration,
.dracula-keyword-control {
    color: #ff79c6;
    font-weight: bold;
}

.dracula-keyword-operator {
    color: #ff79c6;
}

.dracula-operator {
    color: #ff79c6;
}

.dracula-string,
.dracula-string-interpolated,
.dracula-string-heredoc {
    color: #f1fa8c;
}

.dracula-number {
    color: #bd93f9;
}

.dracula-boolean,
.dracula-null {
    color: #bd93f9;
}

.dracula-variable,
.dracula-variable-parameter,
.dracula-variable-property {
    color: #8be9fd;
}

.dracula-variable-this {
    color: #50fa7b;
    font-weight: bold;
}

.dracula-function-definition {
    color: #50fa7b;
    font-weight: bold;
}

.dracula-function-call {
    color: #50fa7b;
}

.dracula-class-definition,
.dracula-interface-definition,
.dracula-trait-definition,
.dracula-enum-definition {
    color: #ffb86c;
    font-weight: bold;
}

.dracula-class-name {
    color: #ffb86c;
}

.dracula-enum-case {
    color: #bd93f9;
}

.dracula-constant {
    color: #ffb86c;
}

.dracula-constant-magic {
    color: #ff79c6;
}

.dracula-constant-class {
    color: #ffb86c;
}

.dracula-attribute {
    color: #ff79c6;
}

.dracula-typehint,
.dracula-typehint-class {
    color: #8be9fd;
}
CSS;
    }
}
