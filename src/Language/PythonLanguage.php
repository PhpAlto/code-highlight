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

use Alto\Code\Highlight\Language\Python\PythonLexer;
use Alto\Code\Highlight\Language\Python\PythonSemanticParser;
use Alto\Code\Highlight\Parser\ParsedStream;

/**
 * Python language highlighter.
 *
 * Uses a two-pass parser to semantically distinguish function definitions
 * from calls and class declarations from references.
 */
final class PythonLanguage implements LanguageInterface
{
    public function getIdentifier(): string
    {
        return 'python';
    }

    public function parse(string $code): ParsedStream
    {
        $lexer = new PythonLexer();
        $tokens = $lexer->tokenize($code);

        $parser = new PythonSemanticParser();

        return $parser->parse($tokens);
    }
}
