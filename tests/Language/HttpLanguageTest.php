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

use Alto\Code\Highlight\Language\HttpLanguage;
use Alto\Code\Highlight\Tests\LanguageTestCase;
use PHPUnit\Framework\Attributes\CoversClass;

/**
 * Test cases for HTTP language highlighting.
 *
 * Fixtures are auto-discovered from tests/Language/http/ directory.
 * Naming convention:
 *   - Code file: *.http
 *   - Expected HTML: *.http.html
 *   - Example: request.http -> request.http.html
 */
#[CoversClass(HttpLanguage::class)]
class HttpLanguageTest extends LanguageTestCase
{
    protected string $language = 'http';
    protected string $languageClass = HttpLanguage::class;

    protected function getLanguageIdentifier(): string
    {
        return 'http';
    }

    protected function getFileExtensions(): array
    {
        return ['http'];
    }
}
