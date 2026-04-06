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

namespace Alto\Code\Highlight\Tests\Unit\Language;

use Alto\Code\Highlight\Language\LanguageInterface;
use Alto\Code\Highlight\Language\TwigLanguage;
use Alto\Code\Highlight\Scope;
use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(TwigLanguage::class)]
final class TwigLanguageSemanticTest extends SemanticLanguageTestCase
{
    protected function createLanguage(): LanguageInterface
    {
        return new TwigLanguage();
    }

    public function testExpressionsAndControlStructures(): void
    {
        $code = <<<'TWIG'
        Hello {{ user.name|upper }} {% if active %}Yes{% endif %}
        TWIG;

        $this->assertSemanticTokens(
            $code,
            [
                ['Hello ', Scope::MarkupText],
                ['{{', Scope::Punctuation],
                ['user', Scope::Variable],
                ['.', Scope::Operator],
                ['name', Scope::Variable],
                ['|', Scope::Operator],
                ['upper', Scope::Variable],
                ['}}', Scope::Punctuation],
                ['{%', Scope::Punctuation],
                ['if', Scope::KeywordControl],
                ['active', Scope::Variable],
                ['%}', Scope::Punctuation],
                ['Yes', Scope::MarkupText],
                ['{%', Scope::Punctuation],
                ['endif', Scope::KeywordControl],
                ['%}', Scope::Punctuation],
            ],
        );
    }
}
