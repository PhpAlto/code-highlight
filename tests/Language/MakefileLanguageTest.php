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

use Alto\Code\Highlight\Language\MakefileLanguage;
use Alto\Code\Highlight\Tests\LanguageTestCase;
use PHPUnit\Framework\Attributes\CoversClass;

/**
 * Test cases for Makefile language highlighting.
 *
 * Fixtures are auto-discovered from tests/Language/makefile/ directory.
 * Naming convention:
 *   - Code file: *.mk
 *   - Expected HTML: *.mk.html
 */
#[CoversClass(MakefileLanguage::class)]
class MakefileLanguageTest extends LanguageTestCase
{
    protected string $language = 'makefile';
    protected string $languageClass = MakefileLanguage::class;

    protected function getLanguageIdentifier(): string
    {
        return 'makefile';
    }

    protected function getFileExtensions(): array
    {
        return ['mk'];
    }
}
