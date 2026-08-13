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

namespace Alto\Code\Highlight\Language\Ruby;

/**
 * Ruby Lexer - Pass 1: Tokenization.
 *
 * Converts raw Ruby code into a stream of tokens.
 * Does NOT assign semantic meaning — that's the SemanticParser's job.
 *
 * @internal
 *
 * @final Not declared final to allow test doubles, but treat as final in production code
 *
 * @author Simon André <smn.andre@gmail.com>
 */
class RubyLexer
{
    private const KEYWORDS = [
        '__ENCODING__', '__LINE__', '__FILE__',
        'BEGIN', 'END',
        'alias', 'and', 'begin', 'break', 'case', 'class', 'def',
        'defined?', 'do', 'else', 'elsif', 'end', 'ensure',
        'for', 'if', 'in', 'module', 'next', 'not', 'or',
        'redo', 'rescue', 'retry', 'return', 'self', 'super',
        'then', 'undef', 'unless', 'until', 'when', 'while', 'yield',
    ];

    private const BOOLEAN_LITERALS = ['true', 'false'];

    private const NIL_LITERALS = ['nil'];

    /**
     * Tokenize Ruby source code.
     *
     * @return list<RubyToken>
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
                $tokens[] = new RubyToken($ws, RubyTokenType::Whitespace);
                continue;
            }

            // Comment: # to end of line
            if ('#' === $char) {
                $comment = '';
                while ($position < $length && "\n" !== $code[$position]) {
                    $comment .= $code[$position];
                    ++$position;
                }
                $tokens[] = new RubyToken($comment, RubyTokenType::Comment);
                continue;
            }

            // Multi-line comment: =begin ... =end (must be at start of line)
            if ('=' === $char && $position + 5 < $length && 'begin' === substr($code, $position + 1, 5) && (0 === $position || "\n" === $code[$position - 1])) {
                $comment = '';
                while ($position < $length) {
                    // Read until we find =end at start of a line
                    $lineStart = $position;
                    $line = '';
                    while ($position < $length && "\n" !== $code[$position]) {
                        $line .= $code[$position];
                        ++$position;
                    }
                    $comment .= $line;
                    if ($position < $length) {
                        $comment .= "\n";
                        ++$position;
                    }
                    if (str_starts_with($line, '=end')) {
                        break;
                    }
                }
                $tokens[] = new RubyToken($comment, RubyTokenType::Comment);
                continue;
            }

            // Here document: <<IDENTIFIER or <<-IDENTIFIER or <<~IDENTIFIER
            if ('<' === $char && $position + 1 < $length && '<' === $code[$position + 1]) {
                $heredoc = $this->parseHeredoc($code, $position);
                if (null !== $heredoc) {
                    $tokens[] = new RubyToken($heredoc, RubyTokenType::String);
                    continue;
                }
            }

            // Percent literals: %q{}, %Q{}, %w[], %i[], etc.
            if ('%' === $char && $position + 1 < $length && ctype_alpha($code[$position + 1])) {
                $percent = $this->parsePercentLiteral($code, $position);
                if (null !== $percent) {
                    $type = in_array($percent[1], ['s'], true) ? RubyTokenType::Symbol : RubyTokenType::String;
                    $tokens[] = new RubyToken($percent, $type);
                    continue;
                }
            }

            // Double-quoted string "..." (supports interpolation — tokenized as one unit)
            if ('"' === $char) {
                $str = $this->parseString($code, $position, '"');
                $tokens[] = new RubyToken($str, RubyTokenType::String);
                $position += strlen($str);
                continue;
            }

            // Single-quoted string '...'
            if ('\'' === $char) {
                $str = $this->parseSingleQuotedString($code, $position);
                $tokens[] = new RubyToken($str, RubyTokenType::String);
                $position += strlen($str);
                continue;
            }

            // Symbol: :name, :"...", :'...'
            if (':' === $char && $position + 1 < $length && ':' !== $code[$position + 1]) {
                $symbol = $this->parseSymbol($code, $position);
                if (null !== $symbol) {
                    $tokens[] = new RubyToken($symbol, RubyTokenType::Symbol);
                    continue;
                }
            }

            // Instance variable: @name or @@name
            if ('@' === $char && $position + 1 < $length) {
                if ('@' === $code[$position + 1] && $position + 2 < $length && (ctype_alpha($code[$position + 2]) || '_' === $code[$position + 2])) {
                    // Class variable @@name
                    $var = '@@';
                    $position += 2;
                    while ($position < $length && (ctype_alnum($code[$position]) || '_' === $code[$position])) {
                        $var .= $code[$position];
                        ++$position;
                    }
                    $tokens[] = new RubyToken($var, RubyTokenType::ClassVariable);
                    continue;
                }
                if (ctype_alpha($code[$position + 1]) || '_' === $code[$position + 1]) {
                    // Instance variable @name
                    $var = '@';
                    ++$position;
                    while ($position < $length && (ctype_alnum($code[$position]) || '_' === $code[$position])) {
                        $var .= $code[$position];
                        ++$position;
                    }
                    $tokens[] = new RubyToken($var, RubyTokenType::InstanceVariable);
                    continue;
                }
            }

            // Global variable: $name
            if ('$' === $char && $position + 1 < $length && (ctype_alpha($code[$position + 1]) || '_' === $code[$position + 1] || ctype_digit($code[$position + 1]))) {
                $var = '$';
                ++$position;
                while ($position < $length && (ctype_alnum($code[$position]) || '_' === $code[$position])) {
                    $var .= $code[$position];
                    ++$position;
                }
                $tokens[] = new RubyToken($var, RubyTokenType::GlobalVariable);
                continue;
            }

            // Number
            if (ctype_digit($char)) {
                $number = $this->parseNumber($code, $position);
                $tokens[] = new RubyToken($number, RubyTokenType::Number);
                continue;
            }

            // Regex literal /pattern/flags (simple context detection)
            if ('/' === $char && $this->isRegexContext($tokens)) {
                $regex = $this->parseRegex($code, $position);
                if (null !== $regex) {
                    $tokens[] = new RubyToken($regex, RubyTokenType::Regex);
                    continue;
                }
            }

            // Identifiers and keywords
            if (ctype_alpha($char) || '_' === $char) {
                $identifier = $this->parseIdentifier($code, $position);
                $type = $this->classifyIdentifier($identifier);
                $tokens[] = new RubyToken($identifier, $type);
                continue;
            }

            // Multi-character operators
            if ($position + 1 < $length) {
                $two = $char . $code[$position + 1];
                if (in_array($two, ['::', '..', '...', '<=', '>=', '==', '!=', '=~', '!~', '&&', '||', '**', '+=', '-=', '*=', '/=', '%=', '**=', '&&=', '||=', '&=', '|=', '^=', '<<=', '>>=', '<<', '>>', '->', '=>', '<=>', '!~'], true)) {
                    // Three-char check
                    if (in_array($two, ['...', '**=', '&&=', '||=', '<<=', '>>='], true)) {
                        // Already 3-char in the list above — handled below
                    }
                    if ($position + 2 < $length) {
                        $three = $two . $code[$position + 2];
                        if (in_array($three, ['...', '**=', '&&=', '||=', '<<=', '>>='], true)) {
                            $tokens[] = new RubyToken($three, RubyTokenType::Operator);
                            $position += 3;
                            continue;
                        }
                    }
                    if (in_array($two, ['..', '::', '<=', '>=', '==', '!=', '=~', '!~', '&&', '||', '**', '+=', '-=', '*=', '/=', '%=', '&=', '|=', '^=', '<<', '>>', '->', '=>', '<=>'], true)) {
                        $tokens[] = new RubyToken($two, RubyTokenType::Operator);
                        $position += 2;
                        continue;
                    }
                }
            }

            // Single-character operators
            if (in_array($char, ['+', '-', '*', '/', '%', '=', '<', '>', '!', '&', '|', '^', '~', '?'], true)) {
                $tokens[] = new RubyToken($char, RubyTokenType::Operator);
                ++$position;
                continue;
            }

            // Punctuation
            if (in_array($char, ['(', ')', '{', '}', '[', ']', ';', ',', '.', ':'], true)) {
                $tokens[] = new RubyToken($char, RubyTokenType::Punctuation);
                ++$position;
                continue;
            }

            // Unknown character — skip
            ++$position;
        }

        return $tokens;
    }

    private function parseHeredoc(string $code, int &$position): ?string
    {
        $length = strlen($code);
        $start = $position;
        $prefix = '<<';
        $position += 2;

        // Optional dash or tilde for indented heredoc
        $modifier = '';
        if ($position < $length && ('-' === $code[$position] || '~' === $code[$position])) {
            $modifier = $code[$position];
            ++$position;
        }

        // Optional quoted delimiter
        $quoteChar = null;
        if ($position < $length && ('"' === $code[$position] || '\'' === $code[$position] || '`' === $code[$position])) {
            $quoteChar = $code[$position];
            ++$position;
        }

        // Read delimiter identifier
        $delimiter = '';
        while ($position < $length && (ctype_alnum($code[$position]) || '_' === $code[$position])) {
            $delimiter .= $code[$position];
            ++$position;
        }

        if ('' === $delimiter) {
            $position = $start;

            return null;
        }

        if (null !== $quoteChar && $position < $length && $quoteChar === $code[$position]) {
            ++$position;
        }

        // Read to end of current line (the <<HEREDOC is on that line)
        $heredoc = $prefix . $modifier . ($quoteChar ?? '') . $delimiter . ($quoteChar ?? '');
        while ($position < $length && "\n" !== $code[$position]) {
            $heredoc .= $code[$position];
            ++$position;
        }
        if ($position < $length) {
            $heredoc .= "\n";
            ++$position;
        }

        // Read content lines until we hit the delimiter
        $closing = $delimiter . "\n";
        while ($position < $length) {
            $lineStart = $position;
            $line = '';
            while ($position < $length && "\n" !== $code[$position]) {
                $line .= $code[$position];
                ++$position;
            }
            if ($position < $length) {
                $line .= "\n";
                ++$position;
            }
            $heredoc .= $line;
            $trimmed = ltrim($line);
            if (rtrim($trimmed) === $delimiter) {
                break;
            }
        }

        return $heredoc;
    }

    private function parsePercentLiteral(string $code, int &$position): ?string
    {
        $length = strlen($code);
        $start = $position;
        $literal = '%';
        ++$position;

        $type = '';
        if ($position < $length && ctype_alpha($code[$position])) {
            $type = $code[$position];
            ++$position;
        }

        if ($position >= $length) {
            $position = $start;

            return null;
        }

        $open = $code[$position];
        $close = match ($open) {
            '(' => ')',
            '[' => ']',
            '{' => '}',
            '<' => '>',
            default => $open,
        };
        $literal .= $type . $open;
        ++$position;

        $depth = 1;
        while ($position < $length && $depth > 0) {
            $char = $code[$position];
            if ('\\' === $char && $position + 1 < $length) {
                $literal .= $char . $code[$position + 1];
                $position += 2;
                continue;
            }
            if ($char === $open && $open !== $close) {
                ++$depth;
            } elseif ($char === $close) {
                --$depth;
            }
            $literal .= $char;
            ++$position;
        }

        return $literal;
    }

    private function parseString(string $code, int $position, string $quote): string
    {
        $str = $quote;
        ++$position;
        $length = strlen($code);

        while ($position < $length) {
            $char = $code[$position];
            $str .= $char;

            if ('\\' === $char && $position + 1 < $length) {
                $str .= $code[$position + 1];
                $position += 2;
                continue;
            }

            if ($char === $quote) {
                break;
            }

            ++$position;
        }

        return $str;
    }

    private function parseSingleQuotedString(string $code, int $position): string
    {
        $str = "'";
        ++$position;
        $length = strlen($code);

        while ($position < $length) {
            $char = $code[$position];
            $str .= $char;

            // Only \\ and \' are escape sequences in single-quoted strings
            if ('\\' === $char && $position + 1 < $length && in_array($code[$position + 1], ['\\', "'"], true)) {
                $str .= $code[$position + 1];
                $position += 2;
                continue;
            }

            if ('\'' === $char) {
                break;
            }

            ++$position;
        }

        return $str;
    }

    private function parseSymbol(string $code, int &$position): ?string
    {
        $length = strlen($code);
        $symbol = ':';
        ++$position;

        if ($position >= $length) {
            return null;
        }

        $char = $code[$position];

        // Symbol with quotes: :"..." or :'...'
        if ('"' === $char || '\'' === $char) {
            $inner = $this->parseString($code, $position, $char);
            $symbol .= $inner;
            $position += strlen($inner);

            return $symbol;
        }

        // Symbol with identifier chars
        if (ctype_alpha($char) || '_' === $char) {
            while ($position < $length && (ctype_alnum($code[$position]) || '_' === $code[$position] || '?' === $code[$position] || '!' === $code[$position])) {
                $symbol .= $code[$position];
                ++$position;
                // ? and ! are terminal
                if (in_array($code[$position - 1], ['?', '!'], true)) {
                    break;
                }
            }

            return $symbol;
        }

        // Not a valid symbol — back up
        --$position;

        return null;
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

            return $number;
        }

        // Octal: 0o... or 0...
        if ($position + 1 < $length && '0' === $code[$position] && 'o' === strtolower($code[$position + 1])) {
            $number = substr($code, $position, 2);
            $position += 2;
            while ($position < $length && (($code[$position] >= '0' && $code[$position] <= '7') || '_' === $code[$position])) {
                $number .= $code[$position];
                ++$position;
            }

            return $number;
        }

        // Integer and float
        while ($position < $length && (ctype_digit($code[$position]) || '_' === $code[$position])) {
            $number .= $code[$position];
            ++$position;
        }

        // Float
        if ($position < $length && '.' === $code[$position] && $position + 1 < $length && ctype_digit($code[$position + 1])) {
            $number .= '.';
            ++$position;
            while ($position < $length && (ctype_digit($code[$position]) || '_' === $code[$position])) {
                $number .= $code[$position];
                ++$position;
            }
        }

        // Exponent
        if ($position < $length && 'e' === strtolower($code[$position])) {
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

        // Rational/Complex suffix: 5r, 5i, 5ri
        if ($position < $length && in_array($code[$position], ['r', 'i'], true)) {
            $number .= $code[$position];
            ++$position;
            if ($position < $length && 'i' === $code[$position] && 'r' === $code[$position - 1]) {
                $number .= $code[$position];
                ++$position;
            }
        }

        return $number;
    }

    private function parseRegex(string $code, int &$position): ?string
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
                // Flags: imxosuenr
                while ($position < $length && preg_match('/[imxosuenr]/', $code[$position])) {
                    $regex .= $code[$position];
                    ++$position;
                }

                return $regex;
            }

            if ("\n" === $char) {
                return null;
            }

            $regex .= $char;
            ++$position;
        }

        return null;
    }

    /**
     * @param list<RubyToken> $tokens
     */
    private function isRegexContext(array $tokens): bool
    {
        if (empty($tokens)) {
            return true;
        }

        $lastToken = end($tokens);
        if (RubyTokenType::Whitespace === $lastToken->type) {
            for ($i = count($tokens) - 1; $i >= 0; --$i) {
                if (RubyTokenType::Whitespace !== $tokens[$i]->type) {
                    $lastToken = $tokens[$i];
                    break;
                }
            }
        }

        return in_array($lastToken->text, ['=', '(', '[', ',', ':', 'return', '{', ';', 'if', 'unless', 'while', 'until', 'and', 'or', 'not'], true)
            || RubyTokenType::Operator === $lastToken->type
            || RubyTokenType::Keyword === $lastToken->type;
    }

    private function parseIdentifier(string $code, int &$position): string
    {
        $identifier = '';
        $length = strlen($code);

        while ($position < $length && (ctype_alnum($code[$position]) || '_' === $code[$position])) {
            $identifier .= $code[$position];
            ++$position;
        }

        // Method names can end with ? or !
        if ($position < $length && in_array($code[$position], ['?', '!'], true)) {
            // But not !=
            if ('!' !== $code[$position] || $position + 1 >= $length || '=' !== $code[$position + 1]) {
                $identifier .= $code[$position];
                ++$position;
            }
        }

        return $identifier;
    }

    private function classifyIdentifier(string $identifier): RubyTokenType
    {
        if (in_array($identifier, self::BOOLEAN_LITERALS, true)) {
            return RubyTokenType::BooleanLiteral;
        }

        if (in_array($identifier, self::NIL_LITERALS, true)) {
            return RubyTokenType::NilLiteral;
        }

        if (in_array($identifier, self::KEYWORDS, true)) {
            return RubyTokenType::Keyword;
        }

        return RubyTokenType::Identifier;
    }
}
