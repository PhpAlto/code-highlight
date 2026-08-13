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

namespace Alto\Code\Highlight\Tests\Unit\Theme;

use Alto\Code\Highlight\Scope;
use Alto\Code\Highlight\Tests\TestCase;
use Alto\Code\Highlight\ThemeInterface;
use PHPUnit\Framework\Attributes\DataProvider;

/**
 * Reusable assertions shared by all theme tests.
 */
abstract class ThemeTestCase extends TestCase
{
    /**
     * @return iterable<string, array{ThemeInterface, string, bool, array<string, string>}>
     */
    abstract protected static function themeExpectations(): iterable;

    #[DataProvider('themeExpectations')]
    final public function testThemeMetadataMatchesExpectations(
        ThemeInterface $theme,
        string $expectedName,
        bool $isDark,
        array $expectedCssClasses,
    ): void {
        self::assertInstanceOf(ThemeInterface::class, $theme);
        self::assertSame($expectedName, $theme->getName());
        self::assertSame($isDark, $theme->isDark());
        self::assertSame($expectedCssClasses, $theme->getCssClasses());
    }

    #[DataProvider('themeExpectations')]
    final public function testStylesheetContainsAllCssClasses(
        ThemeInterface $theme,
        string $expectedName,
        bool $isDark,
        array $expectedCssClasses,
    ): void {
        $stylesheet = $theme->getStylesheet();

        self::assertNotEmpty(trim($stylesheet));
        self::assertStringContainsString('.alto-highlight', $stylesheet);

        foreach (array_unique(array_values($expectedCssClasses)) as $className) {
            self::assertStringContainsString('.' . $className, $stylesheet);
        }
    }

    /**
     * @return list<array{Scope, string, string}>
     */
    final protected static function canonicalScopeTokens(): array
    {
        return [
            [Scope::Comment, 'comment', 'comment'],
            [Scope::CommentDocblock, 'comment-doc', 'comment-doc'],
            [Scope::CommentTask, 'comment-task', 'comment'],
            [Scope::Keyword, 'keyword', 'keyword'],
            [Scope::KeywordDeclaration, 'keyword-declaration', 'keyword-declaration'],
            [Scope::KeywordOperator, 'keyword-operator', 'keyword-operator'],
            [Scope::KeywordControl, 'keyword-control', 'keyword-control'],
            [Scope::StorageModifier, 'storage-modifier', 'keyword'],
            [Scope::Operator, 'operator', 'operator'],
            [Scope::Punctuation, 'punctuation', 'punctuation'],
            [Scope::String, 'string', 'string'],
            [Scope::StringInterpolated, 'string-interpolated', 'string-interpolated'],
            [Scope::StringTemplateExpression, 'string-template-expression', 'string'],
            [Scope::Number, 'number', 'number'],
            [Scope::Boolean, 'boolean', 'boolean'],
            [Scope::Null, 'null', 'null'],
            [Scope::RegExp, 'regexp', 'constant'],
            [Scope::Constant, 'constant', 'constant'],
            [Scope::BuiltInConstant, 'constant-builtin', 'constant-magic'],
            [Scope::EnumCase, 'enum-case', 'enum-case'],
            [Scope::Variable, 'variable', 'variable'],
            [Scope::VariableParameter, 'variable-parameter', 'variable-parameter'],
            [Scope::VariableProperty, 'variable-property', 'variable-property'],
            [Scope::VariableThis, 'variable-this', 'variable-this'],
            [Scope::Namespace, 'namespace', 'class-definition'],
            [Scope::TypeDefinition, 'type-definition', 'class-definition'],
            [Scope::TypeReference, 'type-reference', 'class-name'],
            [Scope::BuiltInType, 'builtin-type', 'typehint'],
            [Scope::FunctionDefinition, 'function-definition', 'function-definition'],
            [Scope::FunctionCall, 'function-call', 'function-call'],
            [Scope::FunctionBuiltin, 'function-builtin', 'function-call'],
            [Scope::AttributeName, 'attribute', 'attribute'],
            [Scope::AttributeValue, 'attribute-value', 'string'],
            [Scope::TagName, 'tag-name', 'keyword'],
            [Scope::TagAttributeName, 'tag-attribute-name', 'variable'],
            [Scope::TagAttributeValue, 'tag-attribute-value', 'string'],
            [Scope::MarkupText, 'markup-text', 'punctuation'],
            [Scope::SectionName, 'section-name', 'type'],
            [Scope::DiffAdded, 'diff-added', 'string'],
            [Scope::DiffRemoved, 'diff-removed', 'constant'],
            [Scope::DiffChanged, 'diff-changed', 'number'],
            [Scope::Meta, 'meta', 'constant-class'],
            [Scope::DiagnosticError, 'diagnostic-error', 'constant'],
            [Scope::DiagnosticWarning, 'diagnostic-warning', 'constant'],
            [Scope::DiagnosticInfo, 'diagnostic-info', 'constant'],
            [Scope::SupportType, 'support-type', 'class-name'],
            [Scope::SupportFunction, 'support-function', 'function-call'],
            [Scope::SupportConstant, 'support-constant', 'constant'],
            [Scope::Whitespace, 'whitespace', 'punctuation'],
        ];
    }

    /**
     * @param array<string, string> $overrides
     *
     * @return array<string, string>
     */
    final protected static function prefixedExpectedClasses(string $prefix, array $overrides = []): array
    {
        $classes = [];
        foreach (self::canonicalScopeTokens() as [$scope, $key, $default]) {
            $suffix = $overrides[$key] ?? $default;
            $classes[$scope->value] = '' === $suffix ? '' : $prefix . $suffix;
        }

        return $classes;
    }
}
