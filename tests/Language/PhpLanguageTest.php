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

use Alto\Code\Highlight\Language\Php\PhpLexer;
use Alto\Code\Highlight\Language\Php\PhpSemanticParser;
use Alto\Code\Highlight\Language\Php\PhpState;
use Alto\Code\Highlight\Language\PhpLanguage;
use Alto\Code\Highlight\Tests\LanguageTestCase;
use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(PhpLanguage::class)]
#[CoversClass(PhpLexer::class)]
#[CoversClass(PhpSemanticParser::class)]
#[CoversClass(PhpState::class)]
final class PhpLanguageTest extends LanguageTestCase
{
    protected string $language = 'php';
    protected string $languageClass = PhpLanguage::class;

    protected function getLanguageIdentifier(): string
    {
        return 'php';
    }

    protected function getFileExtensions(): array
    {
        return ['php'];
    }
}
