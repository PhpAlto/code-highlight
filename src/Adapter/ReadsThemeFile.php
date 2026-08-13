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

namespace Alto\Code\Highlight\Adapter;

/**
 * Provides safe file reading for theme adapters.
 *
 * Validates file paths to prevent path traversal attacks and ensures
 * the file exists, is a regular file, and is readable before reading.
 *
 * @internal
 *
 * @author Simon André <smn.andre@gmail.com>
 */
trait ReadsThemeFile
{
    /**
     * Safely read a theme file from the filesystem.
     *
     * @param string $path The file path to read
     *
     * @throws \InvalidArgumentException If the path is invalid, the file doesn't exist, or isn't readable
     */
    private static function readThemeFile(string $path): string
    {
        if ('' === $path) {
            throw new \InvalidArgumentException('Theme file path must not be empty.');
        }

        $realPath = realpath($path);

        if (false === $realPath) {
            throw new \InvalidArgumentException(sprintf('Theme file does not exist: "%s".', $path));
        }

        if (!is_file($realPath)) {
            throw new \InvalidArgumentException(sprintf('Theme path is not a file: "%s".', $path));
        }

        if (!is_readable($realPath)) {
            throw new \InvalidArgumentException(sprintf('Theme file is not readable: "%s".', $path));
        }

        $content = @file_get_contents($realPath);

        if (false === $content) {
            throw new \InvalidArgumentException(sprintf('Failed to read theme file: "%s".', $path));
        }

        return $content;
    }
}
