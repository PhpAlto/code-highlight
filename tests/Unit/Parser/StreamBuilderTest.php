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

use Alto\Code\Highlight\Parser\ParsedStream;
use Alto\Code\Highlight\Parser\StreamBuilder;
use Alto\Code\Highlight\Scope;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(StreamBuilder::class)]
final class StreamBuilderTest extends TestCase
{
    public function testBuildsEmptyStream(): void
    {
        $builder = new StreamBuilder();
        $stream = $builder->build();

        $this->assertCount(0, $stream->getTokens());
    }

    public function testAddsSingleToken(): void
    {
        $builder = new StreamBuilder();
        $builder->add('hello', Scope::String);

        $stream = $builder->build();

        $this->assertCount(1, $stream->getTokens());
        $this->assertEquals('hello', $stream->getTokens()[0]->getText());
        $this->assertEquals(Scope::String, $stream->getTokens()[0]->getScope());
    }

    public function testAddsMultipleTokens(): void
    {
        $builder = new StreamBuilder();
        $builder->add('function', Scope::Keyword);
        $builder->add(' ', Scope::Whitespace);
        $builder->add('foo', Scope::FunctionDefinition);

        $stream = $builder->build();

        $this->assertCount(3, $stream->getTokens());
    }

    public function testAppendsStream(): void
    {
        $builder = new StreamBuilder();
        $builder->add('start', Scope::Keyword);

        $otherStream = new ParsedStream([
            new \Alto\Code\Highlight\Parser\ParsedToken('middle', Scope::Variable),
            new \Alto\Code\Highlight\Parser\ParsedToken('end', Scope::Operator),
        ]);

        $builder->appendStream($otherStream);

        $stream = $builder->build();

        $this->assertCount(3, $stream->getTokens());
        $this->assertEquals('start', $stream->getTokens()[0]->getText());
        $this->assertEquals('middle', $stream->getTokens()[1]->getText());
        $this->assertEquals('end', $stream->getTokens()[2]->getText());
    }

    public function testMaintainsTokenOrder(): void
    {
        $builder = new StreamBuilder();
        $builder->add('first', Scope::Keyword);
        $builder->add('second', Scope::Variable);
        $builder->add('third', Scope::Operator);

        $stream = $builder->build();
        $tokens = $stream->getTokens();

        $this->assertEquals('first', $tokens[0]->getText());
        $this->assertEquals('second', $tokens[1]->getText());
        $this->assertEquals('third', $tokens[2]->getText());
    }

    public function testHandlesEmptyStrings(): void
    {
        $builder = new StreamBuilder();
        $builder->add('', Scope::Whitespace);
        $builder->add('text', Scope::String);

        $stream = $builder->build();

        $this->assertCount(2, $stream->getTokens());
    }

    public function testBuildsComplexStream(): void
    {
        $builder = new StreamBuilder();
        $builder->add('<?php', Scope::TagName);
        $builder->add("\n", Scope::Whitespace);
        $builder->add('function', Scope::KeywordDeclaration);
        $builder->add(' ', Scope::Whitespace);
        $builder->add('test', Scope::FunctionDefinition);
        $builder->add('(', Scope::Punctuation);
        $builder->add(')', Scope::Punctuation);
        $builder->add(' ', Scope::Whitespace);
        $builder->add('{', Scope::Punctuation);
        $builder->add('}', Scope::Punctuation);

        $stream = $builder->build();

        $this->assertCount(10, $stream->getTokens());
    }

    public function testAddsParsedTokenDirectly(): void
    {
        $builder = new StreamBuilder();
        $token = new \Alto\Code\Highlight\Parser\ParsedToken('hello', Scope::String, line: 5);

        $builder->addToken($token);

        $stream = $builder->build();

        $this->assertCount(1, $stream->getTokens());
        $this->assertSame($token, $stream->getTokens()[0]);
        $this->assertEquals(5, $stream->getTokens()[0]->getLine());
    }

    public function testAddsMultipleTokensDirectly(): void
    {
        $builder = new StreamBuilder();
        $token1 = new \Alto\Code\Highlight\Parser\ParsedToken('first', Scope::Keyword);
        $token2 = new \Alto\Code\Highlight\Parser\ParsedToken('second', Scope::Variable, line: 2);

        $builder->addToken($token1);
        $builder->addToken($token2);

        $stream = $builder->build();

        $this->assertCount(2, $stream->getTokens());
        $this->assertEquals('first', $stream->getTokens()[0]->getText());
        $this->assertEquals('second', $stream->getTokens()[1]->getText());
    }

    public function testIsEmptyReturnsTrueForNewBuilder(): void
    {
        $builder = new StreamBuilder();

        $this->assertTrue($builder->isEmpty());
    }

    public function testIsEmptyReturnsFalseAfterAddingToken(): void
    {
        $builder = new StreamBuilder();
        $builder->add('text', Scope::String);

        $this->assertFalse($builder->isEmpty());
    }

    public function testIsEmptyReturnsFalseAfterAppendingStream(): void
    {
        $builder = new StreamBuilder();
        $otherStream = new ParsedStream([
            new \Alto\Code\Highlight\Parser\ParsedToken('token', Scope::String),
        ]);

        $builder->appendStream($otherStream);

        $this->assertFalse($builder->isEmpty());
    }

    public function testMixingAddAndAddTokenMethods(): void
    {
        $builder = new StreamBuilder();
        $builder->add('keyword', Scope::Keyword);
        $builder->addToken(new \Alto\Code\Highlight\Parser\ParsedToken('variable', Scope::Variable));
        $builder->add('operator', Scope::Operator);

        $stream = $builder->build();

        $this->assertCount(3, $stream->getTokens());
        $this->assertEquals('keyword', $stream->getTokens()[0]->getText());
        $this->assertEquals('variable', $stream->getTokens()[1]->getText());
        $this->assertEquals('operator', $stream->getTokens()[2]->getText());
    }

    public function testMixingAddAndAppendStream(): void
    {
        $builder = new StreamBuilder();
        $builder->add('start', Scope::Keyword);

        $otherStream = new ParsedStream([
            new \Alto\Code\Highlight\Parser\ParsedToken('middle', Scope::Variable),
        ]);

        $builder->appendStream($otherStream);
        $builder->add('end', Scope::Operator);

        $stream = $builder->build();

        $this->assertCount(3, $stream->getTokens());
        $this->assertEquals('start', $stream->getTokens()[0]->getText());
        $this->assertEquals('middle', $stream->getTokens()[1]->getText());
        $this->assertEquals('end', $stream->getTokens()[2]->getText());
    }
}
