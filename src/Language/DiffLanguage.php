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
 * Unified diff format language parser.
 *
 * Handles parsing of diff/patch files with line-by-line scope assignment.
 */
final class DiffLanguage implements LanguageInterface
{
    public function getIdentifier(): string
    {
        return 'diff';
    }

    public function parse(string $code): ParsedStream
    {
        $tokens = [];
        $lines = explode("\n", $code);

        foreach ($lines as $index => $line) {
            // Determine scope based on line prefix
            $scope = $this->determineScopeForLine($line);
            $tokens[] = new ParsedToken($line, $scope);

            // Add newline token between lines (not after the last line)
            if ($index < count($lines) - 1) {
                $tokens[] = new ParsedToken("\n", Scope::Whitespace);
            }
        }

        return new ParsedStream($tokens);
    }

    /**
     * Determine the scope for a single line based on its prefix.
     */
    private function determineScopeForLine(string $line): Scope
    {
        // File headers: must check --- and +++ before single - and +
        if (str_starts_with($line, '---')) {
            return Scope::Meta;
        }

        if (str_starts_with($line, '+++')) {
            return Scope::Meta;
        }

        // Hunk headers
        if (str_starts_with($line, '@@')) {
            return Scope::Meta;
        }

        // Removed lines
        if (str_starts_with($line, '-')) {
            return Scope::DiffRemoved;
        }

        // Added lines
        if (str_starts_with($line, '+')) {
            return Scope::DiffAdded;
        }

        // Comment (\ No newline at end of file)
        if (str_starts_with($line, '\\')) {
            return Scope::Comment;
        }

        // Context lines or anything else
        return Scope::MarkupText;
    }
}
