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

use Alto\Code\Highlight\Language\ScssLanguage;
use Alto\Code\Highlight\Tests\LanguageTestCase;
use PHPUnit\Framework\Attributes\CoversClass;

/**
 * Test cases for SCSS language highlighting.
 *
 * Fixtures are auto-discovered from tests/Language/scss/ directory.
 * Naming convention:
 *   - Code file: *.scss
 *   - Expected HTML: *.scss.html
 */
#[CoversClass(ScssLanguage::class)]
class ScssLanguageTest extends LanguageTestCase
{
    protected string $language = 'scss';
    protected string $languageClass = ScssLanguage::class;

    protected function getLanguageIdentifier(): string
    {
        return 'scss';
    }

    protected function getFileExtensions(): array
    {
        return ['scss'];
    }
}
