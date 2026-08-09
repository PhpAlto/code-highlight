# Documentation showcase

This local-only tool renders Alto's canonical compact examples as
deterministic 800 × 400 documentation cards. It uses the package through a
Composer path repository and the real `Highlighter` API. Generated HTML is
written to `build/`; reviewed PNG files are written to
`../../docs/assets/examples/`.

## Setup

```bash
composer install
npm install
npx playwright install chromium
```

The generated cards contain the theme CSS, highlighted HTML, metadata, source,
and a locally installed JetBrains Mono font. They make no network requests.

## Commands

Generate, capture, and validate the five featured languages in Alto and GitHub
dark/light variants:

```bash
npm run refresh
```

Filter a generation or capture:

```bash
npm run generate -- --language=php
npm run generate -- --theme=dracula
npm run generate -- --language=twig --theme=github-light
npm run capture -- --language=twig --theme=github-light
```

Generate and capture every cataloged language/theme combination:

```bash
npm run generate -- --all
npm run capture -- --all
npm run verify -- --all
```

The complete matrix is an inspection artifact and stays under
`build/screenshots/`. It is not published to `docs/` unless an explicit
`--output-dir` is passed. This also applies when the current manifest was
generated with `--all` and capture is run without filters.

`--all` cannot be combined with a language or theme filter. Run any command
with `--help` for its exact interface. Filters are stable identifiers, not
display names.

## Inputs and outputs

- `../../examples/catalog.php` is the language catalog and points to the source
  files relative to `../../examples/`.
- `build/cards/<theme>/<language>.html` contains standalone card documents.
- `build/gallery.html` presents the current generated selection.
- `build/manifest.json` is the machine-readable generation contract.
- `build/captures.json` records the last captured selection.
- `../../docs/assets/examples/<theme>/<language>.png` contains captured cards.

Generation fails when a catalog entry is missing, duplicated, unknown to Alto,
unreadable, or outside the 8-13 visible-line budget. Capture additionally
checks source reconstruction, font readiness, dimensions, and overflow before
writing an image. Verification reads the PNG header and requires every captured
image to be exactly 800 × 400.
