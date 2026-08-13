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
use Alto\Code\Highlight\Parser\ParsedToken;
use Alto\Code\Highlight\Scope;

/**
 * SCSS language parser.
 *
 * Extends CSS with SCSS-specific features like variables, nesting, mixins, etc.
 *
 * @author Simon André <smn.andre@gmail.com>
 */
final class ScssLanguage extends CssLanguage
{
    public function getIdentifier(): string
    {
        return 'scss';
    }

    public function parse(string $code): ParsedStream
    {
        $tokens = [];
        $position = 0;
        $length = strlen($code);

        while ($position < $length) {
            $char = $code[$position];

            // Skip whitespace
            if (preg_match('/\s/', $char)) {
                $ws = '';
                while ($position < $length && preg_match('/\s/', $code[$position])) {
                    $ws .= $code[$position];
                    ++$position;
                }
                $tokens[] = new ParsedToken($ws, Scope::Whitespace);

                continue;
            }

            // Comments (both // and /* */)
            if ('/' === $char) {
                if ($position + 1 < $length && '/' === $code[$position + 1]) {
                    // Single-line comment
                    $comment = '';
                    while ($position < $length && "\n" !== $code[$position]) {
                        $comment .= $code[$position];
                        ++$position;
                    }
                    $tokens[] = new ParsedToken($comment, Scope::Comment);

                    continue;
                } elseif ($position + 1 < $length && '*' === $code[$position + 1]) {
                    $comment = $this->parseComment($code, $position);
                    $tokens[] = new ParsedToken($comment, Scope::Comment);
                    $position += strlen($comment);

                    continue;
                }
            }

            // Variables: $variable-name
            if ('$' === $char) {
                $variable = $this->parseScssVariable($code, $position);
                $tokens[] = new ParsedToken($variable, Scope::Variable);
                $position += strlen($variable);

                continue;
            }

            // Interpolation: #{$variable}
            if ($position + 1 < $length && '#' === $char && '{' === $code[$position + 1]) {
                $interpolation = $this->parseInterpolation($code, $position);
                $tokens[] = new ParsedToken($interpolation, Scope::StringTemplateExpression);
                $position += strlen($interpolation);

                continue;
            }

            // Placeholder: %placeholder
            if ('%' === $char) {
                $placeholder = $this->parsePlaceholder($code, $position);
                $tokens[] = new ParsedToken($placeholder, Scope::TagName);
                $position += strlen($placeholder);

                continue;
            }

            // Nesting indicator: &
            if ('&' === $char) {
                $tokens[] = new ParsedToken($char, Scope::Operator);
                ++$position;

                continue;
            }

            // @mixin, @include, @extend, @function, etc.
            if ('@' === $char) {
                $atRule = $this->parseScssAtRule($code, $position);
                $scope = $this->determineScssAtRuleScope($atRule);
                $tokens[] = new ParsedToken($atRule, $scope);
                $position += strlen($atRule);

                continue;
            }

            // Fallback to regular CSS parsing
            $tokens[] = new ParsedToken($char, Scope::Punctuation);
            ++$position;
        }

        return new ParsedStream($tokens);
    }

    private function parseScssVariable(string $code, int $position): string
    {
        $variable = '$';
        ++$position;
        $length = strlen($code);

        while ($position < $length && preg_match('/[a-zA-Z0-9_-]/', $code[$position])) {
            $variable .= $code[$position];
            ++$position;
        }

        return $variable;
    }

    private function parseInterpolation(string $code, int $position): string
    {
        $interpolation = '#{';
        $position += 2;
        $length = strlen($code);

        while ($position < $length && '}' !== $code[$position]) {
            $interpolation .= $code[$position];
            ++$position;
        }

        if ($position < $length) {
            $interpolation .= '}';
        }

        return $interpolation;
    }

    private function parsePlaceholder(string $code, int $position): string
    {
        $placeholder = '%';
        ++$position;
        $length = strlen($code);

        while ($position < $length && preg_match('/[a-zA-Z0-9_-]/', $code[$position])) {
            $placeholder .= $code[$position];
            ++$position;
        }

        return $placeholder;
    }

    private function parseScssAtRule(string $code, int $position): string
    {
        $atRule = '@';
        ++$position;
        $length = strlen($code);

        while ($position < $length && preg_match('/[a-zA-Z-]/', $code[$position])) {
            $atRule .= $code[$position];
            ++$position;
        }

        return $atRule;
    }

    private function determineScssAtRuleScope(string $atRule): Scope
    {
        $rule = strtolower($atRule);

        if (in_array($rule, ['@mixin', '@include', '@function', '@return'], true)) {
            return Scope::KeywordDeclaration;
        }

        return Scope::Keyword;
    }
}
