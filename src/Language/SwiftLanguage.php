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

use Alto\Code\Highlight\Language\Swift\SwiftLexer;
use Alto\Code\Highlight\Language\Swift\SwiftSemanticParser;
use Alto\Code\Highlight\Parser\ParsedStream;

/**
 * Swift language highlighter.
 *
 * Two-pass semantic highlighter: lexer → semantic parser.
 *
 * @author Simon André <smn.andre@gmail.com>
 */
final class SwiftLanguage implements LanguageInterface
{
    public function getIdentifier(): string
    {
        return 'swift';
    }

    public function parse(string $code): ParsedStream
    {
        return (new SwiftSemanticParser())->parse((new SwiftLexer())->tokenize($code));
    }
}
