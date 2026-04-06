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

use Alto\Code\Highlight\Exception\ParseException;
use Alto\Code\Highlight\Language\JavaScript\JavaScriptLexer;
use Alto\Code\Highlight\Language\JavaScript\JavaScriptSemanticParser;
use Alto\Code\Highlight\Parser\ParsedStream;

/**
 * JavaScript language parser with ES6+ support.
 *
 * Handles modern JavaScript syntax including classes, arrow functions,
 * template literals, async/await, destructuring, and more.
 *
 * Uses a two-pass architecture:
 * - Pass 1 (Lexer): Tokenization
 * - Pass 2 (Semantic Parser): Context-aware scope assignment
 */
class JavaScriptLanguage implements LanguageInterface
{
    private readonly JavaScriptLexer $lexer;

    private readonly JavaScriptSemanticParser $parser;

    public function __construct(?JavaScriptLexer $lexer = null, ?JavaScriptSemanticParser $parser = null)
    {
        $this->lexer = $lexer ?? new JavaScriptLexer();
        $this->parser = $parser ?? new JavaScriptSemanticParser();
    }

    public function getIdentifier(): string
    {
        return 'javascript';
    }

    public function parse(string $code): ParsedStream
    {
        try {
            // Pass 1: Tokenize
            $tokens = $this->lexer->tokenize($code);

            // Pass 2: Semantic analysis
            return $this->parser->parse($tokens);
        } catch (\Throwable $e) {
            throw new ParseException('Failed to parse JavaScript code: '.$e->getMessage());
        }
    }
}
