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

namespace Alto\Code\Highlight\Tests\Language;

use Alto\Code\Highlight\Language\Rust\RustLexer;
use Alto\Code\Highlight\Language\Rust\RustSemanticParser;
use Alto\Code\Highlight\Language\Rust\RustState;
use Alto\Code\Highlight\Language\Rust\RustToken;
use Alto\Code\Highlight\Language\Rust\RustTokenType;
use Alto\Code\Highlight\Language\RustLanguage;
use Alto\Code\Highlight\Tests\LanguageTestCase;
use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(RustLanguage::class)]
#[CoversClass(RustLexer::class)]
#[CoversClass(RustSemanticParser::class)]
#[CoversClass(RustState::class)]
#[CoversClass(RustToken::class)]
#[CoversClass(RustTokenType::class)]
class RustLanguageTest extends LanguageTestCase
{
    protected string $language = 'rust';
    protected string $languageClass = RustLanguage::class;

    protected function getLanguageIdentifier(): string
    {
        return 'rust';
    }

    protected function getFileExtensions(): array
    {
        return ['rs'];
    }
}
