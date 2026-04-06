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

namespace Alto\Code\Highlight\Tests\Unit\Language\JavaScript;

use Alto\Code\Highlight\Language\JavaScript\JavaScriptLexer;
use Alto\Code\Highlight\Language\JavaScript\JavaScriptSemanticParser;
use Alto\Code\Highlight\Parser\ParsedStream;
use Alto\Code\Highlight\Parser\ParsedToken;
use Alto\Code\Highlight\Scope;
use Alto\Code\Highlight\Tests\TestCase;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;

#[CoversClass(JavaScriptSemanticParser::class)]
final class JavaScriptSemanticParserTest extends TestCase
{
    private JavaScriptLexer $lexer;

    private JavaScriptSemanticParser $parser;

    protected function setUp(): void
    {
        parent::setUp();

        $this->lexer = new JavaScriptLexer();
        $this->parser = new JavaScriptSemanticParser();
    }

    public function testAssignsScopesToTokens(): void
    {
        $stream = $this->parse('const x = 42;');

        self::assertGreaterThan(0, $stream->count());
        self::assertFalse($stream->isEmpty());
    }

    public function testFunctionKeywordIsDeclaration(): void
    {
        $stream = $this->parse('function test() {}');

        self::assertTrue($this->streamContainsScope($stream, Scope::KeywordDeclaration, static fn (ParsedToken $token) => 'function' === $token->getText()));
    }

    public function testConstLetVarAreDeclarations(): void
    {
        $stream = $this->parse('const x = 1; let y = 2; var z = 3;');

        self::assertTrue($this->streamContainsScope($stream, Scope::KeywordDeclaration, static fn (ParsedToken $token) => 'const' === $token->getText()));
        self::assertTrue($this->streamContainsScope($stream, Scope::KeywordDeclaration, static fn (ParsedToken $token) => 'let' === $token->getText()));
        self::assertTrue($this->streamContainsScope($stream, Scope::KeywordDeclaration, static fn (ParsedToken $token) => 'var' === $token->getText()));
    }

    public function testReturnIfWhileAreControl(): void
    {
        $stream = $this->parse('if (true) return; while (false) {}');

        self::assertTrue($this->streamContainsScope($stream, Scope::KeywordControl, static fn (ParsedToken $token) => 'if' === $token->getText()));
        self::assertTrue($this->streamContainsScope($stream, Scope::KeywordControl, static fn (ParsedToken $token) => 'return' === $token->getText()));
        self::assertTrue($this->streamContainsScope($stream, Scope::KeywordControl, static fn (ParsedToken $token) => 'while' === $token->getText()));
    }

    public function testNewTypeofAreOperators(): void
    {
        $stream = $this->parse('new Object(); typeof x;');

        self::assertTrue($this->streamContainsScope($stream, Scope::KeywordOperator, static fn (ParsedToken $token) => 'new' === $token->getText()));
        self::assertTrue($this->streamContainsScope($stream, Scope::KeywordOperator, static fn (ParsedToken $token) => 'typeof' === $token->getText()));
    }

    public function testThisIsVariableThis(): void
    {
        $stream = $this->parse('this.method();');

        self::assertTrue($this->streamContainsScope($stream, Scope::VariableThis, static fn (ParsedToken $token) => 'this' === $token->getText()));
    }

    public function testFunctionDefinitionScope(): void
    {
        $stream = $this->parse('function greet(name) { return "Hello"; }');

        self::assertTrue($this->streamContainsScope($stream, Scope::FunctionDefinition, static fn (ParsedToken $token) => 'greet' === $token->getText()));
    }

    public function testFunctionCallScope(): void
    {
        $stream = $this->parse('console.log("test");');

        self::assertTrue($this->streamContainsScope($stream, Scope::FunctionCall, static fn (ParsedToken $token) => 'log' === $token->getText()));
    }

    public function testClassDefinitionScope(): void
    {
        $stream = $this->parse('class MyClass {}');

        self::assertTrue($this->streamContainsScope($stream, Scope::TypeDefinition, static fn (ParsedToken $token) => 'MyClass' === $token->getText()));
    }

    public function testDistinguishesFunctionCallFromDefinition(): void
    {
        $stream = $this->parse('function foo() {} foo();');

        $fooTokens = array_filter(
            $stream->getTokens(),
            static fn (ParsedToken $token) => 'foo' === $token->getText()
        );

        self::assertCount(2, $fooTokens);

        $scopes = array_map(static fn (ParsedToken $token) => $token->getScope(), $fooTokens);

        self::assertContains(Scope::FunctionDefinition, $scopes);
        self::assertContains(Scope::FunctionCall, $scopes);
    }

    public function testStateTransitions(): void
    {
        $stream = $this->parse('class Test {} function method() {}');

        self::assertTrue($this->streamContainsScope($stream, Scope::TypeDefinition, static fn (ParsedToken $token) => 'Test' === $token->getText()));
        self::assertTrue($this->streamContainsScope($stream, Scope::FunctionDefinition, static fn (ParsedToken $token) => 'method' === $token->getText()));
    }

    public function testDefaultIdentifierIsVariable(): void
    {
        $stream = $this->parse('const x = myVariable;');

        self::assertTrue($this->streamContainsScope($stream, Scope::Variable, static fn (ParsedToken $token) => 'myVariable' === $token->getText()));
    }

    public function testImportStatement(): void
    {
        $stream = $this->parse('import { Component } from "react";');

        self::assertTrue($this->streamContainsScope($stream, Scope::KeywordDeclaration, static fn (ParsedToken $token) => 'import' === $token->getText()));
        self::assertTrue($this->streamContainsScope($stream, Scope::Variable, static fn (ParsedToken $token) => 'Component' === $token->getText()));
    }

    public function testExportStatement(): void
    {
        $stream = $this->parse('export const API_KEY = "secret";');

        self::assertTrue($this->streamContainsScope($stream, Scope::KeywordDeclaration, static fn (ParsedToken $token) => 'export' === $token->getText()));
    }

    public function testComplexNestedCode(): void
    {
        $code = <<<'JS'
class Calculator {}
function add(a, b) {
    return a + b;
}
const result = add(1, 2);
JS;
        $stream = $this->parse($code);

        self::assertTrue($this->streamContainsScope($stream, Scope::TypeDefinition, static fn (ParsedToken $token) => 'Calculator' === $token->getText()));
        self::assertTrue($this->streamContainsScope($stream, Scope::FunctionDefinition, static fn (ParsedToken $token) => 'add' === $token->getText()));
        self::assertTrue($this->streamContainsScope($stream, Scope::FunctionCall, static fn (ParsedToken $token) => 'add' === $token->getText()));
    }

    #[DataProvider('scopeProvider')]
    public function testDetectsExpectedScope(string $code, Scope $scope, ?callable $filter = null): void
    {
        $stream = $this->parse($code);

        self::assertTrue(
            $this->streamContainsScope($stream, $scope, $filter),
            sprintf('Failed asserting that scope %s exists for code: %s', $scope->value, $code)
        );
    }

    /**
     * @return array<string, array{string, Scope, (callable(ParsedToken): bool)|null}>
     */
    public static function scopeProvider(): array
    {
        return [
            'whitespace' => ['  ', Scope::Whitespace, null],
            'comment' => ['// comment', Scope::Comment, null],
            'string' => ['"test"', Scope::String, null],
            'template literal' => ['`template`', Scope::String, null],
            'template expression' => ['`${x}`', Scope::StringTemplateExpression, static fn (ParsedToken $token) => str_contains($token->getText(), '$')],
            'regex' => ['/test/', Scope::RegExp, null],
            'number' => ['42', Scope::Number, null],
            'boolean true' => ['true', Scope::Boolean, null],
            'boolean false' => ['false', Scope::Boolean, null],
            'null' => ['null', Scope::Null, null],
            'undefined' => ['undefined', Scope::Null, null],
            'operator' => ['a + b', Scope::Operator, static fn (ParsedToken $token) => '+' === $token->getText()],
            'punctuation' => ['()', Scope::Punctuation, null],
        ];
    }

    public function testArrowFunctionExpression(): void
    {
        $stream = $this->parse('const fn = (x) => x * 2;');

        self::assertTrue($this->streamContainsScope($stream, Scope::Operator, static fn (ParsedToken $token) => '=>' === $token->getText()));
    }

    public function testLookaheadForFunctionCalls(): void
    {
        $stream = $this->parse('doSomething ();');

        self::assertTrue($this->streamContainsScope($stream, Scope::FunctionCall, static fn (ParsedToken $token) => 'doSomething' === $token->getText()));
    }

    public function testInstanceofAndDeleteAreOperators(): void
    {
        $stream = $this->parse('obj instanceof Class; delete obj.prop;');

        self::assertTrue($this->streamContainsScope($stream, Scope::KeywordOperator, static fn (ParsedToken $token) => 'instanceof' === $token->getText()));
        self::assertTrue($this->streamContainsScope($stream, Scope::KeywordOperator, static fn (ParsedToken $token) => 'delete' === $token->getText()));
    }

    private function parse(string $code): ParsedStream
    {
        $tokens = $this->lexer->tokenize($code);

        return $this->parser->parse($tokens);
    }
}
