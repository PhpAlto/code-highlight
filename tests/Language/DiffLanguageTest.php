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

use Alto\Code\Highlight\Language\DiffLanguage;
use Alto\Code\Highlight\Tests\LanguageTestCase;
use PHPUnit\Framework\Attributes\CoversClass;

/**
 * Test cases for Diff language highlighting.
 *
 * Fixtures are auto-discovered from tests/Language/diff/ directory.
 * Naming convention:
 *   - Code file: *.diff
 *   - Expected HTML: *.diff.html
 *   - Example: basic.diff → basic.diff.html
 */
#[CoversClass(DiffLanguage::class)]
final class DiffLanguageTest extends LanguageTestCase
{
    protected string $language = 'diff';
    protected string $languageClass = DiffLanguage::class;

    protected function getLanguageIdentifier(): string
    {
        return 'diff';
    }

    protected function getFileExtensions(): array
    {
        return ['diff'];
    }
}
