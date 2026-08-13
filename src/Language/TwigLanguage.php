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
 * Twig language parser.
 *
 * Provides a lightweight, semantic tokenizer for Twig templates, including:
 *  - Expressions: {{ ... }}
 *  - Tags: {% ... %}
 *  - Comments: {# ... #}
 *
 * It does not aim to be a full Twig parser, but to capture enough structure for
 * high-quality syntax highlighting.
 *
 * @author Simon André <smn.andre@gmail.com>
 */
final class TwigLanguage implements EmbeddedLanguageCapable
{
    public function getIdentifier(): string
    {
        return 'twig';
    }

    public function parse(string $code): ParsedStream
    {
        return $this->parseWithEmbedding($code, EmbeddedLanguageContext::disabled());
    }

    public function parseWithEmbedding(string $code, EmbeddedLanguageContext $context): ParsedStream
    {
        $stream = new StreamBuilder();
        $length = strlen($code);
        $position = 0;
        $blockStack = [];

        while ($position < $length) {
            if ('{#' === substr($code, $position, 2)) {
                $end = strpos($code, '#}', $position + 2);
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

            if ('{{' === substr($code, $position, 2)) {
                $end = strpos($code, '}}', $position + 2);
                if (false === $end) {
                    $end = $length;
                }

                $inner = substr($code, $position + 2, $end - ($position + 2));
                $stream->add('{{', Scope::Punctuation);
                $this->tokenizeTwigInner($inner, $stream);
                $stream->add('}}', Scope::Punctuation);

                $position = ($end === $length) ? $length : $end + 2;

                continue;
            }

            if ('{%' === substr($code, $position, 2)) {
                $end = strpos($code, '%}', $position + 2);
                if (false === $end) {
                    $end = $length;
                }

                $inner = substr($code, $position + 2, $end - ($position + 2));

                // Track block stack
                if (preg_match('/^\s*block\s+([a-zA-Z0-9_]+)/', $inner, $matches)) {
                    $blockStack[] = $matches[1];
                } elseif (preg_match('/^\s*endblock\b/', $inner)) {
                    array_pop($blockStack);
                }

                $stream->add('{%', Scope::Punctuation);
                $this->tokenizeTwigInner($inner, $stream, isTag: true);
                $stream->add('%}', Scope::Punctuation);

                $position = ($end === $length) ? $length : $end + 2;

                continue;
            }

            $text = '';
            while (
                $position < $length
                && '{{' !== substr($code, $position, 2)
                && '{%' !== substr($code, $position, 2)
                && '{#' !== substr($code, $position, 2)
            ) {
                $text .= $code[$position];
                ++$position;
            }

            if ('' !== $text) {
                $targetLang = 'html';

                // Check if we are inside a mapped block
                if (!empty($blockStack) && $context->getPlan()) {
                    $currentBlock = end($blockStack);
                    $trigger = $context->getPlan()->findBlockTrigger($currentBlock);
                    if ($trigger) {
                        $targetLang = $trigger->targetLanguage;
                    }
                }

                if ($context->supportsEmbedding()) {
                    // If target is html, and we are already in HTML (default), this is fine.
                    // But if we are in a JS block, we want JS.
                    $embeddedStream = $context->parseEmbedded($targetLang, $text);
                    $stream->appendStream($embeddedStream);
                } elseif (preg_match('/^\s+$/', $text)) {
                    $stream->add($text, Scope::Whitespace);
                } else {
                    $stream->add($text, Scope::MarkupText);
                }
            }
        }

        return $stream->build();
    }

    /**
     * Tokenize inner Twig content (inside {{ }} or {% %}).
     */
    private function tokenizeTwigInner(string $inner, StreamBuilder $stream, bool $isTag = false): void
    {
        $length = strlen($inner);
        $position = 0;

        $keywords = [
            'if',
            'else',
            'elseif',
            'endif',
            'for',
            'endfor',
            'in',
            'set',
            'block',
            'endblock',
            'extends',
            'include',
            'with',
            'without',
            'only',
            'as',
            'true',
            'false',
            'null',
        ];

        while ($position < $length) {
            $char = $inner[$position];

            // Whitespace
            if (preg_match('/\s/', $char)) {
                $ws = '';
                while ($position < $length && preg_match('/\s/', $inner[$position])) {
                    $ws .= $inner[$position];
                    ++$position;
                }
                $stream->add($ws, Scope::Whitespace);

                continue;
            }

            // Strings
            if ('"' === $char || "'" === $char) {
                $quote = $char;
                $value = $quote;
                ++$position;

                while ($position < $length && $inner[$position] !== $quote) {
                    $value .= $inner[$position];
                    ++$position;
                }

                if ($position < $length) {
                    $value .= $inner[$position];
                    ++$position;
                }

                $stream->add($value, Scope::String);

                continue;
            }

            // Numbers
            if (preg_match('/[0-9]/', $char)) {
                $number = '';
                while ($position < $length && preg_match('/[0-9_]/', $inner[$position])) {
                    $number .= $inner[$position];
                    ++$position;
                }
                $stream->add($number, Scope::Number);

                continue;
            }

            // Identifiers / keywords
            if (preg_match('/[A-Za-z_]/', $char)) {
                $identifier = '';
                while ($position < $length && preg_match('/[A-Za-z0-9_]/', $inner[$position])) {
                    $identifier .= $inner[$position];
                    ++$position;
                }

                $lower = strtolower($identifier);

                if (in_array($lower, ['true', 'false'], true)) {
                    $stream->add($identifier, Scope::Boolean);
                } elseif ('null' === $lower) {
                    $stream->add($identifier, Scope::Null);
                } elseif (in_array($lower, $keywords, true)) {
                    $stream->add($identifier, Scope::KeywordControl);
                } else {
                    // Treat first identifier in a tag as a keyword-ish token (e.g. "if", "for", "set")
                    $stream->add($identifier, Scope::Variable);
                }

                continue;
            }

            // Operators and punctuation
            if (str_contains('=+-/*%()[]{}.,:|>?<!', $char)) {
                $stream->add($char, Scope::Operator);
                ++$position;

                continue;
            }

            // Fallback
            $stream->add($char, Scope::Punctuation);
            ++$position;
        }
    }
}
