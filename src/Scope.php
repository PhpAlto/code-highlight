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

namespace Alto\Code\Highlight;

/**
 * Semantic scopes for syntax highlighting.
 *
 * These scopes intentionally stay generic so new languages can reuse the
 * existing vocabulary without adding theme-specific CSS classes.
 *
 * @author Simon André <smn.andre@gmail.com>
 */
enum Scope: string
{
    // Comments & annotations
    case Comment = 'comment';
    case CommentDocblock = 'comment.docblock';
    case CommentTask = 'comment.task';

    // Structural tokens
    case Punctuation = 'punctuation';
    case Operator = 'operator';

    // Keywords & modifiers
    case Keyword = 'keyword';
    case KeywordDeclaration = 'keyword.declaration';
    case KeywordOperator = 'keyword.operator';
    case KeywordControl = 'keyword.control';
    case StorageModifier = 'storage.modifier';

    // Literals & constants
    case String = 'string';
    case StringInterpolated = 'string.interpolated';
    case StringTemplateExpression = 'string.template.expression';
    case Number = 'number';
    case Boolean = 'boolean';
    case Null = 'null';
    case RegExp = 'regexp';
    case Constant = 'constant';
    case BuiltInConstant = 'constant.builtin';

    // Variables
    case Variable = 'variable';
    case VariableParameter = 'variable.parameter';
    case VariableProperty = 'variable.property';
    case VariableThis = 'variable.this';

    // Namespaces, types & functions
    case Namespace = 'namespace';
    case TypeDefinition = 'type.definition';
    case TypeReference = 'type.reference';
    case BuiltInType = 'type.builtin';
    case FunctionDefinition = 'function.definition';
    case FunctionCall = 'function.call';
    case FunctionBuiltin = 'function.builtin';
    case EnumCase = 'enum.case';

    // Attributes / annotations
    case AttributeName = 'attribute.name';
    case AttributeValue = 'attribute.value';

    // Markup & embedded content
    case TagName = 'tag.name';
    case TagAttributeName = 'tag.attribute.name';
    case TagAttributeValue = 'tag.attribute.value';
    case MarkupText = 'markup.text';
    case SectionName = 'section.name';

    // Diff/Meta/Diagnostics
    case DiffAdded = 'diff.added';
    case DiffRemoved = 'diff.removed';
    case DiffChanged = 'diff.changed';
    case Meta = 'meta';
    case DiagnosticError = 'diagnostic.error';
    case DiagnosticWarning = 'diagnostic.warning';
    case DiagnosticInfo = 'diagnostic.info';

    // Framework/library support buckets
    case SupportType = 'support.type';
    case SupportFunction = 'support.function';
    case SupportConstant = 'support.constant';

    // Internal (not typically styled)
    case Whitespace = 'whitespace';

    /**
     * Get the CSS class name for this scope.
     */
    public function toCssClass(): string
    {
        return $this->value;
    }
}
