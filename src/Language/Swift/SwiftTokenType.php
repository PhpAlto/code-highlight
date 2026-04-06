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

namespace Alto\Code\Highlight\Language\Swift;

/**
 * Token types produced by the Swift lexer.
 */
enum SwiftTokenType
{
    case Whitespace;
    case Comment;
    case DocComment;
    case String;        // "...", """...""", with interpolation tokenized as one unit
    case Number;
    case Identifier;
    case Keyword;
    case BooleanLiteral;
    case NilLiteral;
    case Operator;
    case Punctuation;
    case Attribute;     // @available, @objc, etc.
    case Directive;     // #if, #else, #endif, #available, etc.
}
