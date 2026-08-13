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

/**
 * Java Lexer - Pass 1: Tokenization.
 *
 * Converts raw Java source code into a stream of typed tokens.
 * Does not assign semantic meaning — that is the SemanticParser's job.
 *
 * @internal
 *
 * @author Simon André <smn.andre@gmail.com>
 */
final class JavaLexer
{
    private const array KEYWORDS = [
        // Modifiers
        'abstract', 'final', 'native', 'private', 'protected', 'public', 'static',
        'strictfp', 'synchronized', 'transient', 'volatile', 'sealed', 'non-sealed',
        // Declarations
        'class', 'interface', 'enum', 'extends', 'implements', 'import', 'package',
        'record', 'permits', 'throws',
        // Control flow
        'break', 'case', 'catch', 'continue', 'default', 'do', 'else', 'finally',
        'for', 'if', 'instanceof', 'new', 'return', 'switch', 'throw', 'try', 'while',
        // Other
        'assert', 'const', 'goto', 'super', 'this', 'var', 'yield',
    ];

    private const array BOOLEAN_LITERALS = ['true', 'false'];

    private const array NULL_LITERALS = ['null'];

    /**
     * Tokenize Java source code.
     *
     * @return list<JavaToken>
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
                $tokens[] = new JavaToken($ws, JavaTokenType::Whitespace);
                continue;
            }

            // Documentation comment /** ... */
            if ('/' === $char && $position + 2 < $length && '*' === $code[$position + 1] && '*' === $code[$position + 2]) {
                $comment = '/**';
                $position += 3;
                while ($position < $length - 1) {
                    if ('*' === $code[$position] && '/' === $code[$position + 1]) {
                        $comment .= '*/';
                        $position += 2;
                        break;
                    }
                    $comment .= $code[$position++];
                }
                $tokens[] = new JavaToken($comment, JavaTokenType::DocComment);
                continue;
            }

            // Block comment /* ... */
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
                $tokens[] = new JavaToken($comment, JavaTokenType::Comment);
                continue;
            }

            // Single-line comment //
            if ('/' === $char && $position + 1 < $length && '/' === $code[$position + 1]) {
                $comment = '//';
                $position += 2;
                while ($position < $length && "\n" !== $code[$position]) {
                    $comment .= $code[$position++];
                }
                $tokens[] = new JavaToken($comment, JavaTokenType::Comment);
                continue;
            }

            // Annotation @Identifier
            if ('@' === $char && $position + 1 < $length && preg_match('/[a-zA-Z_]/', $code[$position + 1])) {
                $annotation = '@';
                ++$position;
                $identifier = $this->parseIdentifier($code, $position);
                $annotation .= $identifier;
                $position += strlen($identifier);
                $tokens[] = new JavaToken($annotation, JavaTokenType::Annotation);
                continue;
            }

            // String literal "..."
            if ('"' === $char) {
                $string = $this->parseString($code, $position);
                $tokens[] = new JavaToken($string, JavaTokenType::String);
                $position += strlen($string);
                continue;
            }

            // Character literal '...'
            if ("'" === $char) {
                $char_lit = $this->parseCharLiteral($code, $position);
                $tokens[] = new JavaToken($char_lit, JavaTokenType::CharLiteral);
                $position += strlen($char_lit);
                continue;
            }

            // Number
            if (ctype_digit($char) || ('.' === $char && $position + 1 < $length && ctype_digit($code[$position + 1]))) {
                $number = $this->parseNumber($code, $position);
                $tokens[] = new JavaToken($number, JavaTokenType::Number);
                $position += strlen($number);
                continue;
            }

            // Operators and compound operators
            if (preg_match('/[+\-*\/%=<>!&|^~?:]/', $char)) {
                $operator = $this->parseOperator($code, $position);
                $tokens[] = new JavaToken($operator, JavaTokenType::Operator);
                $position += strlen($operator);
                continue;
            }

            // Punctuation
            if (preg_match('/[(){}\[\];,.]/', $char)) {
                $tokens[] = new JavaToken($char, JavaTokenType::Punctuation);
                ++$position;
                continue;
            }

            // Identifier or keyword
            if (preg_match('/[a-zA-Z_]/', $char)) {
                $identifier = $this->parseIdentifier($code, $position);
                $type = $this->classifyIdentifier($identifier);
                $tokens[] = new JavaToken($identifier, $type);
                $position += strlen($identifier);
                continue;
            }

            // Unknown character — skip it
            ++$position;
        }

        return $tokens;
    }

    private function parseString(string $code, int $position): string
    {
        $string = '"';
        ++$position;
        $length = strlen($code);

        while ($position < $length && '"' !== $code[$position]) {
            if ('\\' === $code[$position] && $position + 1 < $length) {
                $string .= $code[$position++];
                $string .= $code[$position++];
            } else {
                $string .= $code[$position++];
            }
        }

        if ($position < $length) {
            $string .= '"';
        }

        return $string;
    }

    private function parseCharLiteral(string $code, int $position): string
    {
        $char = "'";
        ++$position;
        $length = strlen($code);

        while ($position < $length && "'" !== $code[$position]) {
            if ('\\' === $code[$position] && $position + 1 < $length) {
                $char .= $code[$position++];
                $char .= $code[$position++];
            } else {
                $char .= $code[$position++];
            }
        }

        if ($position < $length) {
            $char .= "'";
        }

        return $char;
    }

    private function parseNumber(string $code, int $position): string
    {
        $number = '';
        $length = strlen($code);

        // Hexadecimal: 0x or 0X
        if ('0' === $code[$position] && $position + 1 < $length && 'x' === strtolower($code[$position + 1])) {
            $number = substr($code, $position, 2);
            $position += 2;
            while ($position < $length && (ctype_xdigit($code[$position]) || '_' === $code[$position])) {
                $number .= $code[$position++];
            }
            // Suffix: L, l, f, F, d, D
            if ($position < $length && in_array($code[$position], ['L', 'l', 'f', 'F', 'd', 'D'], true)) {
                $number .= $code[$position++];
            }

            return $number;
        }

        // Binary: 0b or 0B
        if ('0' === $code[$position] && $position + 1 < $length && 'b' === strtolower($code[$position + 1])) {
            $number = substr($code, $position, 2);
            $position += 2;
            while ($position < $length && ('0' === $code[$position] || '1' === $code[$position] || '_' === $code[$position])) {
                $number .= $code[$position++];
            }
            // Suffix
            if ($position < $length && in_array($code[$position], ['L', 'l', 'f', 'F', 'd', 'D'], true)) {
                $number .= $code[$position++];
            }

            return $number;
        }

        // Octal: 0 followed by digits
        if ('0' === $code[$position] && $position + 1 < $length && ctype_digit($code[$position + 1])) {
            $number = '0';
            ++$position;
            while ($position < $length && (($code[$position] >= '0' && $code[$position] <= '7') || '_' === $code[$position])) {
                $number .= $code[$position++];
            }
            // Suffix
            if ($position < $length && in_array($code[$position], ['L', 'l', 'f', 'F', 'd', 'D'], true)) {
                $number .= $code[$position++];
            }

            return $number;
        }

        // Decimal integer / float
        while ($position < $length && (ctype_digit($code[$position]) || '_' === $code[$position])) {
            $number .= $code[$position++];
        }

        // Decimal part
        if ($position < $length && '.' === $code[$position] && ($position + 1 >= $length || '.' !== $code[$position + 1])) {
            $number .= '.';
            ++$position;
            while ($position < $length && (ctype_digit($code[$position]) || '_' === $code[$position])) {
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

        // Suffix: L, l, f, F, d, D
        if ($position < $length && in_array($code[$position], ['L', 'l', 'f', 'F', 'd', 'D'], true)) {
            $number .= $code[$position++];
        }

        return $number;
    }

    private function parseOperator(string $code, int $position): string
    {
        $length = strlen($code);

        // Three-character operators
        if ($position + 2 < $length) {
            $three = substr($code, $position, 3);
            if (in_array($three, ['<<=', '>>=', '>>>', '...'], true)) {
                return $three;
            }
        }

        // Two-character operators
        if ($position + 1 < $length) {
            $two = substr($code, $position, 2);
            if (in_array($two, ['++', '--', '==', '!=', '<=', '>=', '&&', '||', '+=', '-=', '*=', '/=', '%=', '&=', '|=', '^=', '<<', '>>', '->', '::'], true)) {
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

    private function classifyIdentifier(string $identifier): JavaTokenType
    {
        if (in_array($identifier, self::BOOLEAN_LITERALS, true)) {
            return JavaTokenType::BooleanLiteral;
        }

        if (in_array($identifier, self::NULL_LITERALS, true)) {
            return JavaTokenType::NullLiteral;
        }

        if (in_array($identifier, self::KEYWORDS, true)) {
            return JavaTokenType::Keyword;
        }

        return JavaTokenType::Identifier;
    }
}
