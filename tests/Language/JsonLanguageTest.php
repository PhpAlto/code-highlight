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

use Alto\Code\Highlight\Language\JsonLanguage;
use Alto\Code\Highlight\Tests\LanguageTestCase;
use PHPUnit\Framework\Attributes\CoversClass;

/**
 * Test cases for JSON language highlighting.
 *
 * Fixtures are auto-discovered from tests/Language/json/ directory.
 * Naming convention:
 *   - Code file: *.json
 *   - Expected HTML: *.json.html
 *   - Example: simple.json → simple.json.html
 */
#[CoversClass(JsonLanguage::class)]
class JsonLanguageTest extends LanguageTestCase
{
    protected string $language = 'json';
    protected string $languageClass = JsonLanguage::class;

    protected function getLanguageIdentifier(): string
    {
        return 'json';
    }

    protected function getFileExtensions(): array
    {
        return ['json'];
    }
}
