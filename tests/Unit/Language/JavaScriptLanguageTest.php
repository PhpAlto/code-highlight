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

namespace Alto\Code\Highlight\Tests\Unit\Language;

use Alto\Code\Highlight\Exception\ParseException;
use Alto\Code\Highlight\Language\JavaScript\JavaScriptLexer;
use Alto\Code\Highlight\Language\JavaScript\JavaScriptSemanticParser;
use Alto\Code\Highlight\Language\JavaScript\JavaScriptToken;
use Alto\Code\Highlight\Language\JavaScriptLanguage;
use Alto\Code\Highlight\Parser\ParsedStream;
use Alto\Code\Highlight\Tests\TestCase;
use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(JavaScriptLanguage::class)]
final class JavaScriptLanguageTest extends TestCase
{
    private function createThrowingLexer(): JavaScriptLexer
    {
        return new class extends JavaScriptLexer {
            public function tokenize(string $code): array
            {
                throw new \RuntimeException('Lexer error!');
            }
        };
    }

    private function createThrowingParser(): JavaScriptSemanticParser
    {
        return new class extends JavaScriptSemanticParser {
            /**
             * @param list<JavaScriptToken> $tokens
             */
            public function parse(array $tokens): ParsedStream
            {
                throw new \RuntimeException('Parser error!');
            }
        };
    }

    public function testGetIdentifierReturnsJavascript(): void
    {
        $language = new JavaScriptLanguage();

        self::assertSame('javascript', $language->getIdentifier());
    }

    public function testConstructorWithDefaultDependencies(): void
    {
        $language = new JavaScriptLanguage();

        $stream = $language->parse('const x = 1;');

        self::assertInstanceOf(ParsedStream::class, $stream);
        self::assertGreaterThan(0, $stream->count());
    }

    public function testConstructorWithCustomLexer(): void
    {
        $customLexer = new JavaScriptLexer();
        $language = new JavaScriptLanguage($customLexer);

        $stream = $language->parse('let y = 2;');

        self::assertInstanceOf(ParsedStream::class, $stream);
    }

    public function testConstructorWithCustomParser(): void
    {
        $customParser = new JavaScriptSemanticParser();
        $language = new JavaScriptLanguage(null, $customParser);

        $stream = $language->parse('var z = 3;');

        self::assertInstanceOf(ParsedStream::class, $stream);
    }

    public function testConstructorWithBothCustomDependencies(): void
    {
        $customLexer = new JavaScriptLexer();
        $customParser = new JavaScriptSemanticParser();
        $language = new JavaScriptLanguage($customLexer, $customParser);

        $stream = $language->parse('const foo = () => {};');

        self::assertInstanceOf(ParsedStream::class, $stream);
    }

    public function testParseReturnsParsedStream(): void
    {
        $language = new JavaScriptLanguage();

        $code = <<<'JS'
function greet(name) {
    const message = `Hello, ${name}!`;
    return message;
}
JS;

        $stream = $language->parse($code);

        self::assertInstanceOf(ParsedStream::class, $stream);
        self::assertGreaterThan(10, $stream->count(), 'Should have multiple tokens for this code');
    }

    public function testParseHandlesEmptyCode(): void
    {
        $language = new JavaScriptLanguage();

        $stream = $language->parse('');

        self::assertInstanceOf(ParsedStream::class, $stream);
        self::assertSame(0, $stream->count());
    }

    public function testParseHandlesWhitespaceOnly(): void
    {
        $language = new JavaScriptLanguage();

        $stream = $language->parse('   \n\t  ');

        self::assertInstanceOf(ParsedStream::class, $stream);
    }

    public function testParseThrowsOnLexerError(): void
    {
        $throwingLexer = $this->createThrowingLexer();
        $language = new JavaScriptLanguage($throwingLexer);

        $this->expectException(ParseException::class);
        $this->expectExceptionMessage('Failed to parse JavaScript code: Lexer error!');

        $language->parse('const x = 1;');
    }

    public function testParseWrapsParserExceptions(): void
    {
        $throwingParser = $this->createThrowingParser();
        $language = new JavaScriptLanguage(null, $throwingParser);

        $this->expectException(ParseException::class);
        $this->expectExceptionMessage('Failed to parse JavaScript code: Parser error!');

        $language->parse('const x = 1;');
    }

    public function testParseComplexJavascript(): void
    {
        $language = new JavaScriptLanguage();

        $code = <<<'JS'
class Calculator {
    add(a, b) {
        return a + b;
    }
}

const calc = new Calculator();
const result = calc.add(5, 3);
console.log(result);
JS;

        $stream = $language->parse($code);

        self::assertInstanceOf(ParsedStream::class, $stream);
        self::assertGreaterThan(20, $stream->count());
    }
}
