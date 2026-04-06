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

namespace Alto\Code\Highlight\Tests\Unit\Parser;

use Alto\Code\Highlight\Parser\ParsedStream;
use Alto\Code\Highlight\Parser\ParsedToken;
use Alto\Code\Highlight\Scope;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(ParsedStream::class)]
final class ParsedStreamTest extends TestCase
{
    public function testCreatesEmptyStream(): void
    {
        $stream = new ParsedStream([]);

        $this->assertCount(0, $stream->getTokens());
    }

    public function testCreatesStreamWithTokens(): void
    {
        $tokens = [
            new ParsedToken('hello', Scope::String),
            new ParsedToken(' ', Scope::Whitespace),
            new ParsedToken('world', Scope::String),
        ];

        $stream = new ParsedStream($tokens);

        $this->assertCount(3, $stream->getTokens());
        $this->assertEquals($tokens, $stream->getTokens());
    }

    public function testIsIterable(): void
    {
        $tokens = [
            new ParsedToken('foo', Scope::Variable),
            new ParsedToken('bar', Scope::Variable),
        ];

        $stream = new ParsedStream($tokens);

        $result = [];
        foreach ($stream as $token) {
            $result[] = $token;
        }

        $this->assertEquals($tokens, $result);
    }

    public function testProvidesTokenCount(): void
    {
        $stream = new ParsedStream([
            new ParsedToken('a', Scope::String),
            new ParsedToken('b', Scope::String),
            new ParsedToken('c', Scope::String),
        ]);

        $this->assertCount(3, $stream);
    }

    public function testPreservesTokenOrder(): void
    {
        $stream = new ParsedStream([
            new ParsedToken('first', Scope::Keyword),
            new ParsedToken('second', Scope::Variable),
            new ParsedToken('third', Scope::Operator),
        ]);

        $tokens = $stream->getTokens();

        $this->assertEquals('first', $tokens[0]->getText());
        $this->assertEquals('second', $tokens[1]->getText());
        $this->assertEquals('third', $tokens[2]->getText());
    }

    public function testHandlesUnicodeTokens(): void
    {
        $stream = new ParsedStream([
            new ParsedToken('こんにちは', Scope::String),
            new ParsedToken('🚀', Scope::String),
        ]);

        $this->assertCount(2, $stream);
        $this->assertEquals('こんにちは', $stream->getTokens()[0]->getText());
        $this->assertEquals('🚀', $stream->getTokens()[1]->getText());
    }

    public function testIsEmptyReturnsTrueForEmptyStream(): void
    {
        $stream = new ParsedStream([]);

        $this->assertTrue($stream->isEmpty());
    }

    public function testIsEmptyReturnsFalseForNonEmptyStream(): void
    {
        $stream = new ParsedStream([
            new ParsedToken('token', Scope::String),
        ]);

        $this->assertFalse($stream->isEmpty());
    }

    public function testIsEmptyReturnsFalseForStreamWithMultipleTokens(): void
    {
        $stream = new ParsedStream([
            new ParsedToken('token1', Scope::String),
            new ParsedToken('token2', Scope::String),
            new ParsedToken('token3', Scope::String),
        ]);

        $this->assertFalse($stream->isEmpty());
    }

    public function testWithoutCommentsRemovesCommentTokens(): void
    {
        $stream = new ParsedStream([
            new ParsedToken('// comment', Scope::Comment),
            new ParsedToken('code', Scope::Keyword),
            new ParsedToken('/* block */', Scope::Comment),
        ]);

        $filtered = $stream->withoutComments();

        $this->assertCount(1, $filtered);
        $this->assertEquals('code', $filtered->getTokens()[0]->getText());
    }

    public function testWithoutWhitespaceRemovesWhitespaceTokens(): void
    {
        $stream = new ParsedStream([
            new ParsedToken(' ', Scope::Whitespace),
            new ParsedToken('code', Scope::Keyword),
            new ParsedToken("\n", Scope::Whitespace),
        ]);

        $filtered = $stream->withoutWhitespace();

        $this->assertCount(1, $filtered);
        $this->assertEquals('code', $filtered->getTokens()[0]->getText());
    }

    public function testWithoutRemovableRemovesCommentsAndWhitespace(): void
    {
        $stream = new ParsedStream([
            new ParsedToken(' ', Scope::Whitespace, \Alto\Code\Highlight\Parser\TokenType::Whitespace),
            new ParsedToken('code', Scope::Keyword, \Alto\Code\Highlight\Parser\TokenType::Keyword),
            new ParsedToken('// comment', Scope::Comment, \Alto\Code\Highlight\Parser\TokenType::Comment),
        ]);

        $filtered = $stream->withoutRemovable();

        $this->assertCount(1, $filtered);
        $this->assertEquals('code', $filtered->getTokens()[0]->getText());
    }

    public function testFilterKeepsOnlySpecifiedTypes(): void
    {
        $stream = new ParsedStream([
            new ParsedToken('func', Scope::Keyword, \Alto\Code\Highlight\Parser\TokenType::Keyword),
            new ParsedToken('"str"', Scope::String, \Alto\Code\Highlight\Parser\TokenType::String),
            new ParsedToken('var', Scope::Variable, \Alto\Code\Highlight\Parser\TokenType::Identifier),
        ]);

        $filtered = $stream->filter(\Alto\Code\Highlight\Parser\TokenType::Keyword, \Alto\Code\Highlight\Parser\TokenType::String);

        $this->assertCount(2, $filtered);
        $this->assertEquals('func', $filtered->getTokens()[0]->getText());
        $this->assertEquals('"str"', $filtered->getTokens()[1]->getText());
    }

    public function testExcludeRemovesSpecifiedTypes(): void
    {
        $stream = new ParsedStream([
            new ParsedToken('func', Scope::Keyword, \Alto\Code\Highlight\Parser\TokenType::Keyword),
            new ParsedToken('"str"', Scope::String, \Alto\Code\Highlight\Parser\TokenType::String),
            new ParsedToken('var', Scope::Variable, \Alto\Code\Highlight\Parser\TokenType::Identifier),
        ]);

        $filtered = $stream->exclude(\Alto\Code\Highlight\Parser\TokenType::String);

        $this->assertCount(2, $filtered);
        $this->assertEquals('func', $filtered->getTokens()[0]->getText());
        $this->assertEquals('var', $filtered->getTokens()[1]->getText());
    }

    public function testFilterByScopeKeepsOnlySpecifiedScopes(): void
    {
        $stream = new ParsedStream([
            new ParsedToken('func', Scope::Keyword),
            new ParsedToken('"str"', Scope::String),
            new ParsedToken('var', Scope::Variable),
        ]);

        $filtered = $stream->filterByScope(Scope::Keyword, Scope::String);

        $this->assertCount(2, $filtered);
        $this->assertEquals('func', $filtered->getTokens()[0]->getText());
        $this->assertEquals('"str"', $filtered->getTokens()[1]->getText());
    }

    public function testToStringReconstructsOriginalCode(): void
    {
        $stream = new ParsedStream([
            new ParsedToken('hello', Scope::String),
            new ParsedToken(' ', Scope::Whitespace),
            new ParsedToken('world', Scope::String),
        ]);

        $this->assertEquals('hello world', $stream->toString());
    }

    public function testTextsReturnsArrayOfTokenTexts(): void
    {
        $stream = new ParsedStream([
            new ParsedToken('one', Scope::String),
            new ParsedToken('two', Scope::String),
            new ParsedToken('three', Scope::String),
        ]);

        $this->assertEquals(['one', 'two', 'three'], $stream->texts());
    }

    public function testGetStringsReturnsOnlyStringTokens(): void
    {
        $stream = new ParsedStream([
            new ParsedToken('"hello"', Scope::String),
            new ParsedToken('var', Scope::Variable),
            new ParsedToken('`world`', Scope::StringInterpolated),
        ]);

        $strings = $stream->getStrings();

        $this->assertCount(2, $strings);
        $this->assertEquals('"hello"', $strings[0]);
        $this->assertEquals('`world`', $strings[1]);
    }

    public function testGetCommentsReturnsOnlyCommentTokens(): void
    {
        $stream = new ParsedStream([
            new ParsedToken('// comment1', Scope::Comment),
            new ParsedToken('code', Scope::Keyword),
            new ParsedToken('/* comment2 */', Scope::Comment),
        ]);

        $comments = $stream->getComments();

        $this->assertCount(2, $comments);
        $this->assertEquals('// comment1', $comments[0]);
        $this->assertEquals('/* comment2 */', $comments[1]);
    }

    public function testGetDefinitionsReturnsOnlyDefinitionTokens(): void
    {
        $stream = new ParsedStream([
            new ParsedToken('myFunc', Scope::FunctionDefinition, \Alto\Code\Highlight\Parser\TokenType::FunctionName),
            new ParsedToken('call', Scope::FunctionCall, \Alto\Code\Highlight\Parser\TokenType::Identifier),
            new ParsedToken('MyClass', Scope::TypeDefinition, \Alto\Code\Highlight\Parser\TokenType::ClassName),
        ]);

        $definitions = $stream->getDefinitions();

        $this->assertCount(2, $definitions);
        $this->assertEquals('myFunc', $definitions[0]->getText());
        $this->assertEquals('MyClass', $definitions[1]->getText());
    }

    public function testStatisticsReturnsCountsByType(): void
    {
        $stream = new ParsedStream([
            new ParsedToken('func', Scope::Keyword, \Alto\Code\Highlight\Parser\TokenType::Keyword),
            new ParsedToken('"str1"', Scope::String, \Alto\Code\Highlight\Parser\TokenType::String),
            new ParsedToken('"str2"', Scope::String, \Alto\Code\Highlight\Parser\TokenType::String),
            new ParsedToken('var', Scope::Variable, \Alto\Code\Highlight\Parser\TokenType::Identifier),
        ]);

        $stats = $stream->statistics();

        $this->assertEquals(1, $stats['keyword']);
        $this->assertEquals(2, $stats['string']);
        $this->assertEquals(1, $stats['identifier']);
    }

    public function testStatisticsByScopeReturnsCountsByScope(): void
    {
        $stream = new ParsedStream([
            new ParsedToken('func', Scope::Keyword),
            new ParsedToken('"str1"', Scope::String),
            new ParsedToken('"str2"', Scope::String),
            new ParsedToken('var', Scope::Variable),
        ]);

        $stats = $stream->statisticsByScope();

        $this->assertEquals(1, $stats[Scope::Keyword->value]);
        $this->assertEquals(2, $stats[Scope::String->value]);
        $this->assertEquals(1, $stats[Scope::Variable->value]);
    }

    public function testHasTypeReturnsTrueWhenTypeExists(): void
    {
        $stream = new ParsedStream([
            new ParsedToken('func', Scope::Keyword, \Alto\Code\Highlight\Parser\TokenType::Keyword),
            new ParsedToken('"str"', Scope::String, \Alto\Code\Highlight\Parser\TokenType::String),
        ]);

        $this->assertTrue($stream->hasType(\Alto\Code\Highlight\Parser\TokenType::Keyword));
        $this->assertTrue($stream->hasType(\Alto\Code\Highlight\Parser\TokenType::String));
    }

    public function testHasTypeReturnsFalseWhenTypeDoesNotExist(): void
    {
        $stream = new ParsedStream([
            new ParsedToken('func', Scope::Keyword, \Alto\Code\Highlight\Parser\TokenType::Keyword),
        ]);

        $this->assertFalse($stream->hasType(\Alto\Code\Highlight\Parser\TokenType::String));
    }

    public function testHasScopeReturnsTrueWhenScopeExists(): void
    {
        $stream = new ParsedStream([
            new ParsedToken('func', Scope::Keyword),
            new ParsedToken('"str"', Scope::String),
        ]);

        $this->assertTrue($stream->hasScope(Scope::Keyword));
        $this->assertTrue($stream->hasScope(Scope::String));
    }

    public function testHasScopeReturnsFalseWhenScopeDoesNotExist(): void
    {
        $stream = new ParsedStream([
            new ParsedToken('func', Scope::Keyword),
        ]);

        $this->assertFalse($stream->hasScope(Scope::String));
    }

    public function testFindReturnsFirstMatchingToken(): void
    {
        $stream = new ParsedStream([
            new ParsedToken('one', Scope::String),
            new ParsedToken('two', Scope::Keyword),
            new ParsedToken('three', Scope::String),
        ]);

        $found = $stream->find(fn ($t) => Scope::String === $t->getScope());

        $this->assertNotNull($found);
        $this->assertEquals('one', $found->getText());
    }

    public function testFindReturnsNullWhenNoMatch(): void
    {
        $stream = new ParsedStream([
            new ParsedToken('one', Scope::String),
        ]);

        $found = $stream->find(fn ($t) => Scope::Variable === $t->getScope());

        $this->assertNull($found);
    }

    public function testFindAllReturnsAllMatchingTokens(): void
    {
        $stream = new ParsedStream([
            new ParsedToken('one', Scope::String),
            new ParsedToken('two', Scope::Keyword),
            new ParsedToken('three', Scope::String),
        ]);

        $found = $stream->findAll(fn ($t) => Scope::String === $t->getScope());

        $this->assertCount(2, $found);
        $this->assertEquals('one', $found[0]->getText());
        $this->assertEquals('three', $found[1]->getText());
    }

    public function testFindAllReturnsEmptyArrayWhenNoMatch(): void
    {
        $stream = new ParsedStream([
            new ParsedToken('one', Scope::String),
        ]);

        $found = $stream->findAll(fn ($t) => Scope::Variable === $t->getScope());

        $this->assertEmpty($found);
    }

    public function testMapTransformsEachToken(): void
    {
        $stream = new ParsedStream([
            new ParsedToken('one', Scope::String),
            new ParsedToken('two', Scope::String),
        ]);

        $mapped = $stream->map(fn ($t) => $t->withScope(Scope::Keyword));

        $this->assertCount(2, $mapped);
        $this->assertEquals(Scope::Keyword, $mapped->getTokens()[0]->getScope());
        $this->assertEquals(Scope::Keyword, $mapped->getTokens()[1]->getScope());
    }

    public function testMergeCombinesTwoStreams(): void
    {
        $stream1 = new ParsedStream([
            new ParsedToken('one', Scope::String),
        ]);
        $stream2 = new ParsedStream([
            new ParsedToken('two', Scope::String),
        ]);

        $merged = $stream1->merge($stream2);

        $this->assertCount(2, $merged);
        $this->assertEquals('one', $merged->getTokens()[0]->getText());
        $this->assertEquals('two', $merged->getTokens()[1]->getText());
    }

    public function testSliceReturnsSubsetOfTokens(): void
    {
        $stream = new ParsedStream([
            new ParsedToken('one', Scope::String),
            new ParsedToken('two', Scope::String),
            new ParsedToken('three', Scope::String),
            new ParsedToken('four', Scope::String),
        ]);

        $sliced = $stream->slice(1, 2);

        $this->assertCount(2, $sliced);
        $this->assertEquals('two', $sliced->getTokens()[0]->getText());
        $this->assertEquals('three', $sliced->getTokens()[1]->getText());
    }

    public function testSliceWithoutLengthTakesRemainingTokens(): void
    {
        $stream = new ParsedStream([
            new ParsedToken('one', Scope::String),
            new ParsedToken('two', Scope::String),
            new ParsedToken('three', Scope::String),
        ]);

        $sliced = $stream->slice(1);

        $this->assertCount(2, $sliced);
        $this->assertEquals('two', $sliced->getTokens()[0]->getText());
        $this->assertEquals('three', $sliced->getTokens()[1]->getText());
    }

    public function testFirstReturnsFirstToken(): void
    {
        $stream = new ParsedStream([
            new ParsedToken('first', Scope::String),
            new ParsedToken('second', Scope::String),
        ]);

        $first = $stream->first();

        $this->assertNotNull($first);
        $this->assertEquals('first', $first->getText());
    }

    public function testFirstReturnsNullForEmptyStream(): void
    {
        $stream = new ParsedStream([]);

        $this->assertNull($stream->first());
    }

    public function testLastReturnsLastToken(): void
    {
        $stream = new ParsedStream([
            new ParsedToken('first', Scope::String),
            new ParsedToken('last', Scope::String),
        ]);

        $last = $stream->last();

        $this->assertNotNull($last);
        $this->assertEquals('last', $last->getText());
    }

    public function testLastReturnsNullForEmptyStream(): void
    {
        $stream = new ParsedStream([]);

        $this->assertNull($stream->last());
    }
}
