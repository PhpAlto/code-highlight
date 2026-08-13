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

namespace Alto\Code\Highlight\Language\JavaScript;

/**
 * Token types from the JavaScript lexer.
 *
 * @author Simon André <smn.andre@gmail.com>
 */
enum JavaScriptTokenType
{
    case Whitespace;
    case Comment;
    case String;
    case TemplateLiteral;
    case TemplateExpression;
    case Regex;
    case Number;
    case Identifier;
    case Keyword;
    case BooleanLiteral;
    case NullLiteral;
    case Operator;
    case Punctuation;
}
