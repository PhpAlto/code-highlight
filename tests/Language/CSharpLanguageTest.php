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

namespace Alto\Code\Highlight\Tests\Language;

use Alto\Code\Highlight\Language\CSharp\CSharpLexer;
use Alto\Code\Highlight\Language\CSharp\CSharpSemanticParser;
use Alto\Code\Highlight\Language\CSharp\CSharpState;
use Alto\Code\Highlight\Language\CSharp\CSharpToken;
use Alto\Code\Highlight\Language\CSharp\CSharpTokenType;
use Alto\Code\Highlight\Language\CSharpLanguage;
use Alto\Code\Highlight\Tests\LanguageTestCase;
use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(CSharpLanguage::class)]
#[CoversClass(CSharpLexer::class)]
#[CoversClass(CSharpSemanticParser::class)]
#[CoversClass(CSharpState::class)]
#[CoversClass(CSharpToken::class)]
#[CoversClass(CSharpTokenType::class)]
final class CSharpLanguageTest extends LanguageTestCase
{
    protected string $language = 'csharp';

    protected string $languageClass = CSharpLanguage::class;

    protected function getLanguageIdentifier(): string
    {
        return 'csharp';
    }

    protected function getFileExtensions(): array
    {
        return ['cs'];
    }
}
