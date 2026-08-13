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
use Alto\Code\Highlight\Parser\StreamBuilder;
use Alto\Code\Highlight\Scope;

/**
 * Markdown language parser.
 *
 * Supports CommonMark and GitHub Flavored Markdown.
 *
 * @author Simon André <smn.andre@gmail.com>
 */
final class MarkdownLanguage implements LanguageInterface, EmbeddedLanguageCapable
{
    public function getIdentifier(): string
    {
        return 'markdown';
    }

    public function parse(string $code): ParsedStream
    {
        return $this->parseWithEmbedding($code, EmbeddedLanguageContext::disabled());
    }

    public function parseWithEmbedding(string $code, EmbeddedLanguageContext $context): ParsedStream
    {
        $stream = new StreamBuilder();
        $lines = explode("\n", $code);
        $count = count($lines);

        for ($i = 0; $i < $count; ++$i) {
            $line = $lines[$i];

            if ($i > 0) {
                $stream->add("\n", Scope::Whitespace);
            }

            // Handle empty lines
            if ('' === trim($line)) {
                if ('' !== $line) {
                    $stream->add($line, Scope::Whitespace);
                }

                continue;
            }

            // Headings: # ## ### etc.
            if (preg_match('/^(#{1,6})\s+(.+)$/', $line, $matches)) {
                $stream->add($matches[1], Scope::TagName);
                $stream->add(' ', Scope::Whitespace);
                $stream->add($matches[2], Scope::TagName);

                continue;
            }

            // Horizontal rule: ---, ***, ___
            if (preg_match('/^(\s*)([-*_])\2{2,}\s*$/', $line, $matches)) {
                if ('' !== $matches[1]) {
                    $stream->add($matches[1], Scope::Whitespace);
                }
                $stream->add(trim($line), Scope::Punctuation);

                continue;
            }

            // Fenced code blocks: ```language
            if (preg_match('/^(\s*)```(.*)$/', $line, $matches)) {
                $stream->add($line, Scope::String);

                $lang = trim($matches[2]);
                $blockLines = [];

                // Peek ahead to find closing fence
                $j = $i + 1;
                $closed = false;

                while ($j < $count) {
                    $nextLine = $lines[$j];
                    if (preg_match('/^\s*```/', $nextLine)) {
                        $closed = true;
                        break;
                    }
                    $blockLines[] = $nextLine;
                    ++$j;
                }

                if ($closed) {
                    // Add the content
                    if (!empty($blockLines)) {
                        $stream->add("\n", Scope::Whitespace);
                        $content = implode("\n", $blockLines);

                        if ('' !== $lang && $context->supportsEmbedding()) {
                            $embeddedStream = $context->parseEmbedded($lang, $content);
                            $stream->appendStream($embeddedStream);
                        } else {
                            $stream->add($content, Scope::String);
                        }
                    }

                    // Add closing fence
                    $stream->add("\n", Scope::Whitespace);
                    $stream->add($lines[$j], Scope::String);

                    // Advance main loop index
                    $i = $j;

                    continue;
                }

                // If not closed, we fall through.
                // However, standard markdown parses unclosed blocks to the end.
                // But our loop structure is line-based.
                // If we don't handle it here, the opening fence is added, and subsequent lines are processed normally.
                // This might be acceptable behavior for a simple parser, or we can consume to end.
                // Let's consume to end to be safe.
                $stream->add("\n", Scope::Whitespace);
                $content = implode("\n", array_slice($lines, $i + 1));
                if ('' !== $content) {
                    if ('' !== $lang && $context->supportsEmbedding()) {
                        $embeddedStream = $context->parseEmbedded($lang, $content);
                        $stream->appendStream($embeddedStream);
                    } else {
                        $stream->add($content, Scope::String);
                    }
                }
                $i = $count; // Finish
                continue;
            }

            // Blockquote: >
            if (preg_match('/^(>\s*)(.*)$/', $line, $matches)) {
                $stream->add($matches[1], Scope::TagName);
                if ('' !== $matches[2]) {
                    $this->parseInline($matches[2], $stream);
                }

                continue;
            }

            // Lists: - * + or 1. 2. etc.
            if (preg_match('/^(\s*)([-*+]|\d+\.)\s+(.*)$/', $line, $matches)) {
                if ('' !== $matches[1]) {
                    $stream->add($matches[1], Scope::Whitespace);
                }
                $stream->add($matches[2], Scope::TagName);
                $stream->add(' ', Scope::Whitespace);
                $this->parseInline($matches[3], $stream);

                continue;
            }

            // Table row: | cell | cell |
            if (str_contains($line, '|')) {
                $this->parseTableRow($line, $stream);

                continue;
            }

            // Regular text with inline formatting
            $this->parseInline($line, $stream);
        }

        return $stream->build();
    }

    /**
     * Parse inline formatting (bold, italic, code, links, etc.).
     */
    private function parseInline(string $text, StreamBuilder $stream): void
    {
        $position = 0;
        $length = strlen($text);

        while ($position < $length) {
            // Bold: **text** or __text__
            if (($position + 1 < $length && '*' === $text[$position] && '*' === $text[$position + 1])
                || ($position + 1 < $length && '_' === $text[$position] && '_' === $text[$position + 1])) {
                $delimiter = $text[$position] . $text[$position + 1];
                $end = strpos($text, $delimiter, $position + 2);
                if (false !== $end) {
                    $content = substr($text, $position, $end - $position + 2);
                    $stream->add($content, Scope::Keyword);
                    $position = $end + 2;

                    continue;
                }
            }

            // Italic: *text* or _text_
            if (('*' === $text[$position] || '_' === $text[$position])
                && !($position + 1 < $length && $text[$position + 1] === $text[$position])) {
                $delimiter = $text[$position];
                $end = strpos($text, $delimiter, $position + 1);
                if (false !== $end) {
                    $content = substr($text, $position, $end - $position + 1);
                    $stream->add($content, Scope::Keyword);
                    $position = $end + 1;

                    continue;
                }
            }

            // Strikethrough: ~~text~~
            if ($position + 1 < $length && '~' === $text[$position] && '~' === $text[$position + 1]) {
                $end = strpos($text, '~~', $position + 2);
                if (false !== $end) {
                    $content = substr($text, $position, $end - $position + 2);
                    $stream->add($content, Scope::Keyword);
                    $position = $end + 2;

                    continue;
                }
            }

            // Inline code: `code`
            if ('`' === $text[$position]) {
                $end = strpos($text, '`', $position + 1);
                if (false !== $end) {
                    $content = substr($text, $position, $end - $position + 1);
                    $stream->add($content, Scope::String);
                    $position = $end + 1;

                    continue;
                }
            }

            // Links: [text](url)
            if ('[' === $text[$position]) {
                $linkEnd = strpos($text, '](', $position);
                if (false !== $linkEnd) {
                    $urlEnd = strpos($text, ')', $linkEnd + 2);
                    if (false !== $urlEnd) {
                        $content = substr($text, $position, $urlEnd - $position + 1);
                        $stream->add($content, Scope::AttributeValue);
                        $position = $urlEnd + 1;

                        continue;
                    }
                }
            }

            // Images: ![alt](url)
            if ('!' === $text[$position] && $position + 1 < $length && '[' === $text[$position + 1]) {
                $linkEnd = strpos($text, '](', $position);
                if (false !== $linkEnd) {
                    $urlEnd = strpos($text, ')', $linkEnd + 2);
                    if (false !== $urlEnd) {
                        $content = substr($text, $position, $urlEnd - $position + 1);
                        $stream->add($content, Scope::AttributeValue);
                        $position = $urlEnd + 1;

                        continue;
                    }
                }
            }

            // Regular text
            $plainText = '';
            while ($position < $length
                   && !in_array($text[$position], ['*', '_', '`', '[', '!', '~'], true)) {
                $plainText .= $text[$position];
                ++$position;
            }

            if ('' !== $plainText) {
                $stream->add($plainText, Scope::MarkupText);
            }

            if ($position < $length
                && in_array($text[$position], ['*', '_', '`', '[', '!', '~'], true)) {
                // If we couldn't parse a special character, treat as plain text
                $plainText = $text[$position];
                $stream->add($plainText, Scope::MarkupText);
                ++$position;
            }
        }
    }

    /**
     * Parse table row.
     */
    private function parseTableRow(string $line, StreamBuilder $stream): void
    {
        $parts = explode('|', $line);
        foreach ($parts as $i => $part) {
            if ($i > 0) {
                $stream->add('|', Scope::TagName);
            }
            if ('' !== $part) {
                $stream->add($part, Scope::TagName);
            }
        }
    }
}
