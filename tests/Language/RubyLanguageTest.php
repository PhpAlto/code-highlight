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

use Alto\Code\Highlight\Language\Ruby\RubyLexer;
use Alto\Code\Highlight\Language\Ruby\RubySemanticParser;
use Alto\Code\Highlight\Language\Ruby\RubyState;
use Alto\Code\Highlight\Language\Ruby\RubyToken;
use Alto\Code\Highlight\Language\Ruby\RubyTokenType;
use Alto\Code\Highlight\Language\RubyLanguage;
use Alto\Code\Highlight\Tests\LanguageTestCase;
use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(RubyLanguage::class)]
#[CoversClass(RubyLexer::class)]
#[CoversClass(RubySemanticParser::class)]
#[CoversClass(RubyState::class)]
#[CoversClass(RubyToken::class)]
#[CoversClass(RubyTokenType::class)]
class RubyLanguageTest extends LanguageTestCase
{
    protected string $language = 'ruby';
    protected string $languageClass = RubyLanguage::class;

    protected function getLanguageIdentifier(): string
    {
        return 'ruby';
    }

    protected function getFileExtensions(): array
    {
        return ['rb'];
    }
}
