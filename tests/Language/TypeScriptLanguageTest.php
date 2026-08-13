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

namespace Alto\Code\Highlight\Tests\Language;

use Alto\Code\Highlight\Language\LanguageInterface;
use Alto\Code\Highlight\Language\TypeScriptLanguage;
use Alto\Code\Highlight\Parser\ParsedToken;
use Alto\Code\Highlight\Scope;
use Alto\Code\Highlight\Tests\Unit\Language\SemanticLanguageTestCase;
use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(TypeScriptLanguage::class)]
final class TypeScriptLanguageTest extends SemanticLanguageTestCase
{
    protected function createLanguage(): LanguageInterface
    {
        return new TypeScriptLanguage();
    }

    public function testInterfacesNamespacesAndDecoratorsEmitSemanticTokens(): void
    {
        $code = <<<'TS'
        interface User<T extends Model> {
            readonly id: number;
        }
        
        namespace App {
            @Injectable()
            export class Store implements Registry {}
        }
        TS;

        $this->assertSemanticTokens(
            $code,
            [
                ['interface', Scope::KeywordDeclaration],
                ['User', Scope::TypeDefinition],
                ['<', Scope::Operator],
                ['T', Scope::TypeReference],
                ['extends', Scope::Keyword],
                ['Model', Scope::TypeReference],
                ['>', Scope::Operator],
                ['{', Scope::Punctuation],
                ['readonly', Scope::Keyword],
                ['id', Scope::Variable],
                [':', Scope::Punctuation],
                ['number', Scope::BuiltInType],
                [';', Scope::Punctuation],
                ['}', Scope::Punctuation],
                ['namespace', Scope::KeywordDeclaration],
                ['App', Scope::Namespace],
                ['{', Scope::Punctuation],
                ['@Injectable', Scope::AttributeName],
                ['(', Scope::Punctuation],
                [')', Scope::Punctuation],
                ['export', Scope::Keyword],
                ['class', Scope::KeywordDeclaration],
                ['Store', Scope::TypeDefinition],
                ['implements', Scope::Keyword],
                ['Registry', Scope::TypeReference],
                ['{', Scope::Punctuation],
                ['}', Scope::Punctuation],
                ['}', Scope::Punctuation],
            ],
        );
    }

    public function testTypeAliasesAndTypeAssertions(): void
    {
        $code = <<<'TS'
        type Result<T> = T | null;
        const value = <string>getValue();
        TS;

        $this->assertSemanticTokens(
            $code,
            [
                ['type', Scope::KeywordDeclaration],
                ['Result', Scope::TypeDefinition],
                ['<', Scope::Operator],
                ['T', Scope::TypeReference],
                ['>', Scope::Operator],
                ['=', Scope::Operator],
                ['T', Scope::TypeReference],
                ['|', Scope::Operator],
                ['null', Scope::TypeReference],
                [';', Scope::Punctuation],
                ['const', Scope::Keyword],
                ['value', Scope::Variable],
                ['=', Scope::Operator],
                ['<string>', Scope::Meta],
                ['getValue', Scope::FunctionCall],
                ['(', Scope::Punctuation],
                [')', Scope::Punctuation],
                [';', Scope::Punctuation],
            ],
        );
    }

    public function testGenericsDoNotTriggerTypeAssertion(): void
    {
        $tokens = $this->tokensFrom('const result = identity<T>(value);');

        self::assertFalse($this->hasScope($tokens, Scope::Meta));

        $typeParameter = $this->findToken($tokens, 'T');
        self::assertNotNull($typeParameter);
        self::assertSame(Scope::TypeReference, $typeParameter->getScope());
    }

    public function testModuleAndNamespaceIdentifiersReceiveNamespaceScope(): void
    {
        $tokens = $this->tokensFrom('module App { namespace Core {} }');

        $moduleIdentifier = $this->findToken($tokens, 'App');
        $namespaceIdentifier = $this->findToken($tokens, 'Core');

        self::assertNotNull($moduleIdentifier);
        self::assertSame(Scope::Namespace, $moduleIdentifier->getScope());
        self::assertNotNull($namespaceIdentifier);
        self::assertSame(Scope::Namespace, $namespaceIdentifier->getScope());
    }

    public function testTypeAnnotationStateResetsBetweenStatements(): void
    {
        $tokens = $this->tokensFrom('let first: string; let second = first;');

        $builtIn = $this->findToken($tokens, 'string');
        $secondIdentifier = $this->findToken($tokens, 'second');

        self::assertNotNull($builtIn);
        self::assertSame(Scope::BuiltInType, $builtIn->getScope());
        self::assertNotNull($secondIdentifier);
        self::assertSame(Scope::Variable, $secondIdentifier->getScope());
    }

    public function testHeritageClausesMarkTypeReferences(): void
    {
        $tokens = $this->tokensFrom('class Store extends Base implements Repository {}');

        $extendsKeyword = $this->findToken($tokens, 'extends');
        $implementsKeyword = $this->findToken($tokens, 'implements');
        $baseType = $this->findToken($tokens, 'Base');
        $repositoryType = $this->findToken($tokens, 'Repository');

        self::assertNotNull($extendsKeyword);
        self::assertSame(Scope::Keyword, $extendsKeyword->getScope());
        self::assertNotNull($implementsKeyword);
        self::assertSame(Scope::Keyword, $implementsKeyword->getScope());
        self::assertNotNull($baseType);
        self::assertSame(Scope::TypeReference, $baseType->getScope());
        self::assertNotNull($repositoryType);
        self::assertSame(Scope::TypeReference, $repositoryType->getScope());
    }

    public function testAsKeywordMarksTypeAnnotationsForBuiltInTypes(): void
    {
        $tokens = $this->tokensFrom('const amount = value as number;');

        $asKeyword = $this->findToken($tokens, 'as');
        $numberType = $this->findToken($tokens, 'number');

        self::assertNotNull($asKeyword);
        self::assertSame(Scope::Keyword, $asKeyword->getScope());
        self::assertNotNull($numberType);
        self::assertSame(Scope::BuiltInType, $numberType->getScope());
    }

    /**
     * @return list<ParsedToken>
     */
    private function tokensFrom(string $code): array
    {
        return $this->parseCode($code)->getTokens();
    }

    /**
     * @param list<ParsedToken> $tokens
     */
    private function findToken(array $tokens, string $text, int $occurrence = 1): ?ParsedToken
    {
        $count = 0;
        foreach ($tokens as $token) {
            if ($token->getText() !== $text) {
                continue;
            }

            ++$count;
            if ($count === $occurrence) {
                return $token;
            }
        }

        return null;
    }

    /**
     * @param list<ParsedToken> $tokens
     */
    private function hasScope(array $tokens, Scope $scope): bool
    {
        foreach ($tokens as $token) {
            if ($token->getScope() === $scope) {
                return true;
            }
        }

        return false;
    }
}
