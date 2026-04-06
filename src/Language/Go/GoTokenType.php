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

namespace Alto\Code\Highlight\Language\Go;

/**
 * Token types produced by the Go lexer.
 */
enum GoTokenType
{
    case Whitespace;
    case Comment;
    case String;
    case RawString;
    case Rune;
    case Number;
    case Identifier;
    case Keyword;
    case BooleanLiteral;
    case NilLiteral;
    case Operator;
    case Punctuation;
}
