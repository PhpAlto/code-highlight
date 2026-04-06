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

use Alto\Code\Highlight\Language\Java\JavaLexer;
use Alto\Code\Highlight\Language\Java\JavaSemanticParser;
use Alto\Code\Highlight\Language\Java\JavaState;
use Alto\Code\Highlight\Language\Java\JavaToken;
use Alto\Code\Highlight\Language\Java\JavaTokenType;
use Alto\Code\Highlight\Language\JavaLanguage;
use Alto\Code\Highlight\Tests\LanguageTestCase;
use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(JavaLanguage::class)]
#[CoversClass(JavaLexer::class)]
#[CoversClass(JavaSemanticParser::class)]
#[CoversClass(JavaState::class)]
#[CoversClass(JavaToken::class)]
#[CoversClass(JavaTokenType::class)]
class JavaLanguageTest extends LanguageTestCase
{
    protected string $language = 'java';
    protected string $languageClass = JavaLanguage::class;

    protected function getLanguageIdentifier(): string
    {
        return 'java';
    }

    protected function getFileExtensions(): array
    {
        return ['java'];
    }
}
