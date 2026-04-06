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

namespace Alto\Code\Highlight\Language\JavaScript;

/**
 * Parser state machine for JavaScript semantic analysis.
 */
enum JavaScriptState
{
    case TopLevel;
    case ExpectingClassName;
    case ExpectingFunctionName;
    case ExpectingMethodName;
    case InFunctionParams;
    case InFunctionBody;
    case InClassBody;
    case ExpectingImportSpecifier;
    case ExpectingExportName;
}
