<?php

declare(strict_types=1);

/**
 * @var list<array{language: array{id: string, name: string}, theme: array{id: string, name: string}, relativeHtml: string}> $cards
 */

$escape = static fn (string $value): string => htmlspecialchars($value, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
?>
<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="icon" href="data:,">
    <title>Alto documentation showcase</title>
    <style>
        :root {
            color-scheme: light;
            font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", sans-serif;
            background: #f5f5f3;
            color: #171717;
        }

        * {
            box-sizing: border-box;
        }

        body {
            margin: 0;
            padding: 48px;
        }

        header {
            max-width: 1120px;
            margin: 0 auto 36px;
        }

        h1 {
            margin: 0 0 8px;
            font-size: 32px;
            letter-spacing: -0.035em;
        }

        p {
            margin: 0;
            color: #666;
        }

        .gallery {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(400px, 1fr));
            gap: 28px;
            max-width: 1120px;
            margin: 0 auto;
        }

        article {
            min-width: 0;
        }

        .preview {
            position: relative;
            width: 400px;
            height: 200px;
            max-width: 100%;
            overflow: hidden;
            background: #ddd;
            box-shadow: 0 18px 42px rgba(0, 0, 0, 0.12);
        }

        iframe {
            width: 800px;
            height: 400px;
            border: 0;
            transform: scale(0.5);
            transform-origin: top left;
        }

        a {
            display: inline-block;
            margin-top: 10px;
            color: inherit;
            font-size: 14px;
            font-weight: 620;
            text-decoration: none;
        }

        a:hover {
            text-decoration: underline;
        }
    </style>
</head>
<body>
    <header>
        <h1>Alto documentation showcase</h1>
        <p><?= count($cards) ?> generated <?= 1 === count($cards) ? 'card' : 'cards' ?>, rendered from canonical examples.</p>
    </header>
    <main class="gallery">
<?php foreach ($cards as $card): ?>
        <article>
            <div class="preview">
                <iframe
                    src="<?= $escape($card['relativeHtml']) ?>"
                    title="<?= $escape($card['language']['name'].' - '.$card['theme']['name']) ?>"
                    loading="lazy"
                ></iframe>
            </div>
            <a href="<?= $escape($card['relativeHtml']) ?>">
                <?= $escape($card['language']['name']) ?> · <?= $escape($card['theme']['name']) ?>
            </a>
        </article>
<?php endforeach; ?>
    </main>
</body>
</html>
