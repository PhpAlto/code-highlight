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

use Alto\Code\Highlight\Theme\CupertinoTheme;
use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(CupertinoTheme::class)]
final class CupertinoThemeTest extends ThemeTestCase
{
    public static function themeExpectations(): iterable
    {
        yield 'dark mode' => [new CupertinoTheme(), 'Cupertino Dark', true, self::expectedCssClasses('cupertino-dark-')];
        yield 'light mode' => [new CupertinoTheme(dark: false), 'Cupertino Light', false, self::expectedCssClasses('cupertino-light-')];
    }

    /**
     * @return array<string, string>
     */
    private static function expectedCssClasses(string $prefix): array
    {
        return self::prefixedExpectedClasses($prefix, [
            'diagnostic-warning' => 'number',
            'diagnostic-info' => 'variable',
            'whitespace' => '',
        ]);
    }
}
