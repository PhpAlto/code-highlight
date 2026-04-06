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

use PhpToken;

/**
 * The Lexer is responsible for tokenizing PHP source code.
 *
 * This is Pass 1 of the two-pass architecture. It uses PHP's native
 * PhpToken::tokenize() for 100% accurate tokenization and performs
 * normalization on the token stream.
 *
 * @internal
 *
 * @final Not declared final to allow test doubles, but treat as final in production code
 */
class PhpLexer
{
    /**
     * Tokenize the given PHP source code.
     *
     * @param string $code The PHP source code to tokenize
     *
     * @return list<\PhpToken> Stream of tokens from PHP's native tokenizer
     */
    public function tokenize(string $code): array
    {
        // Use PHP's native tokenizer for 100% accuracy
        // Note: PhpToken::tokenize() never produces adjacent whitespace tokens,
        // so no normalization is needed.
        return array_values(\PhpToken::tokenize($code));
    }
}
