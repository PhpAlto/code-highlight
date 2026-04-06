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

use Alto\Code\Highlight\Language\TwigLanguage;
use Alto\Code\Highlight\Tests\LanguageTestCase;
use PHPUnit\Framework\Attributes\CoversClass;

/**
 * Test cases for Twig language highlighting.
 *
 * Fixtures are auto-discovered from tests/Language/twig/ directory.
 * Includes embedded language testing (Twig+CSS, Twig+JS).
 * Naming convention:
 *   - Code file: *.twig
 *   - Expected HTML: *.twig.html
 *   - Embedded: twig+css.twig → twig+css.twig.html
 */
#[CoversClass(TwigLanguage::class)]
class TwigLanguageTest extends LanguageTestCase
{
    protected string $language = 'twig';
    protected string $languageClass = TwigLanguage::class;

    protected function getLanguageIdentifier(): string
    {
        return 'twig';
    }

    protected function getFileExtensions(): array
    {
        return ['twig'];
    }
}
