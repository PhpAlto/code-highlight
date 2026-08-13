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

use Alto\Code\Highlight\Language\Rust\RustLexer;
use Alto\Code\Highlight\Language\Rust\RustSemanticParser;
use Alto\Code\Highlight\Parser\ParsedStream;

/**
 * Rust language highlighter.
 *
 * Two-pass semantic highlighter: lexer → semantic parser.
 *
 * @author Simon André <smn.andre@gmail.com>
 */
final class RustLanguage implements LanguageInterface
{
    public function getIdentifier(): string
    {
        return 'rust';
    }

    public function parse(string $code): ParsedStream
    {
        return (new RustSemanticParser())->parse((new RustLexer())->tokenize($code));
    }
}
