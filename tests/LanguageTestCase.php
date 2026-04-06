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

namespace Alto\Code\Highlight\Tests;

use Alto\Code\Highlight\Highlighter;
use Alto\Code\Highlight\Theme\AltoTheme;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

/**
 * Base test case for language fixture testing.
 *
 * Automatically discovers and tests language fixtures from tests/Language/[language]/ directory.
 *
 * Fixture Naming Conventions:
 * - Code file: name.ext (e.g., basic.sh)
 * - Expected HTML: name.ext.html (e.g., basic.sh.html)
 * - Embedded language: name+embed.ext (e.g., php+twig.twig)
 * - Embedded HTML: name+embed.ext.html (e.g., php+twig.twig.html)
 *
 * Example directory structure:
 *   tests/Language/bash/
 *   ├── basic.sh
 *   ├── basic.sh.html
 *   ├── bash+heredoc.sh
 *   └── bash+heredoc.sh.html
 */
abstract class LanguageTestCase extends TestCase
{
    protected Highlighter $highlighter;

    /**
     * Language identifier (e.g., 'bash', 'json', 'php').
     * Must be set by subclasses.
     */
    protected string $language = '';

    /**
     * File extensions for this language (e.g., ['sh'], ['json'], ['yml', 'yaml']).
     * Returned by getFileExtensions().
     */

    /**
     * Primary language class to highlight with.
     * Must be set by subclasses.
     */
    protected string $languageClass = '';

    protected function setUp(): void
    {
        $this->highlighter = new Highlighter(new AltoTheme());

        // Register primary language
        if ($this->languageClass) {
            $class = $this->languageClass;
            /** @var \Alto\Code\Highlight\Language\LanguageInterface $languageInstance */
            $languageInstance = new $class();
            $this->highlighter->registerLanguage($languageInstance);
        }
    }

    /**
     * Get the fixtures directory for this language.
     */
    protected function getFixturesDirectory(): string
    {
        return __DIR__.'/Language/'.$this->language;
    }

    /**
     * Discover all fixture files for this language.
     *
     * Returns array of fixture filenames:
     * ['basic.sh', 'variables.sh', 'config.yaml', 'config.yml', ...]
     *
     * @return array<string>
     */
    protected function discoverFixtures(): array
    {
        $dir = $this->getFixturesDirectory();

        if (!is_dir($dir)) {
            return [];
        }

        $fixtures = [];
        $files = scandir($dir);
        $extensions = $this->getFileExtensions();

        if (false === $files) {
            return [];
        }

        foreach ($files as $file) {
            // Skip hidden files
            if (str_starts_with($file, '.')) {
                continue;
            }

            // Check if it's an expected HTML result (ends with .html)
            // But we must be careful: if we have "basic.sh.html", str_ends_with($file, '.html') is true.
            // A code file "basic.html" also ends with .html.
            // We need to check if it matches {something}.{valid_ext}.html
            $isExpectedHtml = false;
            foreach ($extensions as $ext) {
                if (str_ends_with($file, ".{$ext}.html")) {
                    $isExpectedHtml = true;
                    break;
                }
            }
            if ($isExpectedHtml) {
                continue;
            }

            // Check if file has a correct extension
            $hasValidExtension = false;
            foreach ($extensions as $ext) {
                if (str_ends_with($file, ".{$ext}")) {
                    $hasValidExtension = true;
                    break;
                }
            }

            if (!$hasValidExtension) {
                continue;
            }

            // Use the full filename as the fixture identifier
            if (!in_array($file, $fixtures, true)) {
                $fixtures[] = $file;
            }
        }

        sort($fixtures);

        return $fixtures;
    }

    /**
     * Get code file path for a fixture.
     *
     * @param string $fixture Fixture filename (e.g., 'basic.sh')
     *
     * @return string Path to code file
     */
    protected function getFixtureCodePath(string $fixture): string
    {
        return $this->getFixturesDirectory().'/'.$fixture;
    }

    /**
     * Get expected HTML file path for a fixture.
     *
     * @param string $fixture Fixture filename (e.g., 'basic.sh')
     *
     * @return string Path to expected HTML file
     */
    protected function getFixtureHtmlPath(string $fixture): string
    {
        return $this->getFixtureCodePath($fixture).'.html';
    }

    /**
     * Get embedded language(s) from fixture name.
     *
     * Fixture name format: name+embed1+embed2.ext
     * Examples:
     *   - 'basic.sh' -> []
     *   - 'php+twig.twig' -> ['twig']
     *
     * @param string $fixture Fixture filename
     *
     * @return array<string> Embedded language identifiers
     */
    protected function getEmbeddedLanguages(string $fixture): array
    {
        // Remove extension
        $name = $fixture;
        foreach ($this->getFileExtensions() as $ext) {
            if (str_ends_with($name, '.'.$ext)) {
                $name = substr($name, 0, -(strlen($ext) + 1));
                break;
            }
        }

        if (false === strpos($name, '+')) {
            return [];
        }

        $parts = explode('+', $name);

        // First part is the base name, rest are embeds
        return array_slice($parts, 1);
    }

    /**
     * Get the language identifier to use for highlighting.
     *
     * By default, uses the same as the fixture language.
     * Can be overridden in subclasses for cases where fixtures are organized
     * differently from how they're highlighted (e.g., SVG fixtures using HTML highlighter).
     */
    protected function getHighlightLanguageIdentifier(): string
    {
        return $this->language;
    }

    /**
     * Highlight code and return HTML.
     *
     * @param string $code Source code to highlight
     *
     * @return string HTML output
     */
    protected function highlightCode(string $code): string
    {
        return $this->highlighter->highlight($code, $this->getHighlightLanguageIdentifier(), lineNumbers: false);
    }

    /**
     * Normalize HTML for comparison.
     *
     * Removes extra whitespace and normalizes line endings.
     */
    protected function normalizeHtml(string $html): string
    {
        // Normalize line endings
        $html = str_replace("\r\n", "\n", $html);

        // Remove trailing whitespace on each line
        $lines = explode("\n", $html);
        $lines = array_map('rtrim', $lines);
        $html = implode("\n", $lines);

        // Trim overall
        return trim($html);
    }

    /**
     * Test a single fixture by name.
     *
     * This method can be called by parametrized tests or manually.
     * It highlights the code file and compares with expected HTML.
     *
     * @param string $fixture Fixture filename (e.g., 'basic.sh')
     */
    protected function testFixture(string $fixture): void
    {
        $codePath = $this->getFixtureCodePath($fixture);
        $htmlPath = $this->getFixtureHtmlPath($fixture);

        $this->assertFileExists($codePath, "Fixture code file not found: {$codePath}");
        $this->assertFileExists($htmlPath, "Expected HTML file not found: {$htmlPath}");

        // Load fixture files
        $code = file_get_contents($codePath);
        $expectedHtml = file_get_contents($htmlPath);

        $this->assertIsString($code);
        $this->assertIsString($expectedHtml);

        // Highlight code
        $actualHtml = $this->highlightCode($code);

        // Normalize for comparison
        $expectedHtml = $this->normalizeHtml($expectedHtml);
        $actualHtml = $this->normalizeHtml($actualHtml);

        // Compare
        $this->assertSame(
            $expectedHtml,
            $actualHtml,
            "HTML output does not match for fixture: {$fixture}\n".
            "Code file: {$codePath}\n".
            "Expected HTML: {$htmlPath}"
        );
    }

    /**
     * Get language identifier (override in subclass).
     */
    abstract protected function getLanguageIdentifier(): string;

    /**
     * Get file extensions for this language (override in subclass).
     *
     * @return array<string>
     */
    abstract protected function getFileExtensions(): array;

    /**
     * Generate test methods for all discovered fixtures.
     *
     * This is called by PHPUnit's testcase discovery to create dynamic tests.
     * Each fixture becomes a test method named testFixture[FixtureName].
     *
     * @return array<int, array<int, string>> Data provider array for parametrized tests
     */
    public static function fixtureProvider(): array
    {
        // We need to instantiate the class to get language/extension info
        // This is a bit of a workaround but necessary for PHPUnit 12.x
        $testClass = static::class;
        $instance = new $testClass('testLanguageFixtures');

        $language = $instance->getLanguageIdentifier();
        $extensions = $instance->getFileExtensions();

        if (empty($language) || empty($extensions)) {
            return [];
        }

        // Build fixtures directory dynamically
        $fixturesDir = __DIR__.'/Language/'.$language;

        if (!is_dir($fixturesDir)) {
            return [];
        }

        $fixtures = [];
        $files = scandir($fixturesDir);

        if (false === $files) {
            return [];
        }

        foreach ($files as $file) {
            // Skip hidden files
            if (str_starts_with($file, '.')) {
                continue;
            }

            // Check if it's an expected HTML result (ends with .html)
            $isExpectedHtml = false;
            foreach ($extensions as $ext) {
                if (str_ends_with($file, ".{$ext}.html")) {
                    $isExpectedHtml = true;
                    break;
                }
            }
            if ($isExpectedHtml) {
                continue;
            }

            // Check if file has a correct extension
            $hasValidExtension = false;
            foreach ($extensions as $ext) {
                if (str_ends_with($file, ".{$ext}")) {
                    $hasValidExtension = true;
                    break;
                }
            }

            if (!$hasValidExtension) {
                continue;
            }

            // Use the full filename as the fixture identifier
            if (!in_array($file, $fixtures, true)) {
                $fixtures[] = $file;
            }
        }

        sort($fixtures);

        return array_map(fn ($fixture) => [$fixture], $fixtures);
    }

    /**
     * Parametrized test for all discovered fixtures.
     */
    #[DataProvider('fixtureProvider')]
    public function testLanguageFixtures(string $fixture): void
    {
        $this->testFixture($fixture);
    }

    /**
     * Display fixture loading information (for debugging).
     */
    protected function debugFixtureInfo(): void
    {
        $fixtures = $this->discoverFixtures();

        echo "\n--- Language: {$this->language} ---\n";
        echo 'Directory: '.$this->getFixturesDirectory()."\n";
        echo 'Fixtures found: '.count($fixtures)."\n";

        foreach ($fixtures as $fixture) {
            $embeds = $this->getEmbeddedLanguages($fixture);
            $embedStr = !empty($embeds) ? ' (+'.implode('+', $embeds).')' : '';
            echo "  • {$fixture}{$embedStr}\n";
        }
    }
}
