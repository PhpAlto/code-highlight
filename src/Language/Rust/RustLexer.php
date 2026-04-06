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

namespace Alto\Code\Highlight\Language\Rust;

/**
 * Rust Lexer - Pass 1: Tokenization.
 *
 * Converts raw Rust code into a stream of tokens.
 * Does NOT assign semantic meaning — that's the SemanticParser's job.
 *
 * @internal
 *
 * @final Not declared final to allow test doubles, but treat as final in production code
 */
class RustLexer
{
    private const KEYWORDS = [
        'as', 'async', 'await', 'break', 'const', 'continue', 'crate',
        'dyn', 'else', 'enum', 'extern', 'false', 'fn', 'for', 'if',
        'impl', 'in', 'let', 'loop', 'match', 'mod', 'move', 'mut',
        'pub', 'ref', 'return', 'self', 'Self', 'static', 'struct',
        'super', 'trait', 'true', 'type', 'union', 'unsafe', 'use',
        'where', 'while',
    ];

    private const BOOLEAN_LITERALS = ['true', 'false'];

    /**
     * Tokenize Rust source code.
     *
     * @return list<RustToken>
     */
    public function tokenize(string $code): array
    {
        $tokens = [];
        $position = 0;
        $length = strlen($code);

        while ($position < $length) {
            $char = $code[$position];

            // Whitespace
            if (' ' === $char || "\t" === $char || "\n" === $char || "\r" === $char) {
                $ws = '';
                while ($position < $length && (' ' === $code[$position] || "\t" === $code[$position] || "\n" === $code[$position] || "\r" === $code[$position])) {
                    $ws .= $code[$position];
                    ++$position;
                }
                $tokens[] = new RustToken($ws, RustTokenType::Whitespace);
                continue;
            }

            // Attribute: #[  or  #![
            if ('#' === $char && $position + 1 < $length && '[' === $code[$position + 1]) {
                $tokens[] = new RustToken('#', RustTokenType::Punctuation);
                ++$position;
                continue;
            }
            if ('#' === $char && $position + 2 < $length && '!' === $code[$position + 1] && '[' === $code[$position + 2]) {
                $tokens[] = new RustToken('#!', RustTokenType::Punctuation);
                $position += 2;
                continue;
            }

            // Comments
            if ('/' === $char && $position + 1 < $length) {
                $next = $code[$position + 1];

                // Doc comment ///  or  //!
                if ('/' === $next && $position + 2 < $length && ('/' === $code[$position + 2] || '!' === $code[$position + 2])) {
                    $comment = '//';
                    $position += 2;
                    while ($position < $length && "\n" !== $code[$position]) {
                        $comment .= $code[$position];
                        ++$position;
                    }
                    $tokens[] = new RustToken($comment, RustTokenType::DocComment);
                    continue;
                }

                // Line comment //
                if ('/' === $next) {
                    $comment = '//';
                    $position += 2;
                    while ($position < $length && "\n" !== $code[$position]) {
                        $comment .= $code[$position];
                        ++$position;
                    }
                    $tokens[] = new RustToken($comment, RustTokenType::Comment);
                    continue;
                }

                // Block comment /* */  (may be nested in Rust, but we do simple parsing)
                if ('*' === $next) {
                    $comment = '/*';
                    $position += 2;
                    $depth = 1;
                    while ($position < $length && $depth > 0) {
                        if ($position + 1 < $length && '/' === $code[$position] && '*' === $code[$position + 1]) {
                            $comment .= '/*';
                            $position += 2;
                            ++$depth;
                        } elseif ($position + 1 < $length && '*' === $code[$position] && '/' === $code[$position + 1]) {
                            $comment .= '*/';
                            $position += 2;
                            --$depth;
                        } else {
                            $comment .= $code[$position];
                            ++$position;
                        }
                    }
                    // Determine if doc comment: /** or /*!
                    $type = (strlen($comment) > 3 && ('*' === $comment[2] || '!' === $comment[2]))
                        ? RustTokenType::DocComment
                        : RustTokenType::Comment;
                    $tokens[] = new RustToken($comment, $type);
                    continue;
                }
            }

            // Raw strings: r"..." or r#"..."# or r##"..."##
            if ('r' === $char && $position + 1 < $length && ('#' === $code[$position + 1] || '"' === $code[$position + 1])) {
                $raw = $this->parseRawString($code, $position);
                if (null !== $raw) {
                    $tokens[] = new RustToken($raw, RustTokenType::RawString);
                    continue;
                }
            }

            // Byte string literals: b"..." or b'...'
            if ('b' === $char && $position + 1 < $length && ('"' === $code[$position + 1] || '\'' === $code[$position + 1])) {
                $quote = $code[$position + 1];
                if ('"' === $quote) {
                    $str = 'b'.$this->parseQuotedString($code, $position + 1, '"');
                    $tokens[] = new RustToken($str, RustTokenType::String);
                    $position += strlen($str);
                    continue;
                }
            }

            // String literals "..."
            if ('"' === $char) {
                $str = $this->parseQuotedString($code, $position, '"');
                $tokens[] = new RustToken($str, RustTokenType::String);
                $position += strlen($str);
                continue;
            }

            // Lifetime or char literal: starts with '
            if ('\'' === $char) {
                // Char literal: '.' or '\n' etc. — must have closing '
                // Lifetime: 'identifier (no closing quote)
                $result = $this->parseLifetimeOrChar($code, $position);
                $tokens[] = $result;
                $position += strlen($result->text);
                continue;
            }

            // Numbers
            if (ctype_digit($char)) {
                $number = $this->parseNumber($code, $position);
                $tokens[] = new RustToken($number, RustTokenType::Number);
                continue;
            }

            // Identifiers and keywords (including macro detection)
            if (ctype_alpha($char) || '_' === $char) {
                $identifier = $this->parseIdentifier($code, $position);

                // Macro invocation: identifier followed by !  (but not !=)
                if ($position < $length && '!' === $code[$position] && ($position + 1 >= $length || '=' !== $code[$position + 1])) {
                    $tokens[] = new RustToken($identifier.'!', RustTokenType::Macro);
                    ++$position;
                    continue;
                }

                $type = $this->classifyIdentifier($identifier);
                $tokens[] = new RustToken($identifier, $type);
                continue;
            }

            // Multi-character operators
            if ($position + 1 < $length) {
                $two = $char.$code[$position + 1];

                if (in_array($two, ['->', '=>', '::', '..', '&&', '||', '==', '!=', '<=', '>=', '+=', '-=', '*=', '/=', '%=', '&=', '|=', '^=', '<<', '>>', '..='], true)) {
                    // Three-char: ..= <<= >>=
                    if (in_array($two, ['..', '<<', '>>'], true) && $position + 2 < $length && '=' === $code[$position + 2]) {
                        $tokens[] = new RustToken($two.'=', RustTokenType::Operator);
                        $position += 3;
                        continue;
                    }
                    $tokens[] = new RustToken($two, RustTokenType::Operator);
                    $position += 2;
                    continue;
                }
            }

            // Single-character operators and punctuation
            if (in_array($char, ['+', '-', '*', '/', '%', '=', '<', '>', '!', '&', '|', '^', '~', '@'], true)) {
                $tokens[] = new RustToken($char, RustTokenType::Operator);
                ++$position;
                continue;
            }

            if (in_array($char, ['(', ')', '{', '}', '[', ']', ';', ',', '.', ':', '?'], true)) {
                $tokens[] = new RustToken($char, RustTokenType::Punctuation);
                ++$position;
                continue;
            }

            // Unknown character — skip
            ++$position;
        }

        return $tokens;
    }

    private function parseRawString(string $code, int &$position): ?string
    {
        $start = $position;
        $length = strlen($code);
        $raw = 'r';
        ++$position;

        // Count opening hashes
        $hashes = 0;
        while ($position < $length && '#' === $code[$position]) {
            ++$hashes;
            ++$position;
        }

        if ($position >= $length || '"' !== $code[$position]) {
            // Not a valid raw string — reset
            $position = $start;

            return null;
        }

        $raw .= str_repeat('#', $hashes).'"';
        ++$position;

        $closing = '"'.str_repeat('#', $hashes);
        $closingLen = strlen($closing);

        while ($position < $length) {
            if (substr($code, $position, $closingLen) === $closing) {
                $raw .= $closing;
                $position += $closingLen;

                return $raw;
            }
            $raw .= $code[$position];
            ++$position;
        }

        return $raw; // Unclosed raw string
    }

    private function parseQuotedString(string $code, int $position, string $quote): string
    {
        $str = $quote;
        ++$position;
        $length = strlen($code);
        $escaped = false;

        while ($position < $length) {
            $char = $code[$position];
            $str .= $char;

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

        return $str;
    }

    private function parseLifetimeOrChar(string $code, int $position): RustToken
    {
        $length = strlen($code);
        // After the opening '
        $next = $position + 1 < $length ? $code[$position + 1] : null;

        // Empty char ''  — treat as operator/punctuation
        if (null === $next) {
            return new RustToken("'", RustTokenType::Punctuation);
        }

        // If next is a letter or underscore, this could be a lifetime OR char like 'a'
        if (ctype_alpha($next) || '_' === $next) {
            // Read the identifier-like content
            $content = '';
            $i = $position + 1;
            while ($i < $length && (ctype_alnum($code[$i]) || '_' === $code[$i])) {
                $content .= $code[$i];
                ++$i;
            }

            // If followed by a closing quote, it's a char literal (e.g., 'a')
            if ($i < $length && '\'' === $code[$i] && 1 === strlen($content)) {
                return new RustToken("'".$content."'", RustTokenType::Char);
            }

            // Otherwise it's a lifetime: 'ident
            return new RustToken("'".$content, RustTokenType::Lifetime);
        }

        // Char literal: '\n', '\t', '\\', '\'' etc. or any single char
        if ('\\' === $next) {
            // Escaped char
            $escaped = '\'\\';
            $i = $position + 2;
            if ($i < $length) {
                $escaped .= $code[$i];
                ++$i;
                // Unicode escape \u{...}
                if ('u' === $code[$i - 1] && $i < $length && '{' === $code[$i]) {
                    $escaped .= $code[$i];
                    ++$i;
                    while ($i < $length && '}' !== $code[$i]) {
                        $escaped .= $code[$i];
                        ++$i;
                    }
                    if ($i < $length) {
                        $escaped .= $code[$i];
                        ++$i;
                    }
                }
                if ($i < $length && '\'' === $code[$i]) {
                    $escaped .= '\'';
                }
            }

            return new RustToken($escaped, RustTokenType::Char);
        }

        // Single char literal like '+'  or 'x'
        if ($position + 2 < $length && '\'' === $code[$position + 2]) {
            return new RustToken("'".$next."'", RustTokenType::Char);
        }

        // Fallback: bare '
        return new RustToken("'", RustTokenType::Punctuation);
    }

    private function parseNumber(string $code, int &$position): string
    {
        $number = '';
        $length = strlen($code);

        // Hex: 0x...
        if ($position + 1 < $length && '0' === $code[$position] && 'x' === strtolower($code[$position + 1])) {
            $number = substr($code, $position, 2);
            $position += 2;
            while ($position < $length && (ctype_xdigit($code[$position]) || '_' === $code[$position])) {
                $number .= $code[$position];
                ++$position;
            }
            $number .= $this->parseNumericSuffix($code, $position);

            return $number;
        }

        // Binary: 0b...
        if ($position + 1 < $length && '0' === $code[$position] && 'b' === strtolower($code[$position + 1])) {
            $number = substr($code, $position, 2);
            $position += 2;
            while ($position < $length && in_array($code[$position], ['0', '1', '_'], true)) {
                $number .= $code[$position];
                ++$position;
            }
            $number .= $this->parseNumericSuffix($code, $position);

            return $number;
        }

        // Octal: 0o...
        if ($position + 1 < $length && '0' === $code[$position] && 'o' === strtolower($code[$position + 1])) {
            $number = substr($code, $position, 2);
            $position += 2;
            while ($position < $length && (($code[$position] >= '0' && $code[$position] <= '7') || '_' === $code[$position])) {
                $number .= $code[$position];
                ++$position;
            }
            $number .= $this->parseNumericSuffix($code, $position);

            return $number;
        }

        // Integer and float (decimal)
        while ($position < $length && (ctype_digit($code[$position]) || '_' === $code[$position])) {
            $number .= $code[$position];
            ++$position;
        }

        // Float: decimal part
        if ($position < $length && '.' === $code[$position] && ($position + 1 >= $length || '.' !== $code[$position + 1])) {
            $number .= '.';
            ++$position;
            while ($position < $length && (ctype_digit($code[$position]) || '_' === $code[$position])) {
                $number .= $code[$position];
                ++$position;
            }
        }

        // Float: exponent
        if ($position < $length && in_array(strtolower($code[$position]), ['e'], true)) {
            $number .= $code[$position];
            ++$position;
            if ($position < $length && in_array($code[$position], ['+', '-'], true)) {
                $number .= $code[$position];
                ++$position;
            }
            while ($position < $length && (ctype_digit($code[$position]) || '_' === $code[$position])) {
                $number .= $code[$position];
                ++$position;
            }
        }

        $number .= $this->parseNumericSuffix($code, $position);

        return $number;
    }

    /**
     * Parse optional numeric type suffix: u8, i32, f64, usize, isize, etc.
     */
    private function parseNumericSuffix(string $code, int &$position): string
    {
        $length = strlen($code);
        if ($position >= $length) {
            return '';
        }

        $suffixes = ['u8', 'u16', 'u32', 'u64', 'u128', 'usize', 'i8', 'i16', 'i32', 'i64', 'i128', 'isize', 'f32', 'f64'];
        foreach ($suffixes as $suffix) {
            $len = strlen($suffix);
            if (substr($code, $position, $len) === $suffix) {
                // Make sure it's not part of a longer identifier
                $after = $position + $len;
                if ($after >= $length || (!ctype_alnum($code[$after]) && '_' !== $code[$after])) {
                    $position += $len;

                    return $suffix;
                }
            }
        }

        return '';
    }

    private function parseIdentifier(string $code, int &$position): string
    {
        $identifier = '';
        $length = strlen($code);

        while ($position < $length && (ctype_alnum($code[$position]) || '_' === $code[$position])) {
            $identifier .= $code[$position];
            ++$position;
        }

        return $identifier;
    }

    private function classifyIdentifier(string $identifier): RustTokenType
    {
        if (in_array($identifier, self::BOOLEAN_LITERALS, true)) {
            return RustTokenType::BooleanLiteral;
        }

        if (in_array($identifier, self::KEYWORDS, true)) {
            return RustTokenType::Keyword;
        }

        return RustTokenType::Identifier;
    }
}
