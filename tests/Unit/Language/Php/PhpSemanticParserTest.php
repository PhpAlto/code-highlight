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

namespace Alto\Code\Highlight\Tests\Unit\Language\Php;

use Alto\Code\Highlight\Language\Php\PhpLexer;
use Alto\Code\Highlight\Language\Php\PhpSemanticParser;
use Alto\Code\Highlight\Parser\ParsedStream;
use Alto\Code\Highlight\Parser\ParsedToken;
use Alto\Code\Highlight\Scope;
use Alto\Code\Highlight\Tests\TestCase;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;

#[CoversClass(PhpSemanticParser::class)]
final class PhpSemanticParserTest extends TestCase
{
    private PhpLexer $lexer;

    private PhpSemanticParser $parser;

    protected function setUp(): void
    {
        parent::setUp();

        $this->lexer = new PhpLexer();
        $this->parser = new PhpSemanticParser();
    }

    public function testAssignsScopesToTokens(): void
    {
        $stream = $this->parse('<?php echo "hello";');

        self::assertGreaterThan(0, $stream->count());
        self::assertFalse($stream->isEmpty());
    }

    #[DataProvider('scopeProvider')]
    public function testDetectsExpectedScope(string $code, Scope $scope, ?callable $filter = null): void
    {
        $stream = $this->parse($code);

        self::assertTrue(
            $this->streamContainsScope($stream, $scope, $filter),
            sprintf('Failed asserting that scope %s exists for code: %s', $scope->value, $code),
        );
    }

    /**
     * @return array<string, array{string, Scope, (callable|null)}>
     */
    public static function scopeProvider(): array
    {
        return [
            'whitespace' => ['<?php  ', Scope::Whitespace, null],
            'comment' => ['<?php // comment', Scope::Comment, null],
            'docblock' => ['<?php /** docblock */', Scope::CommentDocblock, null],
            'string' => ['<?php "string";', Scope::String, null],
            'number' => ['<?php 42;', Scope::Number, null],
            'variable' => ['<?php $var;', Scope::Variable, null],
            'this variable' => ['<?php $this;', Scope::VariableThis, null],
            'function parameter' => ['<?php function test($param) {}', Scope::VariableParameter, null],
            'class definition' => ['<?php class MyClass {}', Scope::TypeDefinition, static fn(ParsedToken $token) => 'MyClass' === $token->getText()],
            'function definition' => ['<?php function myFunc() {}', Scope::FunctionDefinition, static fn(ParsedToken $token) => 'myFunc' === $token->getText()],
            'trait definition' => ['<?php trait MyTrait {}', Scope::TypeDefinition, null],
            'interface definition' => ['<?php interface MyInterface {}', Scope::TypeDefinition, null],
            'enum definition' => ['<?php enum Status {}', Scope::TypeDefinition, null],
            'method definition in class' => ['<?php class A { public function foo() {} }', Scope::FunctionDefinition, static fn(ParsedToken $token) => 'foo' === $token->getText()],
            'if control keyword' => ['<?php if (true) {}', Scope::KeywordControl, static fn(ParsedToken $token) => 'if' === $token->getText()],
            'while control keyword' => ['<?php while (true) {}', Scope::KeywordControl, static fn(ParsedToken $token) => 'while' === $token->getText()],
            'for control keyword' => ['<?php for (;;) {}', Scope::KeywordControl, static fn(ParsedToken $token) => 'for' === $token->getText()],
            'foreach control keyword' => ['<?php foreach ([1] as $v) {}', Scope::KeywordControl, static fn(ParsedToken $token) => 'foreach' === $token->getText()],
            'return keyword' => ['<?php function f() { return 1; }', Scope::KeywordControl, static fn(ParsedToken $token) => 'return' === $token->getText()],
            'new operator keyword' => ['<?php new MyClass();', Scope::KeywordOperator, static fn(ParsedToken $token) => 'new' === $token->getText()],
            'function call' => ['<?php strlen("test");', Scope::FunctionCall, static fn(ParsedToken $token) => 'strlen' === $token->getText()],
            'user function call' => ['<?php myFunc();', Scope::FunctionCall, static fn(ParsedToken $token) => 'myFunc' === $token->getText()],
            'magic constant __CLASS__' => ['<?php __CLASS__;', Scope::BuiltInConstant, null],
            'magic constant __DIR__' => ['<?php __DIR__;', Scope::BuiltInConstant, null],
            'instanceof operator' => ['<?php $x instanceof MyClass;', Scope::KeywordOperator, static fn(ParsedToken $token) => 'instanceof' === $token->getText()],
            'null constant' => ['<?php null;', Scope::Null, null],
            'true constant' => ['<?php true;', Scope::Boolean, null],
            'false constant' => ['<?php false;', Scope::Boolean, null],
        ];
    }

    private function parse(string $code): ParsedStream
    {
        $tokens = $this->lexer->tokenize($code);

        return $this->parser->parse($tokens);
    }
}
