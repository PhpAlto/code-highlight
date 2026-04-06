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

use Alto\Code\Highlight\Language\Swift\SwiftLexer;
use Alto\Code\Highlight\Language\Swift\SwiftSemanticParser;
use Alto\Code\Highlight\Language\Swift\SwiftState;
use Alto\Code\Highlight\Language\Swift\SwiftToken;
use Alto\Code\Highlight\Language\Swift\SwiftTokenType;
use Alto\Code\Highlight\Language\SwiftLanguage;
use Alto\Code\Highlight\Tests\LanguageTestCase;
use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(SwiftLanguage::class)]
#[CoversClass(SwiftLexer::class)]
#[CoversClass(SwiftSemanticParser::class)]
#[CoversClass(SwiftState::class)]
#[CoversClass(SwiftToken::class)]
#[CoversClass(SwiftTokenType::class)]
class SwiftLanguageTest extends LanguageTestCase
{
    protected string $language = 'swift';
    protected string $languageClass = SwiftLanguage::class;

    protected function getLanguageIdentifier(): string
    {
        return 'swift';
    }

    protected function getFileExtensions(): array
    {
        return ['swift'];
    }
}
