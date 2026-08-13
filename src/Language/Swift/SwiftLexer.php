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

namespace Alto\Code\Highlight\Language\Swift;

/**
 * Swift Lexer - Pass 1: Tokenization.
 *
 * Converts raw Swift code into a stream of tokens.
 * Does NOT assign semantic meaning — that's the SemanticParser's job.
 *
 * @internal
 *
 * @final Not declared final to allow test doubles, but treat as final in production code
 *
 * @author Simon André <smn.andre@gmail.com>
 */
class SwiftLexer
{
    private const KEYWORDS = [
        // Declarations
        'associatedtype', 'class', 'deinit', 'enum', 'extension', 'fileprivate',
        'func', 'import', 'init', 'inout', 'internal', 'let', 'open',
        'operator', 'private', 'precedencegroup', 'protocol', 'public',
        'rethrows', 'static', 'struct', 'subscript', 'typealias', 'var', 'actor',
        // Statements
        'break', 'case', 'catch', 'continue', 'default', 'defer', 'do',
        'else', 'fallthrough', 'for', 'guard', 'if', 'in', 'repeat',
        'return', 'throw', 'switch', 'where', 'while',
        // Expressions & types
        'Any', 'as', 'await', 'false', 'is', 'nil', 'rethrows', 'self',
        'Self', 'super', 'throw', 'throws', 'true', 'try',
        // Patterns
        'some', 'any',
        // Modifiers
        'convenience', 'dynamic', 'final', 'indirect', 'lazy', 'mutating',
        'nonmutating', 'optional', 'override', 'postfix', 'prefix',
        'required', 'unowned', 'weak', 'willSet', 'didSet', 'get', 'set',
        'async',
    ];

    private const BOOLEAN_LITERALS = ['true', 'false'];

    private const NIL_LITERAL = 'nil';

    /**
     * Tokenize Swift source code.
     *
     * @return list<SwiftToken>
     */
    public function tokenize(string $code): array
    {
        $tokens = [];
        $position = 0;
        $length = strlen($code);

        while ($position < $length) {
            $char = $code[$position];

            // Whitespace
            if (' ' === $char || "\t" === $char || "\n" === $char || "\r" === $char) {
                $ws = '';
                while ($position < $length && (' ' === $code[$position] || "\t" === $code[$position] || "\n" === $code[$position] || "\r" === $code[$position])) {
                    $ws .= $code[$position];
                    ++$position;
                }
                $tokens[] = new SwiftToken($ws, SwiftTokenType::Whitespace);
                continue;
            }

            // Comments
            if ('/' === $char && $position + 1 < $length) {
                $next = $code[$position + 1];

                // Doc comment ///
                if ('/' === $next && $position + 2 < $length && '/' === $code[$position + 2]) {
                    $comment = '///';
                    $position += 3;
                    while ($position < $length && "\n" !== $code[$position]) {
                        $comment .= $code[$position];
                        ++$position;
                    }
                    $tokens[] = new SwiftToken($comment, SwiftTokenType::DocComment);
                    continue;
                }

                // Line comment //
                if ('/' === $next) {
                    $comment = '//';
                    $position += 2;
                    while ($position < $length && "\n" !== $code[$position]) {
                        $comment .= $code[$position];
                        ++$position;
                    }
                    $tokens[] = new SwiftToken($comment, SwiftTokenType::Comment);
                    continue;
                }

                // Block comment /* */ (nested in Swift)
                if ('*' === $next) {
                    $comment = '/*';
                    $position += 2;
                    $depth = 1;
                    while ($position < $length && $depth > 0) {
                        if ($position + 1 < $length && '/' === $code[$position] && '*' === $code[$position + 1]) {
                            $comment .= '/*';
                            $position += 2;
                            ++$depth;
                        } elseif ($position + 1 < $length && '*' === $code[$position] && '/' === $code[$position + 1]) {
                            $comment .= '*/';
                            $position += 2;
                            --$depth;
                        } else {
                            $comment .= $code[$position];
                            ++$position;
                        }
                    }
                    $type = strlen($comment) > 3 && '*' === $comment[2]
                        ? SwiftTokenType::DocComment
                        : SwiftTokenType::Comment;
                    $tokens[] = new SwiftToken($comment, $type);
                    continue;
                }
            }

            // Compiler directives: #if, #else, #endif, #available, #selector, #file, etc.
            if ('#' === $char && $position + 1 < $length && ctype_alpha($code[$position + 1])) {
                $directive = '#';
                ++$position;
                while ($position < $length && (ctype_alnum($code[$position]) || '_' === $code[$position])) {
                    $directive .= $code[$position];
                    ++$position;
                }
                $tokens[] = new SwiftToken($directive, SwiftTokenType::Directive);
                continue;
            }

            // Attribute: @name
            if ('@' === $char && $position + 1 < $length && (ctype_alpha($code[$position + 1]) || '_' === $code[$position + 1])) {
                $attr = '@';
                ++$position;
                while ($position < $length && (ctype_alnum($code[$position]) || '_' === $code[$position])) {
                    $attr .= $code[$position];
                    ++$position;
                }
                $tokens[] = new SwiftToken($attr, SwiftTokenType::Attribute);
                continue;
            }

            // Multi-line string """..."""
            if ('"' === $char && $position + 2 < $length && '"' === $code[$position + 1] && '"' === $code[$position + 2]) {
                $str = $this->parseMultilineString($code, $position);
                $tokens[] = new SwiftToken($str, SwiftTokenType::String);
                continue;
            }

            // String "..." (with \(...) interpolation tokenized as one unit)
            if ('"' === $char) {
                $str = $this->parseString($code, $position);
                $tokens[] = new SwiftToken($str, SwiftTokenType::String);
                continue;
            }

            // Backtick identifier: `class`, `init`, etc.
            if ('`' === $char) {
                $ident = '`';
                ++$position;
                while ($position < $length && '`' !== $code[$position]) {
                    $ident .= $code[$position];
                    ++$position;
                }
                if ($position < $length) {
                    $ident .= '`';
                    ++$position;
                }
                $tokens[] = new SwiftToken($ident, SwiftTokenType::Identifier);
                continue;
            }

            // Number
            if (ctype_digit($char)) {
                $number = $this->parseNumber($code, $position);
                $tokens[] = new SwiftToken($number, SwiftTokenType::Number);
                continue;
            }

            // Identifiers and keywords
            if (ctype_alpha($char) || '_' === $char) {
                $identifier = $this->parseIdentifier($code, $position);
                $type = $this->classifyIdentifier($identifier);
                $tokens[] = new SwiftToken($identifier, $type);
                continue;
            }

            // Multi-character operators
            if ($position + 2 < $length) {
                $three = substr($code, $position, 3);
                if (in_array($three, ['...', '..<', '??=', '<<=', '>>=', '===', '!=='], true)) {
                    $tokens[] = new SwiftToken($three, SwiftTokenType::Operator);
                    $position += 3;
                    continue;
                }
            }

            if ($position + 1 < $length) {
                $two = $char . $code[$position + 1];
                if (in_array($two, ['->', '??', '?.', '..', '&&', '||', '==', '!=', '<=', '>=', '+=', '-=', '*=', '/=', '%=', '&=', '|=', '^=', '<<', '>>', '!~', '=~'], true)) {
                    $tokens[] = new SwiftToken($two, SwiftTokenType::Operator);
                    $position += 2;
                    continue;
                }
            }

            // Single-char operators
            if (in_array($char, ['+', '-', '*', '/', '%', '=', '<', '>', '!', '&', '|', '^', '~', '?'], true)) {
                $tokens[] = new SwiftToken($char, SwiftTokenType::Operator);
                ++$position;
                continue;
            }

            // Punctuation
            if (in_array($char, ['(', ')', '{', '}', '[', ']', ';', ',', '.', ':'], true)) {
                $tokens[] = new SwiftToken($char, SwiftTokenType::Punctuation);
                ++$position;
                continue;
            }

            // Unknown — skip
            ++$position;
        }

        return $tokens;
    }

    private function parseMultilineString(string $code, int &$position): string
    {
        $str = '"""';
        $position += 3;
        $length = strlen($code);

        while ($position < $length) {
            if ($position + 2 < $length && '"' === $code[$position] && '"' === $code[$position + 1] && '"' === $code[$position + 2]) {
                $str .= '"""';
                $position += 3;

                return $str;
            }

            if ('\\' === $code[$position] && $position + 1 < $length) {
                $str .= $code[$position] . $code[$position + 1];
                $position += 2;
                // Skip interpolation content \( ... )
                if ('(' === $code[$position - 1]) {
                    $str .= $this->skipInterpolation($code, $position);
                }
                continue;
            }

            $str .= $code[$position];
            ++$position;
        }

        return $str;
    }

    private function parseString(string $code, int &$position): string
    {
        $str = '"';
        ++$position;
        $length = strlen($code);

        while ($position < $length) {
            $char = $code[$position];

            if ('\\' === $char && $position + 1 < $length) {
                $next = $code[$position + 1];
                $str .= '\\' . $next;
                $position += 2;

                // \(...) string interpolation — consume the expression as part of the string token
                if ('(' === $next) {
                    $str .= $this->skipInterpolation($code, $position);
                }
                continue;
            }

            $str .= $char;
            ++$position;

            if ('"' === $char) {
                break;
            }
        }

        return $str;
    }

    private function skipInterpolation(string $code, int &$position): string
    {
        $depth = 1;
        $expr = '';
        $length = strlen($code);

        while ($position < $length && $depth > 0) {
            $char = $code[$position];
            if ('(' === $char) {
                ++$depth;
            } elseif (')' === $char) {
                --$depth;
            }
            $expr .= $char;
            ++$position;
        }

        return $expr;
    }

    private function parseNumber(string $code, int &$position): string
    {
        $number = '';
        $length = strlen($code);

        // Hex: 0x...
        if ($position + 1 < $length && '0' === $code[$position] && 'x' === strtolower($code[$position + 1])) {
            $number = substr($code, $position, 2);
            $position += 2;
            while ($position < $length && (ctype_xdigit($code[$position]) || '_' === $code[$position] || '.' === $code[$position] || 'p' === strtolower($code[$position]))) {
                $number .= $code[$position];
                ++$position;
            }

            return $number;
        }

        // Binary: 0b...
        if ($position + 1 < $length && '0' === $code[$position] && 'b' === strtolower($code[$position + 1])) {
            $number = substr($code, $position, 2);
            $position += 2;
            while ($position < $length && in_array($code[$position], ['0', '1', '_'], true)) {
                $number .= $code[$position];
                ++$position;
            }

            return $number;
        }

        // Octal: 0o...
        if ($position + 1 < $length && '0' === $code[$position] && 'o' === strtolower($code[$position + 1])) {
            $number = substr($code, $position, 2);
            $position += 2;
            while ($position < $length && (($code[$position] >= '0' && $code[$position] <= '7') || '_' === $code[$position])) {
                $number .= $code[$position];
                ++$position;
            }

            return $number;
        }

        // Integer and float
        while ($position < $length && (ctype_digit($code[$position]) || '_' === $code[$position])) {
            $number .= $code[$position];
            ++$position;
        }

        // Float decimal
        if ($position < $length && '.' === $code[$position] && $position + 1 < $length && ctype_digit($code[$position + 1])) {
            $number .= '.';
            ++$position;
            while ($position < $length && (ctype_digit($code[$position]) || '_' === $code[$position])) {
                $number .= $code[$position];
                ++$position;
            }
        }

        // Float exponent
        if ($position < $length && 'e' === strtolower($code[$position])) {
            $number .= $code[$position];
            ++$position;
            if ($position < $length && in_array($code[$position], ['+', '-'], true)) {
                $number .= $code[$position];
                ++$position;
            }
            while ($position < $length && (ctype_digit($code[$position]) || '_' === $code[$position])) {
                $number .= $code[$position];
                ++$position;
            }
        }

        return $number;
    }

    private function parseIdentifier(string $code, int &$position): string
    {
        $identifier = '';
        $length = strlen($code);

        while ($position < $length && (ctype_alnum($code[$position]) || '_' === $code[$position])) {
            $identifier .= $code[$position];
            ++$position;
        }

        return $identifier;
    }

    private function classifyIdentifier(string $identifier): SwiftTokenType
    {
        if (self::NIL_LITERAL === $identifier) {
            return SwiftTokenType::NilLiteral;
        }

        if (in_array($identifier, self::BOOLEAN_LITERALS, true)) {
            return SwiftTokenType::BooleanLiteral;
        }

        if (in_array($identifier, self::KEYWORDS, true)) {
            return SwiftTokenType::Keyword;
        }

        return SwiftTokenType::Identifier;
    }
}
