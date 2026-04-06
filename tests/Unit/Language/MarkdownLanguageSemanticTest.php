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
use Alto\Code\Highlight\Language\MarkdownLanguage;
use Alto\Code\Highlight\Scope;
use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(MarkdownLanguage::class)]
final class MarkdownLanguageSemanticTest extends SemanticLanguageTestCase
{
    protected function createLanguage(): LanguageInterface
    {
        return new MarkdownLanguage();
    }

    public function testHeadingsListsAndFencedCodeBlocks(): void
    {
        $code = <<<'MD'
        # Title
        
        - Item
        ```php
        echo "Hi";
        ```
        MD;

        $this->assertSemanticTokens(
            $code,
            [
                ['#', Scope::TagName],
                ['Title', Scope::TagName],
                ['-', Scope::TagName],
                ['Item', Scope::MarkupText],
                ['```php', Scope::String],
                ['echo "Hi";', Scope::String],
                ['```', Scope::String],
            ],
        );
    }
}
