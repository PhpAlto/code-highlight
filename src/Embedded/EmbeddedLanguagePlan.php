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

namespace Alto\Code\Highlight\Embedded;

/**
 * Declarative description of embedded-language triggers for a host language.
 *
 * @author Simon André <smn.andre@gmail.com>
 */
final class EmbeddedLanguagePlan
{
    /**
     * @param list<EmbeddedTrigger> $triggers
     */
    private function __construct(
        public readonly string $hostLanguage,
        private readonly array $triggers,
    ) {
    }

    /**
     * @param list<EmbeddedTrigger> $triggers
     */
    public static function forHost(string $hostLanguage, array $triggers): self
    {
        return new self(strtolower($hostLanguage), $triggers);
    }

    /**
     * @return list<EmbeddedTrigger>
     */
    public function getTriggers(): array
    {
        return $this->triggers;
    }

    /**
     * @param array<string, string|true> $attributes
     */
    public function findTagTrigger(string $tagName, array $attributes = []): ?EmbeddedTrigger
    {
        foreach ($this->triggers as $trigger) {
            if ($trigger->matchesTag($tagName, $attributes)) {
                return $trigger;
            }
        }

        return null;
    }

    public function findBlockTrigger(string $blockName): ?EmbeddedTrigger
    {
        foreach ($this->triggers as $trigger) {
            if ($trigger->matchesBlock($blockName)) {
                return $trigger;
            }
        }

        return null;
    }
}
