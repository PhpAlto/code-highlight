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

namespace Alto\Code\Highlight\Language;

use Alto\Code\Highlight\Language\Go\GoLexer;
use Alto\Code\Highlight\Language\Go\GoSemanticParser;
use Alto\Code\Highlight\Parser\ParsedStream;

/**
 * Go language highlighter.
 *
 * Uses a two-pass parser to semantically distinguish function definitions
 * from calls and type declarations from references, including Go's method
 * receiver syntax: func (r *Receiver) MethodName().
 */
final class GoLanguage implements LanguageInterface
{
    public function getIdentifier(): string
    {
        return 'go';
    }

    public function parse(string $code): ParsedStream
    {
        $lexer = new GoLexer();
        $tokens = $lexer->tokenize($code);

        $parser = new GoSemanticParser();

        return $parser->parse($tokens);
    }
}
