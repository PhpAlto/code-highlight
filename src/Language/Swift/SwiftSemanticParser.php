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

namespace Alto\Code\Highlight\Language\Swift;

use Alto\Code\Highlight\Parser\ParsedStream;
use Alto\Code\Highlight\Parser\ParsedToken;
use Alto\Code\Highlight\Scope;

/**
 * Swift Semantic Parser - Pass 2: Context-Aware Scope Assignment.
 *
 * Takes tokens from the lexer and assigns semantic scopes based on context.
 *
 * @internal
 *
 * @final Not declared final to allow test doubles, but treat as final in production code
 */
class SwiftSemanticParser
{
    private const BUILTIN_TYPES = [
        'Int', 'Int8', 'Int16', 'Int32', 'Int64',
        'UInt', 'UInt8', 'UInt16', 'UInt32', 'UInt64',
        'Float', 'Double', 'Bool', 'String', 'Character',
        'Optional', 'Array', 'Dictionary', 'Set',
        'Data', 'Date', 'URL', 'UUID',
        'Never', 'Void', 'AnyObject', 'AnyClass',
        'Error', 'Result', 'Codable', 'Hashable', 'Equatable',
        'Comparable', 'Identifiable', 'CustomStringConvertible',
    ];

    private const BUILTIN_FUNCTIONS = [
        'print', 'debugPrint', 'dump', 'fatalError', 'precondition',
        'preconditionFailure', 'assert', 'assertionFailure',
        'abs', 'max', 'min', 'zip', 'stride',
        'withUnsafePointer', 'withUnsafeMutablePointer',
        'MemoryLayout', 'type', 'unsafeBitCast',
    ];

    private SwiftState $state = SwiftState::TopLevel;

    /** @var list<SwiftState> */
    private array $stateStack = [];

    /**
     * Parse tokens and assign semantic scopes.
     *
     * @param list<SwiftToken> $tokens
     */
    public function parse(array $tokens): ParsedStream
    {
        $parsedTokens = [];

        for ($i = 0; $i < count($tokens); ++$i) {
            $token = $tokens[$i];
            $scope = $this->determineScope($token, $tokens, $i);

            $parsedTokens[] = new ParsedToken(
                text: $token->text,
                scope: $scope,
            );

            $this->updateState($token, $tokens, $i);
        }

        return new ParsedStream($parsedTokens);
    }

    /**
     * @param list<SwiftToken> $tokens
     */
    private function determineScope(SwiftToken $token, array $tokens, int $index): Scope
    {
        return match ($token->type) {
            SwiftTokenType::Whitespace => Scope::Whitespace,
            SwiftTokenType::Comment => Scope::Comment,
            SwiftTokenType::DocComment => Scope::Comment,
            SwiftTokenType::String => Scope::String,
            SwiftTokenType::Number => Scope::Number,
            SwiftTokenType::BooleanLiteral => Scope::Boolean,
            SwiftTokenType::NilLiteral => Scope::Null,
            SwiftTokenType::Operator => Scope::Operator,
            SwiftTokenType::Punctuation => Scope::Punctuation,
            SwiftTokenType::Attribute => Scope::AttributeName,
            SwiftTokenType::Directive => Scope::Keyword,
            SwiftTokenType::Keyword => $this->classifyKeyword($token->text),
            SwiftTokenType::Identifier => $this->classifyIdentifier($token, $tokens, $index),
        };
    }

    private function classifyKeyword(string $text): Scope
    {
        return match ($text) {
            'class', 'struct', 'enum', 'protocol', 'extension', 'typealias',
            'actor', 'func', 'var', 'let', 'import', 'init', 'deinit',
            'subscript', 'operator', 'associatedtype', 'precedencegroup' => Scope::KeywordDeclaration,
            'if', 'else', 'guard', 'switch', 'case', 'default', 'for',
            'while', 'repeat', 'return', 'break', 'continue', 'throw',
            'try', 'catch', 'defer', 'fallthrough', 'where', 'in' => Scope::KeywordControl,
            'is', 'as', 'await', 'rethrows', 'throws' => Scope::KeywordOperator,
            'private', 'fileprivate', 'internal', 'public', 'open',
            'static', 'final', 'override', 'required', 'convenience',
            'lazy', 'weak', 'unowned', 'inout', 'mutating', 'nonmutating',
            'dynamic', 'optional', 'indirect', 'async', 'some', 'any',
            'postfix', 'prefix', 'willSet', 'didSet', 'get', 'set' => Scope::StorageModifier,
            'self' => Scope::VariableThis,
            'Self' => Scope::TypeReference,
            'super' => Scope::Keyword,
            default => Scope::Keyword,
        };
    }

    /**
     * @param list<SwiftToken> $tokens
     */
    private function classifyIdentifier(SwiftToken $token, array $tokens, int $index): Scope
    {
        return match ($this->state) {
            SwiftState::ExpectingFunctionName => Scope::FunctionDefinition,
            SwiftState::ExpectingTypeName => Scope::TypeDefinition,
            SwiftState::ExpectingExtensionType => Scope::TypeReference,
            default => $this->classifyIdentifierDefault($token, $tokens, $index),
        };
    }

    /**
     * @param list<SwiftToken> $tokens
     */
    private function classifyIdentifierDefault(SwiftToken $token, array $tokens, int $index): Scope
    {
        $text = $token->text;

        // Builtin types
        if (in_array($text, self::BUILTIN_TYPES, true)) {
            return Scope::BuiltInType;
        }

        // Function call: followed by (
        $next = $this->peekNextNonWhitespace($tokens, $index);
        if (null !== $next && '(' === $next->text) {
            if (in_array($text, self::BUILTIN_FUNCTIONS, true)) {
                return Scope::FunctionBuiltin;
            }

            return Scope::FunctionCall;
        }

        // Type reference: starts with uppercase
        if (ctype_upper($text[0])) {
            return Scope::TypeReference;
        }

        return Scope::Variable;
    }

    /**
     * @param list<SwiftToken> $tokens
     */
    private function updateState(SwiftToken $token, array $tokens, int $index): void
    {
        if (SwiftTokenType::Keyword === $token->type) {
            match ($token->text) {
                'func' => $this->state = SwiftState::ExpectingFunctionName,
                'class', 'struct', 'enum', 'protocol', 'actor', 'typealias' => $this->state = SwiftState::ExpectingTypeName,
                'extension' => $this->state = SwiftState::ExpectingExtensionType,
                default => null,
            };

            return;
        }

        // After seeing an expected name, return to top level
        if (SwiftTokenType::Identifier === $token->type) {
            if (in_array($this->state, [
                SwiftState::ExpectingFunctionName,
                SwiftState::ExpectingTypeName,
                SwiftState::ExpectingExtensionType,
            ], true)) {
                $this->state = SwiftState::TopLevel;
            }
        }

        // Track braces for scope nesting
        if (SwiftTokenType::Punctuation === $token->type) {
            if ('{' === $token->text) {
                $this->pushState($this->state);
                $this->state = SwiftState::TopLevel;
            } elseif ('}' === $token->text) {
                $this->popState();
            }
        }
    }

    /**
     * @param list<SwiftToken> $tokens
     */
    private function peekNextNonWhitespace(array $tokens, int $currentIndex): ?SwiftToken
    {
        for ($i = $currentIndex + 1; $i < count($tokens); ++$i) {
            if (SwiftTokenType::Whitespace !== $tokens[$i]->type) {
                return $tokens[$i];
            }
        }

        return null;
    }

    private function pushState(SwiftState $state): void
    {
        $this->stateStack[] = $state;
    }

    private function popState(): void
    {
        if (!empty($this->stateStack)) {
            $this->state = array_pop($this->stateStack);
        } else {
            $this->state = SwiftState::TopLevel;
        }
    }
}
