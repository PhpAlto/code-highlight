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
use Alto\Code\Highlight\Language\Php\PhpLexer;
use Alto\Code\Highlight\Language\Php\PhpSemanticParser;
use Alto\Code\Highlight\Language\PhpLanguage;
use Alto\Code\Highlight\Parser\ParsedStream;
use Alto\Code\Highlight\Tests\TestCase;
use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(PhpLanguage::class)]
final class PhpLanguageTest extends TestCase
{
    private function createThrowingLexer(): PhpLexer
    {
        return new class extends PhpLexer {
            public function tokenize(string $code): array
            {
                throw new \RuntimeException('Lexer error!');
            }
        };
    }

    private function createThrowingParser(): PhpSemanticParser
    {
        return new class extends PhpSemanticParser {
            /**
             * @param list<\PhpToken> $tokens
             */
            public function parse(array $tokens): ParsedStream
            {
                throw new \RuntimeException('Parser error!');
            }
        };
    }

    public function testGetIdentifierReturnsPhp(): void
    {
        $language = new PhpLanguage();

        self::assertSame('php', $language->getIdentifier());
    }

    public function testConstructorWithDefaultDependencies(): void
    {
        $language = new PhpLanguage();

        $stream = $language->parse('<?php $x = 1;');

        self::assertInstanceOf(ParsedStream::class, $stream);
        self::assertGreaterThan(0, $stream->count());
    }

    public function testConstructorWithCustomLexer(): void
    {
        $customLexer = new PhpLexer();
        $language = new PhpLanguage($customLexer);

        $stream = $language->parse('<?php $y = 2;');

        self::assertInstanceOf(ParsedStream::class, $stream);
    }

    public function testConstructorWithCustomParser(): void
    {
        $customParser = new PhpSemanticParser();
        $language = new PhpLanguage(null, $customParser);

        $stream = $language->parse('<?php $z = 3;');

        self::assertInstanceOf(ParsedStream::class, $stream);
    }

    public function testConstructorWithBothCustomDependencies(): void
    {
        $customLexer = new PhpLexer();
        $customParser = new PhpSemanticParser();
        $language = new PhpLanguage($customLexer, $customParser);

        $stream = $language->parse('<?php function test() {}');

        self::assertInstanceOf(ParsedStream::class, $stream);
    }

    public function testParseReturnsParsedStream(): void
    {
        $language = new PhpLanguage();

        $code = <<<'PHP'
<?php

function greet(string $name): string {
    return "Hello, {$name}!";
}
PHP;

        $stream = $language->parse($code);

        self::assertInstanceOf(ParsedStream::class, $stream);
        self::assertGreaterThan(10, $stream->count(), 'Should have multiple tokens for this code');
    }

    public function testParseHandlesEmptyCode(): void
    {
        $language = new PhpLanguage();

        $stream = $language->parse('');

        self::assertInstanceOf(ParsedStream::class, $stream);
    }

    public function testParseHandlesWhitespaceOnly(): void
    {
        $language = new PhpLanguage();

        $stream = $language->parse('   \n\t  ');

        self::assertInstanceOf(ParsedStream::class, $stream);
    }

    public function testParseThrowsOnLexerError(): void
    {
        $throwingLexer = $this->createThrowingLexer();
        $language = new PhpLanguage($throwingLexer);

        $this->expectException(ParseException::class);
        $this->expectExceptionMessage('Failed to parse PHP code: Lexer error!');

        $language->parse('<?php $x = 1;');
    }

    public function testParseWrapsParserExceptions(): void
    {
        $throwingParser = $this->createThrowingParser();
        $language = new PhpLanguage(null, $throwingParser);

        $this->expectException(ParseException::class);
        $this->expectExceptionMessage('Failed to parse PHP code: Parser error!');

        $language->parse('<?php $x = 1;');
    }

    public function testParseComplexPhp(): void
    {
        $language = new PhpLanguage();

        $code = <<<'PHP'
<?php

namespace App;

class Calculator {
    public function add(int $a, int $b): int {
        return $a + $b;
    }
}

$calc = new Calculator();
$result = $calc->add(5, 3);
echo $result;
PHP;

        $stream = $language->parse($code);

        self::assertInstanceOf(ParsedStream::class, $stream);
        self::assertGreaterThan(20, $stream->count());
    }

    public function testParsePhpWithoutOpeningTag(): void
    {
        $language = new PhpLanguage();

        // Some code without opening tag (should still work as PHP parser handles this)
        $stream = $language->parse('$x = 1;');

        self::assertInstanceOf(ParsedStream::class, $stream);
    }
}
