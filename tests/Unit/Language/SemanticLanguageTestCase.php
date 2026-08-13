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

use Alto\Code\Highlight\Language\LanguageInterface;
use Alto\Code\Highlight\Parser\ParsedStream;
use Alto\Code\Highlight\Parser\ParsedToken;
use Alto\Code\Highlight\Scope;
use PHPUnit\Framework\TestCase;

/**
 * Base helper for verifying semantic tokens emitted by language parsers
 * without relying on HTML snapshot fixtures.
 */
abstract class SemanticLanguageTestCase extends TestCase
{
    abstract protected function createLanguage(): LanguageInterface;

    protected function parseCode(string $code): ParsedStream
    {
        return $this->createLanguage()->parse($code);
    }

    /**
     * @param list<array{string, Scope}> $expectedTokens
     */
    final protected function assertSemanticTokens(string $code, array $expectedTokens, bool $ignoreWhitespace = true): void
    {
        $stream = $this->parseCode($code);
        $tokens = $stream->getTokens();

        if ($ignoreWhitespace) {
            $tokens = array_values(array_filter(
                $tokens,
                static fn(ParsedToken $token): bool => Scope::Whitespace !== $token->getScope(),
            ));
        }

        $actual = array_map(
            static fn(ParsedToken $token): array => [$token->getText(), $token->getScope()],
            $tokens,
        );

        self::assertSame($expectedTokens, $actual);
    }
}
