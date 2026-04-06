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

namespace Alto\Code\Highlight\Tests\Support;

/**
 * A stream wrapper to mock HTTP responses in tests.
 *
 * Usage:
 *   MockHttpStreamWrapper::register();
 *   MockHttpStreamWrapper::setResponse('https://example.com/file.js', 'file contents');
 *   $content = file_get_contents('https://example.com/file.js');
 *   MockHttpStreamWrapper::unregister();
 */
final class MockHttpStreamWrapper
{
    /** @var array<string, string|false> */
    private static array $responses = [];

    /** @var resource|null */
    public $context;

    private string $content = '';
    private int $position = 0;

    public static function register(): void
    {
        stream_wrapper_unregister('https');
        stream_wrapper_unregister('http');
        stream_wrapper_register('https', self::class);
        stream_wrapper_register('http', self::class);
    }

    public static function unregister(): void
    {
        stream_wrapper_unregister('https');
        stream_wrapper_unregister('http');
        stream_wrapper_restore('https');
        stream_wrapper_restore('http');
        self::$responses = [];
    }

    /**
     * Set a mock response for a URL.
     *
     * @param string       $url     The URL to mock
     * @param string|false $content The content to return, or false to simulate failure
     */
    public static function setResponse(string $url, string|false $content): void
    {
        self::$responses[$url] = $content;
    }

    /**
     * Clear all mock responses.
     */
    public static function clearResponses(): void
    {
        self::$responses = [];
    }

    public function stream_open(string $path, string $mode, int $options, ?string &$opened_path): bool
    {
        if (isset(self::$responses[$path])) {
            $response = self::$responses[$path];
            if (false === $response) {
                return false;
            }
            $this->content = $response;
            $this->position = 0;

            return true;
        }

        // URL not mocked - return false
        return false;
    }

    public function stream_read(int $count): string
    {
        $result = substr($this->content, $this->position, $count);
        $this->position += strlen($result);

        return $result;
    }

    public function stream_eof(): bool
    {
        return $this->position >= strlen($this->content);
    }

    /**
     * @return array{size: int}
     */
    public function stream_stat(): array
    {
        return ['size' => strlen($this->content)];
    }

    public function stream_close(): void
    {
        // Nothing to do
    }
}
