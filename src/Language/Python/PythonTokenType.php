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

namespace Alto\Code\Highlight\Language\Python;

/**
 * Token types produced by the Python lexer.
 *
 * @author Simon André <smn.andre@gmail.com>
 */
enum PythonTokenType
{
    case Whitespace;
    case Comment;
    case String;
    case Number;
    case Identifier;
    case Keyword;
    case BooleanLiteral;
    case NilLiteral;
    case Operator;
    case Punctuation;
    case Decorator;
}
