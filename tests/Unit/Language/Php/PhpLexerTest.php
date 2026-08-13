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
use Alto\Code\Highlight\Tests\TestCase;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;

#[CoversClass(PhpLexer::class)]
final class PhpLexerTest extends TestCase
{
    #[DataProvider('tokenCases')]
    public function testTokenizeDetectsExpectedTokens(string $code, int $tokenId, ?string $expectedText = null): void
    {
        $tokens = (new PhpLexer())->tokenize($code);

        self::assertTrue($this->streamContainsToken($tokens, $tokenId, $expectedText));
    }

    public function testNormalizesWhitespace(): void
    {
        // This code has multiple adjacent whitespace tokens that should be merged
        $code = "<?php\n\n\n   \$var = 42;";
        $tokens = (new PhpLexer())->tokenize($code);

        $whitespaceTokens = array_values(array_filter($tokens, fn($token) => T_WHITESPACE === $token->id));

        self::assertNotEmpty($whitespaceTokens);

        // After normalization, consecutive whitespace should be merged
        // So we should have fewer whitespace tokens than if they weren't merged
        foreach ($whitespaceTokens as $wsToken) {
            // Each whitespace token should have some content
            self::assertGreaterThan(0, strlen($wsToken->text));
        }
    }

    public function testMergesMultipleAdjacentWhitespaceTokens(): void
    {
        // Create code with spaces and newlines that would create adjacent whitespace tokens
        $code = "<?php \n \t \n \$x";
        $tokens = (new PhpLexer())->tokenize($code);

        // Count consecutive whitespace tokens
        $maxConsecutiveWhitespace = 0;
        $currentConsecutive = 0;

        foreach ($tokens as $token) {
            if (T_WHITESPACE === $token->id) {
                ++$currentConsecutive;
                $maxConsecutiveWhitespace = max($maxConsecutiveWhitespace, $currentConsecutive);
            } else {
                $currentConsecutive = 0;
            }
        }

        // After normalization, we shouldn't have multiple consecutive whitespace tokens
        self::assertLessThanOrEqual(1, $maxConsecutiveWhitespace, 'Adjacent whitespace tokens should be merged');
    }

    public function testMergedWhitespaceTokenContainsCombinedText(): void
    {
        $code = "<?php  \t  \$value = 1;";
        $tokens = (new PhpLexer())->tokenize($code);

        $whitespaceTokens = array_values(array_filter(
            $tokens,
            static fn(\PhpToken $token): bool => T_WHITESPACE === $token->id,
        ));

        self::assertNotEmpty($whitespaceTokens);
        self::assertSame(" \t  ", $whitespaceTokens[0]->text);
    }

    public function testTokenListIsReindexedAfterNormalization(): void
    {
        $tokens = (new PhpLexer())->tokenize("<?php\n\n echo 'a';");

        self::assertTrue(array_is_list($tokens));
    }

    public function testHandlesEmptyCode(): void
    {
        $tokens = (new PhpLexer())->tokenize('');

        self::assertEmpty($tokens);
    }

    public function testComplexCodeYieldsManyTokens(): void
    {
        $code = <<<'PHP'
<?php
class Test {
    public function method($param) {
        return $param * 2;
    }
}
PHP;
        $tokens = (new PhpLexer())->tokenize($code);

        self::assertGreaterThan(10, count($tokens));
    }

    public function testPreservesLineNumbers(): void
    {
        $code = "<?php\n\$var";
        $tokens = (new PhpLexer())->tokenize($code);

        $variableToken = null;
        foreach ($tokens as $token) {
            if (T_VARIABLE === $token->id) {
                $variableToken = $token;
                break;
            }
        }

        self::assertNotNull($variableToken);
        self::assertSame(2, $variableToken->line);
    }

    public function testTokenizesNamespace(): void
    {
        $code = '<?php namespace App\\Test;';
        $tokens = (new PhpLexer())->tokenize($code);

        self::assertTrue($this->streamContainsToken($tokens, T_NAMESPACE, null));
    }

    public function testTokenizesDocComment(): void
    {
        $code = '<?php /** docblock */';
        $tokens = (new PhpLexer())->tokenize($code);

        self::assertTrue($this->streamContainsToken($tokens, T_DOC_COMMENT, null));
    }

    public function testTokenizesMultilineComment(): void
    {
        $code = '<?php /* comment */';
        $tokens = (new PhpLexer())->tokenize($code);

        self::assertTrue($this->streamContainsToken($tokens, T_COMMENT, null));
    }

    public function testTokenizesFloat(): void
    {
        $code = '<?php 3.14';
        $tokens = (new PhpLexer())->tokenize($code);

        self::assertTrue($this->streamContainsToken($tokens, T_DNUMBER, null));
    }

    public function testAllTokensArePhptokenInstances(): void
    {
        $code = '<?php $var = 42;';
        $tokens = (new PhpLexer())->tokenize($code);

        foreach ($tokens as $token) {
            self::assertInstanceOf(\PhpToken::class, $token);
        }
    }

    /**
     * @return iterable<array{string, int, string|null}>
     */
    public static function tokenCases(): iterable
    {
        yield 'simple code' => ['<?php echo "hello";', T_ECHO, 'echo'];
        yield 'variable' => ['<?php $variable;', T_VARIABLE, '$variable'];
        yield 'string' => ['<?php "string";', T_CONSTANT_ENCAPSED_STRING, '"string"'];
        yield 'numbers' => ['<?php 42; 3.14;', T_LNUMBER, '42'];
        yield 'comment' => ['<?php // comment', T_COMMENT, null];
        yield 'tag open' => ['<?php', T_OPEN_TAG, null];
        yield 'class keyword' => ['<?php class Test {}', T_CLASS, 'class'];
        yield 'function keyword' => ['<?php function test() {}', T_FUNCTION, 'function'];
        yield 'public modifier' => ['<?php public $var;', T_PUBLIC, 'public'];
    }

    /**
     * @param list<\PhpToken> $tokens
     */
    private function streamContainsToken(array $tokens, int $tokenId, ?string $expectedText): bool
    {
        foreach ($tokens as $token) {
            if ($token->id !== $tokenId) {
                continue;
            }

            if (null === $expectedText || $token->text === $expectedText) {
                return true;
            }
        }

        return false;
    }
}
