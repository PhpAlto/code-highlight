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

use Alto\Code\Highlight\Language\Go\GoLexer;
use Alto\Code\Highlight\Language\Go\GoSemanticParser;
use Alto\Code\Highlight\Language\Go\GoState;
use Alto\Code\Highlight\Language\Go\GoToken;
use Alto\Code\Highlight\Language\Go\GoTokenType;
use Alto\Code\Highlight\Language\GoLanguage;
use Alto\Code\Highlight\Tests\LanguageTestCase;
use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(GoLanguage::class)]
#[CoversClass(GoLexer::class)]
#[CoversClass(GoSemanticParser::class)]
#[CoversClass(GoState::class)]
#[CoversClass(GoToken::class)]
#[CoversClass(GoTokenType::class)]
class GoLanguageTest extends LanguageTestCase
{
    protected string $language = 'go';
    protected string $languageClass = GoLanguage::class;

    protected function getLanguageIdentifier(): string
    {
        return 'go';
    }

    protected function getFileExtensions(): array
    {
        return ['go'];
    }
}
