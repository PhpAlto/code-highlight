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

use Alto\Code\Highlight\Language\IniLanguage;
use Alto\Code\Highlight\Tests\LanguageTestCase;
use PHPUnit\Framework\Attributes\CoversClass;

/**
 * Test cases for INI language highlighting.
 *
 * Fixtures are auto-discovered from tests/Language/ini/ directory.
 * Naming convention:
 *   - Code file: *.ini
 *   - Expected HTML: *.ini.html
 *   - Example: simple.ini -> simple.ini.html
 */
#[CoversClass(IniLanguage::class)]
class IniLanguageTest extends LanguageTestCase
{
    protected string $language = 'ini';
    protected string $languageClass = IniLanguage::class;

    protected function getLanguageIdentifier(): string
    {
        return 'ini';
    }

    protected function getFileExtensions(): array
    {
        return ['ini'];
    }
}
