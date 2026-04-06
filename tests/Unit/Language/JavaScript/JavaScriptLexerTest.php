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
use Alto\Code\Highlight\Language\JavaScript\JavaScriptToken;
use Alto\Code\Highlight\Language\JavaScript\JavaScriptTokenType;
use Alto\Code\Highlight\Tests\TestCase;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;

#[CoversClass(JavaScriptLexer::class)]
final class JavaScriptLexerTest extends TestCase
{
    private JavaScriptLexer $lexer;

    protected function setUp(): void
    {
        parent::setUp();

        $this->lexer = new JavaScriptLexer();
    }

    public function testTokenizesKeywords(): void
    {
        $code = 'const let var function class if else for while return';
        $tokens = $this->lexer->tokenize($code);

        $keywordTokens = array_filter($tokens, fn ($token) => JavaScriptTokenType::Keyword === $token->type);

        self::assertCount(10, $keywordTokens);
    }

    public function testTokenizesBooleanAndNullLiterals(): void
    {
        $code = 'true false null undefined';
        $tokens = $this->lexer->tokenize($code);

        self::assertTrue($this->streamContainsToken($tokens, JavaScriptTokenType::BooleanLiteral, 'true'));
        self::assertTrue($this->streamContainsToken($tokens, JavaScriptTokenType::BooleanLiteral, 'false'));
        self::assertTrue($this->streamContainsToken($tokens, JavaScriptTokenType::NullLiteral, 'null'));
        self::assertTrue($this->streamContainsToken($tokens, JavaScriptTokenType::NullLiteral, 'undefined'));
    }

    public function testTokenizesIdentifiers(): void
    {
        $code = 'myVar _privateVar $jquery camelCase snake_case';
        $tokens = $this->lexer->tokenize($code);

        $identifiers = array_filter($tokens, fn ($token) => JavaScriptTokenType::Identifier === $token->type);

        self::assertCount(5, $identifiers);
        self::assertTrue($this->streamContainsToken($tokens, JavaScriptTokenType::Identifier, 'myVar'));
        self::assertTrue($this->streamContainsToken($tokens, JavaScriptTokenType::Identifier, '_privateVar'));
        self::assertTrue($this->streamContainsToken($tokens, JavaScriptTokenType::Identifier, '$jquery'));
    }

    #[DataProvider('stringProvider')]
    public function testTokenizesStrings(string $code, string $expectedText): void
    {
        $tokens = $this->lexer->tokenize($code);

        self::assertTrue($this->streamContainsToken($tokens, JavaScriptTokenType::String, $expectedText));
    }

    public function testTokenizesTemplateLiterals(): void
    {
        $code = '`simple template`';
        $tokens = $this->lexer->tokenize($code);

        self::assertTrue($this->streamContainsToken($tokens, JavaScriptTokenType::TemplateLiteral, '`simple template`'));
    }

    public function testTokenizesTemplateExpressions(): void
    {
        $code = '`hello ${name} world`';
        $tokens = $this->lexer->tokenize($code);

        self::assertTrue($this->streamContainsToken($tokens, JavaScriptTokenType::TemplateLiteral, '`hello '));
        self::assertTrue($this->streamContainsToken($tokens, JavaScriptTokenType::TemplateExpression, '${name}'));
        self::assertTrue($this->streamContainsToken($tokens, JavaScriptTokenType::TemplateLiteral, ' world`'));
    }

    public function testTokenizesNestedTemplateExpressions(): void
    {
        $code = '`outer ${{inner: 1}} end`';
        $tokens = $this->lexer->tokenize($code);

        self::assertTrue($this->streamContainsToken($tokens, JavaScriptTokenType::TemplateExpression, '${{inner: 1}}'));
    }

    public function testTokenizesRegex(): void
    {
        $code = '/pattern/gi';
        $tokens = $this->lexer->tokenize($code);

        self::assertTrue($this->streamContainsToken($tokens, JavaScriptTokenType::Regex, '/pattern/gi'));
    }

    public function testRegexWithCharacterClass(): void
    {
        $code = '/[a-z]/i';
        $tokens = $this->lexer->tokenize($code);

        self::assertTrue($this->streamContainsToken($tokens, JavaScriptTokenType::Regex, '/[a-z]/i'));
    }

    public function testRegexContextDetection(): void
    {
        $code = 'return /test/';
        $tokens = $this->lexer->tokenize($code);

        self::assertTrue($this->streamContainsToken($tokens, JavaScriptTokenType::Regex, '/test/'));
    }

    public function testDivisionOperatorNotRegex(): void
    {
        $code = 'a / b';
        $tokens = $this->lexer->tokenize($code);

        // Should tokenize / as operator, not regex
        self::assertTrue($this->streamContainsToken($tokens, JavaScriptTokenType::Operator, '/'));
        self::assertFalse($this->streamContainsToken($tokens, JavaScriptTokenType::Regex, null));
    }

    #[DataProvider('numberProvider')]
    public function testTokenizesNumbers(string $code, string $expectedNumber): void
    {
        $tokens = $this->lexer->tokenize($code);

        self::assertTrue($this->streamContainsToken($tokens, JavaScriptTokenType::Number, $expectedNumber));
    }

    #[DataProvider('operatorProvider')]
    public function testTokenizesOperators(string $code, string $expectedOperator): void
    {
        $tokens = $this->lexer->tokenize($code);

        self::assertTrue($this->streamContainsToken($tokens, JavaScriptTokenType::Operator, $expectedOperator));
    }

    public function testTokenizesArrowOperator(): void
    {
        $code = '() => {}';
        $tokens = $this->lexer->tokenize($code);

        self::assertTrue($this->streamContainsToken($tokens, JavaScriptTokenType::Operator, '=>'));
    }

    public function testTokenizesSpreadOperator(): void
    {
        $code = '...args';
        $tokens = $this->lexer->tokenize($code);

        self::assertTrue($this->streamContainsToken($tokens, JavaScriptTokenType::Operator, '...'));
    }

    public function testTokenizesPunctuation(): void
    {
        $code = '(){}[];,.';
        $tokens = $this->lexer->tokenize($code);

        $punctuation = array_filter($tokens, fn ($token) => JavaScriptTokenType::Punctuation === $token->type);

        self::assertCount(9, $punctuation);
    }

    public function testTokenizesSingleLineComments(): void
    {
        $code = '// this is a comment';
        $tokens = $this->lexer->tokenize($code);

        self::assertTrue($this->streamContainsToken($tokens, JavaScriptTokenType::Comment, '// this is a comment'));
    }

    public function testTokenizesMultiLineComments(): void
    {
        $code = '/* this is a\nmulti-line\ncomment */';
        $tokens = $this->lexer->tokenize($code);

        self::assertTrue($this->streamContainsToken($tokens, JavaScriptTokenType::Comment, '/* this is a\nmulti-line\ncomment */'));
    }

    public function testHandlesUnicode(): void
    {
        $code = 'const test = "☕"';
        $tokens = $this->lexer->tokenize($code);

        // Unicode in strings is supported
        self::assertTrue($this->streamContainsToken($tokens, JavaScriptTokenType::String, '"☕"'));
        // ASCII identifiers work
        self::assertTrue($this->streamContainsToken($tokens, JavaScriptTokenType::Identifier, 'test'));
    }

    public function testHandlesEmptyInput(): void
    {
        $tokens = $this->lexer->tokenize('');

        self::assertEmpty($tokens);
    }

    public function testComplexExpression(): void
    {
        $code = <<<'JS'
function greet(name) {
    const message = `Hello, ${name}!`;
    return message;
}
JS;
        $tokens = $this->lexer->tokenize($code);

        self::assertGreaterThan(10, count($tokens));
        self::assertTrue($this->streamContainsToken($tokens, JavaScriptTokenType::Keyword, 'function'));
        self::assertTrue($this->streamContainsToken($tokens, JavaScriptTokenType::Identifier, 'greet'));
        self::assertTrue($this->streamContainsToken($tokens, JavaScriptTokenType::Keyword, 'const'));
        self::assertTrue($this->streamContainsToken($tokens, JavaScriptTokenType::TemplateLiteral, '`Hello, '));
    }

    public function testWhitespacePreservation(): void
    {
        $code = 'const   x   =   42;';
        $tokens = $this->lexer->tokenize($code);

        $whitespaceTokens = array_filter($tokens, fn ($token) => JavaScriptTokenType::Whitespace === $token->type);

        self::assertNotEmpty($whitespaceTokens);
    }

    /**
     * @return array<string, array{string, string}>
     */
    public static function stringProvider(): array
    {
        return [
            'double quotes' => ['"hello world"', '"hello world"'],
            'single quotes' => ["'hello world'", "'hello world'"],
            'escaped quotes' => ['"say \\"hello\\""', '"say \\"hello\\""'],
            'escaped backslash' => ['"path\\\\to\\\\file"', '"path\\\\to\\\\file"'],
            'newline escape' => ['"line1\\nline2"', '"line1\\nline2"'],
        ];
    }

    /**
     * @return array<string, array{string, string}>
     */
    public static function numberProvider(): array
    {
        return [
            'integer' => ['42', '42'],
            'decimal' => ['3.14', '3.14'],
            'hex' => ['0xFF', '0xFF'],
            'binary' => ['0b1010', '0b1010'],
            'octal' => ['0o777', '0o777'],
            'scientific notation' => ['1e10', '1e10'],
            'scientific with sign' => ['1.5e-5', '1.5e-5'],
        ];
    }

    /**
     * @return array<string, array{string, string}>
     */
    public static function operatorProvider(): array
    {
        return [
            'addition' => ['a + b', '+'],
            'subtraction' => ['a - b', '-'],
            'multiplication' => ['a * b', '*'],
            'division' => ['a / b', '/'],
            'equality' => ['a == b', '=='],
            'strict equality' => ['a === b', '==='],
            'inequality' => ['a != b', '!='],
            'strict inequality' => ['a !== b', '!=='],
            'logical and' => ['a && b', '&&'],
            'logical or' => ['a || b', '||'],
            'nullish coalescing' => ['a ?? b', '??'],
            'optional chaining' => ['a?.b', '?.'],
            'increment' => ['a++', '++'],
            'decrement' => ['a--', '--'],
            'exponentiation' => ['a ** b', '**'],
        ];
    }

    /**
     * @param list<JavaScriptToken> $tokens
     */
    private function streamContainsToken(array $tokens, JavaScriptTokenType $type, ?string $expectedText = null): bool
    {
        foreach ($tokens as $token) {
            if ($token->type !== $type) {
                continue;
            }

            if (null === $expectedText || $token->text === $expectedText) {
                return true;
            }
        }

        return false;
    }
}
