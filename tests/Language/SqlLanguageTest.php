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

use Alto\Code\Highlight\Language\SqlLanguage;
use Alto\Code\Highlight\Tests\LanguageTestCase;
use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(SqlLanguage::class)]
class SqlLanguageTest extends LanguageTestCase
{
    protected string $language = 'sql';
    protected string $languageClass = SqlLanguage::class;

    protected function getLanguageIdentifier(): string
    {
        return 'sql';
    }

    protected function getFileExtensions(): array
    {
        return ['sql'];
    }
}
