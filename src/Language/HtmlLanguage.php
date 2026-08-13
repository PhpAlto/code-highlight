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
 * HTML language parser.
 *
 * Handles parsing and semantic analysis of HTML code.
 *
 * @author Simon André <smn.andre@gmail.com>
 */
class HtmlLanguage implements EmbeddedLanguageCapable
{
    public function getIdentifier(): string
    {
        return 'html';
    }

    public function parse(string $code): ParsedStream
    {
        return $this->parseWithEmbedding($code, EmbeddedLanguageContext::disabled());
    }

    public function parseWithEmbedding(string $code, EmbeddedLanguageContext $context): ParsedStream
    {
        $stream = new StreamBuilder();
        $position = 0;
        $length = strlen($code);

        while ($position < $length) {
            // Check for XML/PHP processing instructions
            if ('<?' === substr($code, $position, 2)) {
                $end = strpos($code, '?>', $position);
                if (false === $end) {
                    $end = $length;
                } else {
                    $end += 2;
                }
                $text = substr($code, $position, $end - $position);
                $stream->add($text, Scope::Comment);
                $position = $end;

                continue;
            }

            // Check for DOCTYPE
            if ('<!DOCTYPE' === substr($code, $position, 9) || '<!doctype' === substr($code, $position, 9)) {
                $end = strpos($code, '>', $position);
                if (false === $end) {
                    $end = $length;
                } else {
                    ++$end;
                }
                $text = substr($code, $position, $end - $position);
                $stream->add($text, Scope::Comment);
                $position = $end;

                continue;
            }

            // Check for CDATA sections
            if ('<![CDATA[' === substr($code, $position, 9)) {
                $end = strpos($code, ']]>', $position);
                if (false === $end) {
                    $end = $length;
                } else {
                    $end += 3;
                }
                $text = substr($code, $position, $end - $position);
                $stream->add($text, Scope::Comment);
                $position = $end;

                continue;
            }

            // Check for HTML comments
            if ('<!--' === substr($code, $position, 4)) {
                $end = strpos($code, '-->', $position);
                if (false === $end) {
                    $end = $length;
                } else {
                    $end += 3;
                }
                $text = substr($code, $position, $end - $position);
                $stream->add($text, Scope::Comment);
                $position = $end;

                continue;
            }

            // Check for opening tags
            if ('<' === $code[$position] && $position + 1 < $length && '!' !== $code[$position + 1]) {
                ++$position;

                // Skip closing slash if present
                $isClosingTag = false;
                if ($position < $length && '/' === $code[$position]) {
                    $isClosingTag = true;
                    $stream->add('</', Scope::TagName);
                    ++$position;
                } else {
                    $stream->add('<', Scope::TagName);
                }

                // Parse tag name
                $tagName = '';
                while ($position < $length && preg_match('/[a-zA-Z0-9:-]/', $code[$position])) {
                    $tagName .= $code[$position];
                    ++$position;
                }

                if ('' !== $tagName) {
                    $stream->add($tagName, Scope::TagName);
                }

                $normalizedTagName = '' !== $tagName ? strtolower($tagName) : '';
                $attributes = [];

                // Parse attributes
                while ($position < $length && '>' !== $code[$position]) {
                    $currentAttribute = null;

                    // Skip whitespace
                    if (preg_match('/\s/', $code[$position])) {
                        $ws = '';
                        while ($position < $length && preg_match('/\s/', $code[$position])) {
                            $ws .= $code[$position];
                            ++$position;
                        }
                        $stream->add($ws, Scope::Whitespace);

                        continue;
                    }

                    // Self-closing slash
                    if ('/' === $code[$position]) {
                        $stream->add('/', Scope::TagName);
                        ++$position;

                        continue;
                    }

                    // Parse attribute name
                    $attrName = '';
                    while ($position < $length && preg_match('/[a-zA-Z0-9:_-]/', $code[$position])) {
                        $attrName .= $code[$position];
                        ++$position;
                    }

                    if ('' !== $attrName) {
                        $stream->add($attrName, Scope::TagAttributeName);
                        $currentAttribute = strtolower($attrName);
                        $attributes[$currentAttribute] = true;
                    }

                    // Skip whitespace around =
                    while ($position < $length && preg_match('/\s/', $code[$position])) {
                        $stream->add($code[$position], Scope::Whitespace);
                        ++$position;
                    }

                    // Parse = and attribute value
                    if ($position < $length && '=' === $code[$position]) {
                        $stream->add('=', Scope::Punctuation);
                        ++$position;

                        // Skip whitespace after =
                        while ($position < $length && preg_match('/\s/', $code[$position])) {
                            $stream->add($code[$position], Scope::Whitespace);
                            ++$position;
                        }

                        // Parse quoted value
                        if ($position < $length && ('"' === $code[$position] || "'" === $code[$position])) {
                            $quote = $code[$position];
                            $value = $quote;
                            ++$position;

                            while ($position < $length && $code[$position] !== $quote) {
                                $value .= $code[$position];
                                ++$position;
                            }

                            if ($position < $length) {
                                $value .= $code[$position];
                                ++$position;
                            }

                            $stream->add($value, Scope::TagAttributeValue);

                            if (null !== $currentAttribute) {
                                $attributes[$currentAttribute] = trim($value, "'\"");
                            }
                        }
                    }
                }

                // Closing >
                if ($position < $length && '>' === $code[$position]) {
                    $stream->add('>', Scope::TagName);
                    ++$position;
                }

                $embeddedLanguage = null;
                if (!$isClosingTag && '' !== $normalizedTagName) {
                    $embeddedLanguage = $this->resolveEmbeddedLanguage($normalizedTagName, $attributes, $context);
                }

                if (null !== $embeddedLanguage) {
                    $embeddedContent = $this->extractEmbeddedContent($code, $position, $normalizedTagName);
                    if ('' !== $embeddedContent) {
                        if ($context->supportsEmbedding()) {
                            $embeddedStream = $context->parseEmbedded($embeddedLanguage, $embeddedContent);
                            $stream->appendStream($embeddedStream);
                        } else {
                            $stream->add($embeddedContent, Scope::MarkupText);
                        }
                    }

                    continue;
                }

                continue;
            }

            // Text content
            $text = '';
            while ($position < $length && '<' !== $code[$position]) {
                $text .= $code[$position];
                ++$position;
            }

            if ('' !== $text) {
                // Split into whitespace and text
                if (preg_match('/^\s+$/', $text)) {
                    $stream->add($text, Scope::Whitespace);
                } else {
                    $stream->add($text, Scope::MarkupText);
                }
            }
        }

        return $stream->build();
    }

    /**
     * @param array<string, string|true> $attributes
     */
    private function resolveEmbeddedLanguage(string $tagName, array $attributes, EmbeddedLanguageContext $context): ?string
    {
        $plan = $context->getPlan();
        if (null !== $plan) {
            $trigger = $plan->findTagTrigger($tagName, $attributes);
            if (null !== $trigger) {
                return $trigger->targetLanguage;
            }
        }

        return null;
    }

    private function extractEmbeddedContent(string $code, int &$position, string $tagName): string
    {
        $length = strlen($code);
        $closingSequence = '</' . $tagName;
        $currentPos = $position;

        while ($currentPos < $length) {
            if (0 === strcasecmp(substr($code, $currentPos, strlen($closingSequence)), $closingSequence)) {
                $isInsideString = false;
                $quoteChar = null;
                $tempPos = $position;
                while ($tempPos < $currentPos) {
                    $char = $code[$tempPos];
                    if (!$isInsideString) {
                        if ('"' === $char || "'" === $char) {
                            $isInsideString = true;
                            $quoteChar = $char;
                        }
                    } else {
                        if ($char === $quoteChar) {
                            $escaped = false;
                            $checkPos = $tempPos - 1;
                            while ($checkPos >= $position && '\\' === $code[$checkPos]) {
                                $escaped = !$escaped;
                                --$checkPos;
                            }
                            if (!$escaped) {
                                $isInsideString = false;
                                $quoteChar = null;
                            }
                        }
                    }
                    ++$tempPos;
                }

                if (!$isInsideString) {
                    $content = substr($code, $position, $currentPos - $position);
                    $position = $currentPos;

                    return $content;
                }
            }
            ++$currentPos;
        }

        $content = substr($code, $position);
        $position = $length;

        return $content;
    }
}
