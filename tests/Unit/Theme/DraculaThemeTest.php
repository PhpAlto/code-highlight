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

use Alto\Code\Highlight\Theme\DraculaTheme;
use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(DraculaTheme::class)]
final class DraculaThemeTest extends ThemeTestCase
{
    public static function themeExpectations(): iterable
    {
        yield 'dracula' => [new DraculaTheme(), 'Dracula', true, self::expectedCssClasses()];
    }

    /**
     * @return array<string, string>
     */
    private static function expectedCssClasses(): array
    {
        return self::prefixedExpectedClasses('dracula-');
    }
}
