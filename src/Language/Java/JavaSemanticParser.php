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

namespace Alto\Code\Highlight\Language\Java;

use Alto\Code\Highlight\Parser\ParsedStream;
use Alto\Code\Highlight\Parser\ParsedToken;
use Alto\Code\Highlight\Scope;

/**
 * Java Semantic Parser - Pass 2: Context-Aware Scope Assignment.
 *
 * Distinguishes class/interface definitions from references, function
 * definitions from calls, and tracks semantic context through brace depth.
 *
 * @internal
 *
 * @author Simon André <smn.andre@gmail.com>
 */
final class JavaSemanticParser
{
    private JavaState $state = JavaState::TopLevel;
    private int $braceDepth = 0;

    private const array MODIFIER_KEYWORDS = [
        'abstract', 'final', 'native', 'private', 'protected', 'public', 'static',
        'strictfp', 'synchronized', 'transient', 'volatile', 'sealed', 'non-sealed',
    ];

    private const array DECLARATION_KEYWORDS = [
        'class', 'interface', 'enum', 'extends', 'implements', 'import', 'package',
        'record', 'permits', 'throws',
    ];

    private const array CONTROL_KEYWORDS = [
        'break', 'case', 'catch', 'continue', 'default', 'do', 'else', 'finally',
        'for', 'if', 'instanceof', 'new', 'return', 'switch', 'throw', 'try', 'while',
    ];

    private const array BUILTIN_TYPES = [
        'boolean', 'byte', 'char', 'double', 'float', 'int', 'long', 'short', 'void',
    ];

    private const array COMMON_REFERENCE_TYPES = [
        'String', 'Object', 'Integer', 'Long', 'Double', 'Float', 'Boolean',
        'Character', 'Byte', 'Short', 'Number', 'Math', 'System', 'StringBuilder',
        'StringBuffer', 'Comparable', 'Iterable', 'Cloneable', 'Serializable',
        'Runnable', 'Thread', 'Exception', 'RuntimeException', 'Error', 'Override',
        'Deprecated', 'SuppressWarnings', 'List', 'ArrayList', 'LinkedList', 'Map',
        'HashMap', 'Set', 'HashSet', 'Optional', 'Stream', 'Arrays', 'Collections',
        'Objects',
    ];

    /**
     * @param list<JavaToken> $tokens
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
     * @param list<JavaToken> $tokens
     */
    private function determineScope(JavaToken $token, array $tokens, int $index): Scope
    {
        return match ($token->type) {
            JavaTokenType::Whitespace => Scope::Whitespace,
            JavaTokenType::Comment => Scope::Comment,
            JavaTokenType::DocComment => Scope::CommentDocblock,
            JavaTokenType::String => Scope::String,
            JavaTokenType::CharLiteral => Scope::String,
            JavaTokenType::Number => Scope::Number,
            JavaTokenType::BooleanLiteral => Scope::Boolean,
            JavaTokenType::NullLiteral => Scope::Null,
            JavaTokenType::Operator => Scope::Operator,
            JavaTokenType::Punctuation => Scope::Punctuation,
            JavaTokenType::Annotation => Scope::AttributeName,
            JavaTokenType::Keyword => $this->classifyKeyword($token->text),
            JavaTokenType::Identifier => $this->classifyIdentifier($token, $tokens, $index),
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
     * @param list<JavaToken> $tokens
     */
    private function classifyIdentifier(JavaToken $token, array $tokens, int $index): Scope
    {
        // In ExpectingTypeName state
        if (JavaState::ExpectingTypeName === $this->state) {
            return Scope::TypeDefinition;
        }

        // Check for 'this' keyword
        if ('this' === $token->text) {
            return Scope::VariableThis;
        }

        // Check for 'super' keyword
        if ('super' === $token->text) {
            return Scope::TypeReference;
        }

        // Check for builtin types
        if (in_array($token->text, self::BUILTIN_TYPES, true)) {
            return Scope::BuiltInType;
        }

        // Check for common reference types
        if (in_array($token->text, self::COMMON_REFERENCE_TYPES, true)) {
            return Scope::TypeReference;
        }

        // Lookahead: identifier followed by ( is a function call or definition
        $next = $this->peekNextNonWhitespace($tokens, $index);
        if (null !== $next && '(' === $next->text && JavaTokenType::Punctuation === $next->type) {
            // If braceDepth == 1 (class body), it's a method definition
            if (1 === $this->braceDepth) {
                return Scope::FunctionDefinition;
            }

            // If braceDepth >= 2 (method body), it's a function/method call
            if ($this->braceDepth >= 2) {
                return Scope::FunctionCall;
            }

            // If braceDepth == 0 (top level), could be a function definition (like main)
            if (0 === $this->braceDepth) {
                return Scope::FunctionDefinition;
            }
        }

        return Scope::Variable;
    }

    /**
     * @param list<JavaToken> $tokens
     */
    private function peekNextNonWhitespace(array $tokens, int $from): ?JavaToken
    {
        for ($i = $from + 1, $count = count($tokens); $i < $count; ++$i) {
            if (JavaTokenType::Whitespace !== $tokens[$i]->type) {
                return $tokens[$i];
            }
        }

        return null;
    }

    private function updateState(JavaToken $token): void
    {
        // Whitespace never triggers state transitions
        if (JavaTokenType::Whitespace === $token->type) {
            return;
        }

        // Update brace depth
        if (JavaTokenType::Punctuation === $token->type) {
            if ('{' === $token->text) {
                ++$this->braceDepth;

                // After class/interface/enum/record declaration, we enter class body
                if (JavaState::ExpectingTypeName === $this->state) {
                    $this->state = JavaState::InClassBody;
                }
            } elseif ('}' === $token->text) {
                --$this->braceDepth;

                // Exiting class body
                if (JavaState::InClassBody === $this->state && 0 === $this->braceDepth) {
                    $this->state = JavaState::TopLevel;
                }
            }

            return;
        }

        // Keywords that trigger state changes
        if (JavaTokenType::Keyword === $token->type) {
            if (in_array($token->text, ['class', 'interface', 'enum', 'record'], true)) {
                $this->state = JavaState::ExpectingTypeName;

                return;
            }

            if ('package' === $token->text || 'import' === $token->text) {
                // Next identifier is a namespace
                // This is handled inline in classifyIdentifier if needed
                return;
            }

            // Any other keyword resets pending states (except class body)
            if (JavaState::ExpectingTypeName === $this->state) {
                $this->state = JavaState::TopLevel;
            }

            return;
        }

        // Identifier in ExpectingTypeName resets state
        if (JavaTokenType::Identifier === $token->type && JavaState::ExpectingTypeName === $this->state) {
            // State will be reset to InClassBody when we see '{'
            return;
        }
    }
}
