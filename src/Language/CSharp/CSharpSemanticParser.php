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

namespace Alto\Code\Highlight\Language\CSharp;

use Alto\Code\Highlight\Parser\ParsedStream;
use Alto\Code\Highlight\Parser\ParsedToken;
use Alto\Code\Highlight\Scope;

/**
 * C# Semantic Parser - Pass 2: Context-Aware Scope Assignment.
 *
 * Distinguishes function definitions from calls, class declarations from
 * references, and handles special scopes based on parser state.
 *
 * @internal
 *
 * @author Simon André <smn.andre@gmail.com>
 */
final class CSharpSemanticParser
{
    private CSharpState $state = CSharpState::TopLevel;

    private int $braceDepth = 0;

    private const array MODIFIER_KEYWORDS = [
        'abstract', 'async', 'const', 'extern', 'new', 'override', 'partial',
        'private', 'protected', 'public', 'internal', 'readonly', 'sealed',
        'static', 'unsafe', 'virtual', 'volatile',
    ];

    private const array DECLARATION_KEYWORDS = [
        'class', 'delegate', 'enum', 'event', 'interface', 'namespace',
        'record', 'struct', 'using', 'global',
    ];

    private const array CONTROL_KEYWORDS = [
        'break', 'case', 'catch', 'checked', 'continue', 'default', 'do',
        'else', 'finally', 'fixed', 'for', 'foreach', 'goto', 'if', 'lock',
        'return', 'switch', 'throw', 'try', 'unchecked', 'unsafe', 'while',
        'yield',
    ];

    private const array BUILTIN_TYPES = [
        'bool', 'byte', 'char', 'decimal', 'double', 'float', 'int', 'long',
        'nint', 'nuint', 'object', 'sbyte', 'short', 'string', 'uint', 'ulong',
        'ushort', 'void', 'dynamic', 'var',
    ];

    private const array COMMON_REFERENCE_TYPES = [
        'Action', 'Array', 'ArrayList', 'Boolean', 'Byte', 'Char', 'Console',
        'DateTime', 'Decimal', 'Dictionary', 'Double', 'Enum', 'Environment',
        'Exception', 'Func', 'ICollection', 'IComparer', 'IDisposable',
        'IEnumerable', 'IEnumerator', 'IEquatable', 'IList', 'Int32', 'Int64',
        'InvalidOperationException', 'KeyValuePair', 'List', 'Math',
        'NotImplementedException', 'NullReferenceException', 'Object',
        'Predicate', 'Queue', 'Random', 'RuntimeException', 'Single',
        'SortedDictionary', 'Stack', 'String', 'StringBuilder', 'Task',
        'Thread', 'Tuple', 'Type', 'ValueTuple',
    ];

    /**
     * @param list<CSharpToken> $tokens
     */
    public function parse(array $tokens): ParsedStream
    {
        $parsedTokens = [];

        for ($i = 0, $count = count($tokens); $i < $count; ++$i) {
            $token = $tokens[$i];
            $scope = $this->determineScope($token, $tokens, $i);
            $parsedTokens[] = new ParsedToken(text: $token->text, scope: $scope);
            $this->updateState($token, $tokens, $i);
        }

        return new ParsedStream($parsedTokens);
    }

    /**
     * @param list<CSharpToken> $tokens
     */
    private function determineScope(CSharpToken $token, array $tokens, int $index): Scope
    {
        return match ($token->type) {
            CSharpTokenType::Whitespace => Scope::Whitespace,
            CSharpTokenType::Comment => Scope::Comment,
            CSharpTokenType::DocComment => Scope::CommentDocblock,
            CSharpTokenType::Directive => Scope::Punctuation,
            CSharpTokenType::String => Scope::String,
            CSharpTokenType::VerbatimString => Scope::String,
            CSharpTokenType::Interpolation => Scope::StringInterpolated,
            CSharpTokenType::Number => Scope::Number,
            CSharpTokenType::BooleanLiteral => Scope::Boolean,
            CSharpTokenType::NullLiteral => Scope::Null,
            CSharpTokenType::Operator => Scope::Operator,
            CSharpTokenType::Punctuation => Scope::Punctuation,
            CSharpTokenType::Attribute => Scope::AttributeName,
            CSharpTokenType::Keyword => $this->classifyKeyword($token->text),
            CSharpTokenType::Identifier => $this->classifyIdentifier($token, $tokens, $index),
        };
    }

    private function classifyKeyword(string $text): Scope
    {
        if (in_array($text, self::MODIFIER_KEYWORDS, true)) {
            return Scope::StorageModifier;
        }

        if (in_array($text, self::DECLARATION_KEYWORDS, true)) {
            return Scope::KeywordDeclaration;
        }

        if (in_array($text, self::CONTROL_KEYWORDS, true)) {
            return Scope::KeywordControl;
        }

        return Scope::Keyword;
    }

    /**
     * @param list<CSharpToken> $tokens
     */
    private function classifyIdentifier(CSharpToken $token, array $tokens, int $index): Scope
    {
        $text = $token->text;

        // Special variable 'this'
        if ('this' === $text) {
            return Scope::VariableThis;
        }

        // 'base' is a type reference
        if ('base' === $text) {
            return Scope::TypeReference;
        }

        // If we're expecting a type name (after class/interface/etc.)
        if (CSharpState::ExpectingTypeName === $this->state) {
            return Scope::TypeDefinition;
        }

        // If we're in an attribute context
        if (CSharpState::InAttribute === $this->state) {
            return Scope::AttributeName;
        }

        // Check if identifier is a namespace (after 'namespace' keyword)
        // For simplicity, we'll handle this case by checking previous tokens
        $prevNonWhitespace = $this->peekPreviousNonWhitespace($tokens, $index);
        if (null !== $prevNonWhitespace && CSharpTokenType::Keyword === $prevNonWhitespace->type && 'namespace' === $prevNonWhitespace->text) {
            return Scope::Namespace;
        }

        // Check if this identifier is followed by an opening parenthesis (function call/definition)
        $nextNonWhitespace = $this->peekNextNonWhitespace($tokens, $index);

        // In a class body (braceDepth >= 1) and next is '(' → function definition (if first member in class)
        if (null !== $nextNonWhitespace && CSharpTokenType::Punctuation === $nextNonWhitespace->type && '(' === $nextNonWhitespace->text) {
            if (1 === $this->braceDepth) {
                // Class body, top level member → likely function definition
                return Scope::FunctionDefinition;
            } elseif ($this->braceDepth > 1) {
                // Nested block → likely function call
                return Scope::FunctionCall;
            }
        }

        // Built-in types
        if (in_array($text, self::BUILTIN_TYPES, true)) {
            return Scope::BuiltInType;
        }

        // Common reference types
        if (in_array($text, self::COMMON_REFERENCE_TYPES, true)) {
            return Scope::TypeReference;
        }

        // Default to variable
        return Scope::Variable;
    }

    /**
     * @param list<CSharpToken> $tokens
     */
    private function peekNextNonWhitespace(array $tokens, int $from): ?CSharpToken
    {
        for ($i = $from + 1, $count = count($tokens); $i < $count; ++$i) {
            if (CSharpTokenType::Whitespace !== $tokens[$i]->type) {
                return $tokens[$i];
            }
        }

        return null;
    }

    /**
     * @param list<CSharpToken> $tokens
     */
    private function peekPreviousNonWhitespace(array $tokens, int $from): ?CSharpToken
    {
        for ($i = $from - 1; $i >= 0; --$i) {
            if (CSharpTokenType::Whitespace !== $tokens[$i]->type) {
                return $tokens[$i];
            }
        }

        return null;
    }

    /**
     * @param list<CSharpToken> $tokens
     */
    private function updateState(CSharpToken $token, array $tokens, int $index): void
    {
        // Track brace depth
        if (CSharpTokenType::Punctuation === $token->type) {
            if ('{' === $token->text) {
                ++$this->braceDepth;
            } elseif ('}' === $token->text) {
                --$this->braceDepth;
                if ($this->braceDepth < 0) {
                    $this->braceDepth = 0;
                }
            }
        }

        // Skip whitespace for state transitions
        if (CSharpTokenType::Whitespace === $token->type) {
            return;
        }

        // Handle attribute start: [
        if (CSharpTokenType::Punctuation === $token->type && '[' === $token->text && 0 === $this->braceDepth) {
            $this->state = CSharpState::InAttribute;

            return;
        }

        // Handle attribute end: ]
        if (CSharpTokenType::Punctuation === $token->type && ']' === $token->text && CSharpState::InAttribute === $this->state) {
            $this->state = CSharpState::TopLevel;

            return;
        }

        // Handle type declarations
        if (CSharpTokenType::Keyword === $token->type) {
            if (in_array($token->text, ['class', 'interface', 'enum', 'struct', 'record', 'delegate'], true)) {
                $this->state = CSharpState::ExpectingTypeName;

                return;
            }
        }

        // Handle identifier after type declaration keyword
        if (CSharpTokenType::Identifier === $token->type) {
            if (CSharpState::ExpectingTypeName === $this->state) {
                $this->state = CSharpState::TopLevel;

                return;
            }
        }
    }
}
