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

use Alto\Code\Highlight\Theme\GitHubTheme;
use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(GitHubTheme::class)]
final class GitHubThemeTest extends ThemeTestCase
{
    public static function themeExpectations(): iterable
    {
        yield 'dark mode' => [new GitHubTheme(), 'GitHub Dark', true, self::expectedCssClasses('github-dark-')];
        yield 'light mode' => [new GitHubTheme(dark: false), 'GitHub Light', false, self::expectedCssClasses('github-light-')];
    }

    /**
     * @return array<string, string>
     */
    private static function expectedCssClasses(string $prefix): array
    {
        return self::prefixedExpectedClasses($prefix, [
            'comment-doc' => 'comment',
            'comment-task' => 'comment',
            'keyword-declaration' => 'keyword',
            'keyword-operator' => 'keyword',
            'keyword-control' => 'keyword',
            'storage-modifier' => 'keyword',
            'string-interpolated' => 'string',
            'string-template-expression' => 'string',
            'boolean' => 'constant',
            'null' => 'constant',
            'regexp' => 'string',
            'constant-builtin' => 'constant',
            'enum-case' => 'constant',
            'variable-parameter' => 'variable',
            'variable-property' => 'variable',
            'variable-this' => 'keyword',
            'namespace' => 'type',
            'type-definition' => 'type',
            'type-reference' => 'type',
            'builtin-type' => 'keyword',
            'function-definition' => 'function',
            'function-call' => 'function',
            'function-builtin' => 'function',
            'attribute' => 'attribute',
            'attribute-value' => 'string',
            'tag-name' => 'tag',
            'tag-attribute-name' => 'attribute',
            'tag-attribute-value' => 'string',
            'markup-text' => 'punctuation',
            'section-name' => 'type',
            'diff-added' => 'diff-added',
            'diff-removed' => 'diff-removed',
            'diff-changed' => 'diff-changed',
            'diagnostic-error' => 'diff-removed',
            'diagnostic-warning' => 'number',
            'diagnostic-info' => 'constant',
            'support-type' => 'type',
            'support-function' => 'function',
            'support-constant' => 'constant',
            'meta' => 'constant',
            'whitespace' => '',
        ]);
    }
}
