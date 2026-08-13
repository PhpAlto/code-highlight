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

namespace Alto\Code\Highlight\Exception;

/**
 * Exception thrown when an unsupported or unknown language is requested.
 *
 * @author Simon André <smn.andre@gmail.com>
 */
final class LanguageNotFoundException extends \Exception
{
    public function __construct(string $language)
    {
        parent::__construct(
            sprintf('Language "%s" is not supported or could not be found.', $language),
        );
    }
}
