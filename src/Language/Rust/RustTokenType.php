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

namespace Alto\Code\Highlight\Language\Rust;

/**
 * Token types produced by the Rust lexer.
 *
 * @author Simon André <smn.andre@gmail.com>
 */
enum RustTokenType
{
    case Whitespace;
    case Comment;
    case DocComment;
    case String;
    case RawString;
    case Char;
    case Lifetime;
    case Number;
    case Identifier;
    case Macro;       // identifier! (macro invocations)
    case Keyword;
    case BooleanLiteral;
    case Operator;
    case Punctuation;
}
