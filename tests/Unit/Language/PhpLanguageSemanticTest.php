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

namespace Alto\Code\Highlight\Tests\Unit\Language;

use Alto\Code\Highlight\Language\LanguageInterface;
use Alto\Code\Highlight\Language\PhpLanguage;
use Alto\Code\Highlight\Scope;
use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(PhpLanguage::class)]
final class PhpLanguageSemanticTest extends SemanticLanguageTestCase
{
    protected function createLanguage(): LanguageInterface
    {
        return new PhpLanguage();
    }

    public function testNamespaceTypeDefinitionAndBuiltinConstantTokens(): void
    {
        $code = <<<'PHP'
        <?php
        namespace App\Demo;
        
        class Runner
        {
            function boot(): void
            {
                $path = __DIR__;
            }
        }
        PHP;

        $this->assertSemanticTokens(
            $code,
            [
                ["<?php\n", Scope::Punctuation],
                ['namespace', Scope::KeywordDeclaration],
                ['App\\Demo', Scope::Namespace],
                [';', Scope::Punctuation],
                ['class', Scope::KeywordDeclaration],
                ['Runner', Scope::TypeDefinition],
                ['{', Scope::Punctuation],
                ['function', Scope::KeywordDeclaration],
                ['boot', Scope::FunctionDefinition],
                ['(', Scope::Punctuation],
                [')', Scope::Punctuation],
                [':', Scope::Punctuation],
                ['void', Scope::Constant],
                ['{', Scope::Punctuation],
                ['$path', Scope::Variable],
                ['=', Scope::Operator],
                ['__DIR__', Scope::BuiltInConstant],
                [';', Scope::Punctuation],
                ['}', Scope::Punctuation],
                ['}', Scope::Punctuation],
            ],
        );
    }
}
