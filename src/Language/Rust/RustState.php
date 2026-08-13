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
 * Parser state machine for Rust semantic analysis.
 *
 * @author Simon André <smn.andre@gmail.com>
 */
enum RustState
{
    case TopLevel;
    case ExpectingFunctionName;   // After 'fn'
    case ExpectingTypeName;       // After 'struct' | 'enum' | 'trait' | 'type' | 'union'
    case ExpectingImplType;       // After 'impl'
    case ExpectingModuleName;     // After 'mod'
    case InAttribute;             // After '#['
}
