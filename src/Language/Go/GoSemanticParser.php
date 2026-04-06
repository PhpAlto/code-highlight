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

namespace Alto\Code\Highlight\Language\Go;

use Alto\Code\Highlight\Parser\ParsedStream;
use Alto\Code\Highlight\Parser\ParsedToken;
use Alto\Code\Highlight\Scope;

/**
 * Go Semantic Parser - Pass 2: Context-Aware Scope Assignment.
 *
 * Distinguishes function definitions from calls, type definitions from
 * references, and handles Go's method receiver syntax: func (r *T) Method().
 *
 * @internal
 */
final class GoSemanticParser
{
    private GoState $state = GoState::TopLevel;
    private int $receiverDepth = 0;
    private bool $firstReceiverIdent = false;

    private const array BUILTIN_FUNCTIONS = [
        'append', 'cap', 'clear', 'close', 'complex', 'copy', 'delete',
        'imag', 'len', 'make', 'max', 'min', 'new', 'panic', 'print',
        'println', 'real', 'recover',
    ];

    private const array BUILTIN_TYPES = [
        'any', 'bool', 'byte', 'comparable', 'complex64', 'complex128',
        'error', 'float32', 'float64', 'int', 'int8', 'int16', 'int32',
        'int64', 'rune', 'string', 'uint', 'uint8', 'uint16', 'uint32',
        'uint64', 'uintptr',
    ];

    private const array BUILTIN_CONSTANTS = ['iota'];

    /** @var array<int, string> */
    private const array DECLARATION_KEYWORDS = ['func', 'type', 'var', 'const', 'import', 'package', 'interface', 'struct', 'chan', 'map'];

    /** @var array<int, string> */
    private const array CONTROL_KEYWORDS = ['if', 'else', 'for', 'range', 'switch', 'case', 'default', 'break', 'continue', 'goto', 'fallthrough', 'return', 'select', 'go', 'defer'];

    /**
     * @param list<GoToken> $tokens
     */
    public function parse(array $tokens): ParsedStream
    {
        $parsedTokens = [];

        for ($i = 0, $count = count($tokens); $i < $count; ++$i) {
            $token = $tokens[$i];
            $scope = $this->determineScope($token, $tokens, $i);
            $parsedTokens[] = new ParsedToken(text: $token->text, scope: $scope);
            $this->updateState($token);
        }

        return new ParsedStream($parsedTokens);
    }

    /**
     * @param list<GoToken> $tokens
     */
    private function determineScope(GoToken $token, array $tokens, int $index): Scope
    {
        return match ($token->type) {
            GoTokenType::Whitespace => Scope::Whitespace,
            GoTokenType::Comment => Scope::Comment,
            GoTokenType::String, GoTokenType::RawString => Scope::String,
            GoTokenType::Rune => Scope::String,
            GoTokenType::Number => Scope::Number,
            GoTokenType::BooleanLiteral => Scope::Boolean,
            GoTokenType::NilLiteral => Scope::Null,
            GoTokenType::Operator => Scope::Operator,
            GoTokenType::Punctuation => Scope::Punctuation,
            GoTokenType::Keyword => $this->classifyKeyword($token->text),
            GoTokenType::Identifier => $this->classifyIdentifier($token, $tokens, $index),
        };
    }

    private function classifyKeyword(string $text): Scope
    {
        if (in_array($text, self::DECLARATION_KEYWORDS, true)) {
            return Scope::KeywordDeclaration;
        }

        if (in_array($text, self::CONTROL_KEYWORDS, true)) {
            return Scope::KeywordControl;
        }

        return Scope::Keyword;
    }

    /**
     * @param list<GoToken> $tokens
     */
    private function classifyIdentifier(GoToken $token, array $tokens, int $index): Scope
    {
        return match ($this->state) {
            GoState::ExpectingFunctionOrReceiver => Scope::FunctionDefinition,
            GoState::ExpectingFunctionName => Scope::FunctionDefinition,
            GoState::ExpectingTypeName => Scope::TypeDefinition,
            GoState::InReceiver => $this->classifyReceiverIdentifier($token),
            default => $this->classifyDefault($token, $tokens, $index),
        };
    }

    private function classifyReceiverIdentifier(GoToken $token): Scope
    {
        // First identifier in receiver is the variable name (e.g. 'g' in func (g *Greeter))
        if ($this->firstReceiverIdent) {
            return Scope::VariableParameter;
        }

        // Subsequent identifiers are type names — capitalize convention holds in Go
        return Scope::TypeReference;
    }

    /**
     * @param list<GoToken> $tokens
     */
    private function classifyDefault(GoToken $token, array $tokens, int $index): Scope
    {
        $name = $token->text;

        if (in_array($name, self::BUILTIN_CONSTANTS, true)) {
            return Scope::Constant;
        }

        if (in_array($name, self::BUILTIN_FUNCTIONS, true)) {
            return Scope::FunctionBuiltin;
        }

        if (in_array($name, self::BUILTIN_TYPES, true)) {
            return Scope::BuiltInType;
        }

        // Lookahead: identifier followed by ( is a function/method call
        $next = $this->peekNextNonWhitespace($tokens, $index);
        if (null !== $next && '(' === $next->text && GoTokenType::Punctuation === $next->type) {
            return Scope::FunctionCall;
        }

        return Scope::Variable;
    }

    /**
     * @param list<GoToken> $tokens
     */
    private function peekNextNonWhitespace(array $tokens, int $from): ?GoToken
    {
        for ($i = $from + 1, $count = count($tokens); $i < $count; ++$i) {
            if (GoTokenType::Whitespace !== $tokens[$i]->type) {
                return $tokens[$i];
            }
        }

        return null;
    }

    private function updateState(GoToken $token): void
    {
        // Whitespace never triggers state transitions
        if (GoTokenType::Whitespace === $token->type) {
            return;
        }

        if (GoTokenType::Keyword === $token->type) {
            if ('func' === $token->text) {
                $this->state = GoState::ExpectingFunctionOrReceiver;

                return;
            }

            if ('type' === $token->text) {
                $this->state = GoState::ExpectingTypeName;

                return;
            }

            // Any other keyword resets pending function/type states
            if (GoState::TopLevel !== $this->state) {
                $this->state = GoState::TopLevel;
            }

            return;
        }

        switch ($this->state) {
            case GoState::ExpectingFunctionOrReceiver:
                if (GoTokenType::Punctuation === $token->type && '(' === $token->text) {
                    // Method with receiver: func (r *T) Name()
                    $this->state = GoState::InReceiver;
                    $this->receiverDepth = 1;
                    $this->firstReceiverIdent = true;
                } elseif (GoTokenType::Identifier === $token->type) {
                    // Regular function: func Name()
                    $this->state = GoState::TopLevel;
                }
                break;

            case GoState::InReceiver:
                if (GoTokenType::Punctuation === $token->type) {
                    if ('(' === $token->text) {
                        ++$this->receiverDepth;
                    } elseif (')' === $token->text) {
                        --$this->receiverDepth;
                        if (0 === $this->receiverDepth) {
                            $this->state = GoState::ExpectingFunctionName;
                        }
                    }
                } elseif (GoTokenType::Identifier === $token->type && $this->firstReceiverIdent) {
                    $this->firstReceiverIdent = false;
                }
                break;

            case GoState::ExpectingFunctionName:
                if (GoTokenType::Identifier === $token->type) {
                    $this->state = GoState::TopLevel;
                }
                break;

            case GoState::ExpectingTypeName:
                if (GoTokenType::Identifier === $token->type) {
                    $this->state = GoState::TopLevel;
                }
                break;

            default:
                break;
        }
    }
}
