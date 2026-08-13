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

use Alto\Code\Highlight\Language\CssLanguage;
use Alto\Code\Highlight\Language\LanguageInterface;
use Alto\Code\Highlight\Scope;
use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(CssLanguage::class)]
final class CssLanguageSemanticTest extends SemanticLanguageTestCase
{
    protected function createLanguage(): LanguageInterface
    {
        return new CssLanguage();
    }

    public function testBasicRuleTokenization(): void
    {
        $this->assertSemanticTokens(
            'body { color: #fff; margin: 0; display: block; }',
            [
                ['body', Scope::TagName],
                ['{', Scope::Punctuation],
                ['color', Scope::AttributeName],
                [':', Scope::Punctuation],
                ['#fff', Scope::BuiltInConstant],
                [';', Scope::Punctuation],
                ['margin', Scope::AttributeName],
                [':', Scope::Punctuation],
                ['0', Scope::AttributeValue],
                [';', Scope::Punctuation],
                ['display', Scope::AttributeName],
                [':', Scope::Punctuation],
                ['block', Scope::AttributeValue],
                [';', Scope::Punctuation],
                ['}', Scope::Punctuation],
            ],
        );
    }

    public function testAtRuleAndPseudoSelectorHandling(): void
    {
        $this->assertSemanticTokens(
            '@media screen { button:hover { transform: scale(1.1); } }',
            [
                ['@media', Scope::Keyword],
                ['screen', Scope::TagName],
                ['{', Scope::Punctuation],
                ['button', Scope::AttributeName],
                [':', Scope::Punctuation],
                ['hover', Scope::AttributeValue],
                ['{', Scope::Punctuation],
                ['transform', Scope::AttributeName],
                [':', Scope::Punctuation],
                ['scale(', Scope::FunctionCall],
                ['1.1', Scope::AttributeValue],
                [')', Scope::Punctuation],
                [';', Scope::Punctuation],
                ['}', Scope::Punctuation],
                ['}', Scope::Punctuation],
            ],
        );
    }
}
