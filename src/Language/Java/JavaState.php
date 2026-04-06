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

namespace Alto\Code\Highlight\Language\Java;

/**
 * Parser state machine states for Java semantic analysis.
 */
enum JavaState
{
    case TopLevel;
    case ExpectingTypeName;
    case InClassBody;
    case InMethodBody;
}
