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

namespace Alto\Code\Highlight\Language;

use Alto\Code\Highlight\Parser\ParsedStream;

/**
 * Interface for language-specific parsers.
 *
 * Each supported language implements this interface to provide
 * its own lexing and semantic parsing logic.
 *
 * @author Simon André <smn.andre@gmail.com>
 */
interface LanguageInterface
{
    /**
     * Parse the given source code and return a stream of tokens with semantic scopes.
     *
     * @param string $code The source code to parse
     *
     * @return ParsedStream The parsed token stream with assigned scopes
     */
    public function parse(string $code): ParsedStream;

    /**
     * Get the language identifier.
     *
     * @return string The language name (e.g., 'php', 'html', 'css')
     */
    public function getIdentifier(): string;
}
