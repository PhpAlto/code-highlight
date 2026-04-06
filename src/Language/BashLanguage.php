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

namespace Alto\Code\Highlight\Language;

use Alto\Code\Highlight\Parser\ParsedStream;
use Alto\Code\Highlight\Parser\ParsedToken;
use Alto\Code\Highlight\Scope;

/**
 * BashLanguage language parser.
 *
 * Automatically generated from Highlight.js language definition.
 * Generated: 2025-12-20 13:28:55
 * Source: https://github.com/highlightjs/highlight.js
 *
 * MANUAL REVIEW REQUIRED:
 * - Semantic scope assignments may need refinement
 * - Context-aware parsing may need implementation
 * - Test coverage should be verified
 * - Comment syntax should be validated
 */
final class BashLanguage implements LanguageInterface
{
    private const KEYWORDS = [
        'if', 'then', 'else', 'elif', 'fi', 'for', 'while', 'do', 'done',
        'case', 'esac', 'function', 'return', 'export', 'local',
        'declare', 'typeset', 'readonly', 'unset', 'alias', 'unalias',
    ];

    private const BUILTINS = [
        'echo', 'printf', 'read', 'cd', 'pwd', 'ls', 'rm', 'cp', 'mv',
        'mkdir', 'rmdir', 'touch', 'cat', 'grep', 'sed', 'awk',
        'test', 'true', 'false', 'exit', 'return', 'set', 'shopt',
    ];

    public function getIdentifier(): string
    {
        return 'bash';
    }

    public function parse(string $code): ParsedStream
    {
        $tokens = [];
        $position = 0;
        $length = strlen($code);

        while ($position < $length) {
            // Comment (# to end of line)
            if ('#' === $code[$position] && (0 === $position || ctype_space($code[$position - 1]))) {
                $comment = '';
                while ($position < $length && "\n" !== $code[$position]) {
                    $comment .= $code[$position];
                    ++$position;
                }
                $tokens[] = new ParsedToken($comment, Scope::Comment);
                continue;
            }

            // Double-quoted string
            if ('"' === $code[$position]) {
                $string = $this->parseDoubleQuotedString($code, $position);
                $tokens[] = new ParsedToken($string, Scope::String);
                continue;
            }

            // Single-quoted string
            if ("'" === $code[$position]) {
                $string = $this->parseSingleQuotedString($code, $position);
                $tokens[] = new ParsedToken($string, Scope::String);
                continue;
            }

            // Here-doc (<<EOF)
            if ($position + 1 < $length && '<' === $code[$position] && '<' === $code[$position + 1]) {
                $hereDoc = $this->parseHereDoc($code, $position);
                $tokens[] = new ParsedToken($hereDoc, Scope::String);
                continue;
            }

            // Variable ($var, ${var})
            if ('$' === $code[$position]) {
                $var = $this->parseVariable($code, $position);
                $tokens[] = new ParsedToken($var, Scope::Variable);
                continue;
            }

            // Arithmetic expansion $((...))
            if ($position + 3 < $length && '$' === $code[$position] && '(' === $code[$position + 1] && '(' === $code[$position + 2]) {
                $arith = $this->parseArithmetic($code, $position);
                $tokens[] = new ParsedToken($arith, Scope::Number);
                continue;
            }

            // Command substitution $(...) or `...`
            if ('`' === $code[$position] || ('$' === $code[$position] && $position + 1 < $length && '(' === $code[$position + 1])) {
                $subst = $this->parseCommandSubstitution($code, $position);
                $tokens[] = new ParsedToken($subst, Scope::FunctionCall);
                continue;
            }

            // Whitespace
            if (ctype_space($code[$position])) {
                $ws = '';
                while ($position < $length && ctype_space($code[$position])) {
                    $ws .= $code[$position];
                    ++$position;
                }
                $tokens[] = new ParsedToken($ws, Scope::Whitespace);
                continue;
            }

            // Numbers
            if (ctype_digit($code[$position])) {
                $num = '';
                while ($position < $length && ctype_digit($code[$position])) {
                    $num .= $code[$position];
                    ++$position;
                }
                $tokens[] = new ParsedToken($num, Scope::Number);
                continue;
            }

            // Identifiers and keywords
            if (ctype_alpha($code[$position]) || '_' === $code[$position]) {
                $ident = '';
                while ($position < $length && (ctype_alnum($code[$position]) || '_' === $code[$position])) {
                    $ident .= $code[$position];
                    ++$position;
                }

                if (in_array($ident, self::KEYWORDS, true)) {
                    $tokens[] = new ParsedToken($ident, Scope::KeywordControl);
                } elseif (in_array($ident, self::BUILTINS, true)) {
                    $tokens[] = new ParsedToken($ident, Scope::FunctionCall);
                } else {
                    $tokens[] = new ParsedToken($ident, Scope::Constant);
                }
                continue;
            }

            // Operators and punctuation
            $char = $code[$position];
            if (in_array($char, ['=', '+', '-', '*', '/', '%', '&', '|', '!', '<', '>', ';', ':', '?'], true)) {
                $tokens[] = new ParsedToken($char, Scope::Operator);
                ++$position;
                continue;
            }

            // Brackets and parentheses
            if (in_array($char, ['(', ')', '[', ']', '{', '}'], true)) {
                $tokens[] = new ParsedToken($char, Scope::Punctuation);
                ++$position;
                continue;
            }

            // Default: treat as punctuation
            $tokens[] = new ParsedToken($char, Scope::Punctuation);
            ++$position;
        }

        return new ParsedStream($tokens);
    }

    /**
     * Parse double-quoted string with variable expansion.
     */
    private function parseDoubleQuotedString(string $code, int &$position): string
    {
        $string = '"';
        ++$position; // Skip opening quote

        while ($position < strlen($code)) {
            if ('"' === $code[$position] && (0 === $position || '\\' !== $code[$position - 1])) {
                $string .= '"';
                ++$position;
                break;
            }

            if ('\\' === $code[$position] && $position + 1 < strlen($code)) {
                $string .= $code[$position].$code[$position + 1];
                $position += 2;
                continue;
            }

            $string .= $code[$position];
            ++$position;
        }

        return $string;
    }

    /**
     * Parse single-quoted string (no expansion).
     */
    private function parseSingleQuotedString(string $code, int &$position): string
    {
        $string = "'";
        ++$position; // Skip opening quote

        while ($position < strlen($code)) {
            if ("'" === $code[$position]) {
                $string .= "'";
                ++$position;
                break;
            }

            $string .= $code[$position];
            ++$position;
        }

        return $string;
    }

    /**
     * Parse here-doc string.
     */
    private function parseHereDoc(string $code, int &$position): string
    {
        $hereDoc = '';
        // Skip <<
        $hereDoc .= $code[$position].$code[$position + 1];
        $position += 2;

        // Skip optional dash
        if ($position < strlen($code) && '-' === $code[$position]) {
            $hereDoc .= '-';
            ++$position;
        }

        // Get delimiter
        while ($position < strlen($code) && ctype_space($code[$position])) {
            $hereDoc .= $code[$position];
            ++$position;
        }

        $delimiter = '';
        while ($position < strlen($code) && !ctype_space($code[$position])) {
            $delimiter .= $code[$position];
            $hereDoc .= $code[$position];
            ++$position;
        }

        // Skip to end of line
        while ($position < strlen($code) && "\n" !== $code[$position]) {
            $hereDoc .= $code[$position];
            ++$position;
        }

        if ($position < strlen($code) && "\n" === $code[$position]) {
            $hereDoc .= "\n";
            ++$position;
        }

        // Read until delimiter found at start of line
        while ($position < strlen($code)) {
            $line = '';
            while ($position < strlen($code) && "\n" !== $code[$position]) {
                $line .= $code[$position];
                $hereDoc .= $code[$position];
                ++$position;
            }

            if ($position < strlen($code) && "\n" === $code[$position]) {
                $hereDoc .= "\n";
                ++$position;
            }

            if ($line === $delimiter) {
                break;
            }
        }

        return $hereDoc;
    }

    /**
     * Parse variable reference ($var or ${var}).
     */
    private function parseVariable(string $code, int &$position): string
    {
        $var = '$';
        ++$position; // Skip $

        if ($position < strlen($code) && '{' === $code[$position]) {
            $var .= '{';
            ++$position;

            while ($position < strlen($code) && '}' !== $code[$position]) {
                $var .= $code[$position];
                ++$position;
            }

            if ($position < strlen($code) && '}' === $code[$position]) {
                $var .= '}';
                ++$position;
            }
        } else {
            // Simple variable
            while ($position < strlen($code) && (ctype_alnum($code[$position]) || in_array($code[$position], ['_', '#', '@', '?', '-', '$', '!'], true))) {
                $var .= $code[$position];
                ++$position;
            }
        }

        return $var;
    }

    /**
     * Parse arithmetic expansion.
     */
    private function parseArithmetic(string $code, int &$position): string
    {
        $arith = '';
        $parenCount = 0;

        while ($position < strlen($code)) {
            $arith .= $code[$position];

            if ('(' === $code[$position]) {
                ++$parenCount;
            } elseif (')' === $code[$position]) {
                --$parenCount;
                if (0 === $parenCount) {
                    ++$position;
                    break;
                }
            }

            ++$position;
        }

        return $arith;
    }

    /**
     * Parse command substitution.
     */
    private function parseCommandSubstitution(string $code, int &$position): string
    {
        $subst = '';

        if ('`' === $code[$position]) {
            $subst .= '`';
            ++$position;

            while ($position < strlen($code) && '`' !== $code[$position]) {
                if ('\\' === $code[$position] && $position + 1 < strlen($code)) {
                    $subst .= $code[$position].$code[$position + 1];
                    $position += 2;
                } else {
                    $subst .= $code[$position];
                    ++$position;
                }
            }

            if ($position < strlen($code)) {
                $subst .= '`';
                ++$position;
            }
        } else {
            // $(...) form
            $subst .= '$';
            ++$position;

            if ($position < strlen($code) && '(' === $code[$position]) {
                $subst .= '(';
                ++$position;
                $parenCount = 1;

                while ($position < strlen($code) && $parenCount > 0) {
                    $subst .= $code[$position];

                    if ('(' === $code[$position]) {
                        ++$parenCount;
                    } elseif (')' === $code[$position]) {
                        --$parenCount;
                    }

                    ++$position;
                }
            }
        }

        return $subst;
    }
}
