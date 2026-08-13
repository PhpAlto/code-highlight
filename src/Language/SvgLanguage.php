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

/**
 * SVG shares the same parsing rules as HTML but exposes a dedicated identifier.
 *
 * @author Simon André <smn.andre@gmail.com>
 */
final class SvgLanguage extends HtmlLanguage
{
    public function getIdentifier(): string
    {
        return 'svg';
    }
}
