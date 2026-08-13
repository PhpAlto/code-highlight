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

namespace Alto\Code\Highlight\Tests\Unit\Adapter;

use Alto\Code\Highlight\Adapter\TextMateThemeAdapter;
use Alto\Code\Highlight\Scope;
use Alto\Code\Highlight\ThemeInterface;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(TextMateThemeAdapter::class)]
class TextMateThemeAdapterTest extends TestCase
{
    public function testImplementsThemeInterface(): void
    {
        $adapter = new TextMateThemeAdapter($this->getSolarizedDarkTheme(), 'solarized-dark', true);

        $this->assertInstanceOf(ThemeInterface::class, $adapter);
    }

    public function testGetName(): void
    {
        $adapter = new TextMateThemeAdapter($this->getSolarizedDarkTheme(), 'solarized-dark');

        $this->assertSame('solarized-dark', $adapter->getName());
    }

    public function testGetNameDefaultsToTextmate(): void
    {
        $adapter = new TextMateThemeAdapter($this->getSolarizedDarkTheme());

        $this->assertSame('textmate', $adapter->getName());
    }

    public function testIsDark(): void
    {
        $adapter = new TextMateThemeAdapter($this->getSolarizedDarkTheme(), isDark: true);

        $this->assertTrue($adapter->isDark());
    }

    public function testIsNotDarkByDefault(): void
    {
        $adapter = new TextMateThemeAdapter($this->getSolarizedDarkTheme());

        $this->assertFalse($adapter->isDark());
    }

    public function testCorrectlyConvertsTheme(): void
    {
        $adapter = new TextMateThemeAdapter($this->getSolarizedDarkTheme(), 'solarized-dark');
        $stylesheet = $adapter->getStylesheet();

        // Check various rules from the Solarized Dark theme
        $this->assertStringContainsString('#586E75', $stylesheet); // Comment color
        $this->assertStringContainsString('#2AA198', $stylesheet); // String color
        $this->assertStringContainsString('#D33682', $stylesheet); // Number color
        $this->assertStringContainsString('#859900', $stylesheet); // Keyword color
        $this->assertStringContainsString('#268BD2', $stylesheet); // TypeDefinition/Function color
    }

    public function testGetCssClassesReturnsAllScopes(): void
    {
        $adapter = new TextMateThemeAdapter($this->getSolarizedDarkTheme());
        $classes = $adapter->getCssClasses();

        // Should have an entry for every scope
        foreach (Scope::cases() as $scope) {
            $this->assertArrayHasKey($scope->value, $classes);
        }
    }

    public function testGetCssClassesUsesAltoTmPrefix(): void
    {
        $adapter = new TextMateThemeAdapter($this->getSolarizedDarkTheme());
        $classes = $adapter->getCssClasses();

        foreach ($classes as $className) {
            $this->assertStringStartsWith('alto-tm-', $className);
        }
    }

    public function testThrowsExceptionForInvalidXml(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Failed to parse theme XML.');

        @new TextMateThemeAdapter('not valid xml');
    }

    public function testReturnsEmptyStylesheetForNoDict(): void
    {
        $xml = '<?xml version="1.0" encoding="UTF-8"?><plist version="1.0"></plist>';
        $adapter = new TextMateThemeAdapter($xml);

        $this->assertSame('', $adapter->getStylesheet());
    }

    public function testReturnsEmptyStylesheetForEmptySettings(): void
    {
        $xml = <<<'XML'
<?xml version="1.0" encoding="UTF-8"?>
<plist version="1.0">
<dict>
    <key>name</key>
    <string>Test Theme</string>
</dict>
</plist>
XML;

        $adapter = new TextMateThemeAdapter($xml);

        $this->assertSame('', $adapter->getStylesheet());
    }

    public function testWithBackgroundSetting(): void
    {
        $xml = <<<'XML'
<?xml version="1.0" encoding="UTF-8"?>
<plist version="1.0">
<dict>
    <key>settings</key>
    <array>
        <dict>
            <key>scope</key>
            <string>comment</string>
            <key>settings</key>
            <dict>
                <key>background</key>
                <string>#333333</string>
            </dict>
        </dict>
    </array>
</dict>
</plist>
XML;

        $adapter = new TextMateThemeAdapter($xml);

        $this->assertStringContainsString('background-color: #333333', $adapter->getStylesheet());
    }

    public function testWithBoldFontStyle(): void
    {
        $xml = <<<'XML'
<?xml version="1.0" encoding="UTF-8"?>
<plist version="1.0">
<dict>
    <key>settings</key>
    <array>
        <dict>
            <key>scope</key>
            <string>keyword</string>
            <key>settings</key>
            <dict>
                <key>fontStyle</key>
                <string>bold</string>
            </dict>
        </dict>
    </array>
</dict>
</plist>
XML;

        $adapter = new TextMateThemeAdapter($xml);

        $this->assertStringContainsString('font-weight: bold', $adapter->getStylesheet());
    }

    public function testWithItalicFontStyle(): void
    {
        $xml = <<<'XML'
<?xml version="1.0" encoding="UTF-8"?>
<plist version="1.0">
<dict>
    <key>settings</key>
    <array>
        <dict>
            <key>scope</key>
            <string>comment</string>
            <key>settings</key>
            <dict>
                <key>fontStyle</key>
                <string>italic</string>
            </dict>
        </dict>
    </array>
</dict>
</plist>
XML;

        $adapter = new TextMateThemeAdapter($xml);

        $this->assertStringContainsString('font-style: italic', $adapter->getStylesheet());
    }

    public function testWithUnderlineFontStyle(): void
    {
        $xml = <<<'XML'
<?xml version="1.0" encoding="UTF-8"?>
<plist version="1.0">
<dict>
    <key>settings</key>
    <array>
        <dict>
            <key>scope</key>
            <string>string</string>
            <key>settings</key>
            <dict>
                <key>fontStyle</key>
                <string>underline</string>
            </dict>
        </dict>
    </array>
</dict>
</plist>
XML;

        $adapter = new TextMateThemeAdapter($xml);

        $this->assertStringContainsString('text-decoration: underline', $adapter->getStylesheet());
    }

    public function testWithVariableParameterScope(): void
    {
        $xml = <<<'XML'
<?xml version="1.0" encoding="UTF-8"?>
<plist version="1.0">
<dict>
    <key>settings</key>
    <array>
        <dict>
            <key>scope</key>
            <string>variable.parameter</string>
            <key>settings</key>
            <dict>
                <key>foreground</key>
                <string>#9CDCFE</string>
            </dict>
        </dict>
    </array>
</dict>
</plist>
XML;

        $adapter = new TextMateThemeAdapter($xml);

        $this->assertStringContainsString('alto-tm-variable-parameter', $adapter->getStylesheet());
        $this->assertStringContainsString('#9CDCFE', $adapter->getStylesheet());
    }

    public function testWithConstantScope(): void
    {
        $xml = <<<'XML'
<?xml version="1.0" encoding="UTF-8"?>
<plist version="1.0">
<dict>
    <key>settings</key>
    <array>
        <dict>
            <key>scope</key>
            <string>constant</string>
            <key>settings</key>
            <dict>
                <key>foreground</key>
                <string>#B5CEA8</string>
            </dict>
        </dict>
    </array>
</dict>
</plist>
XML;

        $adapter = new TextMateThemeAdapter($xml);

        $this->assertStringContainsString('alto-tm-constant', $adapter->getStylesheet());
        $this->assertStringContainsString('#B5CEA8', $adapter->getStylesheet());
    }

    public function testWithVariableScope(): void
    {
        $xml = <<<'XML'
<?xml version="1.0" encoding="UTF-8"?>
<plist version="1.0">
<dict>
    <key>settings</key>
    <array>
        <dict>
            <key>scope</key>
            <string>variable</string>
            <key>settings</key>
            <dict>
                <key>foreground</key>
                <string>#9CDCFE</string>
            </dict>
        </dict>
    </array>
</dict>
</plist>
XML;

        $adapter = new TextMateThemeAdapter($xml);

        $this->assertStringContainsString('alto-tm-variable', $adapter->getStylesheet());
        $this->assertStringContainsString('#9CDCFE', $adapter->getStylesheet());
    }

    public function testWithEntityScope(): void
    {
        $xml = <<<'XML'
<?xml version="1.0" encoding="UTF-8"?>
<plist version="1.0">
<dict>
    <key>settings</key>
    <array>
        <dict>
            <key>scope</key>
            <string>entity</string>
            <key>settings</key>
            <dict>
                <key>foreground</key>
                <string>#4EC9B0</string>
            </dict>
        </dict>
    </array>
</dict>
</plist>
XML;

        $adapter = new TextMateThemeAdapter($xml);

        $this->assertStringContainsString('alto-tm-type-reference', $adapter->getStylesheet());
        $this->assertStringContainsString('#4EC9B0', $adapter->getStylesheet());
    }

    public function testWithStorageScope(): void
    {
        $xml = <<<'XML'
<?xml version="1.0" encoding="UTF-8"?>
<plist version="1.0">
<dict>
    <key>settings</key>
    <array>
        <dict>
            <key>scope</key>
            <string>storage</string>
            <key>settings</key>
            <dict>
                <key>foreground</key>
                <string>#569CD6</string>
            </dict>
        </dict>
    </array>
</dict>
</plist>
XML;

        $adapter = new TextMateThemeAdapter($xml);

        $this->assertStringContainsString('alto-tm-keyword', $adapter->getStylesheet());
        $this->assertStringContainsString('#569CD6', $adapter->getStylesheet());
    }

    public function testWithSupportScopes(): void
    {
        $xml = <<<'XML'
<?xml version="1.0" encoding="UTF-8"?>
<plist version="1.0">
<dict>
    <key>settings</key>
    <array>
        <dict>
            <key>scope</key>
            <string>support.function</string>
            <key>settings</key>
            <dict>
                <key>foreground</key>
                <string>#DCDCAA</string>
            </dict>
        </dict>
        <dict>
            <key>scope</key>
            <string>support.type</string>
            <key>settings</key>
            <dict>
                <key>foreground</key>
                <string>#4EC9B0</string>
            </dict>
        </dict>
        <dict>
            <key>scope</key>
            <string>support.constant</string>
            <key>settings</key>
            <dict>
                <key>foreground</key>
                <string>#4FC1FF</string>
            </dict>
        </dict>
    </array>
</dict>
</plist>
XML;

        $adapter = new TextMateThemeAdapter($xml);
        $stylesheet = $adapter->getStylesheet();

        $this->assertStringContainsString('alto-tm-support-function', $stylesheet);
        $this->assertStringContainsString('#DCDCAA', $stylesheet);
        $this->assertStringContainsString('alto-tm-support-type', $stylesheet);
        $this->assertStringContainsString('#4EC9B0', $stylesheet);
        $this->assertStringContainsString('alto-tm-support-constant', $stylesheet);
        $this->assertStringContainsString('#4FC1FF', $stylesheet);
    }

    public function testWithPunctuationAndOperatorScopes(): void
    {
        $xml = <<<'XML'
<?xml version="1.0" encoding="UTF-8"?>
<plist version="1.0">
<dict>
    <key>settings</key>
    <array>
        <dict>
            <key>scope</key>
            <string>punctuation</string>
            <key>settings</key>
            <dict>
                <key>foreground</key>
                <string>#D4D4D4</string>
            </dict>
        </dict>
        <dict>
            <key>scope</key>
            <string>operator</string>
            <key>settings</key>
            <dict>
                <key>foreground</key>
                <string>#D4D4D4</string>
            </dict>
        </dict>
    </array>
</dict>
</plist>
XML;

        $adapter = new TextMateThemeAdapter($xml);
        $stylesheet = $adapter->getStylesheet();

        $this->assertStringContainsString('alto-tm-punctuation', $stylesheet);
        $this->assertStringContainsString('alto-tm-operator', $stylesheet);
    }

    public function testWithEntityNameTypeScope(): void
    {
        $xml = <<<'XML'
<?xml version="1.0" encoding="UTF-8"?>
<plist version="1.0">
<dict>
    <key>settings</key>
    <array>
        <dict>
            <key>scope</key>
            <string>entity.name.type</string>
            <key>settings</key>
            <dict>
                <key>foreground</key>
                <string>#4EC9B0</string>
            </dict>
        </dict>
    </array>
</dict>
</plist>
XML;

        $adapter = new TextMateThemeAdapter($xml);

        $this->assertStringContainsString('alto-tm-type-definition', $adapter->getStylesheet());
        $this->assertStringContainsString('#4EC9B0', $adapter->getStylesheet());
    }

    public function testWithIntegerAndBooleanNodes(): void
    {
        $xml = <<<'XML'
<?xml version="1.0" encoding="UTF-8"?>
<plist version="1.0">
<dict>
    <key>version</key>
    <integer>1</integer>
    <key>enabled</key>
    <true/>
    <key>disabled</key>
    <false/>
    <key>settings</key>
    <array>
        <dict>
            <key>scope</key>
            <string>comment</string>
            <key>settings</key>
            <dict>
                <key>foreground</key>
                <string>#888888</string>
            </dict>
        </dict>
    </array>
</dict>
</plist>
XML;

        $adapter = new TextMateThemeAdapter($xml);

        $this->assertStringContainsString('#888888', $adapter->getStylesheet());
    }

    public function testWithNonArraySettingsItem(): void
    {
        $xml = <<<'XML'
<?xml version="1.0" encoding="UTF-8"?>
<plist version="1.0">
<dict>
    <key>settings</key>
    <array>
        <string>not a dict</string>
        <dict>
            <key>scope</key>
            <string>keyword</string>
            <key>settings</key>
            <dict>
                <key>foreground</key>
                <string>#569CD6</string>
            </dict>
        </dict>
    </array>
</dict>
</plist>
XML;

        $adapter = new TextMateThemeAdapter($xml);

        // Should skip non-dict items
        $this->assertStringContainsString('#569CD6', $adapter->getStylesheet());
    }

    public function testWithUnknownScope(): void
    {
        $xml = <<<'XML'
<?xml version="1.0" encoding="UTF-8"?>
<plist version="1.0">
<dict>
    <key>settings</key>
    <array>
        <dict>
            <key>scope</key>
            <string>unknown.scope.type</string>
            <key>settings</key>
            <dict>
                <key>foreground</key>
                <string>#FF0000</string>
            </dict>
        </dict>
    </array>
</dict>
</plist>
XML;

        $adapter = new TextMateThemeAdapter($xml);

        // Unknown scope should not appear in stylesheet
        $this->assertStringNotContainsString('#FF0000', $adapter->getStylesheet());
    }

    public function testWithMissingSettingsKey(): void
    {
        $xml = <<<'XML'
<?xml version="1.0" encoding="UTF-8"?>
<plist version="1.0">
<dict>
    <key>settings</key>
    <array>
        <dict>
            <key>scope</key>
            <string>comment</string>
        </dict>
    </array>
</dict>
</plist>
XML;

        $adapter = new TextMateThemeAdapter($xml);

        // Should handle gracefully
        $this->assertIsArray($adapter->getCssClasses());
    }

    public function testWithMissingScopeKey(): void
    {
        $xml = <<<'XML'
<?xml version="1.0" encoding="UTF-8"?>
<plist version="1.0">
<dict>
    <key>settings</key>
    <array>
        <dict>
            <key>settings</key>
            <dict>
                <key>foreground</key>
                <string>#888888</string>
            </dict>
        </dict>
    </array>
</dict>
</plist>
XML;

        $adapter = new TextMateThemeAdapter($xml);

        // Should handle gracefully
        $this->assertIsArray($adapter->getCssClasses());
    }

    public function testWithNestedArrays(): void
    {
        $xml = <<<'XML'
<?xml version="1.0" encoding="UTF-8"?>
<plist version="1.0">
<dict>
    <key>settings</key>
    <array>
        <dict>
            <key>scope</key>
            <string>comment</string>
            <key>settings</key>
            <dict>
                <key>foreground</key>
                <string>#888888</string>
            </dict>
        </dict>
    </array>
    <key>metadata</key>
    <array>
        <string>value1</string>
        <string>value2</string>
    </array>
</dict>
</plist>
XML;

        $adapter = new TextMateThemeAdapter($xml);

        // Should parse arrays correctly
        $this->assertStringContainsString('#888888', $adapter->getStylesheet());
    }

    public function testWithUnknownNodeType(): void
    {
        $xml = <<<'XML'
<?xml version="1.0" encoding="UTF-8"?>
<plist version="1.0">
<dict>
    <key>settings</key>
    <array>
        <dict>
            <key>scope</key>
            <string>comment</string>
            <key>settings</key>
            <dict>
                <key>foreground</key>
                <string>#888888</string>
                <key>custom</key>
                <data>dGVzdA==</data>
            </dict>
        </dict>
    </array>
</dict>
</plist>
XML;

        $adapter = new TextMateThemeAdapter($xml);

        // Should handle unknown node types gracefully
        $this->assertStringContainsString('#888888', $adapter->getStylesheet());
    }

    public function testSkipsMalformedDictPairsWithMissingValues(): void
    {
        $xml = <<<'XML'
<?xml version="1.0" encoding="UTF-8"?>
<plist version="1.0">
<dict>
    <key>settings</key>
    <array>
        <dict>
            <key>scope</key>
            <string>comment</string>
            <key>settings</key>
            <dict>
                <key>foreground</key>
                <string>#FF0000</string>
                <key>malformed-key</key>
            </dict>
        </dict>
        <dict>
            <key>scope</key>
            <string>keyword</string>
            <key>settings</key>
            <dict>
                <key>foreground</key>
                <string>#00FF00</string>
            </dict>
        </dict>
    </array>
</dict>
</plist>
XML;

        $adapter = new TextMateThemeAdapter($xml);
        $stylesheet = $adapter->getStylesheet();

        // Should parse valid entries despite malformed dict pair
        $this->assertStringContainsString('#FF0000', $stylesheet);
        $this->assertStringContainsString('#00FF00', $stylesheet);
    }

    public function testFromFileThrowsOnNonExistentPath(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('does not exist');

        TextMateThemeAdapter::fromFile('/nonexistent/path/theme.tmTheme');
    }

    public function testFromFileThrowsOnEmptyPath(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('must not be empty');

        TextMateThemeAdapter::fromFile('');
    }

    public function testFromFileThrowsOnDirectoryPath(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('not a file');

        TextMateThemeAdapter::fromFile(__DIR__);
    }

    public function testCssInjectionInForegroundIsBlocked(): void
    {
        $xml = <<<'XML'
<?xml version="1.0" encoding="UTF-8"?>
<plist version="1.0">
<dict>
    <key>settings</key>
    <array>
        <dict>
            <key>scope</key>
            <string>comment</string>
            <key>settings</key>
            <dict>
                <key>foreground</key>
                <string>red; } body { background: url(http://evil.com)</string>
            </dict>
        </dict>
    </array>
</dict>
</plist>
XML;

        $adapter = new TextMateThemeAdapter($xml);
        $stylesheet = $adapter->getStylesheet();

        // The malicious value should be stripped entirely — no color injected
        $this->assertStringNotContainsString('evil.com', $stylesheet);
        $this->assertStringNotContainsString('url(', $stylesheet);
        $this->assertStringNotContainsString('color:', $stylesheet);
    }

    public function testCssInjectionInBackgroundIsBlocked(): void
    {
        $xml = <<<'XML'
<?xml version="1.0" encoding="UTF-8"?>
<plist version="1.0">
<dict>
    <key>settings</key>
    <array>
        <dict>
            <key>scope</key>
            <string>comment</string>
            <key>settings</key>
            <dict>
                <key>background</key>
                <string>#333; } * { display: none</string>
            </dict>
        </dict>
    </array>
</dict>
</plist>
XML;

        $adapter = new TextMateThemeAdapter($xml);
        $stylesheet = $adapter->getStylesheet();

        // The malicious value should be stripped entirely — no background-color injected
        $this->assertStringNotContainsString('display: none', $stylesheet);
        $this->assertStringNotContainsString('background-color:', $stylesheet);
    }

    public function testValidCssColorsAreAccepted(): void
    {
        $xml = <<<'XML'
<?xml version="1.0" encoding="UTF-8"?>
<plist version="1.0">
<dict>
    <key>settings</key>
    <array>
        <dict>
            <key>scope</key>
            <string>comment</string>
            <key>settings</key>
            <dict>
                <key>foreground</key>
                <string>#586E75</string>
                <key>background</key>
                <string>rgb(88, 110, 117)</string>
            </dict>
        </dict>
    </array>
</dict>
</plist>
XML;

        $adapter = new TextMateThemeAdapter($xml);
        $stylesheet = $adapter->getStylesheet();

        $this->assertStringContainsString('#586E75', $stylesheet);
        $this->assertStringContainsString('rgb(88, 110, 117)', $stylesheet);
    }

    private function getSolarizedDarkTheme(): string
    {
        return <<<XML
<?xml version="1.0" encoding="UTF-8"?>
<!DOCTYPE plist PUBLIC "-//Apple//DTD PLIST 1.0//EN" "http://www.apple.com/DTDs/PropertyList-1.0.dtd">
<plist version="1.0">
<dict>
	<key>name</key>
	<string>Solarized (dark)</string>
	<key>settings</key>
	<array>
		<dict>
			<key>settings</key>
			<dict>
				<key>background</key>
				<string>#002B36</string>
				<key>foreground</key>
				<string>#839496</string>
			</dict>
		</dict>
		<dict>
			<key>name</key>
			<string>Comment</string>
			<key>scope</key>
			<string>comment</string>
			<key>settings</key>
			<dict>
				<key>foreground</key>
				<string>#586E75</string>
			</dict>
		</dict>
		<dict>
			<key>name</key>
			<string>String</string>
			<key>scope</key>
			<string>string</string>
			<key>settings</key>
			<dict>
				<key>foreground</key>
				<string>#2AA198</string>
			</dict>
		</dict>
		<dict>
			<key>name</key>
			<string>Number</string>
			<key>scope</key>
			<string>constant.numeric</string>
			<key>settings</key>
			<dict>
				<key>foreground</key>
				<string>#D33682</string>
			</dict>
		</dict>
		<dict>
			<key>name</key>
			<string>Keyword</string>
			<key>scope</key>
			<string>keyword</string>
			<key>settings</key>
			<dict>
				<key>foreground</key>
				<string>#859900</string>
			</dict>
		</dict>
		<dict>
			<key>name</key>
			<string>Class name</string>
			<key>scope</key>
			<string>entity.name.class, entity.name.type.class</string>
			<key>settings</key>
			<dict>
				<key>foreground</key>
				<string>#268BD2</string>
			</dict>
		</dict>
		<dict>
			<key>name</key>
			<string>Function name</string>
			<key>scope</key>
			<string>entity.name.function</string>
			<key>settings</key>
			<dict>
				<key>foreground</key>
				<string>#268BD2</string>
			</dict>
		</dict>
	</array>
</dict>
</plist>
XML;
    }
}
