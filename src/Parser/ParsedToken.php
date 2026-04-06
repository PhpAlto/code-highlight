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

namespace Alto\Code\Highlight\Parser;

use Alto\Code\Highlight\Scope;

/**
 * Represents a single token with its assigned semantic scope.
 *
 * Tokens carry both visual information (Scope for highlighting)
 * and semantic information (TokenType for transformations).
 *
 * @author Simon André <smn.andre@gmail.com>
 */
final readonly class ParsedToken
{
    public function __construct(
        public string $text,
        public Scope $scope,
        public TokenType $type = TokenType::Unknown,
        public int $offset = 0,
        public int $line = 1,
        public int $column = 0,
    ) {
    }

    /**
     * Get the token's text content.
     */
    public function getText(): string
    {
        return $this->text;
    }

    /**
     * Get the token's semantic scope (for highlighting).
     */
    public function getScope(): Scope
    {
        return $this->scope;
    }

    /**
     * Get the token's semantic type (for transformations).
     */
    public function getType(): TokenType
    {
        return $this->type;
    }

    /**
     * Get the byte offset where this token starts.
     */
    public function getOffset(): int
    {
        return $this->offset;
    }

    /**
     * Get the line number where this token appears.
     */
    public function getLine(): int
    {
        return $this->line;
    }

    /**
     * Get the column number where this token starts.
     */
    public function getColumn(): int
    {
        return $this->column;
    }

    /**
     * Check if this token is a comment (single-line or docblock).
     *
     * Checks both TokenType and Scope for maximum compatibility.
     */
    public function isComment(): bool
    {
        return $this->type->isComment()
            || Scope::Comment === $this->scope
            || Scope::CommentDocblock === $this->scope
            || Scope::CommentTask === $this->scope;
    }

    /**
     * Check if this token is whitespace.
     *
     * Checks both TokenType and Scope for maximum compatibility.
     */
    public function isWhitespace(): bool
    {
        return TokenType::Whitespace === $this->type
            || Scope::Whitespace === $this->scope;
    }

    /**
     * Check if this token is a string literal.
     *
     * Checks both TokenType and Scope for maximum compatibility.
     */
    public function isString(): bool
    {
        return TokenType::String === $this->type
            || Scope::String === $this->scope
            || Scope::StringInterpolated === $this->scope
            || Scope::StringTemplateExpression === $this->scope;
    }

    /**
     * Check if this token can be removed during minification.
     */
    public function isRemovable(): bool
    {
        return $this->type->isRemovable();
    }

    /**
     * Check if this token represents a definition (function, class, etc.).
     */
    public function isDefinition(): bool
    {
        return $this->type->isDefinition();
    }

    /**
     * Create a copy of this token with a different type.
     */
    public function withType(TokenType $type): self
    {
        return new self(
            $this->text,
            $this->scope,
            $type,
            $this->offset,
            $this->line,
            $this->column,
        );
    }

    /**
     * Create a copy of this token with a different scope.
     */
    public function withScope(Scope $scope): self
    {
        return new self(
            $this->text,
            $scope,
            $this->type,
            $this->offset,
            $this->line,
            $this->column,
        );
    }
}
