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

namespace Alto\Code\Highlight\Language\Java;

/**
 * Token types produced by the Java lexer.
 *
 * @author Simon André <smn.andre@gmail.com>
 */
enum JavaTokenType
{
    case Whitespace;
    case Comment;
    case DocComment;
    case Annotation;
    case String;
    case CharLiteral;
    case Number;
    case Identifier;
    case Keyword;
    case BooleanLiteral;
    case NullLiteral;
    case Operator;
    case Punctuation;
}
