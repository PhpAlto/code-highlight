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

namespace Alto\Code\Highlight\Language\JavaScript;

use Alto\Code\Highlight\Parser\ParsedStream;
use Alto\Code\Highlight\Parser\ParsedToken;
use Alto\Code\Highlight\Scope;

/**
 * JavaScript Semantic Parser - Pass 2: Context-Aware Scope Assignment.
 *
 * Takes tokens from the lexer and assigns semantic scopes based on context.
 * This is where we distinguish function definitions from calls, class names from instantiations, etc.
 *
 * @internal
 *
 * @final Not declared final to allow test doubles, but treat as final in production code
 */
class JavaScriptSemanticParser
{
    private JavaScriptState $state = JavaScriptState::TopLevel;

    /** @var list<JavaScriptState> */
    private array $stateStack = [];

    /**
     * Parse tokens and assign semantic scopes.
     *
     * @param list<JavaScriptToken> $tokens
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

            // Update state based on token
            $this->updateState($token, $tokens, $i);
        }

        return new ParsedStream($parsedTokens);
    }

    /**
     * Determine the semantic scope for a token based on context.
     *
     * @param list<JavaScriptToken> $tokens
     */
    private function determineScope(JavaScriptToken $token, array $tokens, int $index): Scope
    {
        return match ($token->type) {
            JavaScriptTokenType::Whitespace => Scope::Whitespace,
            JavaScriptTokenType::Comment => Scope::Comment,
            JavaScriptTokenType::String => Scope::String,
            JavaScriptTokenType::TemplateLiteral => Scope::String,
            JavaScriptTokenType::TemplateExpression => Scope::StringTemplateExpression,
            JavaScriptTokenType::Regex => Scope::RegExp,
            JavaScriptTokenType::Number => Scope::Number,
            JavaScriptTokenType::BooleanLiteral => Scope::Boolean,
            JavaScriptTokenType::NullLiteral => Scope::Null,
            JavaScriptTokenType::Operator => Scope::Operator,
            JavaScriptTokenType::Punctuation => Scope::Punctuation,
            JavaScriptTokenType::Keyword => $this->classifyKeyword($token->text),
            JavaScriptTokenType::Identifier => $this->classifyIdentifier($token, $tokens, $index),
        };
    }

    private function classifyKeyword(string $text): Scope
    {
        $declarationKeywords = ['class', 'function', 'const', 'let', 'var', 'import', 'export'];
        $controlKeywords = ['if', 'else', 'for', 'while', 'do', 'switch', 'case', 'break', 'continue', 'return', 'throw', 'try', 'catch', 'finally'];
        $operatorKeywords = ['new', 'typeof', 'instanceof', 'delete', 'void'];

        if (in_array($text, $declarationKeywords, true)) {
            return Scope::KeywordDeclaration;
        }

        if (in_array($text, $controlKeywords, true)) {
            return Scope::KeywordControl;
        }

        if (in_array($text, $operatorKeywords, true)) {
            return Scope::KeywordOperator;
        }

        if ('this' === $text) {
            return Scope::VariableThis;
        }

        return Scope::Keyword;
    }

    /**
     * Classify identifier based on parser state and lookahead.
     *
     * @param list<JavaScriptToken> $tokens
     */
    private function classifyIdentifier(JavaScriptToken $token, array $tokens, int $index): Scope
    {
        // Use state machine for context-aware classification
        return match ($this->state) {
            JavaScriptState::ExpectingClassName => Scope::TypeDefinition,
            JavaScriptState::ExpectingFunctionName => Scope::FunctionDefinition,
            JavaScriptState::ExpectingMethodName => Scope::FunctionDefinition,
            JavaScriptState::ExpectingImportSpecifier => Scope::Variable,
            JavaScriptState::ExpectingExportName => Scope::Variable,
            default => $this->classifyIdentifierDefault($token, $tokens, $index),
        };
    }

    /**
     * Classify identifier when not in a special state.
     *
     * @param list<JavaScriptToken> $tokens
     */
    private function classifyIdentifierDefault(JavaScriptToken $token, array $tokens, int $index): Scope
    {
        // Look ahead for function call pattern: identifier(
        $nextNonWhitespace = $this->peekNextNonWhitespace($tokens, $index);
        if (null !== $nextNonWhitespace && '(' === $nextNonWhitespace->text) {
            return Scope::FunctionCall;
        }

        // Default to variable
        return Scope::Variable;
    }

    /**
     * Update the parser state based on the current token.
     *
     * @param list<JavaScriptToken> $tokens
     */
    private function updateState(JavaScriptToken $token, array $tokens, int $index): void
    {
        // State transitions based on keywords
        if (JavaScriptTokenType::Keyword === $token->type) {
            if ('class' === $token->text) {
                $this->state = JavaScriptState::ExpectingClassName;

                return;
            }

            if ('function' === $token->text) {
                $this->state = JavaScriptState::ExpectingFunctionName;

                return;
            }

            if ('import' === $token->text) {
                $this->state = JavaScriptState::ExpectingImportSpecifier;

                return;
            }

            if ('export' === $token->text) {
                $this->state = JavaScriptState::ExpectingExportName;

                return;
            }
        }

        // After seeing a class/function name, return to top level
        if (
            JavaScriptState::ExpectingClassName === $this->state
            || JavaScriptState::ExpectingFunctionName === $this->state
            || JavaScriptState::ExpectingMethodName === $this->state
        ) {
            if (JavaScriptTokenType::Identifier === $token->type) {
                $this->state = JavaScriptState::TopLevel;

                return;
            }
        }

        // Enter function params after seeing (
        if ('(' === $token->text && JavaScriptState::ExpectingFunctionName === $this->state) {
            $this->state = JavaScriptState::InFunctionParams;

            return;
        }

        // Enter function/class body after seeing {
        if ('{' === $token->text) {
            $this->pushState($this->state);
            if (JavaScriptState::InFunctionParams === $this->state || JavaScriptState::ExpectingFunctionName === $this->state) {
                $this->state = JavaScriptState::InFunctionBody;
            } elseif (JavaScriptState::ExpectingClassName === $this->state) {
                $this->state = JavaScriptState::InClassBody;
            }

            return;
        }

        // Exit block on }
        if ('}' === $token->text) {
            $this->popState();

            return;
        }
    }

    /**
     * Peek at the next non-whitespace token.
     *
     * @param list<JavaScriptToken> $tokens
     */
    private function peekNextNonWhitespace(array $tokens, int $currentIndex): ?JavaScriptToken
    {
        for ($i = $currentIndex + 1; $i < count($tokens); ++$i) {
            if (JavaScriptTokenType::Whitespace !== $tokens[$i]->type) {
                return $tokens[$i];
            }
        }

        return null;
    }

    private function pushState(JavaScriptState $state): void
    {
        $this->stateStack[] = $state;
    }

    private function popState(): void
    {
        if (!empty($this->stateStack)) {
            $this->state = array_pop($this->stateStack);
        } else {
            $this->state = JavaScriptState::TopLevel;
        }
    }
}
