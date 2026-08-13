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

use Alto\Code\Highlight\Language\HtmlLanguage;
use Alto\Code\Highlight\Tests\LanguageTestCase;
use PHPUnit\Framework\Attributes\CoversClass;

/**
 * Test cases for HTML language highlighting.
 *
 * Fixtures are auto-discovered from tests/Language/html/ directory.
 * Includes embedded language testing (HTML+CSS, HTML+JS, HTML+SVG).
 * Naming convention:
 *   - Code file: *.html
 *   - Expected HTML: *.html.html
 *   - Embedded: html+css.html → html+css.html.html
 */
#[CoversClass(HtmlLanguage::class)]
class HtmlLanguageTest extends LanguageTestCase
{
    protected string $language = 'html';
    protected string $languageClass = HtmlLanguage::class;

    protected function getLanguageIdentifier(): string
    {
        return 'html';
    }

    protected function getFileExtensions(): array
    {
        return ['html'];
    }
}
