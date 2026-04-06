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

namespace Alto\Code\Highlight\Parser;

/**
 * Semantic token types for code transformations.
 *
 * While Scope is used for visual highlighting (colors),
 * TokenType is used for semantic operations:
 * - Remove comments
 * - Extract strings
 * - Minify code
 * - Find symbols
 *
 * @author Simon André <smn.andre@gmail.com>
 */
enum TokenType: string
{
    // Removable content
    case Comment = 'comment';
    case Docblock = 'docblock';
    case Whitespace = 'whitespace';

    // Extractable content
    case String = 'string';
    case Number = 'number';
    case RegExp = 'regexp';

    // Structural elements
    case Keyword = 'keyword';
    case Identifier = 'identifier';
    case Operator = 'operator';
    case Punctuation = 'punctuation';

    // Definitions (for symbol extraction)
    case FunctionName = 'function_name';
    case ClassName = 'class_name';
    case VariableName = 'variable_name';
    case ConstantName = 'constant_name';
    case PropertyName = 'property_name';
    case ParameterName = 'parameter_name';

    // Special
    case Embedded = 'embedded';
    case Unknown = 'unknown';

    /**
     * Check if this token type represents a comment.
     */
    public function isComment(): bool
    {
        return self::Comment === $this || self::Docblock === $this;
    }

    /**
     * Check if this token type is removable during minification.
     */
    public function isRemovable(): bool
    {
        return self::Comment === $this
            || self::Docblock === $this
            || self::Whitespace === $this;
    }

    /**
     * Check if this token type represents a definition.
     */
    public function isDefinition(): bool
    {
        return self::FunctionName === $this
            || self::ClassName === $this
            || self::ConstantName === $this;
    }
}
