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

use Alto\Code\Highlight\Language\CssLanguage;
use Alto\Code\Highlight\Tests\LanguageTestCase;
use PHPUnit\Framework\Attributes\CoversClass;

/**
 * Test cases for CSS language highlighting.
 *
 * Fixtures are auto-discovered from tests/Language/css/ directory.
 * Naming convention:
 *   - Code file: *.css
 *   - Expected HTML: *.css.html
 */
#[CoversClass(CssLanguage::class)]
class CssLanguageTest extends LanguageTestCase
{
    protected string $language = 'css';
    protected string $languageClass = CssLanguage::class;

    protected function getLanguageIdentifier(): string
    {
        return 'css';
    }

    protected function getFileExtensions(): array
    {
        return ['css'];
    }
}
