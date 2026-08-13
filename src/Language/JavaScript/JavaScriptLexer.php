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

namespace Alto\Code\Highlight\Language\JavaScript;

/**
 * JavaScript Lexer - Pass 1: Tokenization.
 *
 * Converts raw JavaScript code into a stream of tokens.
 * Does NOT assign semantic meaning - that's the SemanticParser's job.
 *
 * @internal
 *
 * @final Not declared final to allow test doubles, but treat as final in production code
 *
 * @author Simon André <smn.andre@gmail.com>
 */
class JavaScriptLexer
{
    private const KEYWORDS = [
        'await', 'break', 'case', 'catch', 'class', 'const', 'continue',
        'debugger', 'default', 'delete', 'do', 'else', 'enum', 'export',
        'extends', 'finally', 'for', 'function', 'if', 'implements',
        'import', 'in', 'instanceof', 'interface', 'let', 'new', 'package',
        'private', 'protected', 'public', 'return', 'static', 'super',
        'switch', 'this', 'throw', 'try', 'typeof', 'var', 'void', 'while',
        'with', 'yield', 'async', 'of', 'from', 'as', 'get', 'set',
    ];

    private const BOOLEAN_LITERALS = ['true', 'false'];

    private const NULL_LITERALS = ['null', 'undefined'];

    /**
     * Tokenize JavaScript code.
     *
     * @return list<JavaScriptToken>
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
                    $ws .= $code[$position];
                    ++$position;
                }
                $tokens[] = new JavaScriptToken($ws, JavaScriptTokenType::Whitespace);

                continue;
            }

            // Single-line comment
            if ('/' === $char && $position + 1 < $length && '/' === $code[$position + 1]) {
                $comment = '//';
                $position += 2;
                while ($position < $length && "\n" !== $code[$position]) {
                    $comment .= $code[$position];
                    ++$position;
                }
                $tokens[] = new JavaScriptToken($comment, JavaScriptTokenType::Comment);

                continue;
            }

            // Multi-line comment
            if ('/' === $char && $position + 1 < $length && '*' === $code[$position + 1]) {
                $comment = '/*';
                $position += 2;
                while ($position < $length - 1) {
                    $comment .= $code[$position];
                    if ('*' === $code[$position] && '/' === $code[$position + 1]) {
                        $comment .= '/';
                        $position += 2;
                        break;
                    }
                    ++$position;
                }
                $tokens[] = new JavaScriptToken($comment, JavaScriptTokenType::Comment);

                continue;
            }

            // Template literal
            if ('`' === $char) {
                [$literal, $expressions] = $this->parseTemplateLiteral($code, $position);
                foreach ($literal as $part) {
                    $tokens[] = $part;
                }

                continue;
            }

            // String (single or double quotes)
            if ('"' === $char || "'" === $char) {
                $string = $this->parseString($code, $position, $char);
                $tokens[] = new JavaScriptToken($string, JavaScriptTokenType::String);
                $position += strlen($string);

                continue;
            }

            // Regex literal (simple detection)
            if ('/' === $char && $this->isRegexContext($tokens)) {
                $regex = $this->parseRegex($code, $position);
                if (null !== $regex) {
                    $tokens[] = new JavaScriptToken($regex, JavaScriptTokenType::Regex);
                    $position += strlen($regex);

                    continue;
                }
            }

            // Number
            if (ctype_digit($char) || ('.' === $char && $position + 1 < $length && ctype_digit($code[$position + 1]))) {
                $number = $this->parseNumber($code, $position);
                $tokens[] = new JavaScriptToken($number, JavaScriptTokenType::Number);
                $position += strlen($number);

                continue;
            }

            // Arrow function =>
            if ('=' === $char && $position + 1 < $length && '>' === $code[$position + 1]) {
                $tokens[] = new JavaScriptToken('=>', JavaScriptTokenType::Operator);
                $position += 2;

                continue;
            }

            // Spread operator ...
            if ('.' === $char && $position + 2 < $length && '.' === $code[$position + 1] && '.' === $code[$position + 2]) {
                $tokens[] = new JavaScriptToken('...', JavaScriptTokenType::Operator);
                $position += 3;

                continue;
            }

            // Operators
            if (preg_match('/[+\-*\/%=<>!&|^~?:]/', $char)) {
                $operator = $this->parseOperator($code, $position);
                $tokens[] = new JavaScriptToken($operator, JavaScriptTokenType::Operator);
                $position += strlen($operator);

                continue;
            }

            // Punctuation
            if (preg_match('/[(){}\[\];,.]/', $char)) {
                $tokens[] = new JavaScriptToken($char, JavaScriptTokenType::Punctuation);
                ++$position;

                continue;
            }

            // Identifiers and keywords
            if (preg_match('/[a-zA-Z_$]/', $char)) {
                $identifier = $this->parseIdentifier($code, $position);
                $type = $this->classifyIdentifier($identifier);
                $tokens[] = new JavaScriptToken($identifier, $type);
                $position += strlen($identifier);

                continue;
            }

            // Unknown character - skip
            ++$position;
        }

        return $tokens;
    }

    /**
     * @return array{list<JavaScriptToken>, list<string>}
     */
    private function parseTemplateLiteral(string $code, int &$position): array
    {
        $tokens = [];
        $expressions = [];
        $literal = '`';
        ++$position;
        $length = strlen($code);

        while ($position < $length) {
            $char = $code[$position];

            if ('\\' === $char) {
                $literal .= $char;
                ++$position;
                if ($position < $length) {
                    $literal .= $code[$position];
                    ++$position;
                }

                continue;
            }

            if ('$' === $char && $position + 1 < $length && '{' === $code[$position + 1]) {
                // End current literal part (if any content beyond opening backtick)
                if ('' !== $literal && '`' !== $literal) {
                    $tokens[] = new JavaScriptToken($literal, JavaScriptTokenType::TemplateLiteral);
                } elseif ('`' === $literal) {
                    // Just the opening backtick, emit it
                    $tokens[] = new JavaScriptToken('`', JavaScriptTokenType::TemplateLiteral);
                }

                // Parse expression
                $expr = '${';
                $position += 2;
                $braceCount = 1;

                while ($position < $length && $braceCount > 0) {
                    if ('{' === $code[$position]) {
                        ++$braceCount;
                    } elseif ('}' === $code[$position]) {
                        --$braceCount;
                    }
                    $expr .= $code[$position];
                    ++$position;
                }

                $tokens[] = new JavaScriptToken($expr, JavaScriptTokenType::TemplateExpression);
                $expressions[] = $expr;
                $literal = ''; // Start fresh after expression

                continue;
            }

            if ('`' === $char) {
                $literal .= $char;
                ++$position;
                $tokens[] = new JavaScriptToken($literal, JavaScriptTokenType::TemplateLiteral);

                return [$tokens, $expressions];
            }

            $literal .= $char;
            ++$position;
        }

        // Unclosed template literal
        if ('' !== $literal) {
            $tokens[] = new JavaScriptToken($literal, JavaScriptTokenType::TemplateLiteral);
        }

        return [$tokens, $expressions];
    }

    private function parseString(string $code, int $position, string $quote): string
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
                break;
            }

            ++$position;
        }

        return $string;
    }

    private function parseRegex(string $code, int $position): ?string
    {
        $regex = '/';
        ++$position;
        $length = strlen($code);
        $escaped = false;
        $inCharClass = false;

        while ($position < $length) {
            $char = $code[$position];

            if ($escaped) {
                $regex .= $char;
                $escaped = false;
                ++$position;

                continue;
            }

            if ('\\' === $char) {
                $regex .= $char;
                $escaped = true;
                ++$position;

                continue;
            }

            if ('[' === $char) {
                $inCharClass = true;
                $regex .= $char;
                ++$position;

                continue;
            }

            if (']' === $char && $inCharClass) {
                $inCharClass = false;
                $regex .= $char;
                ++$position;

                continue;
            }

            if ('/' === $char && !$inCharClass) {
                $regex .= $char;
                ++$position;

                // Parse flags
                while ($position < $length && preg_match('/[gimsuvy]/', $code[$position])) {
                    $regex .= $code[$position];
                    ++$position;
                }

                return $regex;
            }

            if ("\n" === $char) {
                // Invalid regex (newline not allowed)
                return null;
            }

            $regex .= $char;
            ++$position;
        }

        // Unclosed regex
        return null;
    }

    /**
     * @param list<JavaScriptToken> $tokens
     */
    private function isRegexContext(array $tokens): bool
    {
        // Simple heuristic: regex can follow =, (, [, ,, :, return, etc.
        if (empty($tokens)) {
            return true;
        }

        $lastToken = end($tokens);
        if (JavaScriptTokenType::Whitespace === $lastToken->type) {
            // Find the last non-whitespace token
            for ($i = count($tokens) - 1; $i >= 0; --$i) {
                if (JavaScriptTokenType::Whitespace !== $tokens[$i]->type) {
                    $lastToken = $tokens[$i];
                    break;
                }
            }
        }

        return in_array($lastToken->text, ['=', '(', '[', ',', ':', 'return', '{', ';'], true)
            || JavaScriptTokenType::Operator === $lastToken->type;
    }

    private function parseNumber(string $code, int $position): string
    {
        $number = '';
        $length = strlen($code);

        // Hex: 0x...
        if ($position + 1 < $length && '0' === $code[$position] && 'x' === strtolower($code[$position + 1])) {
            $number = '0x';
            $position += 2;
            while ($position < $length && ctype_xdigit($code[$position])) {
                $number .= $code[$position];
                ++$position;
            }

            return $number;
        }

        // Binary: 0b...
        if ($position + 1 < $length && '0' === $code[$position] && 'b' === strtolower($code[$position + 1])) {
            $number = '0b';
            $position += 2;
            while ($position < $length && in_array($code[$position], ['0', '1'], true)) {
                $number .= $code[$position];
                ++$position;
            }

            return $number;
        }

        // Octal: 0o...
        if ($position + 1 < $length && '0' === $code[$position] && 'o' === strtolower($code[$position + 1])) {
            $number = '0o';
            $position += 2;
            while ($position < $length && $code[$position] >= '0' && $code[$position] <= '7') {
                $number .= $code[$position];
                ++$position;
            }

            return $number;
        }

        // Integer and decimal
        while ($position < $length && ctype_digit($code[$position])) {
            $number .= $code[$position];
            ++$position;
        }

        // Decimal part
        if ($position < $length && '.' === $code[$position]) {
            $number .= '.';
            ++$position;
            while ($position < $length && ctype_digit($code[$position])) {
                $number .= $code[$position];
                ++$position;
            }
        }

        // Exponent
        if ($position < $length && in_array(strtolower($code[$position]), ['e'], true)) {
            $number .= $code[$position];
            ++$position;
            if ($position < $length && in_array($code[$position], ['+', '-'], true)) {
                $number .= $code[$position];
                ++$position;
            }
            while ($position < $length && ctype_digit($code[$position])) {
                $number .= $code[$position];
                ++$position;
            }
        }

        return $number;
    }

    private function parseOperator(string $code, int $position): string
    {
        $length = strlen($code);
        $char = $code[$position];

        // Three-character operators
        if ($position + 2 < $length) {
            $three = substr($code, $position, 3);
            if (in_array($three, ['===', '!==', '>>>', '**=', '<<=', '>>='], true)) {
                return $three;
            }
        }

        // Two-character operators
        if ($position + 1 < $length) {
            $two = substr($code, $position, 2);
            if (in_array($two, ['==', '!=', '<=', '>=', '&&', '||', '++', '--', '<<', '>>', '**', '+=', '-=', '*=', '/=', '%=', '&=', '|=', '^=', '??', '?.'], true)) {
                return $two;
            }
        }

        // Single-character operator
        return $char;
    }

    private function parseIdentifier(string $code, int $position): string
    {
        $identifier = '';
        $length = strlen($code);

        while ($position < $length && preg_match('/[a-zA-Z0-9_$]/', $code[$position])) {
            $identifier .= $code[$position];
            ++$position;
        }

        return $identifier;
    }

    private function classifyIdentifier(string $identifier): JavaScriptTokenType
    {
        if (in_array($identifier, self::BOOLEAN_LITERALS, true)) {
            return JavaScriptTokenType::BooleanLiteral;
        }

        if (in_array($identifier, self::NULL_LITERALS, true)) {
            return JavaScriptTokenType::NullLiteral;
        }

        if (in_array($identifier, self::KEYWORDS, true)) {
            return JavaScriptTokenType::Keyword;
        }

        return JavaScriptTokenType::Identifier;
    }
}
