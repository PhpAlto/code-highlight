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

namespace Alto\Code\Highlight\Tests\Unit;

use Alto\Code\Highlight\Scope;
use Alto\Code\Highlight\Tests\TestCase;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;

#[CoversClass(Scope::class)]
final class ScopeTest extends TestCase
{
    #[DataProvider('valueProvider')]
    public function testScopeValuesAreExpected(Scope $scope, string $expected): void
    {
        self::assertSame($expected, $scope->value);
    }

    #[DataProvider('cssClassProvider')]
    public function testToCssClassReturnsScopeValue(Scope $scope, string $expected): void
    {
        self::assertSame($expected, $scope->toCssClass());
    }

    public function testScopeEnumHasManyCases(): void
    {
        self::assertGreaterThan(30, count(Scope::cases()));
    }

    /**
     * @return array<string, array{Scope, string}>
     */
    public static function valueProvider(): array
    {
        return [
            'comment' => [Scope::Comment, 'comment'],
            'comment docblock' => [Scope::CommentDocblock, 'comment.docblock'],
            'comment task' => [Scope::CommentTask, 'comment.task'],
            'punctuation' => [Scope::Punctuation, 'punctuation'],
            'keyword' => [Scope::Keyword, 'keyword'],
            'keyword declaration' => [Scope::KeywordDeclaration, 'keyword.declaration'],
            'keyword operator' => [Scope::KeywordOperator, 'keyword.operator'],
            'keyword control' => [Scope::KeywordControl, 'keyword.control'],
            'storage modifier' => [Scope::StorageModifier, 'storage.modifier'],
            'tag name' => [Scope::TagName, 'tag.name'],
            'tag attribute name' => [Scope::TagAttributeName, 'tag.attribute.name'],
            'tag attribute value' => [Scope::TagAttributeValue, 'tag.attribute.value'],
            'markup text' => [Scope::MarkupText, 'markup.text'],
            'attribute value' => [Scope::AttributeValue, 'attribute.value'],
            'whitespace' => [Scope::Whitespace, 'whitespace'],
        ];
    }

    /**
     * @return array<string, array{Scope, string}>
     */
    public static function cssClassProvider(): array
    {
        return [
            'comment' => [Scope::Comment, 'comment'],
            'string' => [Scope::String, 'string'],
            'tag attribute value' => [Scope::TagAttributeValue, 'tag.attribute.value'],
            'function call' => [Scope::FunctionCall, 'function.call'],
        ];
    }
}
