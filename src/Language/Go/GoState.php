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
 * Parser state machine states for Go semantic analysis.
 */
enum GoState
{
    case TopLevel;
    case ExpectingFunctionOrReceiver; // After 'func' keyword — next is either name or receiver
    case InReceiver;                  // Inside (receiver Type) parens
    case ExpectingFunctionName;       // After receiver closing paren — next is method name
    case ExpectingTypeName;           // After 'type' keyword
}
