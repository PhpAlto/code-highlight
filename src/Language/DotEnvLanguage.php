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
 * DotEnv language parser (.env files).
 *
 * Handles parsing of .env configuration files with support for:
 * - Comments (lines starting with #)
 * - export keyword declarations
 * - KEY=value pairs
 * - Variable references (${VAR} and $VAR)
 * - Quoted strings and boolean/null values
 */
final class DotEnvLanguage implements LanguageInterface
{
    public function getIdentifier(): string
    {
        return 'dotenv';
    }

    public function parse(string $code): ParsedStream
    {
        $tokens = [];
        $lines = explode("\n", $code);

        foreach ($lines as $lineIndex => $line) {
            // Process the line
            $lineTokens = $this->parseLine($line);
            $tokens = array_merge($tokens, $lineTokens);

            // Add newline after each line except the last one
            if ($lineIndex < count($lines) - 1) {
                $tokens[] = new ParsedToken("\n", Scope::Whitespace);
            }
        }

        return new ParsedStream(array_values($tokens));
    }

    /**
     * Parse a single line and return its tokens.
     *
     * @return array<ParsedToken>
     */
    private function parseLine(string $line): array
    {
        $tokens = [];
        $position = 0;
        $length = strlen($line);

        // Empty line
        if (0 === $length) {
            return [];
        }

        // Comment line
        if ('#' === $line[0]) {
            return [new ParsedToken($line, Scope::Comment)];
        }

        // Check for export keyword at start
        if (str_starts_with($line, 'export ')) {
            $tokens[] = new ParsedToken('export', Scope::KeywordDeclaration);
            $tokens[] = new ParsedToken(' ', Scope::Whitespace);
            $position = 7; // len('export ')
        }

        // Parse the rest: KEY=value or just whitespace
        while ($position < $length) {
            $char = $line[$position];

            // Skip leading whitespace
            if (' ' === $char || "\t" === $char) {
                $ws = '';
                while ($position < $length && (' ' === $line[$position] || "\t" === $line[$position])) {
                    $ws .= $line[$position];
                    ++$position;
                }
                $tokens[] = new ParsedToken($ws, Scope::Whitespace);

                continue;
            }

            // Parse key
            if (ctype_alpha($char) || '_' === $char) {
                $key = '';
                while ($position < $length && (ctype_alnum($line[$position]) || '_' === $line[$position])) {
                    $key .= $line[$position];
                    ++$position;
                }
                $tokens[] = new ParsedToken($key, Scope::Constant);

                // Skip optional whitespace before =
                while ($position < $length && (' ' === $line[$position] || "\t" === $line[$position])) {
                    ++$position;
                }

                // Expect = operator
                if ($position < $length && '=' === $line[$position]) {
                    $tokens[] = new ParsedToken('=', Scope::Operator);
                    ++$position;

                    // Skip optional whitespace after =
                    while ($position < $length && (' ' === $line[$position] || "\t" === $line[$position])) {
                        ++$position;
                    }

                    // Parse value
                    $valueTokens = $this->parseValue($line, $position);
                    $tokens = array_merge($tokens, $valueTokens);
                    $position = strlen($line); // Value consumes rest of line

                    continue;
                }

                // No = found, treat as incomplete
                continue;
            }

            // Unknown character, skip
            ++$position;
        }

        return $tokens;
    }

    /**
     * Parse a value starting at position and return its tokens.
     * Consumes until end of line.
     *
     * @return array<ParsedToken>
     */
    private function parseValue(string $line, int $position): array
    {
        $tokens = [];
        $length = strlen($line);

        // Check if value starts with a quote
        if ($position < $length && ('"' === $line[$position] || "'" === $line[$position])) {
            $quote = $line[$position];
            $quoted = $quote;
            ++$position;

            // Consume until closing quote
            $escaped = false;
            while ($position < $length) {
                $char = $line[$position];
                $quoted .= $char;

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

                if ($quote === $char) {
                    ++$position;
                    break;
                }

                ++$position;
            }

            $tokens[] = new ParsedToken($quoted, Scope::String);

            return $tokens;
        }

        // Unquoted value: parse tokens until end of line
        while ($position < $length) {
            $char = $line[$position];

            // Variable reference: ${VAR}
            if ('$' === $char && $position + 1 < $length && '{' === $line[$position + 1]) {
                $varStart = $position;
                $position += 2;

                // Find closing }
                while ($position < $length && '}' !== $line[$position]) {
                    ++$position;
                }

                if ($position < $length && '}' === $line[$position]) {
                    ++$position;
                }

                $varText = substr($line, $varStart, $position - $varStart);
                $tokens[] = new ParsedToken($varText, Scope::Variable);

                continue;
            }

            // Variable reference: $VAR (no braces)
            if ('$' === $char && $position + 1 < $length && (ctype_alpha($line[$position + 1]) || '_' === $line[$position + 1])) {
                $varStart = $position;
                ++$position;

                while ($position < $length && (ctype_alnum($line[$position]) || '_' === $line[$position])) {
                    ++$position;
                }

                $varText = substr($line, $varStart, $position - $varStart);
                $tokens[] = new ParsedToken($varText, Scope::Variable);

                continue;
            }

            // Check for boolean keywords
            if ('t' === $char && 'true' === substr($line, $position, 4) && ($position + 4 >= $length || !ctype_alnum($line[$position + 4]))) {
                $tokens[] = new ParsedToken('true', Scope::Boolean);
                $position += 4;

                continue;
            }

            if ('f' === $char && 'false' === substr($line, $position, 5) && ($position + 5 >= $length || !ctype_alnum($line[$position + 5]))) {
                $tokens[] = new ParsedToken('false', Scope::Boolean);
                $position += 5;

                continue;
            }

            // Check for null keyword
            if ('n' === $char && 'null' === substr($line, $position, 4) && ($position + 4 >= $length || !ctype_alnum($line[$position + 4]))) {
                $tokens[] = new ParsedToken('null', Scope::Null);
                $position += 4;

                continue;
            }

            // Check for number (integer or float)
            if (ctype_digit($char) || ('.' === $char && $position + 1 < $length && ctype_digit($line[$position + 1]))) {
                $numStart = $position;

                // Handle leading digits
                while ($position < $length && ctype_digit($line[$position])) {
                    ++$position;
                }

                // Handle decimal point
                if ($position < $length && '.' === $line[$position] && $position + 1 < $length && ctype_digit($line[$position + 1])) {
                    ++$position;
                    while ($position < $length && ctype_digit($line[$position])) {
                        ++$position;
                    }
                }

                $numText = substr($line, $numStart, $position - $numStart);
                $tokens[] = new ParsedToken($numText, Scope::Number);

                continue;
            }

            // Plain text (string value)
            $strStart = $position;
            while ($position < $length && '$' !== $line[$position]) {
                ++$position;
            }

            if ($position > $strStart) {
                $strText = substr($line, $strStart, $position - $strStart);
                $tokens[] = new ParsedToken($strText, Scope::String);
            }
        }

        return $tokens;
    }
}
