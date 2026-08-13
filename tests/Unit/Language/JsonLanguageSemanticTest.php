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

namespace Alto\Code\Highlight\Tests\Unit\Language;

use Alto\Code\Highlight\Language\JsonLanguage;
use Alto\Code\Highlight\Language\LanguageInterface;
use Alto\Code\Highlight\Scope;
use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(JsonLanguage::class)]
final class JsonLanguageSemanticTest extends SemanticLanguageTestCase
{
    protected function createLanguage(): LanguageInterface
    {
        return new JsonLanguage();
    }

    public function testObjectLiteralsAreTokenizedWithCanonicalScopes(): void
    {
        $this->assertSemanticTokens(
            '{ "name": "Alto", "enabled": true, "count": 42, "items": null }',
            [
                ['{', Scope::Punctuation],
                ['"name"', Scope::AttributeName],
                [':', Scope::Punctuation],
                ['"Alto"', Scope::String],
                [',', Scope::Punctuation],
                ['"enabled"', Scope::AttributeName],
                [':', Scope::Punctuation],
                ['true', Scope::Boolean],
                [',', Scope::Punctuation],
                ['"count"', Scope::AttributeName],
                [':', Scope::Punctuation],
                ['42', Scope::Number],
                [',', Scope::Punctuation],
                ['"items"', Scope::AttributeName],
                [':', Scope::Punctuation],
                ['null', Scope::Null],
                ['}', Scope::Punctuation],
            ],
        );
    }

    public function testArrayLiteralsCoverStringsNumbersAndBooleans(): void
    {
        $this->assertSemanticTokens(
            '[ "A", -1.5, false, {"inner": []} ]',
            [
                ['[', Scope::Punctuation],
                ['"A"', Scope::String],
                [',', Scope::Punctuation],
                ['-1.5', Scope::Number],
                [',', Scope::Punctuation],
                ['false', Scope::Boolean],
                [',', Scope::Punctuation],
                ['{', Scope::Punctuation],
                ['"inner"', Scope::AttributeName],
                [':', Scope::Punctuation],
                ['[', Scope::Punctuation],
                [']', Scope::Punctuation],
                ['}', Scope::Punctuation],
                [']', Scope::Punctuation],
            ],
        );
    }
}
