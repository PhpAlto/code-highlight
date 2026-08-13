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

namespace Alto\Code\Highlight\Language;

use Alto\Code\Highlight\Parser\ParsedStream;
use Alto\Code\Highlight\Parser\ParsedToken;
use Alto\Code\Highlight\Scope;

/**
 * Makefile language parser.
 *
 * Handles Makefile syntax including targets, dependencies, variables,
 * commands, directives, and functions.
 *
 * @author Simon André <smn.andre@gmail.com>
 */
final class MakefileLanguage implements LanguageInterface
{
    private const DIRECTIVES = [
        'include', 'sinclude', '-include', 'ifdef', 'ifndef', 'ifeq', 'ifneq',
        'else', 'endif', 'define', 'endef', 'export', 'unexport', 'override',
        'private', 'vpath', 'VPATH',
    ];

    public function getIdentifier(): string
    {
        return 'makefile';
    }

    public function parse(string $code): ParsedStream
    {
        $tokens = [];
        $lines = explode("\n", $code);
        $inCommand = false;

        foreach ($lines as $lineNum => $line) {
            $position = 0;
            $length = strlen($line);

            // Check if line starts with tab (command)
            if ($length > 0 && "\t" === $line[0]) {
                $tokens[] = new ParsedToken("\t", Scope::Whitespace);
                $position = 1;
                $inCommand = true;
            } else {
                $inCommand = false;
            }

            // Parse the line
            while ($position < $length) {
                $char = $line[$position];

                // Comment
                if ('#' === $char) {
                    $comment = substr($line, $position);
                    $tokens[] = new ParsedToken($comment, Scope::Comment);
                    break;
                }

                // Variable reference $(VAR) or ${VAR}
                if ('$' === $char && $position + 1 < $length && in_array($line[$position + 1], ['(', '{'], true)) {
                    $varRef = $this->parseVariableReference($line, $position);
                    $tokens[] = new ParsedToken($varRef, Scope::Variable);
                    $position += strlen($varRef);

                    continue;
                }

                // Automatic variables
                if ('$' === $char && $position + 1 < $length && preg_match('/[@%<^+?*]/', $line[$position + 1])) {
                    $tokens[] = new ParsedToken($char . $line[$position + 1], Scope::Variable);
                    $position += 2;

                    continue;
                }

                // Target or variable assignment
                if (!$inCommand) {
                    // Check for special targets
                    if (0 === $position && '.PHONY:' === substr($line, 0, 7)) {
                        $tokens[] = new ParsedToken('.PHONY', Scope::Meta);
                        $position = 6;

                        continue;
                    }

                    // Check for variable assignment operators
                    $assignPos = $this->findAssignmentOperator($line, $position);
                    if (false !== $assignPos) {
                        // Variable name
                        $varName = substr($line, $position, $assignPos - $position);
                        $tokens[] = new ParsedToken($varName, Scope::Variable);
                        $position = $assignPos;

                        // Assignment operator
                        $operator = $this->parseAssignmentOperator($line, $position);
                        $tokens[] = new ParsedToken($operator, Scope::Operator);
                        $position += strlen($operator);

                        continue;
                    }

                    // Check for target (colon)
                    $colonPos = strpos($line, ':', $position);
                    if (false !== $colonPos && $colonPos > $position) {
                        // Target name
                        $target = substr($line, $position, $colonPos - $position);
                        $tokens[] = new ParsedToken($target, Scope::FunctionDefinition);
                        $position = $colonPos;

                        // Colon
                        $tokens[] = new ParsedToken(':', Scope::Punctuation);
                        ++$position;

                        continue;
                    }
                }

                // Whitespace
                if (preg_match('/\s/', $char)) {
                    $ws = '';
                    while ($position < $length && preg_match('/\s/', $line[$position])) {
                        $ws .= $line[$position];
                        ++$position;
                    }
                    $tokens[] = new ParsedToken($ws, Scope::Whitespace);

                    continue;
                }

                // String (single or double quotes)
                if ('"' === $char || "'" === $char) {
                    $string = $this->parseString($line, $position, $char);
                    $tokens[] = new ParsedToken($string, Scope::String);
                    $position += strlen($string);

                    continue;
                }

                // Directives
                if (preg_match('/[a-zA-Z_]/', $char)) {
                    $word = '';
                    while ($position < $length && preg_match('/[a-zA-Z0-9_\-]/', $line[$position])) {
                        $word .= $line[$position];
                        ++$position;
                    }

                    if (in_array($word, self::DIRECTIVES, true)) {
                        $tokens[] = new ParsedToken($word, Scope::Keyword);

                        continue;
                    }

                    // Commands (when line starts with tab)
                    if ($inCommand) {
                        $tokens[] = new ParsedToken($word, Scope::FunctionCall);

                        continue;
                    }

                    // Regular text
                    $tokens[] = new ParsedToken($word, Scope::Whitespace);

                    continue;
                }

                // Other characters
                $tokens[] = new ParsedToken($char, Scope::Whitespace);
                ++$position;
            }

            // Add newline token (except for last line)
            if ($lineNum < count($lines) - 1) {
                $tokens[] = new ParsedToken("\n", Scope::Whitespace);
            }
        }

        return new ParsedStream($tokens);
    }

    /**
     * Parse a variable reference like $(VAR) or ${VAR}.
     *
     * Handles nested variable references by tracking brace/paren depth.
     */
    private function parseVariableReference(string $line, int $position): string
    {
        $openChar = $line[$position + 1];
        $closeChar = '(' === $openChar ? ')' : '}';
        $varRef = '$' . $openChar;
        $position += 2;
        $length = strlen($line);
        $depth = 1;

        while ($position < $length) {
            $char = $line[$position];

            if ($char === $openChar) {
                ++$depth;
            } elseif ($char === $closeChar) {
                --$depth;
            }

            $varRef .= $char;
            ++$position;

            if (0 === $depth) {
                break;
            }
        }

        return $varRef;
    }

    /**
     * Find the position of an assignment operator in the line.
     *
     * Checks for :=, ::=, ?=, +=, and = operators, ensuring they appear
     * before any target colon.
     *
     * @return int|false The position of the operator, or false if not found
     */
    private function findAssignmentOperator(string $line, int $position): int|false
    {
        $operators = [':=', '::=', '=', '?=', '+='];
        $colonPos = strpos($line, ':', $position);

        foreach ($operators as $op) {
            $opPos = strpos($line, $op, $position);
            if (false !== $opPos) {
                // Make sure it's not part of a target (before a colon for target)
                if (false === $colonPos || $opPos < $colonPos) {
                    return $opPos;
                }
            }
        }

        return false;
    }

    /**
     * Parse and return the assignment operator at the given position.
     *
     * Recognizes ::=, :=, ?=, +=, and = operators.
     */
    private function parseAssignmentOperator(string $line, int $position): string
    {
        $length = strlen($line);

        // Three-character operator
        if ($position + 2 < $length && '::=' === substr($line, $position, 3)) {
            return '::=';
        }

        // Two-character operators
        if ($position + 1 < $length) {
            $two = substr($line, $position, 2);
            if (in_array($two, [':=', '?=', '+='], true)) {
                return $two;
            }
        }

        // Single character
        return '=';
    }

    /**
     * Parse a quoted string literal.
     *
     * Handles escape sequences within the string.
     *
     * @param string $quote The quote character (' or ")
     */
    private function parseString(string $line, int $position, string $quote): string
    {
        $string = $quote;
        ++$position;
        $length = strlen($line);
        $escaped = false;

        while ($position < $length) {
            $char = $line[$position];
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
}
