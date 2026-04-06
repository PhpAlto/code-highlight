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

use Alto\Code\Highlight\Language\Java\JavaLexer;
use Alto\Code\Highlight\Language\Java\JavaSemanticParser;
use Alto\Code\Highlight\Parser\ParsedStream;

/**
 * Java language highlighter.
 *
 * Uses a two-pass parser to semantically distinguish function definitions
 * from calls, type declarations from references, and track context
 * through brace depth for accurate scope assignment.
 */
final class JavaLanguage implements LanguageInterface
{
    public function getIdentifier(): string
    {
        return 'java';
    }

    public function parse(string $code): ParsedStream
    {
        $lexer = new JavaLexer();
        $tokens = $lexer->tokenize($code);

        $parser = new JavaSemanticParser();

        return $parser->parse($tokens);
    }
}
