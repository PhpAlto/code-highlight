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

use Alto\Code\Highlight\Language\PythonLanguage;
use Alto\Code\Highlight\Parser\ParsedStream;
use Alto\Code\Highlight\Scope;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(PythonLanguage::class)]
final class PythonLanguageTest extends TestCase
{
    private PythonLanguage $language;

    protected function setUp(): void
    {
        $this->language = new PythonLanguage();
    }

    public function testGetIdentifierReturnsPython(): void
    {
        $this->assertSame('python', $this->language->getIdentifier());
    }

    public function testParseReturnsStream(): void
    {
        $result = $this->language->parse('x = 1');

        $this->assertInstanceOf(ParsedStream::class, $result);
    }

    public function testParseEmptyCode(): void
    {
        $result = $this->language->parse('');

        $this->assertInstanceOf(ParsedStream::class, $result);
        $this->assertCount(0, $result);
    }

    public function testParseComment(): void
    {
        $result = $this->language->parse('# This is a comment');
        $tokens = iterator_to_array($result);

        $this->assertNotEmpty($tokens);
        $this->assertSame(Scope::Comment, $tokens[0]->scope);
        $this->assertSame('# This is a comment', $tokens[0]->text);
    }

    public function testParseCommentAfterWhitespace(): void
    {
        $result = $this->language->parse('   # comment after space');
        $tokens = iterator_to_array($result);

        $commentToken = null;
        foreach ($tokens as $token) {
            if (Scope::Comment === $token->scope) {
                $commentToken = $token;
                break;
            }
        }

        $this->assertNotNull($commentToken);
        $this->assertStringContainsString('# comment after space', $commentToken->text);
    }

    public function testParseWhitespace(): void
    {
        $result = $this->language->parse('   ');
        $tokens = iterator_to_array($result);

        $this->assertCount(1, $tokens);
        $this->assertSame(Scope::Whitespace, $tokens[0]->scope);
    }

    public function testParseMixedContentWithWhitespace(): void
    {
        $result = $this->language->parse("x\n  \ny");
        $tokens = iterator_to_array($result);

        $whitespaceFound = false;
        foreach ($tokens as $token) {
            if (Scope::Whitespace === $token->scope) {
                $whitespaceFound = true;
                break;
            }
        }

        $this->assertTrue($whitespaceFound);
    }

    public function testParseMultipleLines(): void
    {
        $code = <<<PYTHON
# First comment
x = 1
# Second comment
PYTHON;

        $result = $this->language->parse($code);
        $tokens = iterator_to_array($result);

        $commentCount = 0;
        foreach ($tokens as $token) {
            if (Scope::Comment === $token->scope) {
                ++$commentCount;
            }
        }

        $this->assertSame(2, $commentCount);
    }

    public function testParsePunctuationFallback(): void
    {
        $result = $this->language->parse('.');
        $tokens = iterator_to_array($result);

        $this->assertCount(1, $tokens);
        $this->assertSame(Scope::Punctuation, $tokens[0]->scope);
    }

    public function testParseCodePreservesContent(): void
    {
        $code = 'x = 1';
        $result = $this->language->parse($code);

        $reconstructed = '';
        foreach ($result as $token) {
            $reconstructed .= $token->text;
        }

        $this->assertSame($code, $reconstructed);
    }

    public function testCommentNotRecognizedWithoutWhitespace(): void
    {
        // Hash without preceding whitespace (not at position 0) should not be treated as comment
        $result = $this->language->parse('x#y');
        $tokens = iterator_to_array($result);

        // None should be a comment because # is preceded by non-whitespace
        foreach ($tokens as $token) {
            $this->assertNotSame(Scope::Comment, $token->scope);
        }
    }
}
