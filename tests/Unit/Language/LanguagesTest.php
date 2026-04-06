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

namespace Alto\Code\Highlight\Tests\Unit\Language;

use Alto\Code\Highlight\Language\LanguageInterface;
use Alto\Code\Highlight\Language\Languages;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(Languages::class)]
final class LanguagesTest extends TestCase
{
    public function testGetDefaultLanguagesReturnsArray(): void
    {
        $languages = Languages::getDefaultLanguages();

        $this->assertIsArray($languages);
        $this->assertNotEmpty($languages);
    }

    public function testGetDefaultLanguagesReturnsLanguageInstances(): void
    {
        $languages = Languages::getDefaultLanguages();

        foreach ($languages as $language) {
            $this->assertInstanceOf(LanguageInterface::class, $language);
        }
    }

    public function testGetDefaultLanguagesContainsExpectedLanguages(): void
    {
        $languages = Languages::getDefaultLanguages();
        $identifiers = array_map(fn ($l) => $l->getIdentifier(), $languages);

        $this->assertContains('php', $identifiers);
        $this->assertContains('html', $identifiers);
        $this->assertContains('javascript', $identifiers);
        $this->assertContains('css', $identifiers);
        $this->assertContains('json', $identifiers);
        $this->assertContains('yaml', $identifiers);
        $this->assertContains('sql', $identifiers);
        $this->assertContains('markdown', $identifiers);
        $this->assertContains('bash', $identifiers);
        $this->assertContains('typescript', $identifiers);
    }

    public function testGetDefaultLanguagesReturnsAtLeast15Languages(): void
    {
        $languages = Languages::getDefaultLanguages();

        $this->assertGreaterThanOrEqual(15, count($languages));
    }
}
