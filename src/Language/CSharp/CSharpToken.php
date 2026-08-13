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

namespace Alto\Code\Highlight\Language\CSharp;

/**
 * Represents a token produced by the C# lexer.
 *
 * @author Simon André <smn.andre@gmail.com>
 */
final readonly class CSharpToken
{
    public function __construct(
        public string $text,
        public CSharpTokenType $type,
    ) {}
}
