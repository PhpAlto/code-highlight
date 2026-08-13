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

use Alto\Code\Highlight\Language\EmbeddedLanguageCapable;
use Alto\Code\Highlight\Language\EmbeddedLanguageContext;
use Alto\Code\Highlight\Language\HtmlLanguage;
use Alto\Code\Highlight\Language\LanguageInterface;
use Alto\Code\Highlight\Parser\ParsedStream;
use Alto\Code\Highlight\Scope;
use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(HtmlLanguage::class)]
final class HtmlLanguageSemanticTest extends SemanticLanguageTestCase
{
    protected function createLanguage(): LanguageInterface
    {
        return new HtmlLanguage();
    }

    protected function parseCode(string $code): ParsedStream
    {
        $language = $this->createLanguage();
        \assert($language instanceof EmbeddedLanguageCapable);

        return $language->parseWithEmbedding($code, EmbeddedLanguageContext::disabled());
    }

    public function testTagWithAttributesAndNestedElements(): void
    {
        $this->assertSemanticTokens(
            '<div class="hero">Hello <strong>World</strong></div>',
            [
                ['<', Scope::TagName],
                ['div', Scope::TagName],
                ['class', Scope::TagAttributeName],
                ['=', Scope::Punctuation],
                ['"hero"', Scope::TagAttributeValue],
                ['>', Scope::TagName],
                ['Hello ', Scope::MarkupText],
                ['<', Scope::TagName],
                ['strong', Scope::TagName],
                ['>', Scope::TagName],
                ['World', Scope::MarkupText],
                ['</', Scope::TagName],
                ['strong', Scope::TagName],
                ['>', Scope::TagName],
                ['</', Scope::TagName],
                ['div', Scope::TagName],
                ['>', Scope::TagName],
            ],
        );
    }

    public function testMetaConstructsAreTreatedAsComments(): void
    {
        $this->assertSemanticTokens(
            '<!DOCTYPE html><!-- hero --><?php echo "x"; ?>',
            [
                ['<!DOCTYPE html>', Scope::Comment],
                ['<!-- hero -->', Scope::Comment],
                ['<?php echo "x"; ?>', Scope::Comment],
            ],
        );
    }
}
