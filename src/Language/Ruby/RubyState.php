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

namespace Alto\Code\Highlight\Language\Ruby;

/**
 * Parser state machine for Ruby semantic analysis.
 *
 * @author Simon André <smn.andre@gmail.com>
 */
enum RubyState
{
    case TopLevel;
    case ExpectingMethodName;  // After 'def'
    case ExpectingClassName;   // After 'class' or 'module'
}
