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

namespace Alto\Code\Highlight;

/**
 * Interface for syntax highlighting themes.
 *
 * Themes define how different semantic scopes should be styled in the output.
 *
 * @author Simon André <smn.andre@gmail.com>
 */
interface ThemeInterface
{
    /**
     * Get the mapping of scopes to CSS class names.
     *
     * @return array<string, string> Map of scope values to CSS class names
     */
    public function getCssClasses(): array;

    /**
     * Get the complete CSS stylesheet for this theme.
     *
     * This should return a complete CSS string that styles all the classes
     * returned by getCssClasses().
     *
     * @return string The CSS stylesheet content
     */
    public function getStylesheet(): string;

    /**
     * Get the theme name.
     *
     * @return string A human-readable name for the theme
     */
    public function getName(): string;

    /**
     * Whether this is a dark theme.
     *
     * @return bool True if this is a dark theme, false for light themes
     */
    public function isDark(): bool;
}
