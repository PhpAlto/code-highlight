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

namespace Alto\Code\Highlight\Language\Go;

/**
 * Represents a token produced by the Go lexer.
 *
 * @author Simon André <smn.andre@gmail.com>
 */
final readonly class GoToken
{
    public function __construct(
        public string $text,
        public GoTokenType $type,
    ) {}
}
