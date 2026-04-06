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

use Alto\Code\Highlight\Language\JavaScript\JavaScriptLexer;
use Alto\Code\Highlight\Language\JavaScript\JavaScriptSemanticParser;
use Alto\Code\Highlight\Language\JavaScript\JavaScriptToken;
use Alto\Code\Highlight\Language\JavaScript\JavaScriptTokenType;
use Alto\Code\Highlight\Language\JavaScriptLanguage;
use Alto\Code\Highlight\Tests\LanguageTestCase;
use PHPUnit\Framework\Attributes\CoversClass;

/**
 * Test cases for JavaScript language highlighting.
 *
 * Fixtures are auto-discovered from tests/Language/javascript/ directory.
 * Naming convention:
 *   - Code file: *.js
 *   - Expected HTML: *.js.html
 */
#[CoversClass(JavaScriptLanguage::class)]
#[CoversClass(JavaScriptLexer::class)]
#[CoversClass(JavaScriptSemanticParser::class)]
#[CoversClass(JavaScriptToken::class)]
#[CoversClass(JavaScriptTokenType::class)]
class JavaScriptLanguageTest extends LanguageTestCase
{
    protected string $language = 'javascript';
    protected string $languageClass = JavaScriptLanguage::class;

    protected function getLanguageIdentifier(): string
    {
        return 'javascript';
    }

    protected function getFileExtensions(): array
    {
        return ['js'];
    }
}
