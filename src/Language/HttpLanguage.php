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
 * HTTP language parser.
 *
 * Handles parsing of HTTP requests and responses.
 *
 * Supports:
 * - Request line: GET /path HTTP/1.1
 * - Response line: HTTP/1.1 200 OK
 * - Headers: Header-Name: value
 * - Body content (as plain text)
 *
 * @author Simon André <smn.andre@gmail.com>
 */
final class HttpLanguage implements LanguageInterface
{
    private const HTTP_METHODS = [
        'GET',
        'POST',
        'PUT',
        'DELETE',
        'PATCH',
        'HEAD',
        'OPTIONS',
        'TRACE',
        'CONNECT',
    ];

    public function getIdentifier(): string
    {
        return 'http';
    }

    public function parse(string $code): ParsedStream
    {
        $tokens = [];
        $lines = explode("\n", $code);
        $inBody = false;

        foreach ($lines as $lineIndex => $line) {
            if ($lineIndex > 0) {
                $tokens[] = new ParsedToken("\n", Scope::Whitespace);
            }

            // Once we're in the body, collect all remaining lines
            if ($inBody) {
                if ('' !== $line) {
                    $tokens[] = new ParsedToken($line, Scope::String);
                }

                continue;
            }

            // Empty line marks the start of the body
            if ('' === trim($line)) {
                if ('' !== $line) {
                    $tokens[] = new ParsedToken($line, Scope::Whitespace);
                }
                // Next non-empty content will be body
                $inBody = true;

                continue;
            }

            // Check if this is the first line (request or response line)
            if (0 === $lineIndex) {
                $this->parseStartLine($line, $tokens);

                continue;
            }

            // Parse as header
            $this->parseHeader($line, $tokens);
        }

        return new ParsedStream($tokens);
    }

    /**
     * Parse the start line (request line or status line).
     *
     * @param list<ParsedToken> $tokens
     */
    private function parseStartLine(string $line, array &$tokens): void
    {
        // Check for HTTP response: HTTP/1.1 200 OK
        if (preg_match('/^(HTTP\/[\d.]+)\s+(\d{3})(?:\s+(.*))?$/', $line, $matches)) {
            $tokens[] = new ParsedToken($matches[1], Scope::Keyword);
            $tokens[] = new ParsedToken(' ', Scope::Whitespace);
            $this->parseStatusCode($matches[2], $tokens);

            if (isset($matches[3]) && '' !== $matches[3]) {
                $tokens[] = new ParsedToken(' ', Scope::Whitespace);
                $tokens[] = new ParsedToken($matches[3], Scope::String);
            }

            return;
        }

        // Check for HTTP request: GET /path HTTP/1.1
        if (preg_match('/^([A-Z]+)\s+(\S+)(?:\s+(HTTP\/[\d.]+))?$/', $line, $matches)) {
            $method = $matches[1];

            if (in_array($method, self::HTTP_METHODS, true)) {
                $tokens[] = new ParsedToken($method, Scope::Keyword);
            } else {
                $tokens[] = new ParsedToken($method, Scope::String);
            }

            $tokens[] = new ParsedToken(' ', Scope::Whitespace);
            $this->parseUrl($matches[2], $tokens);

            if (isset($matches[3])) {
                $tokens[] = new ParsedToken(' ', Scope::Whitespace);
                $tokens[] = new ParsedToken($matches[3], Scope::Constant);
            }

            return;
        }

        // Fallback: treat as plain text
        $tokens[] = new ParsedToken($line, Scope::String);
    }

    /**
     * Parse a status code with color coding based on range.
     *
     * @param list<ParsedToken> $tokens
     */
    private function parseStatusCode(string $code, array &$tokens): void
    {
        $codeInt = (int) $code;

        // Color code by status range
        if ($codeInt >= 200 && $codeInt < 300) {
            // 2xx Success - green-ish (use String scope)
            $tokens[] = new ParsedToken($code, Scope::Number);
        } elseif ($codeInt >= 300 && $codeInt < 400) {
            // 3xx Redirect - use Constant
            $tokens[] = new ParsedToken($code, Scope::Constant);
        } elseif ($codeInt >= 400 && $codeInt < 500) {
            // 4xx Client Error - warning-ish
            $tokens[] = new ParsedToken($code, Scope::DiagnosticWarning);
        } elseif ($codeInt >= 500) {
            // 5xx Server Error - error
            $tokens[] = new ParsedToken($code, Scope::DiagnosticError);
        } else {
            // 1xx Informational
            $tokens[] = new ParsedToken($code, Scope::Number);
        }
    }

    /**
     * Parse a URL with highlighting for scheme, path, query, and fragment.
     *
     * @param list<ParsedToken> $tokens
     */
    private function parseUrl(string $url, array &$tokens): void
    {
        // Check for full URL with scheme
        if (preg_match('/^(https?):\/\/([^\/:]+)(:\d+)?(\/[^?\#]*)?(\?[^\#]*)?(\#.*)?$/', $url, $matches)) {
            // Scheme
            $tokens[] = new ParsedToken($matches[1] . '://', Scope::Keyword);

            // Host
            $tokens[] = new ParsedToken($matches[2], Scope::FunctionCall);

            // Port
            if (isset($matches[3]) && '' !== $matches[3]) {
                $tokens[] = new ParsedToken($matches[3], Scope::Number);
            }

            // Path
            if (isset($matches[4]) && '' !== $matches[4]) {
                $tokens[] = new ParsedToken($matches[4], Scope::String);
            }

            // Query string
            if (isset($matches[5]) && '' !== $matches[5]) {
                $tokens[] = new ParsedToken($matches[5], Scope::Variable);
            }

            // Fragment
            if (isset($matches[6])) {
                $tokens[] = new ParsedToken($matches[6], Scope::Comment);
            }

            return;
        }

        // Simple path (e.g., /api/users)
        if (preg_match('/^(\/[^?\#]*)(\?[^\#]*)?(\#.*)?$/', $url, $matches)) {
            // Path
            $tokens[] = new ParsedToken($matches[1], Scope::String);

            // Query string
            if (isset($matches[2])) {
                $tokens[] = new ParsedToken($matches[2], Scope::Variable);
            }

            // Fragment
            if (isset($matches[3])) {
                $tokens[] = new ParsedToken($matches[3], Scope::Comment);
            }

            return;
        }

        // Fallback: just output as-is
        $tokens[] = new ParsedToken($url, Scope::String);
    }

    /**
     * Parse a header line: Header-Name: value.
     *
     * @param list<ParsedToken> $tokens
     */
    private function parseHeader(string $line, array &$tokens): void
    {
        // Leading whitespace
        $position = 0;
        $length = strlen($line);

        while ($position < $length && (' ' === $line[$position] || "\t" === $line[$position])) {
            $ws = '';
            while ($position < $length && (' ' === $line[$position] || "\t" === $line[$position])) {
                $ws .= $line[$position];
                ++$position;
            }
            $tokens[] = new ParsedToken($ws, Scope::Whitespace);
        }

        $remaining = substr($line, $position);

        // Parse header name and value
        if (preg_match('/^([A-Za-z0-9-]+)(\s*)(:)(\s*)(.*)$/', $remaining, $matches)) {
            // Header name
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
                $this->parseHeaderValue($matches[1], $matches[5], $tokens);
            }

            return;
        }

        // Fallback: continuation line or malformed
        if ('' !== $remaining) {
            $tokens[] = new ParsedToken($remaining, Scope::String);
        }
    }

    /**
     * Parse a header value with special handling for certain headers.
     *
     * @param list<ParsedToken> $tokens
     */
    private function parseHeaderValue(string $headerName, string $value, array &$tokens): void
    {
        $normalizedHeader = strtolower($headerName);

        // Special handling for Content-Type
        if ('content-type' === $normalizedHeader) {
            $this->parseContentType($value, $tokens);

            return;
        }

        // Special handling for Authorization
        if ('authorization' === $normalizedHeader) {
            $this->parseAuthorization($value, $tokens);

            return;
        }

        // Default: just output the value
        $tokens[] = new ParsedToken($value, Scope::String);
    }

    /**
     * Parse Content-Type header value.
     *
     * @param list<ParsedToken> $tokens
     */
    private function parseContentType(string $value, array &$tokens): void
    {
        // Example: application/json; charset=utf-8
        $parts = explode(';', $value);

        foreach ($parts as $index => $part) {
            if ($index > 0) {
                $tokens[] = new ParsedToken(';', Scope::Punctuation);
            }

            $part = ltrim($part);
            if (0 === $index) {
                // MIME type
                $tokens[] = new ParsedToken($part, Scope::TypeReference);
            } else {
                // Parameters like charset=utf-8
                if (preg_match('/^(\s*)([^=]+)(=)(.+)$/', $part, $matches)) {
                    if ('' !== $matches[1]) {
                        $tokens[] = new ParsedToken($matches[1], Scope::Whitespace);
                    }
                    $tokens[] = new ParsedToken($matches[2], Scope::Constant);
                    $tokens[] = new ParsedToken($matches[3], Scope::Operator);
                    $tokens[] = new ParsedToken($matches[4], Scope::String);
                } else {
                    $tokens[] = new ParsedToken(' ' . $part, Scope::String);
                }
            }
        }
    }

    /**
     * Parse Authorization header value.
     *
     * @param list<ParsedToken> $tokens
     */
    private function parseAuthorization(string $value, array &$tokens): void
    {
        // Example: Bearer token123 or Basic dXNlcjpwYXNz
        if (preg_match('/^(\w+)\s+(.+)$/', $value, $matches)) {
            $tokens[] = new ParsedToken($matches[1], Scope::Keyword);
            $tokens[] = new ParsedToken(' ', Scope::Whitespace);
            $tokens[] = new ParsedToken($matches[2], Scope::String);

            return;
        }

        $tokens[] = new ParsedToken($value, Scope::String);
    }
}
