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

namespace Alto\Code\Highlight\Tests\Unit\Theme;

use Alto\Code\Highlight\Theme\SolarTheme;
use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(SolarTheme::class)]
final class SolarThemeTest extends ThemeTestCase
{
    public static function themeExpectations(): iterable
    {
        yield 'light mode' => [new SolarTheme(), 'Solar Light', false, self::expectedCssClasses('solar-light-')];
        yield 'dark mode' => [new SolarTheme(dark: true), 'Solar Dark', true, self::expectedCssClasses('solar-dark-')];
    }

    /**
     * @return array<string, string>
     */
    private static function expectedCssClasses(string $prefix): array
    {
        return self::prefixedExpectedClasses($prefix);
    }
}
