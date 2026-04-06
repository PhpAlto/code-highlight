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

use Alto\Code\Highlight\Language\CSharp\CSharpLexer;
use Alto\Code\Highlight\Language\CSharp\CSharpSemanticParser;
use Alto\Code\Highlight\Parser\ParsedStream;

/**
 * C# language highlighter.
 *
 * Uses a two-pass parser to semantically distinguish function definitions
 * from calls and class declarations from references.
 */
final class CSharpLanguage implements LanguageInterface
{
    public function getIdentifier(): string
    {
        return 'csharp';
    }

    public function parse(string $code): ParsedStream
    {
        $lexer = new CSharpLexer();
        $tokens = $lexer->tokenize($code);

        $parser = new CSharpSemanticParser();

        return $parser->parse($tokens);
    }
}
