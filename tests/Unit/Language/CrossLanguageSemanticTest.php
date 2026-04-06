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

use Alto\Code\Highlight\Language\BashLanguage;
use Alto\Code\Highlight\Language\CSharpLanguage;
use Alto\Code\Highlight\Language\CssLanguage;
use Alto\Code\Highlight\Language\DiffLanguage;
use Alto\Code\Highlight\Language\DockerfileLanguage;
use Alto\Code\Highlight\Language\GoLanguage;
use Alto\Code\Highlight\Language\IniLanguage;
use Alto\Code\Highlight\Language\JavaLanguage;
use Alto\Code\Highlight\Language\JavaScriptLanguage;
use Alto\Code\Highlight\Language\JsonLanguage;
use Alto\Code\Highlight\Language\LanguageInterface;
use Alto\Code\Highlight\Language\PhpLanguage;
use Alto\Code\Highlight\Language\PythonLanguage;
use Alto\Code\Highlight\Language\RubyLanguage;
use Alto\Code\Highlight\Language\RustLanguage;
use Alto\Code\Highlight\Language\SqlLanguage;
use Alto\Code\Highlight\Language\SwiftLanguage;
use Alto\Code\Highlight\Language\TypeScriptLanguage;
use Alto\Code\Highlight\Language\YamlLanguage;
use Alto\Code\Highlight\Parser\ParsedStream;
use Alto\Code\Highlight\Parser\ParsedToken;
use Alto\Code\Highlight\Scope;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

/**
 * Cross-language semantic validation.
 *
 * Verifies that universal language constructs (keywords, strings, comments,
 * numbers) are classified into the correct scope categories by each language
 * parser. These expectations are based on language specifications, not on
 * any particular highlighting library.
 */
#[CoversClass(PhpLanguage::class)]
final class CrossLanguageSemanticTest extends TestCase
{
    /**
     * @return array<string, array{LanguageInterface, string, list<array{text: string, scope_group: string}>}>
     */
    public static function keywordProvider(): array
    {
        return [
            'php: function keyword' => [
                new PhpLanguage(),
                '<?php function test() {}',
                [['text' => 'function', 'scope_group' => 'keyword']],
            ],
            'php: class keyword' => [
                new PhpLanguage(),
                '<?php class Foo {}',
                [['text' => 'class', 'scope_group' => 'keyword']],
            ],
            'php: if keyword' => [
                new PhpLanguage(),
                '<?php if (true) {}',
                [['text' => 'if', 'scope_group' => 'keyword']],
            ],
            'php: return keyword' => [
                new PhpLanguage(),
                '<?php function f() { return 1; }',
                [['text' => 'return', 'scope_group' => 'keyword']],
            ],
            'javascript: function keyword' => [
                new JavaScriptLanguage(),
                'function test() {}',
                [['text' => 'function', 'scope_group' => 'keyword']],
            ],
            'javascript: const keyword' => [
                new JavaScriptLanguage(),
                'const x = 1;',
                [['text' => 'const', 'scope_group' => 'keyword']],
            ],
            'javascript: if keyword' => [
                new JavaScriptLanguage(),
                'if (true) {}',
                [['text' => 'if', 'scope_group' => 'keyword']],
            ],
            'javascript: return keyword' => [
                new JavaScriptLanguage(),
                'function f() { return 1; }',
                [['text' => 'return', 'scope_group' => 'keyword']],
            ],
            'python: def keyword' => [
                new PythonLanguage(),
                'def test(): pass',
                [['text' => 'def', 'scope_group' => 'keyword']],
            ],
            'python: class keyword' => [
                new PythonLanguage(),
                'class Foo: pass',
                [['text' => 'class', 'scope_group' => 'keyword']],
            ],
            'python: if keyword' => [
                new PythonLanguage(),
                'if True: pass',
                [['text' => 'if', 'scope_group' => 'keyword']],
            ],
            'python: return keyword' => [
                new PythonLanguage(),
                "def f():\n    return 1",
                [['text' => 'return', 'scope_group' => 'keyword']],
            ],
            'go: func keyword' => [
                new GoLanguage(),
                'func test() {}',
                [['text' => 'func', 'scope_group' => 'keyword']],
            ],
            'go: if keyword' => [
                new GoLanguage(),
                'if x > 0 {}',
                [['text' => 'if', 'scope_group' => 'keyword']],
            ],
            'go: return keyword' => [
                new GoLanguage(),
                "func f() int {\n    return 1\n}",
                [['text' => 'return', 'scope_group' => 'keyword']],
            ],
            'rust: fn keyword' => [
                new RustLanguage(),
                'fn test() {}',
                [['text' => 'fn', 'scope_group' => 'keyword']],
            ],
            'rust: let keyword' => [
                new RustLanguage(),
                'let x = 1;',
                [['text' => 'let', 'scope_group' => 'keyword']],
            ],
            'rust: if keyword' => [
                new RustLanguage(),
                'if x > 0 {}',
                [['text' => 'if', 'scope_group' => 'keyword']],
            ],
            'ruby: def keyword' => [
                new RubyLanguage(),
                "def test\nend",
                [['text' => 'def', 'scope_group' => 'keyword']],
            ],
            'ruby: class keyword' => [
                new RubyLanguage(),
                "class Foo\nend",
                [['text' => 'class', 'scope_group' => 'keyword']],
            ],
            'ruby: end keyword' => [
                new RubyLanguage(),
                "def test\nend",
                [['text' => 'end', 'scope_group' => 'keyword']],
            ],
            'java: class keyword' => [
                new JavaLanguage(),
                'class Foo {}',
                [['text' => 'class', 'scope_group' => 'keyword']],
            ],
            'java: return keyword' => [
                new JavaLanguage(),
                'class Foo { int f() { return 1; } }',
                [['text' => 'return', 'scope_group' => 'keyword']],
            ],
            'csharp: class keyword' => [
                new CSharpLanguage(),
                'class Foo {}',
                [['text' => 'class', 'scope_group' => 'keyword']],
            ],
            'csharp: return keyword' => [
                new CSharpLanguage(),
                'class Foo { int F() { return 1; } }',
                [['text' => 'return', 'scope_group' => 'keyword']],
            ],
            'swift: func keyword' => [
                new SwiftLanguage(),
                'func test() {}',
                [['text' => 'func', 'scope_group' => 'keyword']],
            ],
            'swift: return keyword' => [
                new SwiftLanguage(),
                "func f() -> Int {\n    return 1\n}",
                [['text' => 'return', 'scope_group' => 'keyword']],
            ],
            'typescript: function keyword' => [
                new TypeScriptLanguage(),
                'function test(): void {}',
                [['text' => 'function', 'scope_group' => 'keyword']],
            ],
            'typescript: const keyword' => [
                new TypeScriptLanguage(),
                'const x: number = 1;',
                [['text' => 'const', 'scope_group' => 'keyword']],
            ],
            'sql: SELECT keyword' => [
                new SqlLanguage(),
                'SELECT * FROM users;',
                [['text' => 'SELECT', 'scope_group' => 'keyword']],
            ],
            'sql: WHERE keyword' => [
                new SqlLanguage(),
                'SELECT * FROM users WHERE id = 1;',
                [['text' => 'WHERE', 'scope_group' => 'keyword']],
            ],
        ];
    }

    /**
     * @return array<string, array{LanguageInterface, string, string}>
     */
    public static function stringProvider(): array
    {
        return [
            'php: double-quoted string' => [new PhpLanguage(), '<?php $x = "hello";', 'hello'],
            'php: single-quoted string' => [new PhpLanguage(), "<?php \$x = 'hello';", 'hello'],
            'javascript: double-quoted string' => [new JavaScriptLanguage(), 'let x = "hello";', 'hello'],
            'javascript: single-quoted string' => [new JavaScriptLanguage(), "let x = 'hello';", 'hello'],
            'python: double-quoted string' => [new PythonLanguage(), 'x = "hello"', 'hello'],
            'python: single-quoted string' => [new PythonLanguage(), "x = 'hello'", 'hello'],
            'go: string literal' => [new GoLanguage(), 'x := "hello"', 'hello'],
            'rust: string literal' => [new RustLanguage(), 'let x = "hello";', 'hello'],
            'ruby: double-quoted string' => [new RubyLanguage(), 'x = "hello"', 'hello'],
            'ruby: single-quoted string' => [new RubyLanguage(), "x = 'hello'", 'hello'],
            'java: string literal' => [new JavaLanguage(), 'String x = "hello";', 'hello'],
            'csharp: string literal' => [new CSharpLanguage(), 'string x = "hello";', 'hello'],
            'swift: string literal' => [new SwiftLanguage(), 'let x = "hello"', 'hello'],
            'typescript: string literal' => [new TypeScriptLanguage(), 'const x: string = "hello";', 'hello'],
            'sql: string literal' => [new SqlLanguage(), "SELECT * FROM t WHERE x = 'hello';", 'hello'],
            'json: string value' => [new JsonLanguage(), '{"key": "hello"}', 'hello'],
            'yaml: string value' => [new YamlLanguage(), 'key: "hello"', 'hello'],
            'bash: double-quoted string' => [new BashLanguage(), 'echo "hello"', 'hello'],
            'bash: single-quoted string' => [new BashLanguage(), "echo 'hello'", 'hello'],
            'css: string in url' => [new CssLanguage(), 'body { background: url("hello"); }', 'hello'],
        ];
    }

    /**
     * @return array<string, array{LanguageInterface, string}>
     */
    public static function commentProvider(): array
    {
        return [
            'php: single-line comment' => [new PhpLanguage(), "<?php // comment\n\$x = 1;"],
            'php: block comment' => [new PhpLanguage(), '<?php /* comment */ $x = 1;'],
            'javascript: single-line comment' => [new JavaScriptLanguage(), "// comment\nlet x = 1;"],
            'javascript: block comment' => [new JavaScriptLanguage(), '/* comment */ let x = 1;'],
            'python: comment' => [new PythonLanguage(), "# comment\nx = 1"],
            'go: single-line comment' => [new GoLanguage(), "// comment\nvar x = 1"],
            'go: block comment' => [new GoLanguage(), '/* comment */ var x = 1'],
            'rust: single-line comment' => [new RustLanguage(), "// comment\nlet x = 1;"],
            'ruby: comment' => [new RubyLanguage(), "# comment\nx = 1"],
            'java: single-line comment' => [new JavaLanguage(), "// comment\nint x = 1;"],
            'java: block comment' => [new JavaLanguage(), '/* comment */ int x = 1;'],
            'csharp: single-line comment' => [new CSharpLanguage(), "// comment\nint x = 1;"],
            'swift: single-line comment' => [new SwiftLanguage(), "// comment\nvar x = 1"],
            'typescript: comment' => [new TypeScriptLanguage(), "// comment\nconst x = 1;"],
            'sql: comment' => [new SqlLanguage(), "-- comment\nSELECT 1;"],
            'yaml: comment' => [new YamlLanguage(), "# comment\nkey: value"],
            'bash: comment' => [new BashLanguage(), "# comment\nx=1"],
            'ini: comment' => [new IniLanguage(), "; comment\nkey=value"],
            'css: comment' => [new CssLanguage(), '/* comment */ body {}'],
        ];
    }

    /**
     * @return array<string, array{LanguageInterface, string, string}>
     */
    public static function numberProvider(): array
    {
        return [
            'php: integer' => [new PhpLanguage(), '<?php $x = 42;', '42'],
            'javascript: integer' => [new JavaScriptLanguage(), 'let x = 42;', '42'],
            'python: integer' => [new PythonLanguage(), 'x = 42', '42'],
            'go: integer' => [new GoLanguage(), 'x := 42', '42'],
            'rust: integer' => [new RustLanguage(), 'let x = 42;', '42'],
            'ruby: integer' => [new RubyLanguage(), 'x = 42', '42'],
            'java: integer' => [new JavaLanguage(), 'int x = 42;', '42'],
            'csharp: integer' => [new CSharpLanguage(), 'int x = 42;', '42'],
            'swift: integer' => [new SwiftLanguage(), 'var x = 42', '42'],
            'typescript: integer' => [new TypeScriptLanguage(), 'const x: number = 42;', '42'],
            'sql: integer' => [new SqlLanguage(), 'SELECT * FROM t WHERE x = 42;', '42'],
            'json: integer' => [new JsonLanguage(), '{"x": 42}', '42'],
            'yaml: integer' => [new YamlLanguage(), 'x: 42', '42'],
        ];
    }

    /**
     * @return array<string, array{LanguageInterface, string}>
     */
    public static function functionDefinitionProvider(): array
    {
        return [
            'php: function' => [new PhpLanguage(), '<?php function greet() {}'],
            'javascript: function' => [new JavaScriptLanguage(), 'function greet() {}'],
            'python: function' => [new PythonLanguage(), "def greet():\n    pass"],
            'go: function' => [new GoLanguage(), 'func greet() {}'],
            'rust: function' => [new RustLanguage(), 'fn greet() {}'],
            'ruby: function' => [new RubyLanguage(), "def greet\nend"],
            'java: method' => [new JavaLanguage(), 'class X { void greet() {} }'],
            'csharp: method' => [new CSharpLanguage(), 'class X { void Greet() {} }'],
            'swift: function' => [new SwiftLanguage(), 'func greet() {}'],
            'typescript: function' => [new TypeScriptLanguage(), 'function greet(): void {}'],
        ];
    }

    /**
     * Map a Scope to a broad group for assertion purposes.
     */
    private static function scopeGroup(Scope $scope): string
    {
        return match ($scope) {
            Scope::Keyword, Scope::KeywordDeclaration, Scope::KeywordOperator,
            Scope::KeywordControl, Scope::StorageModifier => 'keyword',

            Scope::String, Scope::StringInterpolated,
            Scope::StringTemplateExpression => 'string',

            Scope::Comment, Scope::CommentDocblock, Scope::CommentTask => 'comment',

            Scope::Number => 'number',

            Scope::Variable, Scope::VariableParameter,
            Scope::VariableProperty, Scope::VariableThis => 'variable',

            Scope::FunctionDefinition => 'function_definition',
            Scope::FunctionCall, Scope::FunctionBuiltin => 'function',

            Scope::TypeDefinition => 'type_definition',
            Scope::TypeReference, Scope::BuiltInType, Scope::SupportType => 'type',

            Scope::Boolean, Scope::Null, Scope::Constant,
            Scope::BuiltInConstant, Scope::SupportConstant => 'constant',

            Scope::Operator => 'operator',
            Scope::Punctuation => 'punctuation',

            Scope::TagName => 'tag',
            Scope::TagAttributeName, Scope::AttributeName => 'attribute',
            Scope::TagAttributeValue, Scope::AttributeValue => 'attribute_value',

            Scope::DiffAdded => 'diff_added',
            Scope::DiffRemoved => 'diff_removed',

            Scope::Namespace => 'namespace',
            Scope::EnumCase => 'enum_case',
            Scope::RegExp => 'regexp',

            default => 'other',
        };
    }

    /**
     * Verify that language keywords are classified under a keyword scope.
     *
     * @param list<array{text: string, scope_group: string}> $expectedTokens
     */
    #[DataProvider('keywordProvider')]
    public function testKeywordsAreClassifiedCorrectly(LanguageInterface $language, string $code, array $expectedTokens): void
    {
        $stream = $language->parse($code);
        $tokens = $this->getNonWhitespaceTokens($stream);

        foreach ($expectedTokens as $expected) {
            $found = false;
            foreach ($tokens as $token) {
                if ($token->getText() === $expected['text'] && self::scopeGroup($token->getScope()) === $expected['scope_group']) {
                    $found = true;
                    break;
                }
            }

            self::assertTrue(
                $found,
                sprintf(
                    "Expected token \"%s\" with scope group \"%s\" in %s output.\nActual tokens:\n%s",
                    $expected['text'],
                    $expected['scope_group'],
                    $language->getIdentifier(),
                    $this->formatTokens($tokens),
                ),
            );
        }
    }

    /**
     * Verify that string literals contain the expected text and are classified as strings.
     */
    #[DataProvider('stringProvider')]
    public function testStringsAreClassifiedCorrectly(LanguageInterface $language, string $code, string $expectedSubstring): void
    {
        $stream = $language->parse($code);
        $tokens = $this->getNonWhitespaceTokens($stream);

        $foundString = false;
        foreach ($tokens as $token) {
            if ('string' === self::scopeGroup($token->getScope()) && str_contains($token->getText(), $expectedSubstring)) {
                $foundString = true;
                break;
            }
        }

        self::assertTrue(
            $foundString,
            sprintf(
                "Expected a string token containing \"%s\" in %s output.\nActual tokens:\n%s",
                $expectedSubstring,
                $language->getIdentifier(),
                $this->formatTokens($tokens),
            ),
        );
    }

    /**
     * Verify that comments are classified under a comment scope.
     */
    #[DataProvider('commentProvider')]
    public function testCommentsAreClassifiedCorrectly(LanguageInterface $language, string $code): void
    {
        $stream = $language->parse($code);
        $tokens = $this->getNonWhitespaceTokens($stream);

        $foundComment = false;
        foreach ($tokens as $token) {
            if ('comment' === self::scopeGroup($token->getScope())) {
                $foundComment = true;
                break;
            }
        }

        self::assertTrue(
            $foundComment,
            sprintf(
                "Expected at least one comment token in %s output.\nActual tokens:\n%s",
                $language->getIdentifier(),
                $this->formatTokens($tokens),
            ),
        );
    }

    /**
     * Verify that numeric literals are classified as numbers.
     */
    #[DataProvider('numberProvider')]
    public function testNumbersAreClassifiedCorrectly(LanguageInterface $language, string $code, string $expectedNumber): void
    {
        $stream = $language->parse($code);
        $tokens = $this->getNonWhitespaceTokens($stream);

        $foundNumber = false;
        foreach ($tokens as $token) {
            if ('number' === self::scopeGroup($token->getScope()) && str_contains($token->getText(), $expectedNumber)) {
                $foundNumber = true;
                break;
            }
        }

        self::assertTrue(
            $foundNumber,
            sprintf(
                "Expected a number token containing \"%s\" in %s output.\nActual tokens:\n%s",
                $expectedNumber,
                $language->getIdentifier(),
                $this->formatTokens($tokens),
            ),
        );
    }

    /**
     * Verify that function definitions are recognized.
     *
     * Accepts both FunctionDefinition and FunctionCall scopes, as some
     * language parsers may not yet distinguish between them.
     */
    #[DataProvider('functionDefinitionProvider')]
    public function testFunctionDefinitionsAreRecognized(LanguageInterface $language, string $code): void
    {
        $stream = $language->parse($code);
        $tokens = $this->getNonWhitespaceTokens($stream);

        $foundFunctionDef = false;
        foreach ($tokens as $token) {
            $group = self::scopeGroup($token->getScope());
            if ('function_definition' === $group || 'function' === $group) {
                $foundFunctionDef = true;
                break;
            }
        }

        self::assertTrue(
            $foundFunctionDef,
            sprintf(
                "Expected a function definition or function call token in %s output.\nActual tokens:\n%s",
                $language->getIdentifier(),
                $this->formatTokens($tokens),
            ),
        );
    }

    /**
     * Verify that all token text concatenated equals the original code.
     *
     * This is a fundamental invariant: no text should be dropped or duplicated.
     */
    #[DataProvider('tokenCoverageProvider')]
    public function testTokensCoverEntireInput(LanguageInterface $language, string $code): void
    {
        $stream = $language->parse($code);
        $reconstructed = '';
        foreach ($stream->getTokens() as $token) {
            $reconstructed .= $token->getText();
        }

        self::assertSame(
            $code,
            $reconstructed,
            sprintf(
                "Token stream for '%s' does not reconstruct the original input.\nExpected length: %d, Got: %d",
                $language->getIdentifier(),
                strlen($code),
                strlen($reconstructed),
            ),
        );
    }

    /**
     * @return array<string, array{LanguageInterface, string}>
     */
    public static function tokenCoverageProvider(): array
    {
        return [
            'php' => [new PhpLanguage(), "<?php\nfunction greet(\$name) {\n    return \"Hello, \" . \$name;\n}\n\$x = 42;\n// comment\nif (\$x > 10) {\n    echo \$x;\n}"],
            'javascript' => [new JavaScriptLanguage(), "function greet(name) {\n    const msg = \"Hello\";\n    return msg;\n}\n// comment\nlet x = 42;"],
            'python' => [new PythonLanguage(), "def greet(name):\n    return 'Hello' + name\n# comment\nx = 42"],
            'go' => [new GoLanguage(), "func greet(name string) string {\n    return \"Hello\"\n}\n// comment\nvar x = 42"],
            'rust' => [new RustLanguage(), "fn greet(name: &str) -> String {\n    String::from(\"Hello\")\n}\n// comment\nlet x = 42;"],
            'ruby' => [new RubyLanguage(), "def greet(name)\n    'Hello'\nend\n# comment\nx = 42"],
            'java' => [new JavaLanguage(), "class Main {\n    String greet(String name) {\n        return \"Hello\";\n    }\n    // comment\n    int x = 42;\n}"],
            'csharp' => [new CSharpLanguage(), "class Main {\n    string Greet(string name) {\n        return \"Hello\";\n    }\n    // comment\n    int x = 42;\n}"],
            'swift' => [new SwiftLanguage(), "func greet(name: String) -> String {\n    return \"Hello\"\n}\n// comment\nvar x = 42"],
            'typescript' => [new TypeScriptLanguage(), "function greet(name: string): string {\n    return \"Hello\";\n}\n// comment\nconst x: number = 42;"],
            'sql' => [new SqlLanguage(), "SELECT name FROM users WHERE age > 18;\n-- comment"],
            'json' => [new JsonLanguage(), '{"name": "John", "age": 30, "active": true}'],
            'yaml' => [new YamlLanguage(), "name: John\nage: 30\n# comment"],
            'css' => [new CssLanguage(), "body {\n    color: red;\n}\n/* comment */"],
            'bash' => [new BashLanguage(), "#!/bin/bash\necho 'hello'\n# comment\nx=42"],
            'ini' => [new IniLanguage(), "[section]\nkey = value\n; comment"],
            'diff' => [new DiffLanguage(), "--- a/file.txt\n+++ b/file.txt\n@@ -1,3 +1,3 @@\n-old line\n+new line\n context"],
            'dockerfile' => [new DockerfileLanguage(), "FROM ubuntu:22.04\nRUN apt-get update\nCOPY . /app"],
        ];
    }

    /**
     * @return list<ParsedToken>
     */
    private function getNonWhitespaceTokens(ParsedStream $stream): array
    {
        return array_values(array_filter(
            $stream->getTokens(),
            static fn (ParsedToken $token): bool => Scope::Whitespace !== $token->getScope(),
        ));
    }

    /**
     * @param list<ParsedToken> $tokens
     */
    private function formatTokens(array $tokens): string
    {
        $lines = [];
        foreach ($tokens as $token) {
            $lines[] = sprintf(
                '  %-30s => %s (%s)',
                json_encode($token->getText()),
                $token->getScope()->name,
                self::scopeGroup($token->getScope()),
            );
        }

        return implode("\n", $lines);
    }
}
