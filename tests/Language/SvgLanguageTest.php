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

use Alto\Code\Highlight\Language\SvgLanguage;
use Alto\Code\Highlight\Tests\LanguageTestCase;
use PHPUnit\Framework\Attributes\CoversClass;

/**
 * Test cases for SVG language highlighting.
 *
 * Fixtures are auto-discovered from tests/Language/svg/ directory.
 * SVG is XML-based, tested as part of HTML/XML support.
 * Naming convention:
 *   - Code file: *.svg
 *   - Expected HTML: *.svg.html
 */
#[CoversClass(SvgLanguage::class)]
class SvgLanguageTest extends LanguageTestCase
{
    protected string $language = 'svg';
    protected string $languageClass = SvgLanguage::class;

    protected function getLanguageIdentifier(): string
    {
        return 'svg';
    }

    protected function getFileExtensions(): array
    {
        return ['svg'];
    }
}
