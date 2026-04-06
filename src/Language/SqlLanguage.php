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
use Alto\Code\Highlight\Parser\StreamBuilder;
use Alto\Code\Highlight\Scope;

/**
 * SQL language parser.
 */
final class SqlLanguage implements LanguageInterface
{
    private const KEYWORDS = [
        'select', 'from', 'where', 'insert', 'update', 'delete', 'create', 'drop', 'alter',
        'table', 'into', 'values', 'and', 'or', 'not', 'null', 'as', 'join', 'on',
        'group', 'by', 'order', 'limit', 'offset', 'primary', 'key', 'foreign', 'references',
        'default', 'constraint', 'index', 'unique', 'check', 'view', 'union', 'all',
        'distinct', 'like', 'in', 'is', 'case', 'when', 'then', 'else', 'end', 'having',
        'truncate', 'begin', 'transaction', 'commit', 'rollback', 'grant', 'revoke',
        'left', 'right', 'inner', 'outer', 'full', 'cross', 'exists', 'between', 'asc', 'desc',
    ];

    private const FUNCTIONS = [
        'count', 'sum', 'avg', 'min', 'max', 'upper', 'lower', 'length', 'concat',
        'coalesce', 'now', 'date', 'abs', 'round', 'ceil', 'floor', 'cast', 'convert',
    ];

    public function getIdentifier(): string
    {
        return 'sql';
    }

    public function parse(string $code): ParsedStream
    {
        $stream = new StreamBuilder();
        $length = strlen($code);
        $position = 0;

        while ($position < $length) {
            $char = $code[$position];

            // Whitespace
            if (preg_match('/\s/', $char)) {
                $ws = '';
                while ($position < $length && preg_match('/\s/', $code[$position])) {
                    $ws .= $code[$position];
                    ++$position;
                }
                $stream->add($ws, Scope::Whitespace);
                continue;
            }

            // Comments
            // -- Line comment
            if ('-' === $char && $position + 1 < $length && '-' === $code[$position + 1]) {
                $comment = '';
                while ($position < $length && "\n" !== $code[$position]) {
                    $comment .= $code[$position];
                    ++$position;
                }
                $stream->add($comment, Scope::Comment);
                continue;
            }
            // /* Block comment */
            if ('/' === $char && $position + 1 < $length && '*' === $code[$position + 1]) {
                $comment = '/*';
                $position += 2;
                while ($position < $length) {
                    if ('*' === $code[$position] && $position + 1 < $length && '/' === $code[$position + 1]) {
                        $comment .= '*/';
                        $position += 2;
                        break;
                    }
                    $comment .= $code[$position];
                    ++$position;
                }
                $stream->add($comment, Scope::Comment);
                continue;
            }

            // Strings (Single quotes)
            if ("'" === $char) {
                $string = "'";
                ++$position;
                while ($position < $length) {
                    if ("'" === $code[$position]) {
                        // Handle escaped quotes ''
                        if ($position + 1 < $length && "'" === $code[$position + 1]) {
                            $string .= "''";
                            $position += 2;
                            continue;
                        }
                        $string .= "'";
                        ++$position;
                        break;
                    }
                    // Newline character should terminate the string
                    if ("\n" === $code[$position]) {
                        break;
                    }
                    $string .= $code[$position];
                    ++$position;
                }
                $stream->add($string, Scope::String);
                continue;
            }

            // Quoted Identifiers (Double quotes or Backticks)
            if ('"' === $char || '`' === $char) {
                $quote = $char;
                $identifier = $quote;
                ++$position;
                while ($position < $length) {
                    if ($code[$position] === $quote) {
                        // Handle escaped quote (e.g. "") inside
                        if ($position + 1 < $length && $code[$position + 1] === $quote) {
                            $identifier .= $quote.$quote;
                            $position += 2;
                            continue;
                        }
                        $identifier .= $quote;
                        ++$position;
                        break;
                    }
                    $identifier .= $code[$position];
                    ++$position;
                }
                $stream->add($identifier, Scope::Variable);
                continue;
            }

            // Numbers
            if (preg_match('/[0-9]/', $char)) {
                $number = '';
                while ($position < $length && preg_match('/[0-9.]/', $code[$position])) {
                    // Primitive float check: allow one dot
                    $number .= $code[$position];
                    ++$position;
                }
                $stream->add($number, Scope::Number);
                continue;
            }

            // Variables (@var)
            if ('@' === $char) {
                $variable = '@';
                ++$position;
                while ($position < $length && preg_match('/[a-zA-Z0-9_$]/', $code[$position])) {
                    $variable .= $code[$position];
                    ++$position;
                }
                $stream->add($variable, Scope::Variable);
                continue;
            }

            // Operators
            if (str_contains('=<>!+-*/%', $char)) {
                $op = '';
                while ($position < $length && str_contains('=<>!+-*/%', $code[$position])) {
                    $op .= $code[$position];
                    ++$position;
                }
                $stream->add($op, Scope::Operator);
                continue;
            }

            // Punctuation
            if (str_contains(';,().', $char)) {
                $stream->add($char, Scope::Punctuation);
                ++$position;
                continue;
            }

            // Identifiers and Keywords
            if (preg_match('/[a-zA-Z_]/', $char)) {
                $word = '';
                while ($position < $length && preg_match('/[a-zA-Z0-9_]/', $code[$position])) {
                    $word .= $code[$position];
                    ++$position;
                }

                $lower = strtolower($word);
                if (in_array($lower, self::KEYWORDS, true)) {
                    $stream->add($word, Scope::Keyword);
                } elseif (in_array($lower, self::FUNCTIONS, true)) {
                    $stream->add($word, Scope::FunctionCall);
                } else {
                    $stream->add($word, Scope::Variable);
                }
                continue;
            }

            // Fallback for unknown characters
            $stream->add($char, Scope::MarkupText);
            ++$position;
        }

        return $stream->build();
    }
}
