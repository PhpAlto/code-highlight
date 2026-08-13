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

namespace Alto\Code\Highlight\Tests\Unit\Theme;

use Alto\Code\Highlight\Theme\NoctisTheme;
use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(NoctisTheme::class)]
final class NoctisThemeTest extends ThemeTestCase
{
    public static function themeExpectations(): iterable
    {
        yield 'dark mode' => [new NoctisTheme(), 'Noctis Dark', true, self::expectedCssClasses('noctis-dark-')];
        yield 'light mode' => [new NoctisTheme(dark: false), 'Noctis Light', false, self::expectedCssClasses('noctis-light-')];
    }

    /**
     * @return array<string, string>
     */
    private static function expectedCssClasses(string $prefix): array
    {
        return self::prefixedExpectedClasses($prefix);
    }
}
