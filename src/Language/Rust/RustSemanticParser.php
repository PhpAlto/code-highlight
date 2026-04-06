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

namespace Alto\Code\Highlight\Language\Rust;

use Alto\Code\Highlight\Parser\ParsedStream;
use Alto\Code\Highlight\Parser\ParsedToken;
use Alto\Code\Highlight\Scope;

/**
 * Rust Semantic Parser - Pass 2: Context-Aware Scope Assignment.
 *
 * Takes tokens from the lexer and assigns semantic scopes based on context.
 *
 * @internal
 *
 * @final Not declared final to allow test doubles, but treat as final in production code
 */
class RustSemanticParser
{
    private const BUILTIN_TYPES = [
        'bool', 'char', 'f32', 'f64',
        'i8', 'i16', 'i32', 'i64', 'i128', 'isize',
        'u8', 'u16', 'u32', 'u64', 'u128', 'usize',
        'str', 'String', 'Vec', 'Option', 'Result',
        'Box', 'Rc', 'Arc', 'Cell', 'RefCell',
        'HashMap', 'HashSet', 'BTreeMap', 'BTreeSet',
        'Self',
    ];

    private const BUILTIN_FUNCTIONS = [
        'drop', 'panic', 'todo', 'unimplemented', 'unreachable',
        'assert', 'assert_eq', 'assert_ne', 'debug_assert',
        'dbg', 'eprintln', 'print', 'println', 'format',
        'vec', 'include', 'concat', 'env', 'option_env',
        'std', 'core', 'alloc',
    ];

    private RustState $state = RustState::TopLevel;

    /** @var list<RustState> */
    private array $stateStack = [];

    private int $attributeDepth = 0;

    /**
     * Parse tokens and assign semantic scopes.
     *
     * @param list<RustToken> $tokens
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
     * Determine the semantic scope for a token based on context.
     *
     * @param list<RustToken> $tokens
     */
    private function determineScope(RustToken $token, array $tokens, int $index): Scope
    {
        return match ($token->type) {
            RustTokenType::Whitespace => Scope::Whitespace,
            RustTokenType::Comment => Scope::Comment,
            RustTokenType::DocComment => Scope::Comment,
            RustTokenType::String => Scope::String,
            RustTokenType::RawString => Scope::String,
            RustTokenType::Char => Scope::String,
            RustTokenType::Lifetime => Scope::Keyword,
            RustTokenType::Number => Scope::Number,
            RustTokenType::BooleanLiteral => Scope::Boolean,
            RustTokenType::Operator => Scope::Operator,
            RustTokenType::Punctuation => $this->classifyPunctuation($token, $tokens, $index),
            RustTokenType::Macro => Scope::FunctionBuiltin,
            RustTokenType::Keyword => $this->classifyKeyword($token->text),
            RustTokenType::Identifier => $this->classifyIdentifier($token, $tokens, $index),
        };
    }

    private function classifyKeyword(string $text): Scope
    {
        return match ($text) {
            'fn', 'struct', 'enum', 'trait', 'impl', 'type', 'mod', 'use',
            'const', 'static', 'let', 'extern', 'crate' => Scope::KeywordDeclaration,
            'if', 'else', 'for', 'while', 'loop', 'match', 'return',
            'break', 'continue', 'where', 'in' => Scope::KeywordControl,
            'pub', 'mut', 'ref', 'move', 'unsafe', 'async', 'await',
            'dyn', 'as', 'super', 'self' => Scope::StorageModifier,
            'Self' => Scope::TypeReference,
            default => Scope::Keyword,
        };
    }

    /**
     * @param list<RustToken> $tokens
     */
    private function classifyPunctuation(RustToken $token, array $tokens, int $index): Scope
    {
        // Attribute brackets get AttributeName scope
        if ('[' === $token->text && $index > 0) {
            // Check if previous non-whitespace was # or #!
            for ($i = $index - 1; $i >= 0; --$i) {
                if (RustTokenType::Whitespace === $tokens[$i]->type) {
                    continue;
                }
                if ('#' === $tokens[$i]->text || '#!' === $tokens[$i]->text) {
                    return Scope::AttributeName;
                }
                break;
            }
        }

        return Scope::Punctuation;
    }

    /**
     * Classify identifier based on parser state and lookahead.
     *
     * @param list<RustToken> $tokens
     */
    private function classifyIdentifier(RustToken $token, array $tokens, int $index): Scope
    {
        return match ($this->state) {
            RustState::ExpectingFunctionName => Scope::FunctionDefinition,
            RustState::ExpectingTypeName => Scope::TypeDefinition,
            RustState::ExpectingImplType => Scope::TypeReference,
            RustState::ExpectingModuleName => Scope::Namespace,
            RustState::InAttribute => Scope::AttributeName,
            default => $this->classifyIdentifierDefault($token, $tokens, $index),
        };
    }

    /**
     * @param list<RustToken> $tokens
     */
    private function classifyIdentifierDefault(RustToken $token, array $tokens, int $index): Scope
    {
        $text = $token->text;

        // Builtin primitive types
        if (in_array($text, self::BUILTIN_TYPES, true)) {
            return Scope::BuiltInType;
        }

        // Function call: identifier followed by (
        $next = $this->peekNextNonWhitespace($tokens, $index);
        if (null !== $next && '(' === $next->text) {
            if (in_array($text, self::BUILTIN_FUNCTIONS, true)) {
                return Scope::FunctionBuiltin;
            }

            return Scope::FunctionCall;
        }

        // Type reference: CamelCase (starts uppercase)
        if (ctype_upper($text[0])) {
            return Scope::TypeReference;
        }

        // Constants: ALL_CAPS with underscores
        if (preg_match('/^[A-Z][A-Z0-9_]+$/', $text)) {
            return Scope::Constant;
        }

        return Scope::Variable;
    }

    /**
     * Update parser state based on current token.
     *
     * @param list<RustToken> $tokens
     */
    private function updateState(RustToken $token, array $tokens, int $index): void
    {
        // Attribute state: # or #! followed by [ enters InAttribute; ] exits
        if (RustTokenType::Punctuation === $token->type) {
            if ('#' === $token->text || '#!' === $token->text) {
                return;
            }

            if ('[' === $token->text) {
                // Check if entering attribute
                $prevNonWs = $this->peekPrevNonWhitespace($tokens, $index);
                if (null !== $prevNonWs && ('#' === $prevNonWs->text || '#!' === $prevNonWs->text)) {
                    $this->pushState($this->state);
                    $this->state = RustState::InAttribute;
                    $this->attributeDepth = 1;

                    return;
                }
            }

            if (']' === $token->text && RustState::InAttribute === $this->state) {
                --$this->attributeDepth;
                if (0 === $this->attributeDepth) {
                    $this->popState();
                }

                return;
            }

            if ('{' === $token->text) {
                $this->pushState($this->state);
                $this->state = RustState::TopLevel;

                return;
            }

            if ('}' === $token->text) {
                $this->popState();

                return;
            }
        }

        // Keyword transitions
        if (RustTokenType::Keyword === $token->type) {
            match ($token->text) {
                'fn' => $this->state = RustState::ExpectingFunctionName,
                'struct', 'enum', 'trait', 'type', 'union' => $this->state = RustState::ExpectingTypeName,
                'impl' => $this->state = RustState::ExpectingImplType,
                'mod' => $this->state = RustState::ExpectingModuleName,
                default => null,
            };

            return;
        }

        // After seeing an expected name, return to top level
        if (RustTokenType::Identifier === $token->type) {
            if (in_array($this->state, [
                RustState::ExpectingFunctionName,
                RustState::ExpectingTypeName,
                RustState::ExpectingImplType,
                RustState::ExpectingModuleName,
            ], true)) {
                $this->state = RustState::TopLevel;
            }
        }
    }

    /**
     * @param list<RustToken> $tokens
     */
    private function peekNextNonWhitespace(array $tokens, int $currentIndex): ?RustToken
    {
        for ($i = $currentIndex + 1; $i < count($tokens); ++$i) {
            if (RustTokenType::Whitespace !== $tokens[$i]->type) {
                return $tokens[$i];
            }
        }

        return null;
    }

    /**
     * @param list<RustToken> $tokens
     */
    private function peekPrevNonWhitespace(array $tokens, int $currentIndex): ?RustToken
    {
        for ($i = $currentIndex - 1; $i >= 0; --$i) {
            if (RustTokenType::Whitespace !== $tokens[$i]->type) {
                return $tokens[$i];
            }
        }

        return null;
    }

    private function pushState(RustState $state): void
    {
        $this->stateStack[] = $state;
    }

    private function popState(): void
    {
        if (!empty($this->stateStack)) {
            $this->state = array_pop($this->stateStack);
        } else {
            $this->state = RustState::TopLevel;
        }
    }
}
