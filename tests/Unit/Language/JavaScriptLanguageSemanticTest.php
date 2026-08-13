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

namespace Alto\Code\Highlight\Tests\Unit\Language;

use Alto\Code\Highlight\Language\JavaScriptLanguage;
use Alto\Code\Highlight\Language\LanguageInterface;
use Alto\Code\Highlight\Scope;
use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(JavaScriptLanguage::class)]
final class JavaScriptLanguageSemanticTest extends SemanticLanguageTestCase
{
    protected function createLanguage(): LanguageInterface
    {
        return new JavaScriptLanguage();
    }

    public function testFunctionsTemplateLiteralsAndNullishCoalescing(): void
    {
        $this->assertSemanticTokens(
            'function greet(name) { const message = `Hi ${name}`; return message ?? "friend"; }',
            [
                ['function', Scope::KeywordDeclaration],  // Declaration keyword
                ['greet', Scope::FunctionDefinition],    // Function being defined
                ['(', Scope::Punctuation],
                ['name', Scope::Variable],
                [')', Scope::Punctuation],
                ['{', Scope::Punctuation],
                ['const', Scope::KeywordDeclaration],    // Declaration keyword
                ['message', Scope::Variable],
                ['=', Scope::Operator],
                ['`Hi ', Scope::String],
                ['${name}', Scope::StringTemplateExpression],
                ['`', Scope::String],
                [';', Scope::Punctuation],
                ['return', Scope::KeywordControl],       // Control flow keyword
                ['message', Scope::Variable],
                ['??', Scope::Operator],
                ['"friend"', Scope::String],
                [';', Scope::Punctuation],
                ['}', Scope::Punctuation],
            ],
        );
    }

    public function testRegexBooleanAndNullLiterals(): void
    {
        $this->assertSemanticTokens(
            'const match = /foo\\d+/gi; let fallback = null; const flag = true;',
            [
                ['const', Scope::KeywordDeclaration],  // Declaration keyword
                ['match', Scope::Variable],
                ['=', Scope::Operator],
                ['/foo\\d+/gi', Scope::RegExp],
                [';', Scope::Punctuation],
                ['let', Scope::KeywordDeclaration],    // Declaration keyword
                ['fallback', Scope::Variable],
                ['=', Scope::Operator],
                ['null', Scope::Null],
                [';', Scope::Punctuation],
                ['const', Scope::KeywordDeclaration],  // Declaration keyword
                ['flag', Scope::Variable],
                ['=', Scope::Operator],
                ['true', Scope::Boolean],
                [';', Scope::Punctuation],
            ],
        );
    }
}
