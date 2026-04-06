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

namespace Alto\Code\Highlight\Language\Php;

/**
 * Parser state machine states.
 *
 * The semantic parser maintains a state to track context and determine
 * the semantic meaning of tokens.
 */
enum PhpState
{
    case TopLevel;
    case InClass;
    case InInterface;
    case InTrait;
    case InEnum;
    case InFunction;
    case InFunctionParams;
    case InFunctionBody;
    case InAttribute;
    case ExpectingType;
    case ExpectingNamespaceName;
    case ExpectingClassName;
    case ExpectingFunctionName;
    case ExpectingInterfaceName;
    case ExpectingTraitName;
    case ExpectingEnumName;
    case ExpectingPropertyName;
    case ExpectingConstantName;
}
