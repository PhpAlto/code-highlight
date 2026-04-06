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

final class DockerfileLanguage implements LanguageInterface
{
    private const INSTRUCTIONS = [
        'FROM', 'RUN', 'CMD', 'LABEL', 'EXPOSE', 'ENV', 'ADD', 'COPY',
        'ENTRYPOINT', 'VOLUME', 'USER', 'WORKDIR', 'ARG', 'ONBUILD',
        'STOPSIGNAL', 'HEALTHCHECK', 'SHELL',
    ];

    private const MODIFIERS = [
        'AS', 'NONE',
    ];

    public function getIdentifier(): string
    {
        return 'dockerfile';
    }

    public function parse(string $code): ParsedStream
    {
        $tokens = [];
        $lines = explode("\n", $code);

        foreach ($lines as $index => $line) {
            // Add the line content
            $this->parseLine($line, $tokens);

            // Add newline except after last line if code doesn't end with newline
            if ($index < count($lines) - 1) {
                $tokens[] = new ParsedToken("\n", Scope::Whitespace);
            }
        }

        // Add final newline if code ends with one
        if (str_ends_with($code, "\n")) {
            $tokens[] = new ParsedToken("\n", Scope::Whitespace);
        }

        return new ParsedStream(array_values($tokens));
    }

    /**
     * @param array<ParsedToken> $tokens
     */
    private function parseLine(string $line, array &$tokens): void
    {
        // Trim leading whitespace and track it
        $leadingWs = '';
        $trimmedLine = $line;

        if (preg_match('/^(\s+)/', $line, $matches)) {
            $leadingWs = $matches[1];
            $trimmedLine = substr($line, strlen($leadingWs));
        }

        // Add leading whitespace
        if ('' !== $leadingWs) {
            $tokens[] = new ParsedToken($leadingWs, Scope::Whitespace);
        }

        // Handle empty lines
        if ('' === $trimmedLine) {
            return;
        }

        // Handle comments
        if (str_starts_with($trimmedLine, '#')) {
            $tokens[] = new ParsedToken($trimmedLine, Scope::Comment);

            return;
        }

        // Extract the instruction (first word)
        if (!preg_match('/^(\S+)(\s*)(.*)/u', $trimmedLine, $matches)) {
            // Shouldn't happen with valid Dockerfile
            $tokens[] = new ParsedToken($trimmedLine, Scope::MarkupText);

            return;
        }

        $instruction = $matches[1];
        $separator = $matches[2];
        $restOfLine = $matches[3];

        // Check if it's a known instruction
        $instructionUpper = strtoupper($instruction);
        if (in_array($instructionUpper, self::INSTRUCTIONS, true)) {
            // Emit instruction as KeywordDeclaration
            $tokens[] = new ParsedToken($instruction, Scope::KeywordDeclaration);

            // Emit space between instruction and arguments
            if ('' !== $separator) {
                $tokens[] = new ParsedToken($separator, Scope::Whitespace);
            }

            // Parse the rest of the line
            $this->parseArguments($restOfLine, $tokens);
        } else {
            // Not a recognized instruction, emit as plain text
            $tokens[] = new ParsedToken($instruction, Scope::MarkupText);

            if ('' !== $separator) {
                $tokens[] = new ParsedToken($separator, Scope::Whitespace);
            }

            if ('' !== $restOfLine) {
                $tokens[] = new ParsedToken($restOfLine, Scope::MarkupText);
            }
        }
    }

    /**
     * @param array<ParsedToken> $tokens
     */
    private function parseArguments(string $line, array &$tokens): void
    {
        $position = 0;
        $length = strlen($line);

        while ($position < $length) {
            $char = $line[$position];

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

            // Double-quoted string
            if ('"' === $char) {
                $string = $this->parseQuotedString($line, $position, '"');
                $tokens[] = new ParsedToken($string, Scope::String);
                $position += strlen($string);

                continue;
            }

            // Single-quoted string
            if ("'" === $char) {
                $string = $this->parseQuotedString($line, $position, "'");
                $tokens[] = new ParsedToken($string, Scope::String);
                $position += strlen($string);

                continue;
            }

            // Variable: $VAR or ${VAR}
            if ('$' === $char) {
                if ($position + 1 < $length && '{' === $line[$position + 1]) {
                    // ${VAR}
                    $varEnd = strpos($line, '}', $position + 2);
                    if (false !== $varEnd) {
                        $var = substr($line, $position, $varEnd - $position + 1);
                        $tokens[] = new ParsedToken($var, Scope::Variable);
                        $position += strlen($var);

                        continue;
                    }
                } else {
                    // $VAR - extract alphanumeric and underscore
                    $var = '$';
                    ++$position;
                    while ($position < $length && preg_match('/[a-zA-Z0-9_]/', $line[$position])) {
                        $var .= $line[$position];
                        ++$position;
                    }
                    if (strlen($var) > 1) {
                        $tokens[] = new ParsedToken($var, Scope::Variable);

                        continue;
                    }
                }
            }

            // Line continuation: backslash at end
            if ('\\' === $char && $position === $length - 1) {
                $tokens[] = new ParsedToken('\\', Scope::Operator);
                ++$position;

                continue;
            }

            // Shell operators
            if ('&' === $char && $position + 1 < $length && '&' === $line[$position + 1]) {
                $tokens[] = new ParsedToken('&&', Scope::Operator);
                $position += 2;

                continue;
            }

            if ('|' === $char) {
                if ($position + 1 < $length && '|' === $line[$position + 1]) {
                    $tokens[] = new ParsedToken('||', Scope::Operator);
                    $position += 2;

                    continue;
                }
                $tokens[] = new ParsedToken('|', Scope::Operator);
                ++$position;

                continue;
            }

            if (';' === $char) {
                $tokens[] = new ParsedToken(';', Scope::Operator);
                ++$position;

                continue;
            }

            // Modifiers like AS, NONE
            if (preg_match('/^([A-Z_]+)\b/', substr($line, $position), $matches)) {
                $word = $matches[1];
                if (in_array($word, self::MODIFIERS, true)) {
                    $tokens[] = new ParsedToken($word, Scope::Keyword);
                    $position += strlen($word);

                    continue;
                }
            }

            // Numbers (bare digits)
            if (preg_match('/^\d+\b/', substr($line, $position), $matches)) {
                $tokens[] = new ParsedToken($matches[0], Scope::Number);
                $position += strlen($matches[0]);

                continue;
            }

            // Everything else (arguments, paths, package names)
            $word = '';
            while ($position < $length && !preg_match('/\s/', $line[$position])
                && '"' !== $line[$position] && "'" !== $line[$position]
                && '$' !== $line[$position]) {
                $word .= $line[$position];
                ++$position;
            }

            if ('' !== $word) {
                $tokens[] = new ParsedToken($word, Scope::MarkupText);
            }
        }
    }

    private function parseQuotedString(string $line, int $position, string $quote): string
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

            if ($quote === $char) {
                ++$position;
                break;
            }

            ++$position;
        }

        return $string;
    }
}
