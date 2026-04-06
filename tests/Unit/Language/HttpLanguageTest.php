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

use Alto\Code\Highlight\Language\HttpLanguage;
use Alto\Code\Highlight\Parser\ParsedToken;
use Alto\Code\Highlight\Scope;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(HttpLanguage::class)]
final class HttpLanguageTest extends TestCase
{
    private HttpLanguage $language;

    protected function setUp(): void
    {
        $this->language = new HttpLanguage();
    }

    public function testGetIdentifier(): void
    {
        $this->assertSame('http', $this->language->getIdentifier());
    }

    public function testParseHttpRequest(): void
    {
        $code = "GET /api/users HTTP/1.1\nHost: example.com";
        $tokens = $this->parse($code);

        $this->assertTokenExists($tokens, 'GET', Scope::Keyword);
        $this->assertTokenExists($tokens, '/api/users', Scope::String);
        $this->assertTokenExists($tokens, 'HTTP/1.1', Scope::Constant);
    }

    public function testParseHttpRequestWithAllMethods(): void
    {
        $methods = ['GET', 'POST', 'PUT', 'DELETE', 'PATCH', 'HEAD', 'OPTIONS', 'TRACE', 'CONNECT'];

        foreach ($methods as $method) {
            $code = "{$method} /path HTTP/1.1";
            $tokens = $this->parse($code);

            $this->assertTokenExists($tokens, $method, Scope::Keyword);
        }
    }

    public function testParseHttpRequestWithUnknownMethod(): void
    {
        $code = 'CUSTOM /path HTTP/1.1';
        $tokens = $this->parse($code);

        // Unknown method is treated as String
        $this->assertTokenExists($tokens, 'CUSTOM', Scope::String);
    }

    public function testParseHttpRequestWithoutVersion(): void
    {
        $code = 'GET /path';
        $tokens = $this->parse($code);

        $this->assertTokenExists($tokens, 'GET', Scope::Keyword);
        $this->assertTokenExists($tokens, '/path', Scope::String);
    }

    public function testParseHttpResponse200(): void
    {
        $code = 'HTTP/1.1 200 OK';
        $tokens = $this->parse($code);

        $this->assertTokenExists($tokens, 'HTTP/1.1', Scope::Keyword);
        $this->assertTokenExists($tokens, '200', Scope::Number);
        $this->assertTokenExists($tokens, 'OK', Scope::String);
    }

    public function testParseHttpResponse1xx(): void
    {
        $code = 'HTTP/1.1 100 Continue';
        $tokens = $this->parse($code);

        $this->assertTokenExists($tokens, '100', Scope::Number);
    }

    public function testParseHttpResponse2xx(): void
    {
        $code = 'HTTP/1.1 201 Created';
        $tokens = $this->parse($code);

        $this->assertTokenExists($tokens, '201', Scope::Number);
    }

    public function testParseHttpResponse3xx(): void
    {
        $code = 'HTTP/1.1 301 Moved Permanently';
        $tokens = $this->parse($code);

        $this->assertTokenExists($tokens, '301', Scope::Constant);
    }

    public function testParseHttpResponse4xx(): void
    {
        $code = 'HTTP/1.1 404 Not Found';
        $tokens = $this->parse($code);

        $this->assertTokenExists($tokens, '404', Scope::DiagnosticWarning);
    }

    public function testParseHttpResponse5xx(): void
    {
        $code = 'HTTP/1.1 500 Internal Server Error';
        $tokens = $this->parse($code);

        $this->assertTokenExists($tokens, '500', Scope::DiagnosticError);
    }

    public function testParseHttpResponseWithoutReasonPhrase(): void
    {
        $code = 'HTTP/1.1 200';
        $tokens = $this->parse($code);

        $this->assertTokenExists($tokens, 'HTTP/1.1', Scope::Keyword);
        $this->assertTokenExists($tokens, '200', Scope::Number);
    }

    public function testParseHeaders(): void
    {
        $code = "GET /path HTTP/1.1\nContent-Type: application/json";
        $tokens = $this->parse($code);

        $this->assertTokenExists($tokens, 'Content-Type', Scope::Constant);
        $this->assertTokenExists($tokens, ':', Scope::Punctuation);
    }

    public function testParseHeaderWithLeadingWhitespace(): void
    {
        $code = "GET /path HTTP/1.1\n  X-Custom: value";
        $tokens = $this->parse($code);

        $this->assertTokenExists($tokens, 'X-Custom', Scope::Constant);
    }

    public function testParseHeaderWithWhitespaceAroundColon(): void
    {
        $code = "GET /path HTTP/1.1\nHeader  :  value";
        $tokens = $this->parse($code);

        $this->assertTokenExists($tokens, 'Header', Scope::Constant);
        $this->assertTokenExists($tokens, 'value', Scope::String);
    }

    public function testParseContentTypeHeader(): void
    {
        $code = "GET /path HTTP/1.1\nContent-Type: application/json; charset=utf-8";
        $tokens = $this->parse($code);

        $this->assertTokenExists($tokens, 'application/json', Scope::TypeReference);
        $this->assertTokenExists($tokens, ';', Scope::Punctuation);
        $this->assertTokenExists($tokens, 'charset', Scope::Constant);
        $this->assertTokenExists($tokens, '=', Scope::Operator);
        $this->assertTokenExists($tokens, 'utf-8', Scope::String);
    }

    public function testParseContentTypeWithMultipleParameters(): void
    {
        $code = "GET /path HTTP/1.1\nContent-Type: text/html; charset=utf-8; boundary=something";
        $tokens = $this->parse($code);

        $this->assertTokenExists($tokens, 'text/html', Scope::TypeReference);
    }

    public function testParseContentTypeWithMalformedParameter(): void
    {
        $code = "GET /path HTTP/1.1\nContent-Type: text/html; malformed";
        $tokens = $this->parse($code);

        // Should handle gracefully
        $this->assertNotEmpty($tokens);
    }

    public function testParseAuthorizationHeader(): void
    {
        $code = "GET /path HTTP/1.1\nAuthorization: Bearer token123";
        $tokens = $this->parse($code);

        $this->assertTokenExists($tokens, 'Bearer', Scope::Keyword);
        $this->assertTokenExists($tokens, 'token123', Scope::String);
    }

    public function testParseAuthorizationHeaderBasic(): void
    {
        $code = "GET /path HTTP/1.1\nAuthorization: Basic dXNlcjpwYXNz";
        $tokens = $this->parse($code);

        $this->assertTokenExists($tokens, 'Basic', Scope::Keyword);
    }

    public function testParseAuthorizationHeaderSimple(): void
    {
        $code = "GET /path HTTP/1.1\nAuthorization: simpletoken";
        $tokens = $this->parse($code);

        // Single token without space is treated as string
        $this->assertTokenExists($tokens, 'simpletoken', Scope::String);
    }

    public function testParseFullUrlWithScheme(): void
    {
        $code = 'GET https://example.com:8080/path?query=1#fragment HTTP/1.1';
        $tokens = $this->parse($code);

        $this->assertTokenExists($tokens, 'https://', Scope::Keyword);
        $this->assertTokenExists($tokens, 'example.com', Scope::FunctionCall);
        $this->assertTokenExists($tokens, ':8080', Scope::Number);
        $this->assertTokenExists($tokens, '/path', Scope::String);
        $this->assertTokenExists($tokens, '?query=1', Scope::Variable);
        $this->assertTokenExists($tokens, '#fragment', Scope::Comment);
    }

    public function testParseUrlWithQueryString(): void
    {
        $code = 'GET /api/users?page=1&limit=10 HTTP/1.1';
        $tokens = $this->parse($code);

        $this->assertTokenExists($tokens, '/api/users', Scope::String);
        $this->assertTokenExists($tokens, '?page=1&limit=10', Scope::Variable);
    }

    public function testParseUrlWithFragment(): void
    {
        $code = 'GET /page#section HTTP/1.1';
        $tokens = $this->parse($code);

        $this->assertTokenExists($tokens, '/page', Scope::String);
        $this->assertTokenExists($tokens, '#section', Scope::Comment);
    }

    public function testParseUrlFallback(): void
    {
        $code = 'GET * HTTP/1.1';
        $tokens = $this->parse($code);

        // Asterisk URL falls back to String
        $this->assertTokenExists($tokens, '*', Scope::String);
    }

    public function testParseBody(): void
    {
        $code = "POST /api/users HTTP/1.1\nContent-Type: application/json\n\n{\"name\": \"John\"}";
        $tokens = $this->parse($code);

        $this->assertTokenExists($tokens, '{"name": "John"}', Scope::String);
    }

    public function testParseBodyMultipleLines(): void
    {
        $code = "POST /api HTTP/1.1\n\nline1\nline2";
        $tokens = $this->parse($code);

        $this->assertTokenExists($tokens, 'line1', Scope::String);
        $this->assertTokenExists($tokens, 'line2', Scope::String);
    }

    public function testParseEmptyLineWithWhitespace(): void
    {
        $code = "GET /api HTTP/1.1\n   \nbody";
        $tokens = $this->parse($code);

        // Whitespace-only line marks body start
        $this->assertTokenExists($tokens, 'body', Scope::String);
    }

    public function testParseMultipleHeaders(): void
    {
        $code = "GET /api HTTP/1.1\nHost: example.com\nAccept: application/json\nUser-Agent: Test";
        $tokens = $this->parse($code);

        $this->assertTokenExists($tokens, 'Host', Scope::Constant);
        $this->assertTokenExists($tokens, 'Accept', Scope::Constant);
        $this->assertTokenExists($tokens, 'User-Agent', Scope::Constant);
    }

    public function testParseMalformedHeaderFallback(): void
    {
        $code = "GET /api HTTP/1.1\nnot a header";
        $tokens = $this->parse($code);

        // Should fall back to string
        $this->assertTokenExists($tokens, 'not a header', Scope::String);
    }

    public function testParseStartLineFallback(): void
    {
        $code = 'this is not http';
        $tokens = $this->parse($code);

        // Should fall back to string
        $this->assertTokenExists($tokens, 'this is not http', Scope::String);
    }

    public function testParseNewlinesBetweenLines(): void
    {
        $code = "GET /api HTTP/1.1\nHost: example.com";
        $tokens = $this->parse($code);

        // Check for newline token
        $hasNewline = false;
        foreach ($tokens as $token) {
            if ("\n" === $token->getText() && Scope::Whitespace === $token->getScope()) {
                $hasNewline = true;
                break;
            }
        }
        $this->assertTrue($hasNewline);
    }

    public function testParseHttpUrlWithPath(): void
    {
        $code = 'GET http://example.com/path HTTP/1.1';
        $tokens = $this->parse($code);

        $this->assertTokenExists($tokens, 'http://', Scope::Keyword);
        $this->assertTokenExists($tokens, 'example.com', Scope::FunctionCall);
    }

    public function testParseUrlWithoutPort(): void
    {
        $code = 'GET https://example.com/path HTTP/1.1';
        $tokens = $this->parse($code);

        $this->assertTokenExists($tokens, 'example.com', Scope::FunctionCall);
        $this->assertTokenExists($tokens, '/path', Scope::String);
    }

    public function testParseEmptyBody(): void
    {
        $code = "GET /api HTTP/1.1\n\n";
        $tokens = $this->parse($code);

        // Should handle empty body
        $this->assertNotEmpty($tokens);
    }

    public function testParseHeaderWithEmptyValue(): void
    {
        $code = "GET /api HTTP/1.1\nX-Empty:";
        $tokens = $this->parse($code);

        $this->assertTokenExists($tokens, 'X-Empty', Scope::Constant);
    }

    /**
     * @return list<ParsedToken>
     */
    private function parse(string $code): array
    {
        return $this->language->parse($code)->getTokens();
    }

    /**
     * @param list<ParsedToken> $tokens
     */
    private function assertTokenExists(array $tokens, string $text, Scope $scope): void
    {
        foreach ($tokens as $token) {
            if ($token->getText() === $text && $token->getScope() === $scope) {
                $this->assertTrue(true);

                return;
            }
        }

        $tokenList = array_map(
            fn ($t) => sprintf('[%s: %s]', $t->getScope()->value, json_encode($t->getText())),
            $tokens
        );
        $this->fail(sprintf(
            'Token "%s" with scope %s not found. Found: %s',
            $text,
            $scope->value,
            implode(', ', $tokenList)
        ));
    }
}
