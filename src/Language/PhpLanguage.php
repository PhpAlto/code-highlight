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
use Alto\Code\Highlight\Language\Php\PhpLexer;
use Alto\Code\Highlight\Language\Php\PhpSemanticParser;
use Alto\Code\Highlight\Parser\ParsedStream;

/**
 * PHP language parser.
 *
 * Handles parsing and semantic analysis of PHP code.
 */
final class PhpLanguage implements LanguageInterface
{
    private readonly PhpLexer $lexer;

    private readonly PhpSemanticParser $parser;

    public function __construct(?PhpLexer $lexer = null, ?PhpSemanticParser $parser = null)
    {
        $this->lexer = $lexer ?? new PhpLexer();
        $this->parser = $parser ?? new PhpSemanticParser();
    }

    public function getIdentifier(): string
    {
        return 'php';
    }

    public function parse(string $code): ParsedStream
    {
        try {
            // Pass 1: Tokenize
            $tokens = $this->lexer->tokenize($code);

            // Pass 2: Semantic analysis
            return $this->parser->parse($tokens);
        } catch (\Throwable $e) {
            throw new ParseException('Failed to parse PHP code: '.$e->getMessage());
        }
    }
}
