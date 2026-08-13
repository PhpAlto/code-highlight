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
 * Represents a stream of parsed tokens with assigned semantic scopes.
 *
 * This is the output of the language parser and the input to the formatter.
 * It also provides transformation operations for code manipulation.
 *
 * @implements \IteratorAggregate<int, ParsedToken>
 *
 * @author Simon André <smn.andre@gmail.com>
 */
final readonly class ParsedStream implements \Countable, \IteratorAggregate
{
    /**
     * @param list<ParsedToken> $tokens The parsed tokens with their scopes
     */
    public function __construct(
        public array $tokens,
    ) {}

    /**
     * Get all tokens in the stream.
     *
     * @return list<ParsedToken>
     */
    public function getTokens(): array
    {
        return $this->tokens;
    }

    /**
     * Check if the stream is empty.
     */
    public function isEmpty(): bool
    {
        return empty($this->tokens);
    }

    /**
     * Get the number of tokens in the stream.
     */
    public function count(): int
    {
        return count($this->tokens);
    }

    /**
     * Get an iterator for the tokens.
     *
     * @return \Traversable<int, ParsedToken>
     */
    public function getIterator(): \Traversable
    {
        yield from $this->tokens;
    }

    // =========================================================================
    // Transformation Operations
    // =========================================================================

    /**
     * Create a new stream without comment tokens.
     */
    public function withoutComments(): self
    {
        return new self(
            array_values(array_filter(
                $this->tokens,
                static fn(ParsedToken $token): bool => !$token->isComment(),
            )),
        );
    }

    /**
     * Create a new stream without whitespace tokens.
     */
    public function withoutWhitespace(): self
    {
        return new self(
            array_values(array_filter(
                $this->tokens,
                static fn(ParsedToken $token): bool => !$token->isWhitespace(),
            )),
        );
    }

    /**
     * Create a new stream without removable tokens (comments + whitespace).
     */
    public function withoutRemovable(): self
    {
        return new self(
            array_values(array_filter(
                $this->tokens,
                static fn(ParsedToken $token): bool => !$token->isRemovable(),
            )),
        );
    }

    /**
     * Create a new stream keeping only tokens of the specified types.
     *
     * @param TokenType ...$types Types to keep
     */
    public function filter(TokenType ...$types): self
    {
        return new self(
            array_values(array_filter(
                $this->tokens,
                static fn(ParsedToken $token): bool => in_array($token->type, $types, true),
            )),
        );
    }

    /**
     * Create a new stream excluding tokens of the specified types.
     *
     * @param TokenType ...$types Types to exclude
     */
    public function exclude(TokenType ...$types): self
    {
        return new self(
            array_values(array_filter(
                $this->tokens,
                static fn(ParsedToken $token): bool => !in_array($token->type, $types, true),
            )),
        );
    }

    /**
     * Create a new stream keeping only tokens with the specified scopes.
     *
     * @param Scope ...$scopes Scopes to keep
     */
    public function filterByScope(Scope ...$scopes): self
    {
        return new self(
            array_values(array_filter(
                $this->tokens,
                static fn(ParsedToken $token): bool => in_array($token->scope, $scopes, true),
            )),
        );
    }

    // =========================================================================
    // Extraction Operations
    // =========================================================================

    /**
     * Reconstruct the original code from tokens.
     */
    public function toString(): string
    {
        return implode('', array_map(
            static fn(ParsedToken $token): string => $token->text,
            $this->tokens,
        ));
    }

    /**
     * Get an array of token texts.
     *
     * @return list<string>
     */
    public function texts(): array
    {
        return array_map(
            static fn(ParsedToken $token): string => $token->text,
            $this->tokens,
        );
    }

    /**
     * Get all string literal values.
     *
     * @return list<string>
     */
    public function getStrings(): array
    {
        return array_values(array_map(
            static fn(ParsedToken $token): string => $token->text,
            array_filter(
                $this->tokens,
                static fn(ParsedToken $token): bool => $token->isString(),
            ),
        ));
    }

    /**
     * Get all comment texts.
     *
     * @return list<string>
     */
    public function getComments(): array
    {
        return array_values(array_map(
            static fn(ParsedToken $token): string => $token->text,
            array_filter(
                $this->tokens,
                static fn(ParsedToken $token): bool => $token->isComment(),
            ),
        ));
    }

    /**
     * Get all definition tokens (functions, classes, etc.).
     *
     * @return list<ParsedToken>
     */
    public function getDefinitions(): array
    {
        return array_values(array_filter(
            $this->tokens,
            static fn(ParsedToken $token): bool => $token->isDefinition(),
        ));
    }

    // =========================================================================
    // Analysis Operations
    // =========================================================================

    /**
     * Get token count statistics by type.
     *
     * @return array<string, int> Map of TokenType value to count
     */
    public function statistics(): array
    {
        $stats = [];

        foreach ($this->tokens as $token) {
            $type = $token->type->value;
            $stats[$type] = ($stats[$type] ?? 0) + 1;
        }

        return $stats;
    }

    /**
     * Get token count statistics by scope.
     *
     * @return array<string, int> Map of Scope value to count
     */
    public function statisticsByScope(): array
    {
        $stats = [];

        foreach ($this->tokens as $token) {
            $scope = $token->scope->value;
            $stats[$scope] = ($stats[$scope] ?? 0) + 1;
        }

        return $stats;
    }

    /**
     * Check if the stream contains any tokens of the specified type.
     */
    public function hasType(TokenType $type): bool
    {
        foreach ($this->tokens as $token) {
            if ($token->type === $type) {
                return true;
            }
        }

        return false;
    }

    /**
     * Check if the stream contains any tokens with the specified scope.
     */
    public function hasScope(Scope $scope): bool
    {
        foreach ($this->tokens as $token) {
            if ($token->scope === $scope) {
                return true;
            }
        }

        return false;
    }

    /**
     * Find the first token matching the predicate.
     *
     * @param callable(ParsedToken): bool $predicate
     */
    public function find(callable $predicate): ?ParsedToken
    {
        foreach ($this->tokens as $token) {
            if ($predicate($token)) {
                return $token;
            }
        }

        return null;
    }

    /**
     * Find all tokens matching the predicate.
     *
     * @param callable(ParsedToken): bool $predicate
     *
     * @return list<ParsedToken>
     */
    public function findAll(callable $predicate): array
    {
        return array_values(array_filter($this->tokens, $predicate));
    }

    // =========================================================================
    // Utility Operations
    // =========================================================================

    /**
     * Apply a transformation to each token.
     *
     * @param callable(ParsedToken): ParsedToken $transformer
     */
    public function map(callable $transformer): self
    {
        return new self(
            array_map($transformer, $this->tokens),
        );
    }

    /**
     * Merge this stream with another.
     */
    public function merge(self $other): self
    {
        return new self([...$this->tokens, ...$other->tokens]);
    }

    /**
     * Get a slice of the stream.
     */
    public function slice(int $offset, ?int $length = null): self
    {
        return new self(
            array_slice($this->tokens, $offset, $length),
        );
    }

    /**
     * Get the first token, if any.
     */
    public function first(): ?ParsedToken
    {
        return $this->tokens[0] ?? null;
    }

    /**
     * Get the last token, if any.
     */
    public function last(): ?ParsedToken
    {
        return $this->tokens[count($this->tokens) - 1] ?? null;
    }
}
