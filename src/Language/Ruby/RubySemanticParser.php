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

namespace Alto\Code\Highlight\Language\Ruby;

use Alto\Code\Highlight\Parser\ParsedStream;
use Alto\Code\Highlight\Parser\ParsedToken;
use Alto\Code\Highlight\Scope;

/**
 * Ruby Semantic Parser - Pass 2: Context-Aware Scope Assignment.
 *
 * Takes tokens from the lexer and assigns semantic scopes based on context.
 *
 * @internal
 *
 * @final Not declared final to allow test doubles, but treat as final in production code
 *
 * @author Simon André <smn.andre@gmail.com>
 */
class RubySemanticParser
{
    private const BUILTIN_METHODS = [
        'puts', 'print', 'p', 'pp', 'raise', 'require', 'require_relative',
        'include', 'extend', 'prepend', 'attr_reader', 'attr_writer', 'attr_accessor',
        'private', 'protected', 'public', 'freeze', 'dup', 'clone',
        'send', 'respond_to?', 'is_a?', 'kind_of?', 'instance_of?',
        'nil?', 'frozen?', 'new', 'initialize',
        'lambda', 'proc', 'block_given?',
    ];

    private RubyState $state = RubyState::TopLevel;

    /**
     * @var list<RubyState>
     */
    private array $stateStack = [];

    /**
     * Parse tokens and assign semantic scopes.
     *
     * @param list<RubyToken> $tokens
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
     * @param list<RubyToken> $tokens
     */
    private function determineScope(RubyToken $token, array $tokens, int $index): Scope
    {
        return match ($token->type) {
            RubyTokenType::Whitespace => Scope::Whitespace,
            RubyTokenType::Comment => Scope::Comment,
            RubyTokenType::String => Scope::String,
            RubyTokenType::Symbol => Scope::String,
            RubyTokenType::Regex => Scope::RegExp,
            RubyTokenType::Number => Scope::Number,
            RubyTokenType::BooleanLiteral => Scope::Boolean,
            RubyTokenType::NilLiteral => Scope::Null,
            RubyTokenType::Operator => Scope::Operator,
            RubyTokenType::Punctuation => Scope::Punctuation,
            RubyTokenType::InstanceVariable => Scope::VariableProperty,
            RubyTokenType::ClassVariable => Scope::VariableProperty,
            RubyTokenType::GlobalVariable => Scope::Variable,
            RubyTokenType::Keyword => $this->classifyKeyword($token->text),
            RubyTokenType::Identifier => $this->classifyIdentifier($token, $tokens, $index),
        };
    }

    private function classifyKeyword(string $text): Scope
    {
        return match ($text) {
            'def', 'class', 'module', 'require', 'require_relative' => Scope::KeywordDeclaration,
            'if', 'else', 'elsif', 'unless', 'case', 'when', 'while', 'until',
            'for', 'do', 'return', 'break', 'next', 'redo', 'retry',
            'begin', 'rescue', 'ensure', 'end', 'then', 'yield' => Scope::KeywordControl,
            'and', 'or', 'not', 'defined?' => Scope::KeywordOperator,
            'self' => Scope::VariableThis,
            'super' => Scope::Keyword,
            'alias', 'undef' => Scope::KeywordDeclaration,
            default => Scope::Keyword,
        };
    }

    /**
     * @param list<RubyToken> $tokens
     */
    private function classifyIdentifier(RubyToken $token, array $tokens, int $index): Scope
    {
        return match ($this->state) {
            RubyState::ExpectingMethodName => Scope::FunctionDefinition,
            RubyState::ExpectingClassName => Scope::TypeDefinition,
            default => $this->classifyIdentifierDefault($token, $tokens, $index),
        };
    }

    /**
     * @param list<RubyToken> $tokens
     */
    private function classifyIdentifierDefault(RubyToken $token, array $tokens, int $index): Scope
    {
        $text = $token->text;

        // Builtin methods
        if (in_array($text, self::BUILTIN_METHODS, true)) {
            return Scope::FunctionBuiltin;
        }

        // Function/method call: followed by (
        $next = $this->peekNextNonWhitespace($tokens, $index);
        if (null !== $next && '(' === $next->text) {
            return Scope::FunctionCall;
        }

        // Constants: ALL_CAPS or CamelCase starting with uppercase
        if (ctype_upper($text[0])) {
            // ALL_CAPS = constant
            if (preg_match('/^[A-Z][A-Z0-9_]+$/', $text) || preg_match('/^[A-Z]+$/', $text)) {
                return Scope::Constant;
            }

            // CamelCase = type/class reference
            return Scope::TypeReference;
        }

        return Scope::Variable;
    }

    /**
     * @param list<RubyToken> $tokens
     */
    private function updateState(RubyToken $token, array $tokens, int $index): void
    {
        if (RubyTokenType::Keyword === $token->type) {
            match ($token->text) {
                'def' => $this->state = RubyState::ExpectingMethodName,
                'class', 'module' => $this->state = RubyState::ExpectingClassName,
                'do', 'begin' => $this->pushState(RubyState::TopLevel),
                'end' => $this->popState(),
                default => null,
            };

            return;
        }

        // After seeing a method/class name, return to top level
        if (RubyTokenType::Identifier === $token->type) {
            if (in_array($this->state, [RubyState::ExpectingMethodName, RubyState::ExpectingClassName], true)) {
                $this->state = RubyState::TopLevel;
            }
        }

        // Track blocks
        if (RubyTokenType::Punctuation === $token->type && '{' === $token->text) {
            $this->pushState($this->state);
            $this->state = RubyState::TopLevel;
        }

        if (RubyTokenType::Punctuation === $token->type && '}' === $token->text) {
            $this->popState();
        }
    }

    /**
     * @param list<RubyToken> $tokens
     */
    private function peekNextNonWhitespace(array $tokens, int $currentIndex): ?RubyToken
    {
        for ($i = $currentIndex + 1; $i < count($tokens); ++$i) {
            if (RubyTokenType::Whitespace !== $tokens[$i]->type) {
                return $tokens[$i];
            }
        }

        return null;
    }

    private function pushState(RubyState $state): void
    {
        $this->stateStack[] = $state;
    }

    private function popState(): void
    {
        $this->state = array_pop($this->stateStack) ?? RubyState::TopLevel;
    }
}
