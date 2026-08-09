#!/usr/bin/env php
<?php

declare(strict_types=1);

use Alto\Code\Highlight\Highlighter;
use Alto\Code\Highlight\Language\Languages;
use Alto\Code\Highlight\Theme\AltoTheme;
use Alto\Code\Highlight\Theme\CupertinoTheme;
use Alto\Code\Highlight\Theme\DraculaTheme;
use Alto\Code\Highlight\Theme\GitHubTheme;
use Alto\Code\Highlight\Theme\NoctisTheme;
use Alto\Code\Highlight\Theme\PolarTheme;
use Alto\Code\Highlight\Theme\SolarTheme;
use Alto\Code\Highlight\ThemeInterface;

const EXIT_USAGE = 2;

$toolRoot = dirname(__DIR__);
$projectRoot = dirname($toolRoot, 2);
$autoloadPath = $toolRoot.'/vendor/autoload.php';
$catalogPath = $projectRoot.'/examples/catalog.php';
$examplesRoot = $projectRoot.'/examples';
$buildRoot = $toolRoot.'/build';

try {
    $options = parseOptions(array_slice($argv, 1));

    if ($options['help']) {
        printHelp();
        exit(0);
    }

    if (!is_file($autoloadPath)) {
        throw new RuntimeException('Composer dependencies are missing. Run "composer install" in tools/docs-showcase.');
    }

    require $autoloadPath;

    $themes = themeCatalog();
    $languages = loadLanguageCatalog($catalogPath, $examplesRoot);
    validateLanguageCatalog($languages);
    validateFilters($options, $languages, $themes);

    $selection = selectCards($languages, $themes, $options);
    $font = loadFont($toolRoot);

    ensureDirectory($buildRoot.'/cards');

    $cards = [];
    foreach ($selection as [$language, $themeDefinition]) {
        $sourcePath = $examplesRoot.'/'.$language['source'];
        $source = sourceForDisplay(readRequiredFile($sourcePath));
        $lineCount = visibleLineCount($source);
        $theme = ($themeDefinition['factory'])();
        $highlighter = new Highlighter($theme);
        $highlighted = $highlighter->highlight($source, $language['id']);
        $relativeHtml = 'cards/'.$themeDefinition['id'].'/'.$language['id'].'.html';
        $absoluteHtml = $buildRoot.'/'.$relativeHtml;

        ensureDirectory(dirname($absoluteHtml));

        $html = renderTemplate($toolRoot.'/templates/card.php', [
            'language' => $language,
            'theme' => [
                'id' => $themeDefinition['id'],
                'name' => $themeDefinition['name'],
                'dark' => $theme->isDark(),
            ],
            'themeStylesheet' => $theme->getStylesheet(),
            'highlighted' => $highlighted,
            'source' => $source,
            'sourceHash' => hash('sha256', $source),
            'lineCount' => $lineCount,
            'fontCss' => $font['css'],
            'fontEmbedded' => $font['embedded'],
        ]);
        writeFile($absoluteHtml, $html);

        $cards[] = [
            'language' => [
                'id' => $language['id'],
                'name' => $language['name'],
                'featured' => $language['featured'],
            ],
            'theme' => [
                'id' => $themeDefinition['id'],
                'name' => $themeDefinition['name'],
                'dark' => $theme->isDark(),
            ],
            'source' => '../../examples/'.$language['source'],
            'sourceSha256' => hash('sha256', $source),
            'lineCount' => $lineCount,
            'html' => $relativeHtml,
        ];
    }

    $galleryCards = array_map(
        static fn (array $card): array => [
            'language' => $card['language'],
            'theme' => $card['theme'],
            'relativeHtml' => $card['html'],
        ],
        $cards
    );
    writeFile(
        $buildRoot.'/gallery.html',
        renderTemplate($toolRoot.'/templates/gallery.php', ['cards' => $galleryCards])
    );

    $featuredLanguageIds = array_values(array_map(
        static fn (array $language): string => $language['id'],
        array_filter($languages, static fn (array $language): bool => $language['featured'])
    ));
    $themeIds = array_keys($themes);
    $languageIds = array_column($languages, 'id');
    $selectionName = selectionName($options);
    $manifest = [
        'schemaVersion' => 1,
        'generatedAt' => gmdate('c'),
        'selection' => $selectionName,
        'fontEmbedded' => $font['embedded'],
        'catalog' => [
            'path' => '../../examples/catalog.php',
            'languageIds' => $languageIds,
            'featuredLanguageIds' => $featuredLanguageIds,
            'themeIds' => $themeIds,
            'defaultThemeIds' => defaultThemeIds(),
        ],
        'filters' => [
            'language' => $options['language'],
            'theme' => $options['theme'],
            'all' => $options['all'],
        ],
        'expectedCount' => count($cards),
        'cards' => $cards,
    ];
    writeJson($buildRoot.'/manifest.json', $manifest);

    $fontNote = $font['embedded'] ? 'embedded font' : 'system font fallback';
    fwrite(STDOUT, sprintf(
        "Generated %d card%s and build/gallery.html (%s).\n",
        count($cards),
        1 === count($cards) ? '' : 's',
        $fontNote
    ));
} catch (InvalidArgumentException $exception) {
    fwrite(STDERR, 'Error: '.$exception->getMessage()."\n\n");
    fwrite(STDERR, "Run \"php bin/generate.php --help\" for usage.\n");
    exit(EXIT_USAGE);
} catch (Throwable $exception) {
    fwrite(STDERR, 'Error: '.$exception->getMessage()."\n");
    exit(1);
}

/**
 * @param list<string> $arguments
 *
 * @return array{language: ?string, theme: ?string, all: bool, help: bool}
 */
function parseOptions(array $arguments): array
{
    $options = [
        'language' => null,
        'theme' => null,
        'all' => false,
        'help' => false,
    ];

    foreach ($arguments as $argument) {
        if ('-h' === $argument || '--help' === $argument) {
            $options['help'] = true;
            continue;
        }

        if ('--all' === $argument) {
            $options['all'] = true;
            continue;
        }

        if (str_starts_with($argument, '--language=')) {
            $options['language'] = optionValue($argument, '--language=');
            continue;
        }

        if (str_starts_with($argument, '--theme=')) {
            $options['theme'] = optionValue($argument, '--theme=');
            continue;
        }

        throw new InvalidArgumentException(sprintf('Unknown argument "%s".', $argument));
    }

    if ($options['all'] && (null !== $options['language'] || null !== $options['theme'])) {
        throw new InvalidArgumentException('--all cannot be combined with --language or --theme.');
    }

    return $options;
}

function optionValue(string $argument, string $prefix): string
{
    $value = trim(substr($argument, strlen($prefix)));
    if ('' === $value) {
        throw new InvalidArgumentException(sprintf('%s requires a non-empty value.', rtrim($prefix, '=')));
    }

    return $value;
}

function printHelp(): void
{
    fwrite(STDOUT, <<<'HELP'
Generate standalone Alto documentation cards.

Usage:
  php bin/generate.php [--language=<id>] [--theme=<id>]
  php bin/generate.php --all

Options:
  --language=<id>  Generate one language across the default four themes.
  --theme=<id>     Generate featured languages with one theme.
  --all            Generate every cataloged language/theme pair.
  -h, --help       Show this help.

With no filters, the command generates featured languages in Alto Dark,
Alto Light, GitHub Dark, and GitHub Light.

HELP);
}

/**
 * @return array<string, array{id: string, name: string, factory: Closure(): ThemeInterface}>
 */
function themeCatalog(): array
{
    return [
        'alto-dark' => [
            'id' => 'alto-dark',
            'name' => 'Alto Dark',
            'factory' => static fn (): ThemeInterface => new AltoTheme(),
        ],
        'alto-light' => [
            'id' => 'alto-light',
            'name' => 'Alto Light',
            'factory' => static fn (): ThemeInterface => new AltoTheme(dark: false),
        ],
        'cupertino-dark' => [
            'id' => 'cupertino-dark',
            'name' => 'Cupertino Dark',
            'factory' => static fn (): ThemeInterface => new CupertinoTheme(),
        ],
        'cupertino-light' => [
            'id' => 'cupertino-light',
            'name' => 'Cupertino Light',
            'factory' => static fn (): ThemeInterface => new CupertinoTheme(dark: false),
        ],
        'github-dark' => [
            'id' => 'github-dark',
            'name' => 'GitHub Dark',
            'factory' => static fn (): ThemeInterface => new GitHubTheme(),
        ],
        'github-light' => [
            'id' => 'github-light',
            'name' => 'GitHub Light',
            'factory' => static fn (): ThemeInterface => new GitHubTheme(dark: false),
        ],
        'noctis-dark' => [
            'id' => 'noctis-dark',
            'name' => 'Noctis Dark',
            'factory' => static fn (): ThemeInterface => new NoctisTheme(),
        ],
        'noctis-light' => [
            'id' => 'noctis-light',
            'name' => 'Noctis Light',
            'factory' => static fn (): ThemeInterface => new NoctisTheme(dark: false),
        ],
        'solar-light' => [
            'id' => 'solar-light',
            'name' => 'Solar Light',
            'factory' => static fn (): ThemeInterface => new SolarTheme(),
        ],
        'solar-dark' => [
            'id' => 'solar-dark',
            'name' => 'Solar Dark',
            'factory' => static fn (): ThemeInterface => new SolarTheme(dark: true),
        ],
        'dracula' => [
            'id' => 'dracula',
            'name' => 'Dracula',
            'factory' => static fn (): ThemeInterface => new DraculaTheme(),
        ],
        'polar' => [
            'id' => 'polar',
            'name' => 'Polar',
            'factory' => static fn (): ThemeInterface => new PolarTheme(),
        ],
    ];
}

/**
 * @return list<array{id: string, name: string, category: string, extension: string, source: string, featured: bool, notes: string}>
 */
function loadLanguageCatalog(string $catalogPath, string $examplesRoot): array
{
    if (!is_file($catalogPath)) {
        throw new RuntimeException(sprintf(
            'Language catalog not found at %s. Create examples/catalog.php before generating cards.',
            $catalogPath
        ));
    }

    $catalog = require $catalogPath;
    if (!is_array($catalog) || !array_is_list($catalog)) {
        throw new RuntimeException('examples/catalog.php must return a list of language entries.');
    }

    $requiredKeys = ['id', 'name', 'category', 'extension', 'source', 'featured', 'notes'];
    $languages = [];
    $seen = [];

    foreach ($catalog as $index => $entry) {
        if (!is_array($entry)) {
            throw new RuntimeException(sprintf('Catalog entry %d must be an array.', $index));
        }

        foreach ($requiredKeys as $key) {
            if (!array_key_exists($key, $entry)) {
                throw new RuntimeException(sprintf('Catalog entry %d is missing "%s".', $index, $key));
            }
        }

        foreach (['id', 'name', 'category', 'extension', 'source', 'notes'] as $key) {
            if (!is_string($entry[$key]) || '' === trim($entry[$key])) {
                throw new RuntimeException(sprintf('Catalog entry %d has an invalid "%s".', $index, $key));
            }
        }

        if (!is_bool($entry['featured'])) {
            throw new RuntimeException(sprintf('Catalog entry %d has a non-boolean "featured" value.', $index));
        }

        $id = strtolower($entry['id']);
        if ($id !== $entry['id'] || 1 !== preg_match('/^[a-z][a-z0-9-]*$/', $id)) {
            throw new RuntimeException(sprintf('Catalog identifier "%s" is not a stable lowercase identifier.', $entry['id']));
        }

        if (isset($seen[$id])) {
            throw new RuntimeException(sprintf('Catalog identifier "%s" is duplicated.', $id));
        }
        $seen[$id] = true;

        $sourcePath = resolveSourcePath($examplesRoot, $entry['source']);
        $source = sourceForDisplay(readRequiredFile($sourcePath));
        $lineCount = visibleLineCount($source);
        if ($lineCount < 8 || $lineCount > 13) {
            throw new RuntimeException(sprintf(
                'Example "%s" must contain between 8 and 13 visible lines; found %d.',
                $id,
                $lineCount
            ));
        }

        /** @var array{id: string, name: string, category: string, extension: string, source: string, featured: bool, notes: string} $entry */
        $languages[] = $entry;
    }

    return $languages;
}

function resolveSourcePath(string $examplesRoot, string $relativePath): string
{
    if (str_starts_with($relativePath, '/') || str_contains($relativePath, "\0")) {
        throw new RuntimeException(sprintf('Example source path "%s" must be relative.', $relativePath));
    }

    $sourcePath = realpath($examplesRoot.'/'.$relativePath);
    $realExamplesRoot = realpath($examplesRoot);
    if (false === $sourcePath || false === $realExamplesRoot) {
        throw new RuntimeException(sprintf('Example source "%s" is not readable.', $relativePath));
    }

    if (!str_starts_with($sourcePath, $realExamplesRoot.DIRECTORY_SEPARATOR)) {
        throw new RuntimeException(sprintf('Example source "%s" escapes the examples directory.', $relativePath));
    }

    return $sourcePath;
}

/**
 * @param list<array{id: string}> $catalog
 */
function validateLanguageCatalog(array $catalog): void
{
    $registeredIds = array_map(
        static fn ($language): string => $language->getIdentifier(),
        Languages::getDefaultLanguages()
    );
    sort($registeredIds);

    $catalogIds = array_column($catalog, 'id');
    sort($catalogIds);

    $missing = array_values(array_diff($registeredIds, $catalogIds));
    $unknown = array_values(array_diff($catalogIds, $registeredIds));
    if ([] !== $missing || [] !== $unknown) {
        $details = [];
        if ([] !== $missing) {
            $details[] = 'missing: '.implode(', ', $missing);
        }
        if ([] !== $unknown) {
            $details[] = 'unknown: '.implode(', ', $unknown);
        }

        throw new RuntimeException('Language catalog does not match Alto: '.implode('; ', $details).'.');
    }
}

/**
 * @param array{language: ?string, theme: ?string, all: bool, help: bool} $options
 * @param list<array{id: string}>                                                $languages
 * @param array<string, mixed>                                                  $themes
 */
function validateFilters(array $options, array $languages, array $themes): void
{
    $languageIds = array_column($languages, 'id');
    if (null !== $options['language'] && !in_array($options['language'], $languageIds, true)) {
        throw new InvalidArgumentException(sprintf(
            'Unknown language "%s". Available: %s.',
            $options['language'],
            implode(', ', $languageIds)
        ));
    }

    if (null !== $options['theme'] && !isset($themes[$options['theme']])) {
        throw new InvalidArgumentException(sprintf(
            'Unknown theme "%s". Available: %s.',
            $options['theme'],
            implode(', ', array_keys($themes))
        ));
    }
}

/**
 * @param list<array{id: string, featured: bool}> $languages
 * @param array<string, mixed>                     $themes
 * @param array{language: ?string, theme: ?string, all: bool, help: bool} $options
 *
 * @return list<array{0: array<string, mixed>, 1: array<string, mixed>}>
 */
function selectCards(array $languages, array $themes, array $options): array
{
    $selectedLanguages = $languages;
    $selectedThemes = $themes;

    if (!$options['all']) {
        if (null !== $options['language']) {
            $selectedLanguages = array_values(array_filter(
                $languages,
                static fn (array $language): bool => $options['language'] === $language['id']
            ));
        } else {
            $selectedLanguages = array_values(array_filter(
                $languages,
                static fn (array $language): bool => $language['featured']
            ));
        }

        if (null !== $options['theme']) {
            $selectedThemes = [$options['theme'] => $themes[$options['theme']]];
        } else {
            $selectedThemes = array_intersect_key($themes, array_flip(defaultThemeIds()));
        }
    }

    $selection = [];
    foreach ($selectedLanguages as $language) {
        foreach ($selectedThemes as $theme) {
            $selection[] = [$language, $theme];
        }
    }

    if ([] === $selection) {
        throw new RuntimeException('The selected matrix is empty. Mark at least one catalog entry as featured.');
    }

    return $selection;
}

/**
 * @return list<string>
 */
function defaultThemeIds(): array
{
    return ['alto-dark', 'alto-light', 'github-dark', 'github-light'];
}

/**
 * @param array{language: ?string, theme: ?string, all: bool, help: bool} $options
 */
function selectionName(array $options): string
{
    if ($options['all']) {
        return 'all';
    }

    if (null !== $options['language'] || null !== $options['theme']) {
        return 'filtered';
    }

    return 'default';
}

/**
 * @return array{css: string, embedded: bool}
 */
function loadFont(string $toolRoot): array
{
    $fontPath = $toolRoot.'/node_modules/@fontsource/jetbrains-mono/files/jetbrains-mono-latin-400-normal.woff2';
    if (!is_file($fontPath)) {
        fwrite(STDERR, "Warning: local JetBrains Mono font not installed; using the system monospace fallback.\n");

        return ['css' => '', 'embedded' => false];
    }

    $fontData = readRequiredFile($fontPath);
    $encoded = base64_encode($fontData);

    return [
        'css' => <<<CSS
@font-face {
    font-family: "Showcase Mono";
    font-style: normal;
    font-weight: 400;
    font-display: block;
    src: url("data:font/woff2;base64,{$encoded}") format("woff2");
}
CSS,
        'embedded' => true,
    ];
}

function normalizeNewlines(string $source): string
{
    return str_replace(["\r\n", "\r"], "\n", $source);
}

function sourceForDisplay(string $source): string
{
    return rtrim(normalizeNewlines($source), "\n");
}

function visibleLineCount(string $source): int
{
    if ('' === $source) {
        return 0;
    }

    if (str_ends_with($source, "\n")) {
        $source = substr($source, 0, -1);
    }

    return 1 + substr_count($source, "\n");
}

function readRequiredFile(string $path): string
{
    $contents = @file_get_contents($path);
    if (false === $contents) {
        throw new RuntimeException(sprintf('Unable to read %s.', $path));
    }

    return $contents;
}

function ensureDirectory(string $path): void
{
    if (is_dir($path)) {
        return;
    }

    if (!mkdir($path, 0777, true) && !is_dir($path)) {
        throw new RuntimeException(sprintf('Unable to create directory %s.', $path));
    }
}

function writeFile(string $path, string $contents): void
{
    if (false === file_put_contents($path, $contents)) {
        throw new RuntimeException(sprintf('Unable to write %s.', $path));
    }
}

/**
 * @param array<string, mixed> $data
 */
function writeJson(string $path, array $data): void
{
    $json = json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_THROW_ON_ERROR);
    writeFile($path, $json."\n");
}

/**
 * @param array<string, mixed> $variables
 */
function renderTemplate(string $path, array $variables): string
{
    if (!is_file($path)) {
        throw new RuntimeException(sprintf('Template not found: %s.', $path));
    }

    extract($variables, EXTR_SKIP);
    ob_start();

    try {
        require $path;

        return (string) ob_get_clean();
    } catch (Throwable $exception) {
        ob_end_clean();
        throw $exception;
    }
}
