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
 * Parser state machine for Swift semantic analysis.
 */
enum SwiftState
{
    case TopLevel;
    case ExpectingFunctionName;   // After 'func'
    case ExpectingTypeName;       // After 'class' | 'struct' | 'enum' | 'protocol' | 'actor' | 'typealias'
    case ExpectingExtensionType;  // After 'extension'
}
