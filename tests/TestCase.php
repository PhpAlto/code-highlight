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

namespace Alto\Code\Highlight\Tests;

use Alto\Code\Highlight\Parser\ParsedStream;
use Alto\Code\Highlight\Parser\ParsedToken;
use Alto\Code\Highlight\Scope;
use PHPUnit\Framework\TestCase as BaseTestCase;

abstract class TestCase extends BaseTestCase
{
    /**
     * @param (callable(ParsedToken): bool)|null $filter
     */
    protected function streamContainsScope(ParsedStream $stream, Scope $scope, ?callable $filter = null): bool
    {
        foreach ($stream->getTokens() as $token) {
            if ($token->getScope() !== $scope) {
                continue;
            }

            if (null !== $filter && false === $filter($token)) {
                continue;
            }

            return true;
        }

        return false;
    }
}
