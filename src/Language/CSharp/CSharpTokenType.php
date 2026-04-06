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

namespace Alto\Code\Highlight\Language\CSharp;

/**
 * Token types produced by the C# lexer.
 */
enum CSharpTokenType
{
    case Whitespace;
    case Comment;
    case DocComment;
    case Directive;
    case Attribute;
    case String;
    case VerbatimString;
    case Interpolation;
    case Number;
    case Identifier;
    case Keyword;
    case BooleanLiteral;
    case NullLiteral;
    case Operator;
    case Punctuation;
}
