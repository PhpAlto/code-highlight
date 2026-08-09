<?php

declare(strict_types=1);

/**
 * @var array{id: string, name: string, category: string, extension: string, source: string, featured: bool, notes: string} $language
 * @var array{id: string, name: string, dark: bool} $theme
 * @var string $themeStylesheet
 * @var string $highlighted
 * @var string $source
 * @var string $sourceHash
 * @var int    $lineCount
 * @var string $fontCss
 * @var bool   $fontEmbedded
 */

$escape = static fn (string $value): string => htmlspecialchars($value, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
$dense = $lineCount > 8;
$sourceJson = json_encode(
    $source,
    JSON_THROW_ON_ERROR | JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_UNESCAPED_SLASHES
);
?>
<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=800, initial-scale=1">
    <link rel="icon" href="data:,">
    <title><?= $escape($language['name']) ?> - <?= $escape($theme['name']) ?></title>
    <style>
<?= $fontCss ?>
<?= $themeStylesheet ?>

        :root {
            color-scheme: <?= $theme['dark'] ? 'dark' : 'light' ?>;
            font-synthesis: none;
            text-rendering: geometricPrecision;
        }

        * {
            box-sizing: border-box;
        }

        html,
        body {
            width: 800px;
            height: 400px;
            margin: 0;
            overflow: hidden;
        }

        body {
            font-family: "Showcase Mono", "JetBrains Mono", Menlo, Consolas, monospace;
        }

        .showcase-card {
            position: relative;
            width: 800px;
            height: 400px;
            overflow: hidden;
        }

        .showcase-card > .alto-highlight {
            position: absolute;
            inset: 0;
            width: 800px;
            height: 400px;
            margin: 0;
            padding: <?= $dense ? '80px 48px 20px' : '96px 48px 36px' ?>;
            overflow: hidden;
            border-radius: 0;
            font-family: "Showcase Mono", "JetBrains Mono", Menlo, Consolas, monospace;
            font-size: <?= $dense ? 16 : 19 ?>px;
            font-weight: 400;
            line-height: <?= $dense ? 22 : 31 ?>px;
            tab-size: 4;
            white-space: pre;
        }

        .showcase-card > .alto-highlight code {
            font: inherit;
        }

        .showcase-header {
            position: absolute;
            z-index: 1;
            top: 31px;
            right: 48px;
            left: 48px;
            display: flex;
            align-items: baseline;
            justify-content: space-between;
            gap: 24px;
            pointer-events: none;
        }

        .showcase-language {
            overflow: hidden;
            color: <?= $theme['dark'] ? 'rgba(255, 255, 255, 0.94)' : 'rgba(0, 0, 0, 0.86)' ?>;
            font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", sans-serif;
            font-size: 18px;
            font-weight: 650;
            letter-spacing: -0.01em;
            text-overflow: ellipsis;
            white-space: nowrap;
        }

        .showcase-theme {
            flex: none;
            color: <?= $theme['dark'] ? 'rgba(255, 255, 255, 0.52)' : 'rgba(0, 0, 0, 0.48)' ?>;
            font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", sans-serif;
            font-size: 13px;
            font-weight: 560;
            letter-spacing: 0.055em;
            text-transform: uppercase;
        }
    </style>
</head>
<body
    data-language="<?= $escape($language['id']) ?>"
    data-theme="<?= $escape($theme['id']) ?>"
    data-source-sha256="<?= $escape($sourceHash) ?>"
    data-font-embedded="<?= $fontEmbedded ? 'true' : 'false' ?>"
>
    <main id="showcase-card" class="showcase-card">
        <header class="showcase-header">
            <span class="showcase-language"><?= $escape($language['name']) ?></span>
            <span class="showcase-theme"><?= $escape($theme['name']) ?></span>
        </header>
        <?= $highlighted ?>
    </main>
    <script id="showcase-source" type="application/json"><?= $sourceJson ?></script>
</body>
</html>
