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

namespace Alto\Code\Highlight\Tests\Unit\Language;

use Alto\Code\Highlight\Exception\ParseException;
use Alto\Code\Highlight\Language\Php\PhpLexer;
use Alto\Code\Highlight\Language\Php\PhpSemanticParser;
use Alto\Code\Highlight\Language\PhpLanguage;
use Alto\Code\Highlight\Parser\ParsedStream;
use Alto\Code\Highlight\Parser\ParsedToken;
use Alto\Code\Highlight\Tests\TestCase;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;

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

    public function testParseWithoutOpeningTagPreservesSourceAndTokenPositions(): void
    {
        $language = new PhpLanguage();
        $code = <<<'PHP'
$page->getByRole("button")->click();
    $result = true;
PHP;

        $stream = $language->parse($code);

        self::assertSame($code, $stream->toString());
        self::assertFalse($stream->isEmpty());
        $this->assertTokenPositionsMatchSource($stream->getTokens());

        $page = $stream->find(static fn(ParsedToken $token): bool => '$page' === $token->text);
        self::assertNotNull($page);
        self::assertSame(0, $page->offset);
        self::assertSame(1, $page->line);
        self::assertSame(0, $page->column);

        $result = $stream->find(static fn(ParsedToken $token): bool => '$result' === $token->text);
        self::assertNotNull($result);
        self::assertSame(strpos($code, '$result'), $result->offset);
        self::assertSame(2, $result->line);
        self::assertSame(4, $result->column);
    }

    public function testParseAcceptsEmptySource(): void
    {
        $stream = (new PhpLanguage())->parse('');

        self::assertTrue($stream->isEmpty());
        self::assertSame('', $stream->toString());
    }

    public function testParseDoesNotInheritParserStateFromPreviousSource(): void
    {
        $language = new PhpLanguage();
        $language->parse('<?php function');

        $stream = $language->parse('run();');
        $run = $stream->find(static fn(ParsedToken $token): bool => 'run' === $token->text);

        self::assertNotNull($run);
        self::assertSame(\Alto\Code\Highlight\Scope::FunctionCall, $run->scope);
    }

    public function testParseDoesNotRewriteSourceWithAnOpeningTag(): void
    {
        $language = new PhpLanguage();
        $code = "  <?php\n\$value = 1;";

        $stream = $language->parse($code);

        self::assertSame($code, $stream->toString());
        $this->assertTokenPositionsMatchSource($stream->getTokens());
    }

    #[DataProvider('sourceBoundaryProvider')]
    public function testParsePreservesBoundaryWhitespace(string $code): void
    {
        $stream = (new PhpLanguage())->parse($code);

        self::assertSame($code, $stream->toString());
        $this->assertTokenPositionsMatchSource($stream->getTokens());
    }

    /**
     * @return iterable<string, array{string}>
     */
    public static function sourceBoundaryProvider(): iterable
    {
        yield 'leading whitespace' => ['  $value = 1;'];
        yield 'trailing newline' => ["\$value = 1;\n"];
        yield 'leading whitespace and trailing newline' => ["\t\$value = 1;\n"];
    }

    /**
     * @param list<ParsedToken> $tokens
     */
    private function assertTokenPositionsMatchSource(array $tokens): void
    {
        $offset = 0;
        $line = 1;
        $column = 0;

        foreach ($tokens as $token) {
            self::assertSame($offset, $token->offset, $token->text);
            self::assertSame($line, $token->line, $token->text);
            self::assertSame($column, $token->column, $token->text);

            $offset += strlen($token->text);
            $newlines = substr_count($token->text, "\n");

            if (0 === $newlines) {
                $column += strlen($token->text);
                continue;
            }

            $line += $newlines;
            $column = strlen($token->text) - (int) strrpos($token->text, "\n") - 1;
        }
    }
}
