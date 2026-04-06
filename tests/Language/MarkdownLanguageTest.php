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

use Alto\Code\Highlight\Language\MarkdownLanguage;
use Alto\Code\Highlight\Tests\LanguageTestCase;
use PHPUnit\Framework\Attributes\CoversClass;

/**
 * Test cases for Markdown language highlighting.
 *
 * Fixtures are auto-discovered from tests/Language/markdown/ directory.
 * Naming convention:
 *   - Code file: *.md
 *   - Expected HTML: *.md.html
 */
#[CoversClass(MarkdownLanguage::class)]
class MarkdownLanguageTest extends LanguageTestCase
{
    protected string $language = 'markdown';
    protected string $languageClass = MarkdownLanguage::class;

    protected function getLanguageIdentifier(): string
    {
        return 'markdown';
    }

    protected function getFileExtensions(): array
    {
        return ['md'];
    }
}
