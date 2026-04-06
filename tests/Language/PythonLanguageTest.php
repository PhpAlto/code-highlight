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

use Alto\Code\Highlight\Language\Python\PythonLexer;
use Alto\Code\Highlight\Language\Python\PythonSemanticParser;
use Alto\Code\Highlight\Language\Python\PythonState;
use Alto\Code\Highlight\Language\Python\PythonToken;
use Alto\Code\Highlight\Language\Python\PythonTokenType;
use Alto\Code\Highlight\Language\PythonLanguage;
use Alto\Code\Highlight\Tests\LanguageTestCase;
use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(PythonLanguage::class)]
#[CoversClass(PythonLexer::class)]
#[CoversClass(PythonSemanticParser::class)]
#[CoversClass(PythonState::class)]
#[CoversClass(PythonToken::class)]
#[CoversClass(PythonTokenType::class)]
class PythonLanguageTest extends LanguageTestCase
{
    protected string $language = 'python';
    protected string $languageClass = PythonLanguage::class;

    protected function getLanguageIdentifier(): string
    {
        return 'python';
    }

    protected function getFileExtensions(): array
    {
        return ['py'];
    }
}
