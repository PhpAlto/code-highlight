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

use Alto\Code\Highlight\Language\DotEnvLanguage;
use Alto\Code\Highlight\Tests\LanguageTestCase;
use PHPUnit\Framework\Attributes\CoversClass;

/**
 * Test cases for DotEnv language highlighting.
 *
 * Fixtures are auto-discovered from tests/Language/dotenv/ directory.
 * Naming convention:
 *   - Code file: *.env
 *   - Expected HTML: *.env.html
 */
#[CoversClass(DotEnvLanguage::class)]
final class DotEnvLanguageTest extends LanguageTestCase
{
    protected string $language = 'dotenv';
    protected string $languageClass = DotEnvLanguage::class;

    protected function getLanguageIdentifier(): string
    {
        return 'dotenv';
    }

    protected function getFileExtensions(): array
    {
        return ['env'];
    }
}
