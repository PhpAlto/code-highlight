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
 * YAML language parser.
 *
 * Handles parsing and semantic analysis of YAML code.
 */
final class YamlLanguage implements LanguageInterface
{
    public function getIdentifier(): string
    {
        return 'yaml';
    }

    public function parse(string $code): ParsedStream
    {
        $tokens = [];
        $lines = explode("\n", $code);

        foreach ($lines as $lineIndex => $line) {
            if ($lineIndex > 0) {
                $tokens[] = new ParsedToken("\n", Scope::Whitespace);
            }

            // Handle empty lines
            if ('' === trim($line)) {
                if ('' !== $line) {
                    $tokens[] = new ParsedToken($line, Scope::Whitespace);
                }

                continue;
            }

            // Handle comments
            if (preg_match('/^(\s*)(#.*)$/', $line, $matches)) {
                if ('' !== $matches[1]) {
                    $tokens[] = new ParsedToken($matches[1], Scope::Whitespace);
                }
                $tokens[] = new ParsedToken($matches[2], Scope::Comment);

                continue;
            }

            $position = 0;
            $length = strlen($line);

            // Leading whitespace (indentation)
            while ($position < $length && (' ' === $line[$position] || "\t" === $line[$position])) {
                $ws = '';
                while ($position < $length && (' ' === $line[$position] || "\t" === $line[$position])) {
                    $ws .= $line[$position];
                    ++$position;
                }
                $tokens[] = new ParsedToken($ws, Scope::Whitespace);
            }

            // Check for list item
            if ($position < $length && '-' === $line[$position]
                && ($position + 1 >= $length || ' ' === $line[$position + 1])) {
                $tokens[] = new ParsedToken('-', Scope::Punctuation);
                ++$position;

                if ($position < $length && ' ' === $line[$position]) {
                    $tokens[] = new ParsedToken(' ', Scope::Whitespace);
                    ++$position;
                }
            }

            // Parse key-value pairs or values
            $remainingLine = substr($line, $position);

            // Handle anchors (&anchor) and aliases (*alias)
            if (preg_match('/^(\s*)([&*])([a-zA-Z0-9_.-]+)(\s*)(.*)$/', $remainingLine, $matches)) {
                $leadingWhitespace = $matches[1];
                $marker = $matches[2];
                $name = $matches[3];
                $trailingWhitespace = $matches[4];
                $restOfLine = $matches[5];

                if ('' !== $leadingWhitespace) {
                    $tokens[] = new ParsedToken($leadingWhitespace, Scope::Whitespace);
                }
                $tokens[] = new ParsedToken($marker, Scope::Punctuation);
                $tokens[] = new ParsedToken($name, Scope::Variable); // Anchors and aliases are variables

                $position += strlen($leadingWhitespace) + strlen($marker) + strlen($name);

                if ('' !== $trailingWhitespace) {
                    $tokens[] = new ParsedToken($trailingWhitespace, Scope::Whitespace);
                    $position += strlen($trailingWhitespace);
                }

                // Continue parsing the rest of the line, if any
                $remainingLine = $restOfLine;
                if ('' === trim($remainingLine)) {
                    continue;
                }
            }

            // Check for key: value pattern or merge key
            if (preg_match('/^(\s*)(<<)(\s*)(:)(\s*)(.*)$/', $remainingLine, $matches)) {
                // Merge key '<<:'
                $tokens[] = new ParsedToken($matches[1], Scope::Whitespace); // Leading whitespace
                $tokens[] = new ParsedToken($matches[2], Scope::Punctuation); // <<
                $tokens[] = new ParsedToken($matches[3], Scope::Whitespace); // Whitespace before colon
                $tokens[] = new ParsedToken($matches[4], Scope::Punctuation); // :
                $tokens[] = new ParsedToken($matches[5], Scope::Whitespace); // Whitespace after colon
                $value = $matches[6];
                $scope = $this->determineValueScope($value);
                $tokens[] = new ParsedToken($value, $scope);
            } elseif (preg_match('/^([^:#\'"&*\s]+?)(\s*)(:)(\s*)(.*)$/', $remainingLine, $matches)) {
                // General key: value
                // Key
                $tokens[] = new ParsedToken($matches[1], Scope::Constant);

                // Whitespace before colon
                if ('' !== $matches[2]) {
                    $tokens[] = new ParsedToken($matches[2], Scope::Whitespace);
                }

                // Colon
                $tokens[] = new ParsedToken($matches[3], Scope::Punctuation);

                // Whitespace after colon
                if ('' !== $matches[4]) {
                    $tokens[] = new ParsedToken($matches[4], Scope::Whitespace);
                }

                // Value
                if ('' !== $matches[5]) {
                    $this->parseValue($matches[5], $tokens);
                }
            } else {
                // Just a value or other content
                if ('' !== $remainingLine) {
                    $this->parseValue($remainingLine, $tokens);
                }
            }
        }

        return new ParsedStream($tokens);
    }

    /**
     * Parse a YAML value with support for Symfony-specific syntax.
     *
     * @param list<ParsedToken> $tokens
     */
    private function parseValue(string $value, array &$tokens): void
    {
        $position = 0;
        $length = strlen($value);

        while ($position < $length) {
            $char = $value[$position];

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

            // Symfony parameter reference: %parameter%
            if ('%' === $char) {
                $param = $this->parseSymfonyParameter($value, $position);
                if (null !== $param) {
                    $tokens[] = new ParsedToken($param, Scope::Variable);
                    $position += strlen($param);

                    continue;
                }
            }

            // Symfony service reference: @service or @service_name
            if ('@' === $char) {
                $service = $this->parseSymfonyService($value, $position);
                $tokens[] = new ParsedToken($service, Scope::FunctionCall);
                $position += strlen($service);

                continue;
            }

            // YAML tag: !tagged, !php/const, etc.
            if ('!' === $char) {
                $tag = $this->parseYamlTag($value, $position);
                $tokens[] = new ParsedToken($tag, Scope::Keyword);
                $position += strlen($tag);

                continue;
            }

            // Quoted strings
            if ('"' === $char || "'" === $char) {
                $string = $this->parseQuotedString($value, $position);
                $tokens[] = new ParsedToken($string, Scope::String);
                $position += strlen($string);

                continue;
            }

            // Array/object punctuation
            if ('[' === $char || ']' === $char || '{' === $char || '}' === $char || ',' === $char) {
                $tokens[] = new ParsedToken($char, Scope::Punctuation);
                ++$position;

                continue;
            }

            // Word token (boolean, null, number, or plain value)
            $word = '';
            while ($position < $length
                   && ' ' !== $value[$position]
                   && "\t" !== $value[$position]
                   && '%' !== $value[$position]
                   && '@' !== $value[$position]
                   && '!' !== $value[$position]
                   && '"' !== $value[$position]
                   && "'" !== $value[$position]
                   && '[' !== $value[$position]
                   && ']' !== $value[$position]
                   && '{' !== $value[$position]
                   && '}' !== $value[$position]
                   && ',' !== $value[$position]) {
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
     * Parse a Symfony parameter reference: %parameter_name%.
     */
    private function parseSymfonyParameter(string $value, int $position): ?string
    {
        $length = strlen($value);

        if ($position >= $length || '%' !== $value[$position]) {
            return null;
        }

        // Check for closing %
        $end = strpos($value, '%', $position + 1);
        if (false === $end) {
            // No closing %, not a parameter reference
            return null;
        }

        return substr($value, $position, $end - $position + 1);
    }

    /**
     * Parse a Symfony service reference: @service_name.
     */
    private function parseSymfonyService(string $value, int $position): string
    {
        $service = '@';
        ++$position;
        $length = strlen($value);

        // Handle special @? prefix (optional service)
        if ($position < $length && '?' === $value[$position]) {
            $service .= '?';
            ++$position;
        }

        // Handle @= expression service
        if ($position < $length && '=' === $value[$position]) {
            $service .= '=';
            ++$position;
        }

        while ($position < $length && preg_match('/[a-zA-Z0-9_.\-]/', $value[$position])) {
            $service .= $value[$position];
            ++$position;
        }

        return $service;
    }

    /**
     * Parse a YAML tag: !tagged, !php/const, etc.
     */
    private function parseYamlTag(string $value, int $position): string
    {
        $tag = '!';
        ++$position;
        $length = strlen($value);

        while ($position < $length && preg_match('/[a-zA-Z0-9_\/]/', $value[$position])) {
            $tag .= $value[$position];
            ++$position;
        }

        return $tag;
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
     * Determine the scope of a YAML value based on its content.
     */
    private function determineValueScope(string $value): Scope
    {
        $trimmed = trim($value);

        // Don't classify anchors or aliases as other types
        if (preg_match('/^[&*][a-zA-Z0-9_.-]+$/', $trimmed)) {
            return Scope::Variable;
        }

        // Boolean values
        if (in_array($trimmed, ['true', 'false', 'yes', 'no', 'on', 'off'], true)) {
            return Scope::Boolean;
        }

        // Null values
        if (in_array($trimmed, ['null', '~'], true)) {
            return Scope::Null;
        }

        // Numbers
        if (is_numeric($trimmed)) {
            return Scope::Number;
        }

        // Quoted strings
        if (preg_match('/^["\'].*["\']$/', $trimmed)) {
            return Scope::String;
        }

        // Default to string for unquoted values
        return Scope::String;
    }
}
