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

namespace Alto\Code\Highlight\Parser;

use Alto\Code\Highlight\Scope;

/**
 * A helper class to build a ParsedStream.
 *
 * This simplifies the process of accumulating tokens, especially when
 * dealing with embedded languages or complex parsing logic.
 *
 * @author Simon André <smn.andre@gmail.com>
 */
final class StreamBuilder
{
    /**
     * @var list<ParsedToken>
     */
    private array $tokens = [];

    public function add(string $text, Scope $scope): void
    {
        $this->tokens[] = new ParsedToken($text, $scope);
    }

    public function addToken(ParsedToken $token): void
    {
        $this->tokens[] = $token;
    }

    public function appendStream(ParsedStream $stream): void
    {
        foreach ($stream->getTokens() as $token) {
            $this->tokens[] = $token;
        }
    }

    public function build(): ParsedStream
    {
        return new ParsedStream($this->tokens);
    }

    public function isEmpty(): bool
    {
        return empty($this->tokens);
    }
}
