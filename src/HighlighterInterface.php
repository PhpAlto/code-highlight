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

namespace Alto\Code\Highlight;

/**
 * @author Simon André <smn.andre@gmail.com>
 */
interface HighlighterInterface
{
    /**
     * Highlight source code and return HTML.
     *
     * @param string    $code           The source code to highlight
     * @param string    $language       The language identifier ('php', 'html', etc.)
     * @param bool      $lineNumbers    Whether to include line numbers
     * @param list<int> $highlightLines Line numbers to highlight (1-indexed)
     *
     * @return string The highlighted HTML output
     */
    public function highlight(string $code, string $language, bool $lineNumbers = false, array $highlightLines = []): string;
}
