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

namespace Alto\Code\Highlight\Tests\Unit\Parser;

use Alto\Code\Highlight\Parser\TokenType;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(TokenType::class)]
final class TokenTypeTest extends TestCase
{
    public function testIsCommentReturnsTrueForCommentTokens(): void
    {
        self::assertTrue(TokenType::Comment->isComment());
        self::assertTrue(TokenType::Docblock->isComment());
    }

    public function testIsCommentReturnsFalseForNonCommentTokens(): void
    {
        self::assertFalse(TokenType::Whitespace->isComment());
        self::assertFalse(TokenType::String->isComment());
        self::assertFalse(TokenType::Keyword->isComment());
        self::assertFalse(TokenType::Identifier->isComment());
    }

    public function testIsRemovableReturnsTrueForRemovableTokens(): void
    {
        self::assertTrue(TokenType::Comment->isRemovable());
        self::assertTrue(TokenType::Docblock->isRemovable());
        self::assertTrue(TokenType::Whitespace->isRemovable());
    }

    public function testIsRemovableReturnsFalseForNonRemovableTokens(): void
    {
        self::assertFalse(TokenType::String->isRemovable());
        self::assertFalse(TokenType::Number->isRemovable());
        self::assertFalse(TokenType::Keyword->isRemovable());
        self::assertFalse(TokenType::Identifier->isRemovable());
        self::assertFalse(TokenType::Operator->isRemovable());
        self::assertFalse(TokenType::Punctuation->isRemovable());
    }

    public function testIsDefinitionReturnsTrueForDefinitionTokens(): void
    {
        self::assertTrue(TokenType::FunctionName->isDefinition());
        self::assertTrue(TokenType::ClassName->isDefinition());
        self::assertTrue(TokenType::ConstantName->isDefinition());
    }

    public function testIsDefinitionReturnsFalseForNonDefinitionTokens(): void
    {
        self::assertFalse(TokenType::VariableName->isDefinition());
        self::assertFalse(TokenType::PropertyName->isDefinition());
        self::assertFalse(TokenType::ParameterName->isDefinition());
        self::assertFalse(TokenType::Comment->isDefinition());
        self::assertFalse(TokenType::String->isDefinition());
        self::assertFalse(TokenType::Keyword->isDefinition());
    }

    public function testAllTokenTypeEnumCasesExist(): void
    {
        $cases = TokenType::cases();
        $expectedCount = 18; // All enum cases defined in TokenType

        self::assertCount($expectedCount, $cases);
        self::assertContains(TokenType::Comment, $cases);
        self::assertContains(TokenType::Docblock, $cases);
        self::assertContains(TokenType::Whitespace, $cases);
        self::assertContains(TokenType::String, $cases);
        self::assertContains(TokenType::Number, $cases);
        self::assertContains(TokenType::RegExp, $cases);
        self::assertContains(TokenType::Keyword, $cases);
        self::assertContains(TokenType::Identifier, $cases);
        self::assertContains(TokenType::Operator, $cases);
        self::assertContains(TokenType::Punctuation, $cases);
        self::assertContains(TokenType::FunctionName, $cases);
        self::assertContains(TokenType::ClassName, $cases);
        self::assertContains(TokenType::VariableName, $cases);
        self::assertContains(TokenType::ConstantName, $cases);
        self::assertContains(TokenType::PropertyName, $cases);
        self::assertContains(TokenType::ParameterName, $cases);
        self::assertContains(TokenType::Embedded, $cases);
        self::assertContains(TokenType::Unknown, $cases);
    }

    public function testTokenTypeHasStringValues(): void
    {
        self::assertSame('comment', TokenType::Comment->value);
        self::assertSame('docblock', TokenType::Docblock->value);
        self::assertSame('whitespace', TokenType::Whitespace->value);
        self::assertSame('string', TokenType::String->value);
        self::assertSame('function_name', TokenType::FunctionName->value);
        self::assertSame('class_name', TokenType::ClassName->value);
    }
}
