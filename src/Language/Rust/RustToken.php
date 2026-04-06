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

namespace Alto\Code\Highlight\Language\Rust;

/**
 * @internal
 */
final readonly class RustToken
{
    public function __construct(
        public string $text,
        public RustTokenType $type,
    ) {
    }
}
