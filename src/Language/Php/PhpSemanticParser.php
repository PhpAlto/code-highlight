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

namespace Alto\Code\Highlight\Language\Php;

use Alto\Code\Highlight\Parser\ParsedStream;
use Alto\Code\Highlight\Parser\ParsedToken;
use Alto\Code\Highlight\Scope;

/**
 * The Semantic Parser builds context and assigns semantic meaning to tokens.
 *
 * This is Pass 2 of the two-pass architecture. It consumes the flat token
 * stream from the Lexer and uses a state machine to understand context.
 *
 * This is a simplified initial implementation. The full semantic parser
 * would be significantly more complex and handle all PHP 8.4+ syntax.
 *
 * @internal
 *
 * @final Not declared final to allow test doubles, but treat as final in production code
 *
 * @author Simon André <smn.andre@gmail.com>
 */
class PhpSemanticParser
{
    private const array NAME_TOKEN_IDS = [
        T_STRING,
        T_NAME_FULLY_QUALIFIED,
        T_NAME_QUALIFIED,
        T_NAME_RELATIVE,
        T_NS_C,
        T_DIR,
        T_FILE,
        T_METHOD_C,
        T_FUNC_C,
        T_CLASS_C,
        T_TRAIT_C,
        T_LINE,
    ];

    private const array MAGIC_CONSTANTS = [
        '__CLASS__',
        '__FUNCTION__',
        '__METHOD__',
        '__NAMESPACE__',
        '__TRAIT__',
        '__FILE__',
        '__LINE__',
        '__DIR__',
    ];

    private PhpState $state = PhpState::TopLevel;

    /**
     * @var list<PhpState>
     */
    private array $stateStack = [];

    /**
     * Parse a stream of tokens and assign semantic scopes.
     *
     * @param list<\PhpToken> $tokens
     */
    public function parse(array $tokens): ParsedStream
    {
        $parsedTokens = [];

        for ($i = 0; $i < count($tokens); ++$i) {
            $token = $tokens[$i];

            // Handle PHP 8.4 asymmetric visibility tokens (e.g. "private(set)")
            if ($this->isAsymmetricVisibilityToken($token)) {
                $this->expandAsymmetricVisibility($token, $parsedTokens);
                $this->updateState($token, $tokens, $i);

                continue;
            }

            // Handle pipe operator |> which is a single token in PHP 8.5
            if (defined('T_PIPE') && T_PIPE === $token->id) {
                $parsedTokens[] = new ParsedToken('|', Scope::Operator, line: $token->line);
                $parsedTokens[] = new ParsedToken('>', Scope::Operator, line: $token->line);
                $this->updateState($token, $tokens, $i);

                continue;
            }

            $scope = $this->determineScope($token, $tokens, $i);

            $parsedTokens[] = new ParsedToken(
                text: $token->text,
                scope: $scope,
                line: $token->line,
            );

            // Update state based on token
            $this->updateState($token, $tokens, $i);
        }

        return new ParsedStream($parsedTokens);
    }

    /**
     * Determine the semantic scope for a token based on context.
     */
    /**
     * @param list<\PhpToken> $tokens
     */
    private function determineScope(\PhpToken $token, array $tokens, int $index): Scope
    {
        // Handle whitespace
        if (T_WHITESPACE === $token->id) {
            return Scope::Whitespace;
        }

        // Handle comments
        if (T_COMMENT === $token->id || T_DOC_COMMENT === $token->id) {
            return T_DOC_COMMENT === $token->id
                ? Scope::CommentDocblock
                : Scope::Comment;
        }

        // Handle strings
        if (in_array($token->id, [T_CONSTANT_ENCAPSED_STRING, T_ENCAPSED_AND_WHITESPACE], true)) {
            return Scope::String;
        }

        // Handle numbers
        if (in_array($token->id, [T_LNUMBER, T_DNUMBER], true)) {
            return Scope::Number;
        }

        // Handle variables
        if (T_VARIABLE === $token->id) {
            if ('$this' === $token->text) {
                return Scope::VariableThis;
            }

            return match ($this->state) {
                PhpState::InFunctionParams => Scope::VariableParameter,
                default => Scope::Variable,
            };
        }

        // Handle keywords
        if ($this->isKeyword($token)) {
            return $this->classifyKeyword($token);
        }

        // Handle identifiers / names (T_STRING, qualified names, etc.)
        if ($this->isNameToken($token)) {
            return $this->classifyNameToken($token, $tokens, $index);
        }

        // Handle operators
        if ($this->isOperator($token)) {
            return Scope::Operator;
        }

        // Default to punctuation for most single-character tokens
        return Scope::Punctuation;
    }

    /**
     * Update the parser state based on the current token.
     */
    /**
     * @param list<\PhpToken> $tokens
     */
    private function updateState(\PhpToken $token, array $tokens, int $index): void
    {
        // Update state machine based on tokens
        // This is a simplified version - full implementation would be more complex

        if (T_NAMESPACE === $token->id) {
            $this->state = PhpState::ExpectingNamespaceName;

            return;
        }

        if (PhpState::ExpectingNamespaceName === $this->state) {
            if ($this->isNameToken($token)) {
                $this->state = PhpState::TopLevel;

                return;
            }

            if (in_array($token->text, [';', '{'], true)) {
                $this->state = PhpState::TopLevel;

                return;
            }
        }

        if (
            in_array($this->state, [
                PhpState::ExpectingClassName,
                PhpState::ExpectingInterfaceName,
                PhpState::ExpectingTraitName,
                PhpState::ExpectingEnumName,
            ], true)
            && $this->isNameToken($token)
        ) {
            $this->state = PhpState::TopLevel;

            return;
        }

        if (T_CLASS === $token->id) {
            $this->state = PhpState::ExpectingClassName;
        } elseif (T_INTERFACE === $token->id) {
            $this->state = PhpState::ExpectingInterfaceName;
        } elseif (T_TRAIT === $token->id) {
            $this->state = PhpState::ExpectingTraitName;
        } elseif (T_ENUM === $token->id) {
            $this->state = PhpState::ExpectingEnumName;
        } elseif (T_FUNCTION === $token->id) {
            $this->state = PhpState::ExpectingFunctionName;
        } elseif ('(' === $token->text && PhpState::ExpectingFunctionName === $this->state) {
            $this->state = PhpState::InFunctionParams;
        } elseif ('{' === $token->text) {
            $this->pushState($this->state);
            if (PhpState::InFunctionParams === $this->state || PhpState::ExpectingFunctionName === $this->state) {
                $this->state = PhpState::InFunctionBody;
            }
        } elseif ('}' === $token->text) {
            $this->popState();
        }
    }

    /**
     * Classify a keyword token.
     */
    private function classifyKeyword(\PhpToken $token): Scope
    {
        $declarationKeywords = ['class', 'interface', 'trait', 'enum', 'function', 'namespace', 'const'];
        $controlKeywords = ['if', 'else', 'elseif', 'while', 'for', 'foreach', 'do', 'switch', 'case', 'return', 'break', 'continue', 'yield', 'match', 'try', 'catch', 'finally', 'throw', 'echo', 'print', 'include', 'include_once', 'require', 'require_once'];
        $operatorKeywords = ['new', 'clone', 'instanceof'];
        $modifierKeywords = ['public', 'protected', 'private', 'static', 'abstract', 'final', 'readonly'];
        $inheritanceKeywords = ['extends', 'implements'];

        $text = strtolower($token->text);

        if (in_array($text, $declarationKeywords, true)) {
            return match ($this->state) {
                PhpState::ExpectingClassName => Scope::TypeDefinition,
                PhpState::ExpectingInterfaceName => Scope::TypeDefinition,
                PhpState::ExpectingTraitName => Scope::TypeDefinition,
                PhpState::ExpectingEnumName => Scope::TypeDefinition,
                default => Scope::KeywordDeclaration,
            };
        }

        if (in_array($text, $controlKeywords, true)) {
            return Scope::KeywordControl;
        }

        if (in_array($text, $operatorKeywords, true)) {
            return Scope::KeywordOperator;
        }

        if (in_array($text, $modifierKeywords, true)) {
            return Scope::StorageModifier;
        }

        if (in_array($text, $inheritanceKeywords, true)) {
            return Scope::Keyword;
        }

        return Scope::Keyword;
    }

    /**
     * Classify identifier/name tokens (T_STRING, qualified names, etc.).
     *
     * @param list<\PhpToken> $tokens
     */
    private function classifyNameToken(\PhpToken $token, array $tokens, int $index): Scope
    {
        $lower = strtolower($token->text);

        if (in_array($lower, ['true', 'false'], true)) {
            return Scope::Boolean;
        }

        if ('null' === $lower) {
            return Scope::Null;
        }

        if (PhpState::ExpectingNamespaceName === $this->state) {
            return Scope::Namespace;
        }

        return match ($this->state) {
            PhpState::ExpectingClassName,
            PhpState::ExpectingInterfaceName,
            PhpState::ExpectingTraitName,
            PhpState::ExpectingEnumName => Scope::TypeDefinition,
            PhpState::ExpectingFunctionName => Scope::FunctionDefinition,
            PhpState::ExpectingType => Scope::TypeReference,
            default => $this->classifyIdentifierDefault($token, $tokens, $index),
        };
    }

    /**
     * Classify identifier tokens when not in a special parser state.
     *
     * @param list<\PhpToken> $tokens
     */
    private function classifyIdentifierDefault(\PhpToken $token, array $tokens, int $index): Scope
    {
        if ($this->isBuiltInConstant($token->text)) {
            return Scope::BuiltInConstant;
        }

        // Look ahead for function call pattern
        $nextNonWhitespace = $this->peekNextNonWhitespace($tokens, $index);
        if (null !== $nextNonWhitespace && '(' === $nextNonWhitespace->text) {
            return Scope::FunctionCall;
        }

        return Scope::Constant;
    }

    /**
     * Determine if a token represents an identifier/name that should be
     * classified semantically (e.g., T_STRING or T_NAME_* tokens).
     */
    private function isNameToken(\PhpToken $token): bool
    {
        return in_array($token->id, self::NAME_TOKEN_IDS, true);
    }

    private function isBuiltInConstant(string $text): bool
    {
        $upper = strtoupper($text);

        if (in_array($upper, self::MAGIC_CONSTANTS, true)) {
            return true;
        }

        return \str_starts_with($upper, 'PHP_');
    }

    /**
     * Check if a token is a keyword.
     */
    private function isKeyword(\PhpToken $token): bool
    {
        return in_array($token->id, [
            T_CLASS, T_INTERFACE, T_TRAIT, T_ENUM, T_FUNCTION,
            T_IF, T_ELSE, T_ELSEIF, T_WHILE, T_FOR, T_FOREACH, T_DO,
            T_SWITCH, T_CASE, T_RETURN, T_BREAK, T_CONTINUE,
            T_NEW, T_CLONE, T_INSTANCEOF, T_YIELD, T_NAMESPACE,
            T_ECHO, T_PRINT, T_MATCH,
            T_TRY, T_CATCH, T_FINALLY, T_THROW,
            T_INCLUDE, T_INCLUDE_ONCE, T_REQUIRE, T_REQUIRE_ONCE,
            T_EXTENDS, T_IMPLEMENTS,
            T_ABSTRACT, T_FINAL, T_CONST,
            T_PUBLIC, T_PROTECTED, T_PRIVATE, T_STATIC, T_READONLY,
        ], true);
    }

    /**
     * Check if a token is an operator.
     */
    private function isOperator(\PhpToken $token): bool
    {
        return in_array($token->text, [
            '+', '-', '*', '/', '%', '**',
            '=', '==', '===', '!=', '!==', '<', '>', '<=', '>=', '<=>',
            '&&', '||', '!', '&', '|', '^', '~', '<<', '>>',
            '.', '?:', '??', '->', '=>', '::', '\\',
        ], true);
    }

    /**
     * Peek at the next non-whitespace token.
     *
     * @param list<\PhpToken> $tokens
     */
    private function peekNextNonWhitespace(array $tokens, int $currentIndex): ?\PhpToken
    {
        for ($i = $currentIndex + 1; $i < count($tokens); ++$i) {
            if (T_WHITESPACE !== $tokens[$i]->id) {
                return $tokens[$i];
            }
        }

        return null;
    }

    /**
     * Push current state onto stack.
     */
    private function pushState(PhpState $state): void
    {
        $this->stateStack[] = $state;
    }

    /**
     * Pop state from stack.
     */
    private function popState(): void
    {
        $this->state = array_pop($this->stateStack) ?? PhpState::TopLevel;
    }

    /**
     * Check if a token is a PHP 8.4 asymmetric visibility token.
     */
    private function isAsymmetricVisibilityToken(\PhpToken $token): bool
    {
        return (defined('T_PUBLIC_SET') && T_PUBLIC_SET === $token->id) || (defined('T_PROTECTED_SET') && T_PROTECTED_SET === $token->id) || (defined('T_PRIVATE_SET') && T_PRIVATE_SET === $token->id);
    }

    /**
     * Expand a composite asymmetric visibility token (e.g. "private(set)") into separate tokens.
     *
     * @param list<ParsedToken> $parsedTokens
     */
    private function expandAsymmetricVisibility(\PhpToken $token, array &$parsedTokens): void
    {
        // Extract the keyword part (e.g. "private" from "private(set)")
        $parenPos = (int) strpos($token->text, '(');
        $keyword = substr($token->text, 0, $parenPos);
        $inner = substr($token->text, $parenPos + 1, -1); // "set"

        $parsedTokens[] = new ParsedToken($keyword, Scope::Keyword, line: $token->line);
        $parsedTokens[] = new ParsedToken('(', Scope::Punctuation, line: $token->line);
        $parsedTokens[] = new ParsedToken($inner, Scope::Constant, line: $token->line);
        $parsedTokens[] = new ParsedToken(')', Scope::Punctuation, line: $token->line);
    }
}
