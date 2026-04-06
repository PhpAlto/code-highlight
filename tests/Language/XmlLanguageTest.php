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

use Alto\Code\Highlight\Language\XmlLanguage;
use Alto\Code\Highlight\Tests\LanguageTestCase;
use PHPUnit\Framework\Attributes\CoversClass;

/**
 * Test cases for XML language highlighting.
 *
 * Fixtures are auto-discovered from tests/Language/xml/ directory.
 * Naming convention:
 *   - Code file: *.xml
 *   - Expected HTML: *.xml.html
 */
#[CoversClass(XmlLanguage::class)]
class XmlLanguageTest extends LanguageTestCase
{
    protected string $language = 'xml';
    protected string $languageClass = XmlLanguage::class;

    protected function getLanguageIdentifier(): string
    {
        return 'xml';
    }

    protected function getFileExtensions(): array
    {
        return ['xml'];
    }
}
