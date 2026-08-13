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

namespace Alto\Code\Highlight\Tests\Unit\Exception;

use Alto\Code\Highlight\Exception\LanguageNotFoundException;
use Alto\Code\Highlight\Exception\ParseException;
use Alto\Code\Highlight\Tests\TestCase;
use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(LanguageNotFoundException::class)]
#[CoversClass(ParseException::class)]
final class ExceptionTest extends TestCase
{
    public function testLanguageNotFoundExceptionHasCorrectMessage(): void
    {
        $exception = new LanguageNotFoundException('ruby');

        $this->assertSame(
            'Language "ruby" is not supported or could not be found.',
            $exception->getMessage(),
        );
    }

    public function testLanguageNotFoundExceptionWithDifferentLanguages(): void
    {
        $languages = ['python', 'javascript', 'go', 'rust'];

        foreach ($languages as $lang) {
            $exception = new LanguageNotFoundException($lang);
            $this->assertStringContainsString($lang, $exception->getMessage());
        }
    }

    public function testParseExceptionWithoutLineNumber(): void
    {
        $exception = new ParseException('Unexpected token');

        $this->assertSame('Parse error: Unexpected token', $exception->getMessage());
    }

    public function testParseExceptionWithLineNumber(): void
    {
        $exception = new ParseException('Unexpected token', 42);

        $this->assertSame('Parse error at line 42: Unexpected token', $exception->getMessage());
    }

    public function testParseExceptionWithLineZeroShowsNoLine(): void
    {
        $exception = new ParseException('Invalid syntax', 0);

        $this->assertSame('Parse error: Invalid syntax', $exception->getMessage());
    }

    public function testExceptionsExtendBaseException(): void
    {
        $this->assertInstanceOf(\Exception::class, new LanguageNotFoundException('test'));
        $this->assertInstanceOf(\Exception::class, new ParseException('test'));
    }
}
