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

use Alto\Code\Highlight\Theme\AltoTheme;
use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(AltoTheme::class)]
final class AltoThemeTest extends ThemeTestCase
{
    public static function themeExpectations(): iterable
    {
        yield 'dark mode' => [new AltoTheme(), 'Alto Dark', true, self::expectedCssClasses()];
        yield 'light mode' => [new AltoTheme(false), 'Alto Light', false, self::expectedCssClasses()];
    }

    /**
     * @return array<string, string>
     */
    private static function expectedCssClasses(): array
    {
        return self::prefixedExpectedClasses('alto-', [
            'comment-doc' => 'comment',
            'comment-task' => 'comment',
            'keyword-declaration' => 'keyword',
            'keyword-operator' => 'keyword',
            'keyword-control' => 'keyword',
            'string-interpolated' => 'string',
            'string-template-expression' => 'string',
            'boolean' => 'number',
            'null' => 'number',
            'regexp' => 'number',
            'variable-parameter' => 'variable',
            'variable-property' => 'variable',
            'variable-this' => 'variable-special',
            'namespace' => 'type',
            'type-definition' => 'type',
            'type-reference' => 'type',
            'builtin-type' => 'type',
            'function-definition' => 'function',
            'function-call' => 'function',
            'function-builtin' => 'function',
            'constant-builtin' => 'constant',
            'enum-case' => 'constant',
            'attribute' => 'variable',
            'attribute-value' => 'string',
            'tag-name' => 'keyword',
            'tag-attribute-name' => 'variable',
            'tag-attribute-value' => 'string',
            'markup-text' => 'punctuation',
            'section-name' => 'type',
            'diff-added' => 'string',
            'diff-removed' => 'constant',
            'diff-changed' => 'number',
            'meta' => 'constant',
            'diagnostic-error' => 'constant',
            'diagnostic-warning' => 'constant',
            'diagnostic-info' => 'constant',
            'support-type' => 'type',
            'support-function' => 'function',
            'support-constant' => 'constant',
            'whitespace' => 'punctuation',
        ]);
    }
}
