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

use Alto\Code\Highlight\Language\Ruby\RubyLexer;
use Alto\Code\Highlight\Language\Ruby\RubySemanticParser;
use Alto\Code\Highlight\Parser\ParsedStream;

/**
 * Ruby language highlighter.
 *
 * Two-pass semantic highlighter: lexer → semantic parser.
 */
final class RubyLanguage implements LanguageInterface
{
    public function getIdentifier(): string
    {
        return 'ruby';
    }

    public function parse(string $code): ParsedStream
    {
        return (new RubySemanticParser())->parse((new RubyLexer())->tokenize($code));
    }
}
