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

namespace Alto\Code\Highlight\Tests\Unit\Embedded;

use Alto\Code\Highlight\Embedded\EmbeddedLanguagePlan;
use Alto\Code\Highlight\Embedded\EmbeddedTrigger;
use Alto\Code\Highlight\Tests\TestCase;
use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(EmbeddedLanguagePlan::class)]
final class EmbeddedLanguagePlanTest extends TestCase
{
    public function testForHostCreatesPlan(): void
    {
        $triggers = [
            EmbeddedTrigger::tag('style', 'css'),
            EmbeddedTrigger::tag('script', 'javascript'),
        ];

        $plan = EmbeddedLanguagePlan::forHost('html', $triggers);

        self::assertInstanceOf(EmbeddedLanguagePlan::class, $plan);
        self::assertSame('html', $plan->hostLanguage);
        self::assertSame($triggers, $plan->getTriggers());
    }

    public function testHostLanguageIsNormalized(): void
    {
        $plan = EmbeddedLanguagePlan::forHost('HTML', []);

        self::assertSame('html', $plan->hostLanguage, 'Host language should be normalized to lowercase');
    }

    public function testGetTriggersReturnsTriggers(): void
    {
        $triggers = [
            EmbeddedTrigger::tag('style', 'css'),
            EmbeddedTrigger::tag('script', 'javascript'),
        ];

        $plan = EmbeddedLanguagePlan::forHost('html', $triggers);

        self::assertSame($triggers, $plan->getTriggers());
    }

    public function testFindTagTriggerFindsMatchingTag(): void
    {
        $cssTrigger = EmbeddedTrigger::tag('style', 'css');
        $jsTrigger = EmbeddedTrigger::tag('script', 'javascript');

        $plan = EmbeddedLanguagePlan::forHost('html', [$cssTrigger, $jsTrigger]);

        $found = $plan->findTagTrigger('script');

        self::assertSame($jsTrigger, $found);
    }

    public function testFindTagTriggerWithAttributes(): void
    {
        $trigger = EmbeddedTrigger::tag('script', 'typescript', ['type' => ['text/typescript', 'application/typescript']]);

        $plan = EmbeddedLanguagePlan::forHost('html', [$trigger]);

        // Should match when attribute value is in allowed list
        $found = $plan->findTagTrigger('script', ['type' => 'text/typescript']);
        self::assertSame($trigger, $found);

        // Should not match when attribute value is not in allowed list
        $notFound = $plan->findTagTrigger('script', ['type' => 'text/javascript']);
        self::assertNull($notFound);
    }

    public function testFindTagTriggerReturnsNullWhenNoMatch(): void
    {
        $plan = EmbeddedLanguagePlan::forHost('html', [
            EmbeddedTrigger::tag('style', 'css'),
        ]);

        $found = $plan->findTagTrigger('script');

        self::assertNull($found);
    }

    public function testFindBlockTriggerFindsMatchingBlock(): void
    {
        $cssTrigger = EmbeddedTrigger::block('css', 'css');
        $jsTrigger = EmbeddedTrigger::block('javascript', 'javascript');

        $plan = EmbeddedLanguagePlan::forHost('twig', [$cssTrigger, $jsTrigger]);

        $found = $plan->findBlockTrigger('javascript');

        self::assertSame($jsTrigger, $found);
    }

    public function testFindBlockTriggerReturnsNullWhenNoMatch(): void
    {
        $plan = EmbeddedLanguagePlan::forHost('twig', [
            EmbeddedTrigger::block('css', 'css'),
        ]);

        $found = $plan->findBlockTrigger('javascript');

        self::assertNull($found);
    }

    public function testEmptyTriggersList(): void
    {
        $plan = EmbeddedLanguagePlan::forHost('markdown', []);

        self::assertSame([], $plan->getTriggers());
        self::assertNull($plan->findTagTrigger('code'));
        self::assertNull($plan->findBlockTrigger('code'));
    }

    public function testCaseInsensitiveTagMatching(): void
    {
        $trigger = EmbeddedTrigger::tag('style', 'css');
        $plan = EmbeddedLanguagePlan::forHost('html', [$trigger]);

        // Should match regardless of case
        self::assertSame($trigger, $plan->findTagTrigger('STYLE'));
        self::assertSame($trigger, $plan->findTagTrigger('Style'));
        self::assertSame($trigger, $plan->findTagTrigger('style'));
    }

    public function testCaseInsensitiveBlockMatching(): void
    {
        $trigger = EmbeddedTrigger::block('css', 'css');
        $plan = EmbeddedLanguagePlan::forHost('twig', [$trigger]);

        // Should match regardless of case
        self::assertSame($trigger, $plan->findBlockTrigger('CSS'));
        self::assertSame($trigger, $plan->findBlockTrigger('Css'));
        self::assertSame($trigger, $plan->findBlockTrigger('css'));
    }
}
