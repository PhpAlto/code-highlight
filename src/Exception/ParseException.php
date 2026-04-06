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

namespace Alto\Code\Highlight\Exception;

use Exception;

/**
 * Exception thrown when parsing fails.
 *
 * Note: The highlighter should be tolerant and try to recover from syntax errors
 * when possible, only throwing this exception in truly unrecoverable situations.
 *
 * @author Simon André <smn.andre@gmail.com>
 */
final class ParseException extends \Exception
{
    public function __construct(string $message, int $line = 0)
    {
        $fullMessage = $line > 0
            ? sprintf('Parse error at line %d: %s', $line, $message)
            : sprintf('Parse error: %s', $message);

        parent::__construct($fullMessage);
    }
}
