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
 * @author Simon André <smn.andre@gmail.com>
 */
final class EmbeddedLanguageRegistry
{
    /** @var array<string, EmbeddedLanguagePlan> */
    private array $plans = [];

    /**
     * @param string|array<mixed>|null $config Configuration path or array definitions
     */
    public function __construct(string|array|null $config = null)
    {
        if (is_array($config)) {
            $this->loadFromDefinitions($config);
        } elseif (null === $config) {
            $this->loadFromDefinitions(self::getDefaultPlans());
        } else {
            $this->loadPlans($config);
        }
    }

    /**
     * Get default embedded language plans.
     *
     * This is the built-in configuration that ships with the library.
     *
     * @return list<EmbeddedLanguagePlan>
     */
    public static function getDefaultPlans(): array
    {
        return [
            // HTML: Embedded CSS and JavaScript in tags
            EmbeddedLanguagePlan::forHost('html', [
                EmbeddedTrigger::tag('style', 'css'),
                EmbeddedTrigger::tag('script', 'javascript'),
            ]),

            // SVG: Same as HTML (can contain style and script tags)
            EmbeddedLanguagePlan::forHost('svg', [
                EmbeddedTrigger::tag('style', 'css'),
                EmbeddedTrigger::tag('script', 'javascript'),
            ]),

            // Markdown: Fenced code blocks
            // The language identifier is passed directly from the fence (```language)
            // No triggers needed - MarkdownLanguage handles this inline
            EmbeddedLanguagePlan::forHost('markdown', [
                // Empty for now - Markdown uses dynamic language resolution from fence markers
            ]),

            // Twig: Block-based embedding (handled by TwigLanguage directly)
            // Example: {% block css %} → CSS content
            // Empty for now - Twig uses dynamic block name resolution
            EmbeddedLanguagePlan::forHost('twig', [
                // Empty for now - Twig uses block names for language resolution
            ]),
        ];
    }

    public function getPlan(string $hostLanguage): ?EmbeddedLanguagePlan
    {
        return $this->plans[strtolower($hostLanguage)] ?? null;
    }

    private function loadPlans(string $path): void
    {
        if (!is_file($path)) {
            return;
        }

        $definitions = require $path;

        if (!is_array($definitions)) {
            throw new \RuntimeException('Embedded language configuration must return an array.');
        }

        $this->loadFromDefinitions($definitions);
    }

    /**
     * @param array<mixed> $definitions
     */
    private function loadFromDefinitions(array $definitions): void
    {
        foreach ($definitions as $definition) {
            if ($definition instanceof EmbeddedLanguagePlan) {
                $plan = $definition;
            } elseif (is_array($definition)) {
                /** @var array{host?: string, triggers?: list<EmbeddedTrigger>} $definition */
                $plan = $this->planFromArray($definition);
            } else {
                throw new \RuntimeException('Invalid embedded language plan definition.');
            }

            $this->plans[$plan->hostLanguage] = $plan;
        }
    }

    /**
     * @param array{host?:string,triggers?:list<EmbeddedTrigger>} $definition
     */
    private function planFromArray(array $definition): EmbeddedLanguagePlan
    {
        if (!isset($definition['host'], $definition['triggers'])) {
            throw new \RuntimeException('Embedded plan array definitions must specify host and triggers.');
        }

        return EmbeddedLanguagePlan::forHost($definition['host'], $definition['triggers']);
    }
}
