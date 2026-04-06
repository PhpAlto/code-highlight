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

namespace Alto\Code\Highlight\Language;

final class Languages
{
    /**
     * @return list<LanguageInterface>
     */
    public static function getDefaultLanguages(): array
    {
        return [
            new PhpLanguage(),
            new HtmlLanguage(),
            new SvgLanguage(),
            new XmlLanguage(),
            new YamlLanguage(),
            new SqlLanguage(),
            new JsonLanguage(),
            new CssLanguage(),
            new ScssLanguage(),
            new MarkdownLanguage(),
            new JavaScriptLanguage(),
            new TypeScriptLanguage(),
            new TwigLanguage(),
            new MakefileLanguage(),
            new BashLanguage(),
            new IniLanguage(),
            new HttpLanguage(),
            new GoLanguage(),
            new RustLanguage(),
            new RubyLanguage(),
            new SwiftLanguage(),
            new PythonLanguage(),
            new JavaLanguage(),
            new CSharpLanguage(),
            new DockerfileLanguage(),
            new DiffLanguage(),
            new DotEnvLanguage(),
        ];
    }
}
