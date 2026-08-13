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

use Alto\Code\Highlight\Language\BashLanguage;
use Alto\Code\Highlight\Parser\ParsedStream;
use Alto\Code\Highlight\Tests\LanguageTestCase;
use PHPUnit\Framework\Attributes\CoversClass;

/**
 * Test cases for Bash language highlighting.
 *
 * Fixtures are auto-discovered from tests/Language/bash/ directory.
 * Naming convention:
 *   - Code file: *.sh
 *   - Expected HTML: *.sh.html
 *   - Example: basic.sh → basic.sh.html
 */
#[CoversClass(BashLanguage::class)]
class BashLanguageTest extends LanguageTestCase
{
    protected string $language = 'bash';
    protected string $languageClass = BashLanguage::class;

    protected function getLanguageIdentifier(): string
    {
        return 'bash';
    }

    protected function getFileExtensions(): array
    {
        return ['sh'];
    }

    public function testIdentifier(): void
    {
        $language = new BashLanguage();
        $this->assertSame('bash', $language->getIdentifier());
    }

    public function testParseDirectly(): void
    {
        $language = new BashLanguage();
        $stream = $language->parse('echo "hello"');
        $this->assertInstanceOf(ParsedStream::class, $stream);
    }

    public function testPrivateMethods(): void
    {
        $language = new BashLanguage();
        $reflection = new \ReflectionClass($language);

        // parseDoubleQuotedString
        $method = $reflection->getMethod('parseDoubleQuotedString');
        $code = '"test"';
        $pos = 0;
        $result = $method->invokeArgs($language, [$code, &$pos]);
        $this->assertSame('"test"', $result);

        $code = '"test \" escaped"';
        $pos = 0;
        $result = $method->invokeArgs($language, [$code, &$pos]);
        $this->assertSame('"test \" escaped"', $result);

        // parseSingleQuotedString
        $method = $reflection->getMethod('parseSingleQuotedString');
        $code = "'test'";
        $pos = 0;
        $result = $method->invokeArgs($language, [$code, &$pos]);
        $this->assertSame("'test'", $result);

        // parseVariable
        $method = $reflection->getMethod('parseVariable');
        $code = '$var';
        $pos = 0;
        $result = $method->invokeArgs($language, [$code, &$pos]);
        $this->assertSame('$var', $result);

        $code = '${var}';
        $pos = 0;
        $result = $method->invokeArgs($language, [$code, &$pos]);
        $this->assertSame('${var}', $result);

        // parseArithmetic
        $method = $reflection->getMethod('parseArithmetic');
        $code = '$((1+1))';
        $pos = 0;
        $result = $method->invokeArgs($language, [$code, &$pos]);
        $this->assertSame('$((1+1))', $result);

        // parseHereDoc
        $method = $reflection->getMethod('parseHereDoc');
        $code = "<<EOF\ncontent\nEOF";
        $pos = 0;
        $result = $method->invokeArgs($language, [$code, &$pos]);
        $this->assertSame("<<EOF\ncontent\nEOF", $result);

        $code = "<<-EOF\n\tcontent\nEOF";
        $pos = 0;
        $result = $method->invokeArgs($language, [$code, &$pos]);
        $this->assertSame("<<-EOF\n\tcontent\nEOF", $result);

        $code = "<< EOF \ncontent\nEOF";
        $pos = 0;
        $result = $method->invokeArgs($language, [$code, &$pos]);
        $this->assertSame("<< EOF \ncontent\nEOF", $result);

        // parseCommandSubstitution
        $method = $reflection->getMethod('parseCommandSubstitution');
        $code = '`ls`';
        $pos = 0;
        $result = $method->invokeArgs($language, [$code, &$pos]);
        $this->assertSame('`ls`', $result);

        $code = '$(ls)';
        $pos = 0;
        $result = $method->invokeArgs($language, [$code, &$pos]);
        $this->assertSame('$(ls)', $result);

        $code = '$(echo $(ls))';
        $pos = 0;
        $result = $method->invokeArgs($language, [$code, &$pos]);
        $this->assertSame('$(echo $(ls))', $result);
    }
}
