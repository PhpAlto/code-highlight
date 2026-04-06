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

/**
 * Go Lexer - Pass 1: Tokenization.
 *
 * Converts raw Go source code into a stream of typed tokens.
 * Does not assign semantic meaning — that is the SemanticParser's job.
 *
 * @internal
 */
final class GoLexer
{
    private const array KEYWORDS = [
        'break', 'case', 'chan', 'const', 'continue', 'default', 'defer',
        'else', 'fallthrough', 'for', 'func', 'go', 'goto', 'if', 'import',
        'interface', 'map', 'package', 'range', 'return', 'select', 'struct',
        'switch', 'type', 'var',
    ];

    private const array BOOLEAN_LITERALS = ['true', 'false'];

    private const array NIL_LITERALS = ['nil'];

    /**
     * Tokenize Go source code.
     *
     * @return list<GoToken>
     */
    public function tokenize(string $code): array
    {
        $tokens = [];
        $position = 0;
        $length = strlen($code);

        while ($position < $length) {
            $char = $code[$position];

            // Whitespace
            if (preg_match('/\s/', $char)) {
                $ws = '';
                while ($position < $length && preg_match('/\s/', $code[$position])) {
                    $ws .= $code[$position++];
                }
                $tokens[] = new GoToken($ws, GoTokenType::Whitespace);
                continue;
            }

            // Single-line comment
            if ('/' === $char && $position + 1 < $length && '/' === $code[$position + 1]) {
                $comment = '//';
                $position += 2;
                while ($position < $length && "\n" !== $code[$position]) {
                    $comment .= $code[$position++];
                }
                $tokens[] = new GoToken($comment, GoTokenType::Comment);
                continue;
            }

            // Block comment
            if ('/' === $char && $position + 1 < $length && '*' === $code[$position + 1]) {
                $comment = '/*';
                $position += 2;
                while ($position < $length - 1) {
                    if ('*' === $code[$position] && '/' === $code[$position + 1]) {
                        $comment .= '*/';
                        $position += 2;
                        break;
                    }
                    $comment .= $code[$position++];
                }
                $tokens[] = new GoToken($comment, GoTokenType::Comment);
                continue;
            }

            // Raw string literal (backtick)
            if ('`' === $char) {
                $raw = '`';
                ++$position;
                while ($position < $length && '`' !== $code[$position]) {
                    $raw .= $code[$position++];
                }
                if ($position < $length) {
                    $raw .= $code[$position++]; // closing backtick
                }
                $tokens[] = new GoToken($raw, GoTokenType::RawString);
                continue;
            }

            // Double-quoted string
            if ('"' === $char) {
                $string = $this->parseQuotedLiteral($code, $position, '"');
                $tokens[] = new GoToken($string, GoTokenType::String);
                $position += strlen($string);
                continue;
            }

            // Rune literal (single-quoted character)
            if ("'" === $char) {
                $rune = $this->parseQuotedLiteral($code, $position, "'");
                $tokens[] = new GoToken($rune, GoTokenType::Rune);
                $position += strlen($rune);
                continue;
            }

            // Number
            if (ctype_digit($char) || ('.' === $char && $position + 1 < $length && ctype_digit($code[$position + 1]))) {
                $number = $this->parseNumber($code, $position);
                $tokens[] = new GoToken($number, GoTokenType::Number);
                $position += strlen($number);
                continue;
            }

            // Variadic / spread operator
            if ('.' === $char && $position + 2 < $length && '.' === $code[$position + 1] && '.' === $code[$position + 2]) {
                $tokens[] = new GoToken('...', GoTokenType::Operator);
                $position += 3;
                continue;
            }

            // Short variable declaration :=
            if (':' === $char && $position + 1 < $length && '=' === $code[$position + 1]) {
                $tokens[] = new GoToken(':=', GoTokenType::Operator);
                $position += 2;
                continue;
            }

            // Channel receive operator <-
            if ('<' === $char && $position + 1 < $length && '-' === $code[$position + 1]) {
                $tokens[] = new GoToken('<-', GoTokenType::Operator);
                $position += 2;
                continue;
            }

            // Other operators
            if (preg_match('/[+\-*\/%=<>!&|^]/', $char)) {
                $operator = $this->parseOperator($code, $position);
                $tokens[] = new GoToken($operator, GoTokenType::Operator);
                $position += strlen($operator);
                continue;
            }

            // Punctuation (colon alone after `:=` was already handled)
            if (preg_match('/[(){}\[\];,.]/', $char) || ':' === $char) {
                $tokens[] = new GoToken($char, GoTokenType::Punctuation);
                ++$position;
                continue;
            }

            // Identifier or keyword
            if (preg_match('/[a-zA-Z_]/', $char)) {
                $identifier = $this->parseIdentifier($code, $position);
                $type = $this->classifyIdentifier($identifier);
                $tokens[] = new GoToken($identifier, $type);
                $position += strlen($identifier);
                continue;
            }

            // Unknown character — skip
            ++$position;
        }

        return $tokens;
    }

    private function parseQuotedLiteral(string $code, int $position, string $quote): string
    {
        $literal = $quote;
        ++$position;
        $length = strlen($code);
        $escaped = false;

        while ($position < $length) {
            $char = $code[$position];
            $literal .= $char;

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
                break;
            }

            ++$position;
        }

        return $literal;
    }

    private function parseNumber(string $code, int $position): string
    {
        $number = '';
        $length = strlen($code);

        // Hex: 0x or 0X
        if ($position + 1 < $length && '0' === $code[$position] && 'x' === strtolower($code[$position + 1])) {
            $number = substr($code, $position, 2);
            $position += 2;
            while ($position < $length && (ctype_xdigit($code[$position]) || '_' === $code[$position])) {
                $number .= $code[$position++];
            }

            return $number;
        }

        // Binary: 0b or 0B
        if ($position + 1 < $length && '0' === $code[$position] && 'b' === strtolower($code[$position + 1])) {
            $number = substr($code, $position, 2);
            $position += 2;
            while ($position < $length && in_array($code[$position], ['0', '1', '_'], true)) {
                $number .= $code[$position++];
            }

            return $number;
        }

        // Octal: 0o or 0O
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

        // Decimal part (avoid consuming `...`)
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

        // Exponent
        if ($position < $length && in_array(strtolower($code[$position]), ['e'], true)) {
            $number .= $code[$position++];
            if ($position < $length && in_array($code[$position], ['+', '-'], true)) {
                $number .= $code[$position++];
            }
            while ($position < $length && ctype_digit($code[$position])) {
                $number .= $code[$position++];
            }
        }

        // Imaginary suffix
        if ($position < $length && 'i' === $code[$position]) {
            $number .= 'i';
        }

        return $number;
    }

    private function parseOperator(string $code, int $position): string
    {
        $length = strlen($code);

        // Three-character operators
        if ($position + 2 < $length) {
            $three = substr($code, $position, 3);
            if (in_array($three, ['<<=', '>>=', '&^='], true)) {
                return $three;
            }
        }

        // Two-character operators
        if ($position + 1 < $length) {
            $two = substr($code, $position, 2);
            if (in_array($two, ['==', '!=', '<=', '>=', '&&', '||', '++', '--', '<<', '>>', '+=', '-=', '*=', '/=', '%=', '&=', '|=', '^=', '&^'], true)) {
                return $two;
            }
        }

        return $code[$position];
    }

    private function parseIdentifier(string $code, int $position): string
    {
        $identifier = '';
        $length = strlen($code);

        while ($position < $length && preg_match('/[a-zA-Z0-9_]/', $code[$position])) {
            $identifier .= $code[$position++];
        }

        return $identifier;
    }

    private function classifyIdentifier(string $identifier): GoTokenType
    {
        if (in_array($identifier, self::BOOLEAN_LITERALS, true)) {
            return GoTokenType::BooleanLiteral;
        }

        if (in_array($identifier, self::NIL_LITERALS, true)) {
            return GoTokenType::NilLiteral;
        }

        if (in_array($identifier, self::KEYWORDS, true)) {
            return GoTokenType::Keyword;
        }

        return GoTokenType::Identifier;
    }
}
