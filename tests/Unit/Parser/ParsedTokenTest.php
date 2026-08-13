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

use Alto\Code\Highlight\Parser\ParsedToken;
use Alto\Code\Highlight\Scope;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(ParsedToken::class)]
final class ParsedTokenTest extends TestCase
{
    public function testCreatesTokenWithTextAndScope(): void
    {
        $token = new ParsedToken('hello', Scope::String);

        $this->assertEquals('hello', $token->getText());
        $this->assertEquals(Scope::String, $token->getScope());
    }

    public function testStoresDifferentScopes(): void
    {
        $scopes = [
            Scope::Keyword,
            Scope::Variable,
            Scope::FunctionCall,
            Scope::Comment,
            Scope::String,
        ];

        foreach ($scopes as $scope) {
            $token = new ParsedToken('text', $scope);
            $this->assertEquals($scope, $token->getScope());
        }
    }

    public function testHandlesEmptyText(): void
    {
        $token = new ParsedToken('', Scope::Whitespace);

        $this->assertEquals('', $token->getText());
        $this->assertEquals(Scope::Whitespace, $token->getScope());
    }

    public function testHandlesUnicodeText(): void
    {
        $token = new ParsedToken('λ функция', Scope::Variable);

        $this->assertEquals('λ функция', $token->getText());
    }

    public function testHandlesSpecialCharacters(): void
    {
        $token = new ParsedToken('<?php', Scope::TagName);

        $this->assertEquals('<?php', $token->getText());
    }

    public function testHandlesMultilineText(): void
    {
        $text = "line1\nline2\nline3";
        $token = new ParsedToken($text, Scope::Comment);

        $this->assertEquals($text, $token->getText());
    }

    public function testDefaultsLineNumberToOne(): void
    {
        $token = new ParsedToken('text', Scope::Keyword);

        $this->assertEquals(1, $token->getLine());
    }

    public function testStoresCustomLineNumber(): void
    {
        $token = new ParsedToken('text', Scope::Keyword, line: 42);

        $this->assertEquals(42, $token->getLine());
    }

    public function testPreservesLineNumberWithDifferentScopes(): void
    {
        $scopes = [Scope::Keyword, Scope::Variable, Scope::String, Scope::Comment];

        foreach ($scopes as $index => $scope) {
            $line = $index + 1;
            $token = new ParsedToken('text', $scope, line: $line);
            $this->assertEquals($line, $token->getLine());
        }
    }

    public function testStoresTokenType(): void
    {
        $token = new ParsedToken('function', Scope::Keyword, \Alto\Code\Highlight\Parser\TokenType::Keyword);

        $this->assertEquals(\Alto\Code\Highlight\Parser\TokenType::Keyword, $token->getType());
    }

    public function testDefaultsTokenTypeToUnknown(): void
    {
        $token = new ParsedToken('text', Scope::String);

        $this->assertEquals(\Alto\Code\Highlight\Parser\TokenType::Unknown, $token->getType());
    }

    public function testStoresOffset(): void
    {
        $token = new ParsedToken('text', Scope::String, offset: 42);

        $this->assertEquals(42, $token->getOffset());
    }

    public function testDefaultsOffsetToZero(): void
    {
        $token = new ParsedToken('text', Scope::String);

        $this->assertEquals(0, $token->getOffset());
    }

    public function testStoresColumn(): void
    {
        $token = new ParsedToken('text', Scope::String, column: 10);

        $this->assertEquals(10, $token->getColumn());
    }

    public function testDefaultsColumnToZero(): void
    {
        $token = new ParsedToken('text', Scope::String);

        $this->assertEquals(0, $token->getColumn());
    }

    public function testIsCommentReturnsTrueForCommentScope(): void
    {
        $token = new ParsedToken('// comment', Scope::Comment);

        $this->assertTrue($token->isComment());
    }

    public function testIsCommentReturnsTrueForDocblockScope(): void
    {
        $token = new ParsedToken('/** docblock */', Scope::CommentDocblock);

        $this->assertTrue($token->isComment());
    }

    public function testIsCommentReturnsTrueForTaskScope(): void
    {
        $token = new ParsedToken('// TODO', Scope::CommentTask);

        $this->assertTrue($token->isComment());
    }

    public function testIsCommentReturnsTrueForCommentTokenType(): void
    {
        $token = new ParsedToken('// comment', Scope::String, \Alto\Code\Highlight\Parser\TokenType::Comment);

        $this->assertTrue($token->isComment());
    }

    public function testIsCommentReturnsFalseForNonComment(): void
    {
        $token = new ParsedToken('text', Scope::String);

        $this->assertFalse($token->isComment());
    }

    public function testIsWhitespaceReturnsTrueForWhitespaceScope(): void
    {
        $token = new ParsedToken(' ', Scope::Whitespace);

        $this->assertTrue($token->isWhitespace());
    }

    public function testIsWhitespaceReturnsTrueForWhitespaceTokenType(): void
    {
        $token = new ParsedToken(' ', Scope::String, \Alto\Code\Highlight\Parser\TokenType::Whitespace);

        $this->assertTrue($token->isWhitespace());
    }

    public function testIsWhitespaceReturnsFalseForNonWhitespace(): void
    {
        $token = new ParsedToken('text', Scope::String);

        $this->assertFalse($token->isWhitespace());
    }

    public function testIsStringReturnsTrueForStringScope(): void
    {
        $token = new ParsedToken('"hello"', Scope::String);

        $this->assertTrue($token->isString());
    }

    public function testIsStringReturnsTrueForInterpolatedStringScope(): void
    {
        $token = new ParsedToken('`hello ${name}`', Scope::StringInterpolated);

        $this->assertTrue($token->isString());
    }

    public function testIsStringReturnsTrueForTemplateExpressionScope(): void
    {
        $token = new ParsedToken('${name}', Scope::StringTemplateExpression);

        $this->assertTrue($token->isString());
    }

    public function testIsStringReturnsTrueForStringTokenType(): void
    {
        $token = new ParsedToken('"hello"', Scope::Keyword, \Alto\Code\Highlight\Parser\TokenType::String);

        $this->assertTrue($token->isString());
    }

    public function testIsStringReturnsFalseForNonString(): void
    {
        $token = new ParsedToken('text', Scope::Keyword);

        $this->assertFalse($token->isString());
    }

    public function testIsRemovableDelegatesToTokenType(): void
    {
        $removableToken = new ParsedToken(' ', Scope::Whitespace, \Alto\Code\Highlight\Parser\TokenType::Whitespace);
        $nonRemovableToken = new ParsedToken('text', Scope::Keyword, \Alto\Code\Highlight\Parser\TokenType::Keyword);

        $this->assertTrue($removableToken->isRemovable());
        $this->assertFalse($nonRemovableToken->isRemovable());
    }

    public function testIsDefinitionDelegatesToTokenType(): void
    {
        $definitionToken = new ParsedToken('myFunc', Scope::FunctionDefinition, \Alto\Code\Highlight\Parser\TokenType::FunctionName);
        $nonDefinitionToken = new ParsedToken('text', Scope::Keyword, \Alto\Code\Highlight\Parser\TokenType::Keyword);

        $this->assertTrue($definitionToken->isDefinition());
        $this->assertFalse($nonDefinitionToken->isDefinition());
    }

    public function testWithTypeCreatesNewTokenWithDifferentType(): void
    {
        $original = new ParsedToken('text', Scope::String, \Alto\Code\Highlight\Parser\TokenType::String, 10, 5, 2);
        $modified = $original->withType(\Alto\Code\Highlight\Parser\TokenType::Keyword);

        $this->assertEquals(\Alto\Code\Highlight\Parser\TokenType::Keyword, $modified->getType());
        $this->assertEquals('text', $modified->getText());
        $this->assertEquals(Scope::String, $modified->getScope());
        $this->assertEquals(10, $modified->getOffset());
        $this->assertEquals(5, $modified->getLine());
        $this->assertEquals(2, $modified->getColumn());
        $this->assertNotSame($original, $modified);
    }

    public function testWithScopeCreatesNewTokenWithDifferentScope(): void
    {
        $original = new ParsedToken('text', Scope::String, \Alto\Code\Highlight\Parser\TokenType::String, 10, 5, 2);
        $modified = $original->withScope(Scope::Keyword);

        $this->assertEquals(Scope::Keyword, $modified->getScope());
        $this->assertEquals('text', $modified->getText());
        $this->assertEquals(\Alto\Code\Highlight\Parser\TokenType::String, $modified->getType());
        $this->assertEquals(10, $modified->getOffset());
        $this->assertEquals(5, $modified->getLine());
        $this->assertEquals(2, $modified->getColumn());
        $this->assertNotSame($original, $modified);
    }
}
