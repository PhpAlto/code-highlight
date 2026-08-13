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

namespace Alto\Code\Highlight\Language;

use Alto\Code\Highlight\Parser\ParsedStream;

/**
 * Marker interface for languages that can delegate portions of their source
 * to other language parsers (e.g., HTML embedding CSS or JavaScript).
 *
 * @author Simon André <smn.andre@gmail.com>
 */
interface EmbeddedLanguageCapable extends LanguageInterface
{
    public function parseWithEmbedding(string $code, EmbeddedLanguageContext $context): ParsedStream;
}
