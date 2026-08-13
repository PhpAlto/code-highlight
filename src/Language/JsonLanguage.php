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
 * JSON language parser.
 *
 * Handles parsing and semantic analysis of JSON code.
 *
 * @author Simon André <smn.andre@gmail.com>
 */
final class JsonLanguage implements LanguageInterface
{
    public function getIdentifier(): string
    {
        return 'json';
    }

    public function parse(string $code): ParsedStream
    {
        $tokens = [];
        $position = 0;
        $length = strlen($code);

        while ($position < $length) {
            $char = $code[$position];

            // Skip whitespace
            if (preg_match('/\s/', $char)) {
                $ws = '';
                while ($position < $length && preg_match('/\s/', $code[$position])) {
                    $ws .= $code[$position];
                    ++$position;
                }
                $tokens[] = new ParsedToken($ws, Scope::Whitespace);

                continue;
            }

            // String (keys and values)
            if ('"' === $char) {
                $string = $this->parseString($code, $position);
                $scope = $this->determineStringScope($code, $position + strlen($string));
                $tokens[] = new ParsedToken($string, $scope);
                $position += strlen($string);

                continue;
            }

            // Number
            if ('-' === $char || ctype_digit($char)) {
                $number = $this->parseNumber($code, $position);
                $tokens[] = new ParsedToken($number, Scope::Number);
                $position += strlen($number);

                continue;
            }

            // Boolean: true
            if ('true' === substr($code, $position, 4)) {
                $tokens[] = new ParsedToken('true', Scope::Boolean);
                $position += 4;

                continue;
            }

            // Boolean: false
            if ('false' === substr($code, $position, 5)) {
                $tokens[] = new ParsedToken('false', Scope::Boolean);
                $position += 5;

                continue;
            }

            // Null
            if ('null' === substr($code, $position, 4)) {
                $tokens[] = new ParsedToken('null', Scope::Null);
                $position += 4;

                continue;
            }

            // Punctuation: {, }, [, ], :, ,
            if (in_array($char, ['{', '}', '[', ']', ':', ','], true)) {
                $tokens[] = new ParsedToken($char, Scope::Punctuation);
                ++$position;

                continue;
            }

            // Unknown character (invalid JSON, but don't crash)
            ++$position;
        }

        return new ParsedStream($tokens);
    }

    /**
     * Parse a JSON string starting at the given position.
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
     * Parse a JSON number starting at the given position.
     */
    private function parseNumber(string $code, int $position): string
    {
        $number = '';
        $length = strlen($code);

        // Optional minus
        if ($position < $length && '-' === $code[$position]) {
            $number .= $code[$position];
            ++$position;
        }

        // Integer part
        while ($position < $length && ctype_digit($code[$position])) {
            $number .= $code[$position];
            ++$position;
        }

        // Decimal part
        if ($position < $length && '.' === $code[$position]) {
            $number .= $code[$position];
            ++$position;

            while ($position < $length && ctype_digit($code[$position])) {
                $number .= $code[$position];
                ++$position;
            }
        }

        // Exponent part
        if ($position < $length && in_array($code[$position], ['e', 'E'], true)) {
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

    /**
     * Determine if a string is a key or a value based on what follows.
     */
    private function determineStringScope(string $code, int $position): Scope
    {
        // Skip whitespace
        while ($position < strlen($code) && preg_match('/\s/', $code[$position])) {
            ++$position;
        }

        // If followed by :, it's a key
        if ($position < strlen($code) && ':' === $code[$position]) {
            return Scope::AttributeName;
        }

        // Otherwise it's a string value
        return Scope::String;
    }
}
