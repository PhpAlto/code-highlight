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

namespace Alto\Code\Highlight\Tests\Unit;

use Alto\Code\Highlight\Language\Languages;
use Alto\Code\Highlight\Scope;
use PHPUnit\Framework\Attributes\CoversNothing;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

/**
 * Validates the highlighter output against Pygments (de-facto reference for syntax highlighting).
 *
 * This test compares token classifications produced by our library with those from Pygments.
 * It maps both libraries' scope/token types to a common set of categories and checks that
 * they agree on fundamental token classifications (keywords, strings, comments, numbers, etc.).
 *
 * Requirements:
 *   - Python 3 with Pygments installed (pip install pygments)
 *   - The bin/pygments-tokenize script
 *
 * @see bin/pygments-tokenize
 */
#[CoversNothing]
final class PygmentsComparisonTest extends TestCase
{
    private static string $pygmentsScript;

    private static ?string $pythonCommand = null;

    /**
     * Map of Alto Scope → simplified category for comparison.
     */
    private const array SCOPE_TO_CATEGORY = [
        // Comments
        'comment' => 'comment',
        'comment.docblock' => 'comment',
        'comment.task' => 'comment',

        // Punctuation / Operators
        'punctuation' => 'punctuation',
        'operator' => 'operator',

        // Keywords
        'keyword' => 'keyword',
        'keyword.declaration' => 'keyword',
        'keyword.operator' => 'keyword',
        'keyword.control' => 'keyword',
        'storage.modifier' => 'keyword',

        // Strings
        'string' => 'string',
        'string.interpolated' => 'string',
        'string.template.expression' => 'string',

        // Numbers
        'number' => 'number',

        // Booleans / Null / Constants
        'boolean' => 'constant',
        'null' => 'constant',
        'constant' => 'constant',
        'constant.builtin' => 'constant',
        'regexp' => 'regexp',

        // Variables
        'variable' => 'variable',
        'variable.parameter' => 'variable',
        'variable.property' => 'variable',
        'variable.this' => 'variable',

        // Types / Namespaces / Functions
        'namespace' => 'namespace',
        'type.definition' => 'type',
        'type.reference' => 'type',
        'type.builtin' => 'type',
        'function.definition' => 'function',
        'function.call' => 'function',
        'function.builtin' => 'function',
        'enum.case' => 'constant',

        // Attributes
        'attribute.name' => 'attribute',
        'attribute.value' => 'attribute',

        // Markup
        'tag.name' => 'tag',
        'tag.attribute.name' => 'attribute',
        'tag.attribute.value' => 'string',
        'markup.text' => 'other',
        'section.name' => 'section',

        // Diff
        'diff.added' => 'diff.added',
        'diff.removed' => 'diff.removed',
        'diff.changed' => 'diff.changed',
        'meta' => 'other',
        'diagnostic.error' => 'other',
        'diagnostic.warning' => 'other',
        'diagnostic.info' => 'other',

        // Support
        'support.type' => 'type',
        'support.function' => 'function',
        'support.constant' => 'constant',

        // Internal
        'whitespace' => 'whitespace',
    ];

    /**
     * Tokens that may legitimately differ between highlighters.
     */
    private const array EQUIVALENT_CATEGORIES = [
        ['constant', 'keyword'],
        ['function', 'variable'],
        ['type', 'constant'],
        ['type', 'keyword'],
        ['function', 'keyword'],
        ['attribute', 'property'],
        ['attribute', 'constant'],
        ['attribute', 'variable'],
        ['attribute', 'keyword'],
        ['tag', 'keyword'],
        ['punctuation', 'operator'],
        ['punctuation', 'comment'],
        ['operator', 'keyword'],
        ['other', 'string'],
    ];

    /**
     * Code samples for each language, designed to exercise common constructs.
     */
    private static function getLanguageSamples(): array
    {
        return [
            'php' => '<?php
function greet($name) {
    return "Hello, " . $name;
}
$x = 42;
// comment
if ($x > 10) {
    echo $x;
}',
            'javascript' => 'function greet(name) {
    const message = "Hello, " + name;
    return message;
}
// comment
let x = 42;
if (x > 10) {
    console.log(x);
}',
            'python' => 'def greet(name):
    """Docstring"""
    return "Hello, " + name

# comment
x = 42
if x > 10:
    print(x)',
            'go' => 'package main

import "fmt"

func greet(name string) string {
    return "Hello, " + name
}
// comment
var x = 42',
            'rust' => 'fn greet(name: &str) -> String {
    format!("Hello, {}", name)
}
// comment
let x: i32 = 42;',
            'ruby' => 'def greet(name)
    "Hello, " + name
end
# comment
x = 42',
            'java' => 'public class Main {
    public static String greet(String name) {
        return "Hello, " + name;
    }
    // comment
    int x = 42;
}',
            'csharp' => 'public class Main {
    public static string Greet(string name) {
        return "Hello, " + name;
    }
    // comment
    int x = 42;
}',
            'swift' => 'func greet(name: String) -> String {
    return "Hello, " + name
}
// comment
var x = 42',
            'bash' => '#!/bin/bash
greet() {
    echo "Hello, $1"
}
# comment
x=42',
            'sql' => "SELECT name, age FROM users WHERE age > 18 AND name = 'John';
-- comment",
            'css' => 'body {
    color: red;
    font-size: 14px;
}
/* comment */',
            'json' => '{"name": "John", "age": 30, "active": true}',
            'yaml' => "name: John\nage: 30\nactive: true\n# comment",
            'typescript' => 'function greet(name: string): string {
    return "Hello, " + name;
}
// comment
const x: number = 42;',
            'xml' => '<?xml version="1.0"?>
<root>
    <item id="1">Hello</item>
</root>',
            'html' => '<div class="greeting">
    <p>Hello</p>
</div>',
            'markdown' => "# Heading\n\nSome **bold** text and `code`.\n\n- item 1\n- item 2",
            'diff' => "--- a/file.txt\n+++ b/file.txt\n@@ -1,3 +1,3 @@\n-old line\n+new line\n context",
            'ini' => "[section]\nkey = value\n; comment",
            'dockerfile' => "FROM ubuntu:22.04\nRUN apt-get update\nCOPY . /app\nCMD [\"python\", \"app.py\"]",
            'makefile' => "all: build\n\nbuild:\n\tgcc -o main main.c\n\n# comment",
        ];
    }

    public static function setUpBeforeClass(): void
    {
        self::$pygmentsScript = dirname(__DIR__, 2).'/bin/pygments-tokenize';
    }

    private static function isPygmentsAvailable(): bool
    {
        if (null !== self::$pythonCommand) {
            return true;
        }

        if (!function_exists('proc_open')) {
            return false;
        }

        foreach (['python3', 'python'] as $cmd) {
            $process = @proc_open(
                [$cmd, '-c', 'import pygments'],
                [1 => ['pipe', 'w'], 2 => ['pipe', 'w']],
                $pipes
            );

            if (is_resource($process)) {
                fclose($pipes[1]);
                fclose($pipes[2]);
                if (0 === proc_close($process)) {
                    self::$pythonCommand = $cmd;

                    return true;
                }
            }
        }

        return false;
    }

    private static function getPygmentsTokens(string $language, string $code): array
    {
        if (!self::isPygmentsAvailable()) {
            self::fail('Pygments is not available');
        }

        $process = proc_open(
            [self::$pythonCommand, self::$pygmentsScript, $language],
            [
                0 => ['pipe', 'r'],
                1 => ['pipe', 'w'],
                2 => ['pipe', 'w'],
            ],
            $pipes,
        );

        if (!is_resource($process)) {
            self::fail('Failed to start Pygments process');
        }

        fwrite($pipes[0], $code);
        fclose($pipes[0]);

        $output = stream_get_contents($pipes[1]);
        $errorOutput = stream_get_contents($pipes[2]);
        fclose($pipes[1]);
        fclose($pipes[2]);

        $exitCode = proc_close($process);

        $result = json_decode((string) $output, true);
        if (!is_array($result) || isset($result['error'])) {
            $message = $result['error'] ?? 'unknown error (no JSON output)';
            if (!is_array($result)) {
                $message .= sprintf(
                    "\nOutput: %s\nStderr: %s\nExit Code: %d\nCommand: %s %s %s",
                    $output ?: '(empty)',
                    $errorOutput ?: '(empty)',
                    $exitCode,
                    self::$pythonCommand,
                    self::$pygmentsScript,
                    $language
                );
            }
            self::fail('Pygments error: '.$message);
        }

        return $result['tokens'];
    }

    private static function getAltoTokens(string $language, string $code): array
    {
        $languages = Languages::getDefaultLanguages();
        $languageInstance = null;

        foreach ($languages as $lang) {
            if ($lang->getIdentifier() === $language) {
                $languageInstance = $lang;
                break;
            }
        }

        if (null === $languageInstance) {
            self::fail("Language not found: {$language}");
        }

        $stream = $languageInstance->parse($code);
        $tokens = [];

        foreach ($stream->getTokens() as $token) {
            $scope = $token->getScope();
            if (Scope::Whitespace === $scope) {
                continue;
            }

            $category = self::SCOPE_TO_CATEGORY[$scope->value] ?? 'other';

            $tokens[] = [
                'text' => $token->getText(),
                'scope' => $scope->value,
                'category' => $category,
            ];
        }

        return $tokens;
    }

    private static function areCategoriesEquivalent(string $a, string $b): bool
    {
        if ($a === $b) {
            return true;
        }

        foreach (self::EQUIVALENT_CATEGORIES as [$cat1, $cat2]) {
            if (($a === $cat1 && $b === $cat2) || ($a === $cat2 && $b === $cat1)) {
                return true;
            }
        }

        return false;
    }

    public static function languageProvider(): array
    {
        $samples = self::getLanguageSamples();

        return array_combine(
            array_keys($samples),
            array_map(fn ($lang) => [$lang], array_keys($samples)),
        );
    }

    #[DataProvider('languageProvider')]
    public function testKeyTokensMatchPygments(string $language): void
    {
        if (!self::isPygmentsAvailable()) {
            self::markTestSkipped('Pygments is not installed (pip install pygments)');
        }

        $samples = self::getLanguageSamples();
        $code = $samples[$language];

        $altoTokens = self::getAltoTokens($language, $code);
        $pygmentsTokens = self::getPygmentsTokens($language, $code);

        $pygmentsMap = [];
        $significantCategories = ['keyword', 'string', 'comment', 'number', 'operator'];
        foreach ($pygmentsTokens as $token) {
            if (in_array($token['category'], $significantCategories, true)) {
                $text = trim($token['text']);
                if ('' !== $text) {
                    $pygmentsMap[$text] = $token['category'];
                }
            }
        }

        $mismatches = [];
        foreach ($altoTokens as $token) {
            $text = trim($token['text']);
            if ('' === $text || !isset($pygmentsMap[$text])) {
                continue;
            }

            $altoCategory = $token['category'];
            $pygmentsCategory = $pygmentsMap[$text];

            if (!self::areCategoriesEquivalent($altoCategory, $pygmentsCategory)) {
                $mismatches[] = sprintf(
                    '  Token "%s": Alto=%s (%s), Pygments=%s',
                    $text,
                    $altoCategory,
                    $token['scope'],
                    $pygmentsCategory,
                );
            }
        }

        $altoTexts = array_map(fn ($t) => trim($t['text']), $altoTokens);
        $missingFromAlto = [];
        foreach ($pygmentsMap as $text => $category) {
            $text = (string) $text;
            if (in_array($text, $altoTexts, true)) {
                continue;
            }

            $foundInSubstring = false;
            foreach ($altoTokens as $altoToken) {
                if (str_contains($altoToken['text'], $text)
                    && self::areCategoriesEquivalent($altoToken['category'], $category)) {
                    $foundInSubstring = true;
                    break;
                }
            }

            if (!$foundInSubstring) {
                $missingFromAlto[] = sprintf(
                    '  Token "%s" (Pygments: %s) not found in Alto output',
                    $text,
                    $category,
                );
            }
        }

        $errorMessages = [];
        if (!empty($mismatches)) {
            $errorMessages[] = "Category mismatches for '{$language}':\n".implode("\n", $mismatches);
        }

        if (!empty($errorMessages)) {
            if (!empty($missingFromAlto)) {
                $errorMessages[] = "Note: tokens from Pygments not matched in Alto output:\n".implode("\n", $missingFromAlto);
            }

            self::fail(implode("\n\n", $errorMessages));
        }

        self::assertEmpty($mismatches, "No mismatches for '{$language}'");
    }

    #[DataProvider('languageProvider')]
    public function testLanguageProducesTokens(string $language): void
    {
        $samples = self::getLanguageSamples();
        $code = $samples[$language];

        $tokens = self::getAltoTokens($language, $code);

        self::assertNotEmpty($tokens, "Language '{$language}' produced no tokens for sample code");
    }

    #[DataProvider('languageProvider')]
    public function testTokensCoverEntireInput(string $language): void
    {
        $samples = self::getLanguageSamples();
        $code = $samples[$language];

        $languages = Languages::getDefaultLanguages();
        $languageInstance = null;
        foreach ($languages as $lang) {
            if ($lang->getIdentifier() === $language) {
                $languageInstance = $lang;
                break;
            }
        }

        self::assertNotNull($languageInstance, "Language not found: {$language}");

        $stream = $languageInstance->parse($code);
        $reconstructed = '';
        foreach ($stream->getTokens() as $token) {
            $reconstructed .= $token->getText();
        }

        self::assertSame(
            $code,
            $reconstructed,
            "Token stream for '{$language}' does not reconstruct the original input.",
        );
    }
}
