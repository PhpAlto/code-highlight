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

namespace Alto\Code\Highlight\Tests\Unit;

use Alto\Code\Highlight\Highlighter;
use Alto\Code\Highlight\Language\LanguageInterface;
use Alto\Code\Highlight\Language\Languages;
use Alto\Code\Highlight\Theme\AltoTheme;
use PHPUnit\Framework\Attributes\CoversNothing;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

#[CoversNothing]
final class ExamplesCatalogTest extends TestCase
{
    private const string CATALOG_PATH = __DIR__.'/../../examples/catalog.php';

    public function testCatalogMatchesDefaultLanguageRegistryExactly(): void
    {
        $registryIdentifiers = array_map(
            static fn (LanguageInterface $language): string => $language->getIdentifier(),
            Languages::getDefaultLanguages(),
        );
        $catalogIdentifiers = array_column(self::catalog(), 'id');

        sort($registryIdentifiers);
        sort($catalogIdentifiers);

        self::assertSame($registryIdentifiers, $catalogIdentifiers);
    }

    public function testCatalogHasUniqueIdentifiersAndExactSchema(): void
    {
        $catalog = self::catalog();
        $identifiers = array_column($catalog, 'id');

        self::assertCount(count(array_unique($identifiers)), $identifiers);

        foreach ($catalog as $entry) {
            self::assertSame(
                ['id', 'name', 'category', 'extension', 'source', 'featured', 'notes'],
                array_keys($entry),
                sprintf('Unexpected catalog schema for "%s".', $entry['id']),
            );
        }
    }

    public function testOnlyExpectedLanguagesAreFeatured(): void
    {
        $featured = array_column(
            array_filter(self::catalog(), static fn (array $entry): bool => $entry['featured']),
            'id',
        );

        sort($featured);

        self::assertSame(['css', 'html', 'javascript', 'php', 'twig'], $featured);
    }

    /**
     * @param array{
     *     id: string,
     *     name: string,
     *     category: string,
     *     extension: string,
     *     source: string,
     *     featured: bool,
     *     notes: string
     * } $entry
     */
    #[DataProvider('catalogProvider')]
    public function testExampleIsReadableFitsShowcaseAndRoundTripsThroughHighlighter(array $entry): void
    {
        $path = dirname(self::CATALOG_PATH).'/'.$entry['source'];

        self::assertFileExists($path);
        self::assertIsReadable($path);

        $source = file_get_contents($path);
        self::assertNotFalse($source);
        $source = rtrim($source, "\n");
        $lineCount = count(explode("\n", $source));
        self::assertGreaterThanOrEqual(8, $lineCount);
        self::assertLessThanOrEqual(13, $lineCount);

        $html = (new Highlighter(new AltoTheme()))->highlight($source, $entry['id']);
        $reconstructed = html_entity_decode(
            strip_tags($html),
            ENT_QUOTES | ENT_HTML5,
            'UTF-8',
        );

        self::assertSame($source, $reconstructed);
    }

    /**
     * @return iterable<string, array{array{
     *     id: string,
     *     name: string,
     *     category: string,
     *     extension: string,
     *     source: string,
     *     featured: bool,
     *     notes: string
     * }}>
     */
    public static function catalogProvider(): iterable
    {
        foreach (self::catalog() as $entry) {
            yield $entry['id'] => [$entry];
        }
    }

    /**
     * @return list<array{
     *     id: string,
     *     name: string,
     *     category: string,
     *     extension: string,
     *     source: string,
     *     featured: bool,
     *     notes: string
     * }>
     */
    private static function catalog(): array
    {
        return require self::CATALOG_PATH;
    }
}
