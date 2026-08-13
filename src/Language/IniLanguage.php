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
 * INI language parser.
 *
 * Handles parsing of INI configuration files (.ini, .env, php.ini, etc.).
 *
 * Supports:
 * - Comments: ; and #
 * - Sections: [section]
 * - Key-value pairs: key = value
 * - Quoted values: key = "value" or key = 'value'
 * - Boolean/null values: true, false, yes, no, on, off, null, none
 * - Numbers: integers and floats
 * - Environment variable references: ${VAR} or %VAR%
 *
 * @author Simon André <smn.andre@gmail.com>
 */
final class IniLanguage implements LanguageInterface
{
    public function getIdentifier(): string
    {
        return 'ini';
    }

    public function parse(string $code): ParsedStream
    {
        $tokens = [];
        $lines = explode("\n", $code);

        foreach ($lines as $lineIndex => $line) {
            if ($lineIndex > 0) {
                $tokens[] = new ParsedToken("\n", Scope::Whitespace);
            }

            $this->parseLine($line, $tokens);
        }

        return new ParsedStream($tokens);
    }

    /**
     * Parse a single line and add tokens to the array.
     *
     * @param list<ParsedToken> $tokens
     */
    private function parseLine(string $line, array &$tokens): void
    {
        $position = 0;
        $length = strlen($line);

        // Handle empty lines
        if ('' === trim($line)) {
            if ('' !== $line) {
                $tokens[] = new ParsedToken($line, Scope::Whitespace);
            }

            return;
        }

        // Leading whitespace
        while ($position < $length && (' ' === $line[$position] || "\t" === $line[$position])) {
            $ws = '';
            while ($position < $length && (' ' === $line[$position] || "\t" === $line[$position])) {
                $ws .= $line[$position];
                ++$position;
            }
            $tokens[] = new ParsedToken($ws, Scope::Whitespace);
        }

        if ($position >= $length) {
            return;
        }

        $char = $line[$position];

        // Comment line (;comment or #comment)
        if (';' === $char || '#' === $char) {
            $tokens[] = new ParsedToken(substr($line, $position), Scope::Comment);

            return;
        }

        // Section header [section]
        if ('[' === $char) {
            $this->parseSection($line, $position, $tokens);

            return;
        }

        // Key-value pair
        $this->parseKeyValue($line, $position, $tokens);
    }

    /**
     * Parse a section header: [section] or [section.subsection].
     *
     * @param list<ParsedToken> $tokens
     */
    private function parseSection(string $line, int $position, array &$tokens): void
    {
        $length = strlen($line);

        // Opening bracket
        $tokens[] = new ParsedToken('[', Scope::Punctuation);
        ++$position;

        // Section name
        $name = '';
        while ($position < $length && ']' !== $line[$position]) {
            $name .= $line[$position];
            ++$position;
        }

        if ('' !== $name) {
            $tokens[] = new ParsedToken($name, Scope::SectionName);
        }

        // Closing bracket
        if ($position < $length && ']' === $line[$position]) {
            $tokens[] = new ParsedToken(']', Scope::Punctuation);
            ++$position;
        }

        // Rest of line (could be comment or whitespace)
        if ($position < $length) {
            $rest = substr($line, $position);
            $trimmed = ltrim($rest);

            if ('' !== $trimmed && (';' === $trimmed[0] || '#' === $trimmed[0])) {
                // Whitespace before comment
                $ws = substr($rest, 0, strlen($rest) - strlen($trimmed));
                if ('' !== $ws) {
                    $tokens[] = new ParsedToken($ws, Scope::Whitespace);
                }
                $tokens[] = new ParsedToken($trimmed, Scope::Comment);
            } elseif ('' !== $rest) {
                $tokens[] = new ParsedToken($rest, Scope::Whitespace);
            }
        }
    }

    /**
     * Parse a key-value pair: key = value.
     *
     * @param list<ParsedToken> $tokens
     */
    private function parseKeyValue(string $line, int $position, array &$tokens): void
    {
        $length = strlen($line);

        // Key (everything before = or :)
        $key = '';
        while ($position < $length && '=' !== $line[$position] && ':' !== $line[$position]) {
            $key .= $line[$position];
            ++$position;
        }

        // Trim trailing whitespace from key but tokenize separately
        $keyTrimmed = rtrim($key);
        $keyWs = substr($key, strlen($keyTrimmed));

        if ('' !== $keyTrimmed) {
            $tokens[] = new ParsedToken($keyTrimmed, Scope::Constant);
        }

        if ('' !== $keyWs) {
            $tokens[] = new ParsedToken($keyWs, Scope::Whitespace);
        }

        // No assignment operator found - just a key
        if ($position >= $length) {
            return;
        }

        // Assignment operator (= or :)
        $tokens[] = new ParsedToken($line[$position], Scope::Operator);
        ++$position;

        // Whitespace after operator
        $ws = '';
        while ($position < $length && (' ' === $line[$position] || "\t" === $line[$position])) {
            $ws .= $line[$position];
            ++$position;
        }

        if ('' !== $ws) {
            $tokens[] = new ParsedToken($ws, Scope::Whitespace);
        }

        // Value
        if ($position >= $length) {
            return;
        }

        $this->parseValue(substr($line, $position), $tokens);
    }

    /**
     * Parse a value and add appropriate tokens.
     *
     * @param list<ParsedToken> $tokens
     */
    private function parseValue(string $value, array &$tokens): void
    {
        $position = 0;
        $length = strlen($value);

        while ($position < $length) {
            $char = $value[$position];

            // Check for inline comment (preceded by whitespace)
            if ($position > 0 && (';' === $char || '#' === $char)) {
                $tokens[] = new ParsedToken(substr($value, $position), Scope::Comment);

                return;
            }

            // Quoted string
            if ('"' === $char || "'" === $char) {
                $string = $this->parseQuotedString($value, $position);
                $tokens[] = new ParsedToken($string, Scope::String);
                $position += strlen($string);

                continue;
            }

            // Environment variable ${VAR}
            if ('$' === $char && $position + 1 < $length && '{' === $value[$position + 1]) {
                $envVar = $this->parseEnvVar($value, $position, '}');
                $tokens[] = new ParsedToken($envVar, Scope::Variable);
                $position += strlen($envVar);

                continue;
            }

            // Environment variable %VAR% (Windows style)
            if ('%' === $char) {
                $envVar = $this->parseEnvVar($value, $position, '%');
                $tokens[] = new ParsedToken($envVar, Scope::Variable);
                $position += strlen($envVar);

                continue;
            }

            // Whitespace
            if (' ' === $char || "\t" === $char) {
                $ws = '';
                while ($position < $length && (' ' === $value[$position] || "\t" === $value[$position])) {
                    $ws .= $value[$position];
                    ++$position;
                }
                $tokens[] = new ParsedToken($ws, Scope::Whitespace);

                continue;
            }

            // Word token (boolean, null, number, or plain value)
            $word = '';
            while ($position < $length
                   && ' ' !== $value[$position]
                   && "\t" !== $value[$position]
                   && '"' !== $value[$position]
                   && "'" !== $value[$position]
                   && '$' !== $value[$position]
                   && '%' !== $value[$position]
                   && ';' !== $value[$position]
                   && '#' !== $value[$position]) {
                $word .= $value[$position];
                ++$position;
            }

            if ('' !== $word) {
                $scope = $this->determineValueScope($word);
                $tokens[] = new ParsedToken($word, $scope);
            }
        }
    }

    /**
     * Parse a quoted string.
     */
    private function parseQuotedString(string $value, int $position): string
    {
        $quote = $value[$position];
        $string = $quote;
        ++$position;
        $length = strlen($value);
        $escaped = false;

        while ($position < $length) {
            $char = $value[$position];
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

    /**
     * Parse an environment variable reference.
     */
    private function parseEnvVar(string $value, int $position, string $endChar): string
    {
        $envVar = '';
        $length = strlen($value);

        // ${VAR} style
        if ('}' === $endChar) {
            $envVar = '${';
            $position += 2;

            while ($position < $length && '}' !== $value[$position]) {
                $envVar .= $value[$position];
                ++$position;
            }

            if ($position < $length) {
                $envVar .= '}';
            }

            return $envVar;
        }

        // %VAR% style
        $envVar = '%';
        ++$position;

        while ($position < $length && '%' !== $value[$position]) {
            $envVar .= $value[$position];
            ++$position;
        }

        if ($position < $length) {
            $envVar .= '%';
        }

        return $envVar;
    }

    /**
     * Determine the scope of a value based on its content.
     */
    private function determineValueScope(string $value): Scope
    {
        $lower = strtolower($value);

        // Boolean values
        if (in_array($lower, ['true', 'false', 'yes', 'no', 'on', 'off'], true)) {
            return Scope::Boolean;
        }

        // Null values
        if (in_array($lower, ['null', 'none', ''], true)) {
            return Scope::Null;
        }

        // Numbers (integer or float)
        if (is_numeric($value)) {
            return Scope::Number;
        }

        // Hex numbers (0x...)
        if (preg_match('/^0x[0-9a-fA-F]+$/', $value)) {
            return Scope::Number;
        }

        // Default to string
        return Scope::String;
    }
}
