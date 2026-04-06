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

/**
 * Python Lexer - Pass 1: Tokenization.
 *
 * Converts raw Python source code into a stream of typed tokens.
 * Does not assign semantic meaning — that is the SemanticParser's job.
 *
 * @internal
 */
final class PythonLexer
{
    private const array KEYWORDS = [
        'False', 'None', 'True', 'and', 'as', 'assert', 'async', 'await',
        'break', 'class', 'continue', 'def', 'del', 'elif', 'else', 'except',
        'finally', 'for', 'from', 'global', 'if', 'import', 'in', 'is',
        'lambda', 'nonlocal', 'not', 'or', 'pass', 'raise', 'return', 'try',
        'while', 'with', 'yield',
    ];

    private const array BOOLEAN_LITERALS = ['True', 'False'];

    private const array NIL_LITERALS = ['None'];

    /**
     * Tokenize Python source code.
     *
     * @return list<PythonToken>
     */
    public function tokenize(string $code): array
    {
        $tokens = [];
        $position = 0;
        $length = strlen($code);

        while ($position < $length) {
            $char = $code[$position];

            // Whitespace (greedy, include newlines and indentation)
            if (preg_match('/\s/', $char)) {
                $ws = '';
                while ($position < $length && preg_match('/\s/', $code[$position])) {
                    $ws .= $code[$position++];
                }
                $tokens[] = new PythonToken($ws, PythonTokenType::Whitespace);
                continue;
            }

            // Comment: # to end of line (only if at start or preceded by whitespace)
            if ('#' === $char) {
                $isCommentStart = 0 === $position
                    || (!empty($tokens) && PythonTokenType::Whitespace === end($tokens)->type);

                if ($isCommentStart) {
                    $comment = '';
                    while ($position < $length && "\n" !== $code[$position]) {
                        $comment .= $code[$position++];
                    }
                    $tokens[] = new PythonToken($comment, PythonTokenType::Comment);
                    continue;
                }
                // Otherwise, treat as operator
            }

            // Decorator: @ followed by identifier
            if ('@' === $char) {
                $decorator = '@';
                ++$position;
                $identifier = $this->parseIdentifier($code, $position);
                $decorator .= $identifier;
                $tokens[] = new PythonToken($decorator, PythonTokenType::Decorator);
                $position += strlen($identifier);
                continue;
            }

            // Triple-quoted strings: """...""" or '''...'''
            if (
                ($position + 2 < $length && '"' === $code[$position] && '"' === $code[$position + 1] && '"' === $code[$position + 2])
                || ($position + 2 < $length && "'" === $code[$position] && "'" === $code[$position + 1] && "'" === $code[$position + 2])
            ) {
                $quote = substr($code, $position, 3);
                $string = $this->parseTripleQuotedString($code, $position, $quote);
                $tokens[] = new PythonToken($string, PythonTokenType::String);
                $position += strlen($string);
                continue;
            }

            // Regular strings: "..." or '...'
            if ('"' === $char || "'" === $char) {
                $quote = $char;
                // Check for f/r/b prefix
                $prefix = '';
                if ($position > 0 && preg_match('/[fFrRbB]/', $code[$position - 1])) {
                    // The prefix was already parsed as an identifier or keyword
                    // We'll handle this in a post-processing step, or just include the string
                }
                $string = $this->parseQuotedString($code, $position, $quote);
                $tokens[] = new PythonToken($string, PythonTokenType::String);
                $position += strlen($string);
                continue;
            }

            // Number
            if (ctype_digit($char) || ('.' === $char && $position + 1 < $length && ctype_digit($code[$position + 1]))) {
                $number = $this->parseNumber($code, $position);
                $tokens[] = new PythonToken($number, PythonTokenType::Number);
                $position += strlen($number);
                continue;
            }

            // Two-character operators
            if ($position + 1 < $length) {
                $two = substr($code, $position, 2);
                if (in_array($two, ['==', '!=', '<=', '>=', '//', '**', '<<', '>>', '+=', '-=', '*=', '/=', '%=', '&=', '|=', '^=', '//=', '**=', '@=', '->'], true)) {
                    $tokens[] = new PythonToken($two, PythonTokenType::Operator);
                    $position += 2;
                    continue;
                }
            }

            // Single-character operators
            if (preg_match('/[+\-*\/%=<>!&|^~]/', $char)) {
                $tokens[] = new PythonToken($char, PythonTokenType::Operator);
                ++$position;
                continue;
            }

            // Punctuation
            if (preg_match('/[(){}\[\];:,.]/', $char)) {
                $tokens[] = new PythonToken($char, PythonTokenType::Punctuation);
                ++$position;
                continue;
            }

            // Identifier or keyword
            if (preg_match('/[a-zA-Z_]/', $char)) {
                // Check for f/r/b prefix before strings (e.g., f"...", r"...")
                $identifier = $this->parseIdentifier($code, $position);
                if (
                    ($position + strlen($identifier) < $length)
                    && preg_match('/["\']/', $code[$position + strlen($identifier)])
                    && preg_match('/[fFrRbB]/', $identifier)
                ) {
                    // This is a string prefix; include it in the string token
                    $prefixPos = $position;
                    $position += strlen($identifier);
                    $char = $code[$position];
                    $quote = $char;

                    // Check for triple quotes after prefix
                    if ($position + 2 < $length && $char === $code[$position + 1] && $char === $code[$position + 2]) {
                        $tripleQuote = substr($code, $position, 3);
                        $string = $this->parseTripleQuotedString($code, $position, $tripleQuote);
                        $tokens[] = new PythonToken($identifier.$string, PythonTokenType::String);
                        $position += strlen($string);
                    } else {
                        $string = $this->parseQuotedString($code, $position, $quote);
                        $tokens[] = new PythonToken($identifier.$string, PythonTokenType::String);
                        $position += strlen($string);
                    }
                } else {
                    $type = $this->classifyIdentifier($identifier);
                    $tokens[] = new PythonToken($identifier, $type);
                    $position += strlen($identifier);
                }
                continue;
            }

            // Unknown character — skip
            ++$position;
        }

        return $tokens;
    }

    /**
     * Parse a quoted string (single or double quote).
     * Pass by value: caller increments position.
     */
    private function parseQuotedString(string $code, int $position, string $quote): string
    {
        $string = $quote;
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

            if ($char === $quote) {
                ++$position;
                break;
            }

            ++$position;
        }

        return $string;
    }

    /**
     * Parse a triple-quoted string.
     * Pass by value: caller increments position.
     */
    private function parseTripleQuotedString(string $code, int $position, string $quote): string
    {
        $string = $quote;
        $position += 3;
        $length = strlen($code);
        $quoteChar = $quote[0];

        while ($position < $length - 2) {
            if ($quoteChar === $code[$position] && $quoteChar === $code[$position + 1] && $quoteChar === $code[$position + 2]) {
                $string .= $quote;
                $position += 3;
                break;
            }
            $string .= $code[$position++];
        }

        return $string;
    }

    /**
     * Parse a number (integer or float, with optional exponent).
     * Pass by value: caller increments position.
     */
    private function parseNumber(string $code, int $position): string
    {
        $number = '';
        $length = strlen($code);

        // Hex: 0x
        if ($position + 1 < $length && '0' === $code[$position] && 'x' === strtolower($code[$position + 1])) {
            $number = substr($code, $position, 2);
            $position += 2;
            while ($position < $length && (ctype_xdigit($code[$position]) || '_' === $code[$position])) {
                $number .= $code[$position++];
            }

            return $number;
        }

        // Binary: 0b
        if ($position + 1 < $length && '0' === $code[$position] && 'b' === strtolower($code[$position + 1])) {
            $number = substr($code, $position, 2);
            $position += 2;
            while ($position < $length && in_array($code[$position], ['0', '1', '_'], true)) {
                $number .= $code[$position++];
            }

            return $number;
        }

        // Octal: 0o
        if ($position + 1 < $length && '0' === $code[$position] && 'o' === strtolower($code[$position + 1])) {
            $number = substr($code, $position, 2);
            $position += 2;
            while ($position < $length && (($code[$position] >= '0' && $code[$position] <= '7') || '_' === $code[$position])) {
                $number .= $code[$position++];
            }

            return $number;
        }

        // Decimal integer / float
        while ($position < $length && (ctype_digit($code[$position]) || '_' === $code[$position])) {
            $number .= $code[$position++];
        }

        // Decimal part (avoid consuming multiple dots for list slicing)
        if (
            $position < $length
            && '.' === $code[$position]
            && ($position + 1 >= $length || '.' !== $code[$position + 1])
        ) {
            $number .= '.';
            ++$position;
            while ($position < $length && ctype_digit($code[$position])) {
                $number .= $code[$position++];
            }
        }

        // Exponent (e or E)
        if ($position < $length && in_array(strtolower($code[$position]), ['e'], true)) {
            $number .= $code[$position++];
            if ($position < $length && in_array($code[$position], ['+', '-'], true)) {
                $number .= $code[$position++];
            }
            while ($position < $length && ctype_digit($code[$position])) {
                $number .= $code[$position++];
            }
        }

        // Complex suffix (j)
        if ($position < $length && in_array(strtolower($code[$position]), ['j'], true)) {
            $number .= $code[$position++];
        }

        return $number;
    }

    /**
     * Parse an identifier.
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

    private function classifyIdentifier(string $identifier): PythonTokenType
    {
        if (in_array($identifier, self::BOOLEAN_LITERALS, true)) {
            return PythonTokenType::BooleanLiteral;
        }

        if (in_array($identifier, self::NIL_LITERALS, true)) {
            return PythonTokenType::NilLiteral;
        }

        if (in_array($identifier, self::KEYWORDS, true)) {
            return PythonTokenType::Keyword;
        }

        return PythonTokenType::Identifier;
    }
}
