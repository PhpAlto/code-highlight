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

namespace Alto\Code\Highlight\Tests\Unit\Embedded;

use Alto\Code\Highlight\Embedded\EmbeddedTrigger;
use Alto\Code\Highlight\Embedded\EmbeddedTriggerType;
use Alto\Code\Highlight\Tests\TestCase;
use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(EmbeddedTrigger::class)]
final class EmbeddedTriggerTest extends TestCase
{
    public function testTagCreatesTagTrigger(): void
    {
        $trigger = EmbeddedTrigger::tag('style', 'css');

        self::assertSame(EmbeddedTriggerType::Tag, $trigger->type);
        self::assertSame('css', $trigger->targetLanguage);
    }

    public function testTagNormalizesTagName(): void
    {
        $trigger = EmbeddedTrigger::tag('STYLE', 'css');

        $options = $trigger->getOptions();
        self::assertSame('style', $options['tagName']);
    }

    public function testTagNormalizesLanguage(): void
    {
        $trigger = EmbeddedTrigger::tag('style', 'CSS');

        self::assertSame('css', $trigger->targetLanguage);
    }

    public function testBlockCreatesBlockTrigger(): void
    {
        $trigger = EmbeddedTrigger::block('css', 'css');

        self::assertSame(EmbeddedTriggerType::Block, $trigger->type);
        self::assertSame('css', $trigger->targetLanguage);
    }

    public function testMatchesTagSimple(): void
    {
        $trigger = EmbeddedTrigger::tag('style', 'css');

        self::assertTrue($trigger->matchesTag('style'));
        self::assertTrue($trigger->matchesTag('STYLE')); // case insensitive
    }

    public function testMatchesTagWithAttributes(): void
    {
        $trigger = EmbeddedTrigger::tag('script', 'typescript', ['type' => ['text/typescript']]);

        self::assertTrue($trigger->matchesTag('script', ['type' => 'text/typescript']));
        self::assertFalse($trigger->matchesTag('script', [])); // missing required attribute
    }

    public function testMatchesTagAttributePresenceOnly(): void
    {
        $trigger = EmbeddedTrigger::tag('script', 'javascript', ['async' => null]);

        // null value = presence check
        self::assertTrue($trigger->matchesTag('script', ['async' => true]));
        self::assertTrue($trigger->matchesTag('script', ['async' => 'async']));
        self::assertTrue($trigger->matchesTag('script', ['async' => '']));
        self::assertFalse($trigger->matchesTag('script', [])); // attribute not present
    }

    public function testMatchesTagAttributeValueMatching(): void
    {
        $trigger = EmbeddedTrigger::tag('script', 'javascript', ['type' => ['module', 'importmap']]);

        self::assertTrue($trigger->matchesTag('script', ['type' => 'module']));
        self::assertTrue($trigger->matchesTag('script', ['type' => 'importmap']));
        self::assertFalse($trigger->matchesTag('script', ['type' => 'text/javascript']));
    }

    public function testMatchesTagCaseInsensitive(): void
    {
        $trigger = EmbeddedTrigger::tag('script', 'javascript', ['type' => ['module']]);

        // Tag name case insensitive
        self::assertTrue($trigger->matchesTag('SCRIPT', ['type' => 'module']));
        self::assertTrue($trigger->matchesTag('Script', ['type' => 'module']));

        // Attribute value case insensitive
        self::assertTrue($trigger->matchesTag('script', ['type' => 'MODULE']));
        self::assertTrue($trigger->matchesTag('script', ['type' => 'Module']));
    }

    public function testMatchesTagFailsWrongTag(): void
    {
        $trigger = EmbeddedTrigger::tag('style', 'css');

        self::assertFalse($trigger->matchesTag('script'));
    }

    public function testMatchesTagFailsMissingAttribute(): void
    {
        $trigger = EmbeddedTrigger::tag('script', 'javascript', ['type' => ['module']]);

        self::assertFalse($trigger->matchesTag('script', []));
        self::assertFalse($trigger->matchesTag('script', ['async' => true])); // wrong attribute
    }

    public function testMatchesTagFailsWrongAttributeValue(): void
    {
        $trigger = EmbeddedTrigger::tag('script', 'javascript', ['type' => ['module']]);

        self::assertFalse($trigger->matchesTag('script', ['type' => 'text/javascript']));
    }

    public function testMatchesBlockSimple(): void
    {
        $trigger = EmbeddedTrigger::block('css', 'css');

        self::assertTrue($trigger->matchesBlock('css'));
        self::assertTrue($trigger->matchesBlock('CSS')); // case insensitive
    }

    public function testMatchesBlockCaseInsensitive(): void
    {
        $trigger = EmbeddedTrigger::block('css', 'css');

        self::assertTrue($trigger->matchesBlock('CSS'));
        self::assertTrue($trigger->matchesBlock('Css'));
        self::assertTrue($trigger->matchesBlock('css'));
    }

    public function testMatchesBlockFailsWrongBlock(): void
    {
        $trigger = EmbeddedTrigger::block('css', 'css');

        self::assertFalse($trigger->matchesBlock('javascript'));
    }

    public function testGetAttributeConstraints(): void
    {
        $constraints = ['type' => ['module']];
        $trigger = EmbeddedTrigger::tag('script', 'javascript', $constraints);

        $result = $trigger->getAttributeConstraints();

        self::assertSame(['type' => ['module']], $result);
    }

    public function testGetOptions(): void
    {
        $trigger = EmbeddedTrigger::tag('style', 'css');

        $options = $trigger->getOptions();

        self::assertIsArray($options);
        self::assertArrayHasKey('tagName', $options);
        self::assertSame('style', $options['tagName']);
    }

    public function testTagTriggerDoesNotMatchBlock(): void
    {
        $trigger = EmbeddedTrigger::tag('style', 'css');

        self::assertFalse($trigger->matchesBlock('css'));
    }

    public function testBlockTriggerDoesNotMatchTag(): void
    {
        $trigger = EmbeddedTrigger::block('css', 'css');

        self::assertFalse($trigger->matchesTag('style'));
    }

    public function testMultipleAttributeConstraints(): void
    {
        $trigger = EmbeddedTrigger::tag('script', 'javascript', [
            'type' => ['module'],
            'async' => null,
        ]);

        // Both constraints must be satisfied
        self::assertTrue($trigger->matchesTag('script', ['type' => 'module', 'async' => true]));
        self::assertFalse($trigger->matchesTag('script', ['type' => 'module'])); // missing async
        self::assertFalse($trigger->matchesTag('script', ['async' => true])); // missing type
    }

    public function testAttributeConstraintNormalization(): void
    {
        $trigger = EmbeddedTrigger::tag('SCRIPT', 'javascript', [
            'TYPE' => ['Module', 'ImportMap'],
            'ASYNC' => null,
        ]);

        $constraints = $trigger->getAttributeConstraints();

        // Keys should be lowercase
        self::assertArrayHasKey('type', $constraints);
        self::assertArrayHasKey('async', $constraints);
        self::assertArrayNotHasKey('TYPE', $constraints);
        self::assertArrayNotHasKey('ASYNC', $constraints);

        // Values should be preserved in the array
        self::assertSame(['Module', 'ImportMap'], $constraints['type']);
        self::assertNull($constraints['async']);
    }
}
