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

/**
 * C# Lexer - Pass 1: Tokenization.
 *
 * Converts raw C# source code into a stream of typed tokens.
 * Does not assign semantic meaning — that is the SemanticParser's job.
 *
 * @internal
 *
 * @author Simon André <smn.andre@gmail.com>
 */
final class CSharpLexer
{
    private const array KEYWORDS = [
        'abstract', 'as', 'async', 'await', 'base', 'break', 'case', 'catch',
        'checked', 'class', 'const', 'continue', 'default', 'delegate', 'do',
        'else', 'enum', 'event', 'explicit', 'extern', 'false', 'finally',
        'fixed', 'for', 'foreach', 'goto', 'global', 'if', 'implicit', 'in',
        'interface', 'internal', 'is', 'lock', 'namespace', 'new', 'null',
        'operator', 'out', 'override', 'params', 'partial', 'private',
        'protected', 'public', 'readonly', 'record', 'ref', 'return', 'sealed',
        'sizeof', 'stackalloc', 'static', 'struct', 'switch', 'this', 'throw',
        'true', 'try', 'typeof', 'unchecked', 'unsafe', 'using', 'virtual',
        'volatile', 'while', 'yield', 'nameof', 'where', 'with', 'when',
    ];

    private const array BOOLEAN_LITERALS = ['true', 'false'];

    private const array NULL_LITERALS = ['null'];

    /**
     * Tokenize C# source code.
     *
     * @return list<CSharpToken>
     */
    public function tokenize(string $code): array
    {
        $tokens = [];
        $position = 0;
        $length = strlen($code);

        while ($position < $length) {
            $char = $code[$position];

            // Whitespace (greedy, include newlines)
            if (preg_match('/\s/', $char)) {
                $ws = '';
                while ($position < $length && preg_match('/\s/', $code[$position])) {
                    $ws .= $code[$position++];
                }
                $tokens[] = new CSharpToken($ws, CSharpTokenType::Whitespace);
                continue;
            }

            // Preprocessor directives: # at start of line (with possible whitespace)
            if ('#' === $char) {
                $directive = $this->parseDirective($code, $position);
                $tokens[] = new CSharpToken($directive, CSharpTokenType::Directive);
                $position += strlen($directive);
                continue;
            }

            // Doc comment: ///
            if ($position + 2 < $length && '/' === $char && '/' === $code[$position + 1] && '/' === $code[$position + 2]) {
                $comment = '';
                while ($position < $length && "\n" !== $code[$position]) {
                    $comment .= $code[$position++];
                }
                $tokens[] = new CSharpToken($comment, CSharpTokenType::DocComment);
                continue;
            }

            // Doc comment block: /** ... */
            if ($position + 2 < $length && '/' === $char && '*' === $code[$position + 1] && '*' === $code[$position + 2] && ($position + 3 >= $length || ' ' === $code[$position + 3] || '*' === $code[$position + 3])) {
                $comment = $this->parseBlockComment($code, $position);
                $tokens[] = new CSharpToken($comment, CSharpTokenType::DocComment);
                $position += strlen($comment);
                continue;
            }

            // Line comment: //
            if ('/' === $char && $position + 1 < $length && '/' === $code[$position + 1]) {
                $comment = '';
                while ($position < $length && "\n" !== $code[$position]) {
                    $comment .= $code[$position++];
                }
                $tokens[] = new CSharpToken($comment, CSharpTokenType::Comment);
                continue;
            }

            // Block comment: /* ... */
            if ('/' === $char && $position + 1 < $length && '*' === $code[$position + 1]) {
                $comment = $this->parseBlockComment($code, $position);
                $tokens[] = new CSharpToken($comment, CSharpTokenType::Comment);
                $position += strlen($comment);
                continue;
            }

            // Attribute: [
            if ('[' === $char) {
                $tokens[] = new CSharpToken('[', CSharpTokenType::Punctuation);
                ++$position;
                continue;
            }

            // Attribute closing: ]
            if (']' === $char) {
                $tokens[] = new CSharpToken(']', CSharpTokenType::Punctuation);
                ++$position;
                continue;
            }

            // Verbatim interpolated string: @$"..." or $@"..."
            if (
                ($position + 2 < $length && '@' === $char && '$' === $code[$position + 1] && ('"' === $code[$position + 2]))
                || ($position + 2 < $length && '$' === $char && '@' === $code[$position + 1] && ('"' === $code[$position + 2]))
            ) {
                $prefix = substr($code, $position, 2);
                $position += 2;
                $string = $this->parseVerbatimString($code, $position);
                $tokens[] = new CSharpToken($prefix . $string, CSharpTokenType::VerbatimString);
                $position += strlen($string);
                continue;
            }

            // Interpolated string: $"..."
            if ('$' === $char && $position + 1 < $length && '"' === $code[$position + 1]) {
                $string = $this->parseInterpolatedString($code, $position);
                $tokens[] = new CSharpToken($string, CSharpTokenType::Interpolation);
                $position += strlen($string);
                continue;
            }

            // Verbatim string: @"..."
            if ('@' === $char && $position + 1 < $length && '"' === $code[$position + 1]) {
                $string = $this->parseVerbatimString($code, $position);
                $tokens[] = new CSharpToken($string, CSharpTokenType::VerbatimString);
                $position += strlen($string);
                continue;
            }

            // Regular string: "..."
            if ('"' === $char) {
                $string = $this->parseString($code, $position);
                $tokens[] = new CSharpToken($string, CSharpTokenType::String);
                $position += strlen($string);
                continue;
            }

            // Char literal: '...'
            if ("'" === $char) {
                $string = $this->parseCharLiteral($code, $position);
                $tokens[] = new CSharpToken($string, CSharpTokenType::String);
                $position += strlen($string);
                continue;
            }

            // Number
            if (ctype_digit($char) || ('.' === $char && $position + 1 < $length && ctype_digit($code[$position + 1]))) {
                $number = $this->parseNumber($code, $position);
                $tokens[] = new CSharpToken($number, CSharpTokenType::Number);
                $position += strlen($number);
                continue;
            }

            // Two-character operators
            if ($position + 1 < $length) {
                $two = substr($code, $position, 2);
                if (in_array($two, ['++', '--', '==', '!=', '<=', '>=', '&&', '||', '??', '?.', '=>', '->', '::', '+=', '-=', '*=', '/=', '%=', '&=', '|=', '^=', '<<=', '>>=', '??=', '<<', '>>'], true)) {
                    $tokens[] = new CSharpToken($two, CSharpTokenType::Operator);
                    $position += 2;
                    continue;
                }
            }

            // Single-character operators
            if (preg_match('/[+\-*\/%=<>!&|^~?.]/', $char)) {
                $tokens[] = new CSharpToken($char, CSharpTokenType::Operator);
                ++$position;
                continue;
            }

            // Punctuation
            if (preg_match('/[(){};,]/', $char)) {
                $tokens[] = new CSharpToken($char, CSharpTokenType::Punctuation);
                ++$position;
                continue;
            }

            // Identifier or keyword
            if (preg_match('/[a-zA-Z_]/', $char)) {
                $identifier = $this->parseIdentifier($code, $position);
                $type = $this->classifyIdentifier($identifier);
                $tokens[] = new CSharpToken($identifier, $type);
                $position += strlen($identifier);
                continue;
            }

            // Unknown character — skip
            ++$position;
        }

        return $tokens;
    }

    /**
     * Parse identifier: letters, digits, underscore.
     * Pass by value: caller increments position.
     */
    private function parseIdentifier(string $code, int $position): string
    {
        $identifier = '';
        $length = strlen($code);

        while ($position < $length && preg_match('/[a-zA-Z0-9_]/', $code[$position])) {
            $identifier .= $code[$position++];
        }

        return $identifier;
    }

    /**
     * Parse a string: "..." with escape sequences.
     * Pass by value: caller increments position.
     */
    private function parseString(string $code, int $position): string
    {
        $string = '"';
        ++$position;
        $length = strlen($code);
        $escaped = false;

        while ($position < $length) {
            $char = $code[$position];
            $string .= $char;

            if ($escaped) {
                $escaped = false;
                ++$position;
                continue;
            }

            if ('\\' === $char) {
                $escaped = true;
                ++$position;
                continue;
            }

            if ('"' === $char) {
                ++$position;
                break;
            }

            ++$position;
        }

        return $string;
    }

    /**
     * Parse verbatim string: @"..." (no escape, "" = literal quote).
     * Pass by value: caller increments position.
     */
    private function parseVerbatimString(string $code, int $position): string
    {
        $string = '@"';
        $position += 2;
        $length = strlen($code);

        while ($position < $length) {
            $char = $code[$position];
            $string .= $char;

            if ('"' === $char) {
                ++$position;
                if ($position < $length && '"' === $code[$position]) {
                    // Escaped quote in verbatim string
                    $string .= '"';
                    ++$position;
                } else {
                    // End of string
                    break;
                }
            } else {
                ++$position;
            }
        }

        return $string;
    }

    /**
     * Parse interpolated string: $"..." (may contain {expr}).
     * Pass by value: caller increments position.
     */
    private function parseInterpolatedString(string $code, int $position): string
    {
        $string = '$"';
        $position += 2;
        $length = strlen($code);
        $escaped = false;
        $braceDepth = 0;

        while ($position < $length) {
            $char = $code[$position];
            $string .= $char;

            if ($escaped) {
                $escaped = false;
                ++$position;
                continue;
            }

            if ('\\' === $char) {
                $escaped = true;
                ++$position;
                continue;
            }

            if ('{' === $char) {
                if ($position + 1 < $length && '{' === $code[$position + 1]) {
                    // Escaped brace
                    $string .= '{';
                    $position += 2;
                    continue;
                }
                ++$braceDepth;
                ++$position;
                continue;
            }

            if ('}' === $char) {
                if ($braceDepth > 0) {
                    --$braceDepth;
                } elseif ($position + 1 < $length && '}' === $code[$position + 1]) {
                    // Escaped brace
                    $string .= '}';
                    $position += 2;
                    continue;
                }
                ++$position;
                continue;
            }

            if ('"' === $char && 0 === $braceDepth) {
                ++$position;
                break;
            }

            ++$position;
        }

        return $string;
    }

    /**
     * Parse char literal: '...' (single character).
     * Pass by value: caller increments position.
     */
    private function parseCharLiteral(string $code, int $position): string
    {
        $char_lit = "'";
        ++$position;
        $length = strlen($code);
        $escaped = false;

        while ($position < $length) {
            $c = $code[$position];
            $char_lit .= $c;

            if ($escaped) {
                $escaped = false;
                ++$position;
                continue;
            }

            if ('\\' === $c) {
                $escaped = true;
                ++$position;
                continue;
            }

            if ("'" === $c) {
                ++$position;
                break;
            }

            ++$position;
        }

        return $char_lit;
    }

    /**
     * Parse a number: decimal, hex (0x), binary (0b), with optional _ separator.
     * Pass by value: caller increments position.
     */
    private function parseNumber(string $code, int $position): string
    {
        $number = '';
        $length = strlen($code);

        // Check for hex (0x) or binary (0b)
        if ('0' === $code[$position] && $position + 1 < $length) {
            if ('x' === $code[$position + 1] || 'X' === $code[$position + 1]) {
                // Hex number
                $number = substr($code, $position, 2);
                $position += 2;
                while ($position < $length && (ctype_xdigit($code[$position]) || '_' === $code[$position])) {
                    $number .= $code[$position++];
                }

                return $number;
            } elseif ('b' === $code[$position + 1] || 'B' === $code[$position + 1]) {
                // Binary number
                $number = substr($code, $position, 2);
                $position += 2;
                while ($position < $length && ('0' === $code[$position] || '1' === $code[$position] || '_' === $code[$position])) {
                    $number .= $code[$position++];
                }

                return $number;
            }
        }

        // Decimal or float
        while ($position < $length && (ctype_digit($code[$position]) || '_' === $code[$position])) {
            $number .= $code[$position++];
        }

        // Decimal point
        if ($position < $length && '.' === $code[$position]) {
            $number .= $code[$position++];
            while ($position < $length && (ctype_digit($code[$position]) || '_' === $code[$position])) {
                $number .= $code[$position++];
            }
        }

        // Exponent
        if ($position < $length && ('e' === $code[$position] || 'E' === $code[$position])) {
            $number .= $code[$position++];
            if ($position < $length && ('+' === $code[$position] || '-' === $code[$position])) {
                $number .= $code[$position++];
            }
            while ($position < $length && ctype_digit($code[$position])) {
                $number .= $code[$position++];
            }
        }

        // Suffix (f, F, d, D, m, M, l, L, ul, UL, etc.)
        while ($position < $length && preg_match('/[fFdDmMlLuU]/', $code[$position])) {
            $number .= $code[$position++];
        }

        return $number;
    }

    /**
     * Parse a preprocessor directive (entire line starting with #).
     * Pass by value: caller increments position.
     */
    private function parseDirective(string $code, int $position): string
    {
        $directive = '';
        $length = strlen($code);

        while ($position < $length && "\n" !== $code[$position]) {
            $directive .= $code[$position++];
        }

        return $directive;
    }

    /**
     * Parse a block comment: slash-star to star-slash or slash-star-star to star-slash.
     * Pass by value: caller increments position.
     */
    private function parseBlockComment(string $code, int $position): string
    {
        $comment = '';
        $length = strlen($code);

        // Read /* or /**
        $comment .= $code[$position++];  // /
        $comment .= $code[$position++];  // *
        if ($position < $length && '*' === $code[$position]) {
            $comment .= $code[$position++];  // second *
        }

        // Read until */
        while ($position < $length - 1) {
            if ('*' === $code[$position] && '/' === $code[$position + 1]) {
                $comment .= '*';
                $comment .= '/';
                $position += 2;
                break;
            }
            $comment .= $code[$position++];
        }

        // Handle end of code
        if ($position < $length) {
            $comment .= $code[$position++];
        }

        return $comment;
    }

    /**
     * Classify an identifier as keyword, boolean, null, or generic identifier.
     */
    private function classifyIdentifier(string $text): CSharpTokenType
    {
        if (in_array($text, self::BOOLEAN_LITERALS, true)) {
            return CSharpTokenType::BooleanLiteral;
        }

        if (in_array($text, self::NULL_LITERALS, true)) {
            return CSharpTokenType::NullLiteral;
        }

        if (in_array($text, self::KEYWORDS, true)) {
            return CSharpTokenType::Keyword;
        }

        return CSharpTokenType::Identifier;
    }
}
