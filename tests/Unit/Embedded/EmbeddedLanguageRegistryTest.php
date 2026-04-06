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

namespace Alto\Code\Highlight\Tests\Unit\Embedded;

use Alto\Code\Highlight\Embedded\EmbeddedLanguageRegistry;
use Alto\Code\Highlight\Tests\TestCase;
use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(EmbeddedLanguageRegistry::class)]
final class EmbeddedLanguageRegistryTest extends TestCase
{
    public function testLoadsPlansFromConfigurationFile(): void
    {
        $path = $this->writeConfig(<<<'PHP'
<?php

use Alto\Code\Highlight\Embedded\EmbeddedLanguagePlan;
use Alto\Code\Highlight\Embedded\EmbeddedTrigger;

return [
    EmbeddedLanguagePlan::forHost('html', [EmbeddedTrigger::tag('style', 'css')]),
    [
        'host' => 'markdown',
        'triggers' => [EmbeddedTrigger::tag('code', 'php', ['lang' => ['php']])],
    ],
];
PHP);

        try {
            $registry = new EmbeddedLanguageRegistry($path);

            self::assertNotNull($registry->getPlan('HTML'));
            self::assertNotNull($registry->getPlan('markdown'));
            self::assertNull($registry->getPlan('twig'));
        } finally {
            @unlink($path);
        }
    }

    public function testReturnsNullWhenConfigurationFileMissing(): void
    {
        $registry = new EmbeddedLanguageRegistry(__DIR__.'/does-not-exist.php');

        self::assertNull($registry->getPlan('html'));
    }

    public function testThrowsWhenConfigurationDoesNotReturnArray(): void
    {
        $path = $this->writeConfig("<?php return 'invalid';");

        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('Embedded language configuration must return an array.');

        try {
            new EmbeddedLanguageRegistry($path);
        } finally {
            @unlink($path);
        }
    }

    public function testThrowsWhenDefinitionIsNotArrayOrPlan(): void
    {
        $path = $this->writeConfig('<?php return [123];');

        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('Invalid embedded language plan definition.');

        try {
            new EmbeddedLanguageRegistry($path);
        } finally {
            @unlink($path);
        }
    }

    public function testThrowsWhenArrayDefinitionIsMissingRequiredKeys(): void
    {
        $path = $this->writeConfig('<?php return [["host" => "html"]];');

        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('Embedded plan array definitions must specify host and triggers.');

        try {
            new EmbeddedLanguageRegistry($path);
        } finally {
            @unlink($path);
        }
    }

    public function testGetDefaultPlansReturnsArray(): void
    {
        $plans = EmbeddedLanguageRegistry::getDefaultPlans();

        self::assertIsArray($plans);
        self::assertNotEmpty($plans);
    }

    public function testConstructorWithNullUsesDefaultPlans(): void
    {
        $registry = new EmbeddedLanguageRegistry(null);

        // Default plans include html and svg
        self::assertNotNull($registry->getPlan('html'));
        self::assertNotNull($registry->getPlan('svg'));
    }

    public function testConstructorWithArrayDefinitions(): void
    {
        $registry = new EmbeddedLanguageRegistry([
            ['host' => 'test', 'triggers' => []],
        ]);

        self::assertNotNull($registry->getPlan('test'));
    }

    private function writeConfig(string $contents): string
    {
        $path = tempnam(sys_get_temp_dir(), 'embedded-config');
        file_put_contents($path, $contents);

        return $path;
    }
}
