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

namespace Alto\Code\Highlight\Language\Python;

use Alto\Code\Highlight\Parser\ParsedStream;
use Alto\Code\Highlight\Parser\ParsedToken;
use Alto\Code\Highlight\Scope;

/**
 * Python Semantic Parser - Pass 2: Context-Aware Scope Assignment.
 *
 * Distinguishes function definitions from calls, class definitions from
 * references, and handles special variables like 'self'.
 *
 * @internal
 */
final class PythonSemanticParser
{
    private PythonState $state = PythonState::TopLevel;

    private const array DECLARATION_KEYWORDS = ['def', 'class', 'import', 'from', 'global', 'nonlocal'];

    private const array CONTROL_KEYWORDS = [
        'if', 'elif', 'else', 'for', 'while', 'break', 'continue', 'return',
        'pass', 'raise', 'try', 'except', 'finally', 'with', 'yield', 'async',
        'await', 'lambda', 'del', 'assert', 'and', 'or', 'not', 'in', 'is',
    ];

    private const array BUILTIN_FUNCTIONS = [
        'print', 'len', 'range', 'type', 'str', 'int', 'float', 'list',
        'dict', 'set', 'tuple', 'bool', 'bytes', 'super', 'isinstance',
        'issubclass', 'hasattr', 'getattr', 'setattr', 'delattr', 'callable',
        'repr', 'format', 'input', 'open', 'abs', 'min', 'max', 'sum',
        'all', 'any', 'zip', 'map', 'filter', 'enumerate', 'sorted',
        'reversed', 'iter', 'next', 'vars', 'dir', 'id', 'hash', 'hex',
        'oct', 'bin', 'chr', 'ord', 'round', 'pow', 'divmod', 'classmethod',
        'staticmethod', 'property', 'object', 'Exception', 'ValueError',
        'TypeError', 'KeyError', 'IndexError', 'AttributeError', 'NotImplementedError',
    ];

    /**
     * @param list<PythonToken> $tokens
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
     * @param list<PythonToken> $tokens
     */
    private function determineScope(PythonToken $token, array $tokens, int $index): Scope
    {
        return match ($token->type) {
            PythonTokenType::Whitespace => Scope::Whitespace,
            PythonTokenType::Comment => Scope::Comment,
            PythonTokenType::String => Scope::String,
            PythonTokenType::Number => Scope::Number,
            PythonTokenType::BooleanLiteral => Scope::Boolean,
            PythonTokenType::NilLiteral => Scope::Null,
            PythonTokenType::Operator => Scope::Operator,
            PythonTokenType::Punctuation => Scope::Punctuation,
            PythonTokenType::Decorator => Scope::AttributeName,
            PythonTokenType::Keyword => $this->classifyKeyword($token->text),
            PythonTokenType::Identifier => $this->classifyIdentifier($token, $tokens, $index),
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
     * @param list<PythonToken> $tokens
     */
    private function classifyIdentifier(PythonToken $token, array $tokens, int $index): Scope
    {
        $text = $token->text;

        // Special variable 'self'
        if ('self' === $text) {
            return Scope::VariableThis;
        }

        // If we're expecting a function name (after 'def' keyword)
        if (PythonState::ExpectingFunctionName === $this->state) {
            return Scope::FunctionDefinition;
        }

        // If we're expecting a class name (after 'class' keyword)
        if (PythonState::ExpectingClassName === $this->state) {
            return Scope::TypeDefinition;
        }

        // Check if this identifier is followed by an opening parenthesis (function call)
        $nextNonWhitespace = $this->peekNextNonWhitespace($tokens, $index);
        if (null !== $nextNonWhitespace && PythonTokenType::Punctuation === $nextNonWhitespace->type && '(' === $nextNonWhitespace->text) {
            // Check if it's a known builtin function
            if (in_array($text, self::BUILTIN_FUNCTIONS, true)) {
                return Scope::FunctionBuiltin;
            }

            return Scope::FunctionCall;
        }

        // Known builtin functions (even without parentheses for context)
        if (in_array($text, self::BUILTIN_FUNCTIONS, true)) {
            return Scope::FunctionBuiltin;
        }

        // Default to variable
        return Scope::Variable;
    }

    /**
     * @param list<PythonToken> $tokens
     */
    private function peekNextNonWhitespace(array $tokens, int $from): ?PythonToken
    {
        for ($i = $from + 1, $count = count($tokens); $i < $count; ++$i) {
            if (PythonTokenType::Whitespace !== $tokens[$i]->type) {
                return $tokens[$i];
            }
        }

        return null;
    }

    private function updateState(PythonToken $token): void
    {
        if (PythonTokenType::Whitespace === $token->type) {
            return;
        }

        if (PythonTokenType::Keyword === $token->type) {
            if ('def' === $token->text) {
                $this->state = PythonState::ExpectingFunctionName;

                return;
            }

            if ('class' === $token->text) {
                $this->state = PythonState::ExpectingClassName;

                return;
            }
        }

        // Any other non-whitespace token resets the state
        if (PythonTokenType::Identifier === $token->type) {
            if (PythonState::ExpectingFunctionName === $this->state || PythonState::ExpectingClassName === $this->state) {
                $this->state = PythonState::TopLevel;

                return;
            }
        }

        // Other tokens reset state as well
        if (PythonTokenType::Keyword === $token->type) {
            $this->state = PythonState::TopLevel;
        }
    }
}
