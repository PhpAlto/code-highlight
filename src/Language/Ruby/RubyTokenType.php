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

namespace Alto\Code\Highlight\Language\Ruby;

/**
 * Token types produced by the Ruby lexer.
 */
enum RubyTokenType
{
    case Whitespace;
    case Comment;
    case String;           // "...", '...', %q{}, heredocs
    case Symbol;           // :name, :'...', :"..."
    case Regex;            // /pattern/flags
    case Number;
    case Identifier;       // method names, local variables
    case Keyword;
    case BooleanLiteral;
    case NilLiteral;
    case Operator;
    case Punctuation;
    case InstanceVariable; // @name
    case ClassVariable;    // @@name
    case GlobalVariable;   // $name
}
