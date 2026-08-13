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

namespace Alto\Code\Highlight\Language;

use Alto\Code\Highlight\Embedded\EmbeddedLanguagePlan;
use Alto\Code\Highlight\Parser\ParsedStream;
use Alto\Code\Highlight\Parser\StreamBuilder;
use Alto\Code\Highlight\Scope;

/**
 * Provides embeddable languages with access to other registered languages.
 *
 * Includes safety features:
 * - Circular dependency detection (prevents infinite loops)
 * - Error boundaries (graceful fallback on parse failures)
 * - Nesting depth limits (prevents excessive recursion)
 *
 * @author Simon André <smn.andre@gmail.com>
 */
final class EmbeddedLanguageContext
{
    private const MAX_EMBEDDING_DEPTH = 10;

    /**
     * @var list<string> Stack of currently embedding languages
     */
    private array $embeddingStack = [];

    private int $currentDepth = 0;

    /**
     * @param \Closure(string,string):ParsedStream|null $resolver
     */
    private function __construct(
        private readonly ?\Closure $resolver,
        private readonly ?EmbeddedLanguagePlan $plan,
    ) {}

    /**
     * Create a context that disables embedding entirely.
     */
    public static function disabled(): self
    {
        return new self(null, null);
    }

    /**
     * Create a context backed by the given resolver.
     *
     * @param callable(string,string):ParsedStream $resolver
     */
    public static function fromResolver(callable $resolver, ?EmbeddedLanguagePlan $plan = null): self
    {
        return new self(\Closure::fromCallable($resolver), $plan);
    }

    public function supportsEmbedding(): bool
    {
        return null !== $this->resolver;
    }

    public function getPlan(): ?EmbeddedLanguagePlan
    {
        return $this->plan;
    }

    /**
     * Parse embedded content with safety features.
     *
     * Safety features:
     * - Circular dependency detection: Prevents A → B → A loops
     * - Depth limits: Prevents excessive nesting
     * - Error boundaries: Falls back to plaintext on parse failures
     *
     * @throws \RuntimeException if embedding is disabled
     */
    public function parseEmbedded(string $language, string $code): ParsedStream
    {
        if (null === $this->resolver) {
            throw new \RuntimeException('Embedded parsing is not enabled for this language.');
        }

        // Normalize language identifier
        $language = strtolower(trim($language));

        // Check for circular dependencies
        if (in_array($language, $this->embeddingStack, true)) {
            return $this->createFallbackStream(
                $code,
                "Circular embedding detected: {$language} is already in the embedding stack",
            );
        }

        // Check depth limit
        if ($this->currentDepth >= self::MAX_EMBEDDING_DEPTH) {
            return $this->createFallbackStream(
                $code,
                'Maximum embedding depth ({self::MAX_EMBEDDING_DEPTH}) exceeded',
            );
        }

        // Push language onto stack and increment depth
        $this->embeddingStack[] = $language;
        ++$this->currentDepth;

        try {
            // Attempt to parse with the embedded language
            $result = ($this->resolver)($language, $code);

            return $result;
        } catch (\Throwable $e) {
            // Error boundary: Fall back to plaintext on any parse failure
            return $this->createFallbackStream($code, $e->getMessage());
        } finally {
            // Always pop from stack and decrement depth
            array_pop($this->embeddingStack);
            --$this->currentDepth;
        }
    }

    /**
     * Create a fallback stream for failed embedded parsing.
     *
     * Treats content as plaintext/code (Scope::String) to maintain visibility.
     */
    private function createFallbackStream(string $code, string $reason = ''): ParsedStream
    {
        $builder = new StreamBuilder();

        // Add the content as a code/string block (keeps it visible but unstyled)
        if ('' !== $code) {
            $builder->add($code, Scope::String);
        }

        return $builder->build();
    }
}
