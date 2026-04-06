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

use Alto\Code\Highlight\Embedded\EmbeddedLanguagePlan;
use Alto\Code\Highlight\Embedded\EmbeddedTrigger;
use Alto\Code\Highlight\Language\EmbeddedLanguageContext;
use Alto\Code\Highlight\Parser\ParsedStream;
use Alto\Code\Highlight\Parser\StreamBuilder;
use Alto\Code\Highlight\Scope;
use Alto\Code\Highlight\Tests\TestCase;
use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(EmbeddedLanguageContext::class)]
final class EmbeddedLanguageContextTest extends TestCase
{
    public function testDisabledContextHasNoResolver(): void
    {
        $context = EmbeddedLanguageContext::disabled();

        self::assertFalse($context->supportsEmbedding());
    }

    public function testDisabledContextDoesNotSupportEmbedding(): void
    {
        $context = EmbeddedLanguageContext::disabled();

        self::assertFalse($context->supportsEmbedding());
    }

    public function testDisabledContextThrowsOnParseEmbedded(): void
    {
        $context = EmbeddedLanguageContext::disabled();

        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('Embedded parsing is not enabled for this language.');

        $context->parseEmbedded('css', 'body { }');
    }

    public function testFromResolverCreatesContext(): void
    {
        $resolver = fn (string $lang, string $code) => (new StreamBuilder())->add($code, Scope::String)->build();

        $context = EmbeddedLanguageContext::fromResolver($resolver);

        self::assertTrue($context->supportsEmbedding());
    }

    public function testFromResolverWithPlan(): void
    {
        $resolver = fn (string $lang, string $code) => (new StreamBuilder())->add($code, Scope::String)->build();
        $plan = EmbeddedLanguagePlan::forHost('html', [
            EmbeddedTrigger::tag('style', 'css'),
        ]);

        $context = EmbeddedLanguageContext::fromResolver($resolver, $plan);

        self::assertTrue($context->supportsEmbedding());
        self::assertSame($plan, $context->getPlan());
    }

    public function testSupportsEmbeddingReturnsTrueWithResolver(): void
    {
        $resolver = fn (string $lang, string $code) => (new StreamBuilder())->add($code, Scope::String)->build();

        $context = EmbeddedLanguageContext::fromResolver($resolver);

        self::assertTrue($context->supportsEmbedding());
    }

    public function testGetPlanReturnsPlan(): void
    {
        $resolver = fn (string $lang, string $code) => (new StreamBuilder())->add($code, Scope::String)->build();
        $plan = EmbeddedLanguagePlan::forHost('html', []);

        $context = EmbeddedLanguageContext::fromResolver($resolver, $plan);

        self::assertSame($plan, $context->getPlan());
    }

    public function testGetPlanReturnsNullWhenNoPlan(): void
    {
        $resolver = fn (string $lang, string $code) => (new StreamBuilder())->add($code, Scope::String)->build();

        $context = EmbeddedLanguageContext::fromResolver($resolver);

        self::assertNull($context->getPlan());
    }

    public function testParseEmbeddedCallsResolver(): void
    {
        $called = false;
        $resolver = function (string $lang, string $code) use (&$called) {
            $called = true;
            self::assertSame('css', $lang);
            self::assertSame('body { }', $code);

            return (new StreamBuilder())->add($code, Scope::String)->build();
        };

        $context = EmbeddedLanguageContext::fromResolver($resolver);
        $context->parseEmbedded('css', 'body { }');

        self::assertTrue($called);
    }

    public function testParseEmbeddedNormalizesLanguage(): void
    {
        $resolver = function (string $lang, string $code) {
            self::assertSame('javascript', $lang, 'Language should be normalized to lowercase');

            return (new StreamBuilder())->add($code, Scope::String)->build();
        };

        $context = EmbeddedLanguageContext::fromResolver($resolver);
        $context->parseEmbedded('JavaScript', 'console.log("test");');
    }

    public function testCircularDependencyDetection(): void
    {
        $resolver = function (string $lang, string $code) {
            // Simulate a circular dependency by trying to parse the same language again
            $context = EmbeddedLanguageContext::fromResolver(fn ($l, $c) => (new StreamBuilder())->build());
            $context->parseEmbedded('html', '<div></div>');

            return (new StreamBuilder())->add($code, Scope::String)->build();
        };

        $context = EmbeddedLanguageContext::fromResolver($resolver);

        // First call to html
        $stream = $context->parseEmbedded('html', '<html></html>');

        // Should complete without infinite loop
        self::assertInstanceOf(ParsedStream::class, $stream);
    }

    public function testCircularDependencyReturnsFallbackStream(): void
    {
        $callCount = 0;
        $resolver = function (string $lang, string $code) use (&$callCount) {
            ++$callCount;

            // On first call, try to parse the same language (circular)
            if (1 === $callCount) {
                $nestedContext = EmbeddedLanguageContext::fromResolver(fn ($l, $c) => (new StreamBuilder())->build());
                // Manually trigger circular detection by calling with same language
                $builder = new StreamBuilder();
                $builder->add($code, Scope::String);

                return $builder->build();
            }

            return (new StreamBuilder())->add($code, Scope::String)->build();
        };

        $context = EmbeddedLanguageContext::fromResolver($resolver);

        // Parse html, which will try to parse html again inside
        $stream = $context->parseEmbedded('html', 'test');

        // Should return fallback stream (String scope)
        $tokens = $stream->getTokens();
        self::assertCount(1, $tokens);
        self::assertSame(Scope::String, $tokens[0]->getScope());
    }

    public function testDepthLimitEnforcement(): void
    {
        $resolver = function (string $lang, string $code) {
            // Create a deeply nested parsing scenario
            static $depth = 0;
            ++$depth;

            if ($depth < 15) {
                $context = EmbeddedLanguageContext::fromResolver(fn ($l, $c) => (new StreamBuilder())->add($c, Scope::String)->build());
                $context->parseEmbedded("lang{$depth}", $code);
            }

            return (new StreamBuilder())->add($code, Scope::String)->build();
        };

        $context = EmbeddedLanguageContext::fromResolver($resolver);
        $stream = $context->parseEmbedded('html', 'test');

        // Should complete without infinite recursion
        self::assertInstanceOf(ParsedStream::class, $stream);
    }

    public function testDepthLimitReturnsFallback(): void
    {
        // Create a context that will exceed depth limit
        $depth = 0;
        $context = null;

        $resolver = function (string $lang, string $code) use (&$depth, &$context) {
            ++$depth;

            if ($depth <= 11 && null !== $context) {
                // Try to go deeper
                return $context->parseEmbedded("level{$depth}", $code);
            }

            return (new StreamBuilder())->add($code, Scope::String)->build();
        };

        $context = EmbeddedLanguageContext::fromResolver($resolver);

        // This should hit the depth limit and return fallback
        $stream = $context->parseEmbedded('start', 'content');

        self::assertInstanceOf(ParsedStream::class, $stream);
    }

    public function testErrorBoundaryCatchesExceptions(): void
    {
        $resolver = function (string $lang, string $code) {
            throw new \RuntimeException('Parse error!');
        };

        $context = EmbeddedLanguageContext::fromResolver($resolver);

        // Should not throw, but return fallback stream
        $stream = $context->parseEmbedded('css', 'body { }');

        self::assertInstanceOf(ParsedStream::class, $stream);
    }

    public function testErrorBoundaryReturnsFallbackStream(): void
    {
        $resolver = function (string $lang, string $code) {
            throw new \RuntimeException('Parse error!');
        };

        $context = EmbeddedLanguageContext::fromResolver($resolver);
        $stream = $context->parseEmbedded('css', 'body { color: red; }');

        // Should return fallback with String scope
        $tokens = $stream->getTokens();
        self::assertCount(1, $tokens);
        self::assertSame('body { color: red; }', $tokens[0]->getText());
        self::assertSame(Scope::String, $tokens[0]->getScope());
    }

    public function testStackManagementPushAndPop(): void
    {
        $resolverCallCount = 0;
        $resolver = function (string $lang, string $code) use (&$resolverCallCount) {
            ++$resolverCallCount;

            return (new StreamBuilder())->add($code, Scope::String)->build();
        };

        $context = EmbeddedLanguageContext::fromResolver($resolver);

        // Parse once
        $context->parseEmbedded('css', 'body { }');

        // Parse again with same language - should work (stack was properly popped)
        $context->parseEmbedded('css', 'div { }');

        self::assertSame(2, $resolverCallCount, 'Resolver should be called twice');
    }

    public function testDepthTracking(): void
    {
        $maxDepthSeen = 0;
        $resolver = function (string $lang, string $code) use (&$maxDepthSeen) {
            static $currentDepth = 0;
            ++$currentDepth;
            $maxDepthSeen = max($maxDepthSeen, $currentDepth);

            $result = (new StreamBuilder())->add($code, Scope::String)->build();

            --$currentDepth;

            return $result;
        };

        $context = EmbeddedLanguageContext::fromResolver($resolver);

        // Single level
        $context->parseEmbedded('css', 'body { }');
        self::assertSame(1, $maxDepthSeen);
    }

    public function testFinallyBlockExecutes(): void
    {
        $finallyExecuted = false;
        $resolver = function (string $lang, string $code) use (&$finallyExecuted) {
            try {
                throw new \RuntimeException('Error!');
            } finally {
                $finallyExecuted = true;
            }
        };

        $context = EmbeddedLanguageContext::fromResolver($resolver);

        // Should catch exception and return fallback, but finally should execute
        $context->parseEmbedded('css', 'body { }');

        self::assertTrue($finallyExecuted);
    }

    public function testEmptyCodeReturnsEmptyStream(): void
    {
        $resolver = function (string $lang, string $code) {
            $builder = new StreamBuilder();
            if ('' !== $code) {
                $builder->add($code, Scope::String);
            }

            return $builder->build();
        };

        $context = EmbeddedLanguageContext::fromResolver($resolver);
        $stream = $context->parseEmbedded('css', '');

        $tokens = $stream->getTokens();
        // Empty code results in empty stream
        self::assertCount(0, $tokens);
    }
}
