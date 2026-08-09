#!/usr/bin/env node

import { mkdir, readFile, writeFile } from 'node:fs/promises';
import path from 'node:path';
import process from 'node:process';
import { fileURLToPath, pathToFileURL } from 'node:url';
import { chromium } from 'playwright';

const CARD_WIDTH = 800;
const CARD_HEIGHT = 400;
const toolRoot = path.resolve(path.dirname(fileURLToPath(import.meta.url)), '..');
const projectRoot = path.resolve(toolRoot, '..', '..');
const buildRoot = path.join(toolRoot, 'build');

try {
  const options = parseOptions(process.argv.slice(2));

  if (options.help) {
    printHelp();
    process.exit(0);
  }

  const manifest = await readJson(path.join(buildRoot, 'manifest.json'));
  const cards = selectCards(manifest, options);
  const outputRoot = options.outputDir
    ? path.resolve(process.cwd(), options.outputDir)
    : options.all || manifest.selection === 'all'
      ? path.join(buildRoot, 'screenshots')
      : path.join(projectRoot, 'docs', 'assets', 'examples');

  if (options.all && manifest.selection !== 'all') {
    throw new Error('The build manifest is not complete. Run "npm run generate -- --all" first.');
  }

  await mkdir(outputRoot, { recursive: true });

  const browser = await chromium.launch({ headless: true });
  const captured = [];

  try {
    const context = await browser.newContext({
      viewport: { width: CARD_WIDTH, height: CARD_HEIGHT },
      deviceScaleFactor: 1,
      colorScheme: 'dark',
    });

    for (const [index, card] of cards.entries()) {
      const page = await context.newPage();
      const remoteRequests = [];
      page.on('request', (request) => {
        if (/^https?:/u.test(request.url())) {
          remoteRequests.push(request.url());
        }
      });

      const htmlPath = path.join(buildRoot, card.html);
      await page.goto(pathToFileURL(htmlPath).href, { waitUntil: 'load' });
      await page.evaluate(() => document.fonts.ready);

      const validation = await validateCard(page);
      if (remoteRequests.length > 0) {
        throw new Error(`${card.theme.id}/${card.language.id}: remote request attempted: ${remoteRequests[0]}`);
      }
      assertCard(card, validation);

      const outputPath = path.join(outputRoot, card.theme.id, `${card.language.id}.png`);
      await mkdir(path.dirname(outputPath), { recursive: true });
      await page.locator('#showcase-card').screenshot({
        path: outputPath,
        type: 'png',
        animations: 'disabled',
      });
      await page.close();

      captured.push({
        language: card.language.id,
        theme: card.theme.id,
        sourceSha256: card.sourceSha256,
        html: card.html,
        png: path.relative(toolRoot, outputPath).split(path.sep).join('/'),
      });
      process.stdout.write(
        `[${String(index + 1).padStart(String(cards.length).length, ' ')}/${cards.length}] ` +
        `${card.theme.id}/${card.language.id}.png\n`,
      );
    }
  } finally {
    await browser.close();
  }

  await writeJson(path.join(buildRoot, 'captures.json'), {
    schemaVersion: 1,
    capturedAt: new Date().toISOString(),
    outputRoot: path.relative(toolRoot, outputRoot).split(path.sep).join('/'),
    expectedCount: captured.length,
    captures: captured,
  });

  process.stdout.write(`Captured ${captured.length} card${captured.length === 1 ? '' : 's'} at 800x400.\n`);
} catch (error) {
  process.stderr.write(`Error: ${formatError(error)}\n`);
  process.exitCode = isUsageError(error) ? 2 : 1;
}

function parseOptions(arguments_) {
  const options = {
    language: null,
    theme: null,
    all: false,
    outputDir: null,
    help: false,
  };

  for (const argument of arguments_) {
    if (argument === '-h' || argument === '--help') {
      options.help = true;
    } else if (argument === '--all') {
      options.all = true;
    } else if (argument.startsWith('--language=')) {
      options.language = optionValue(argument, '--language=');
    } else if (argument.startsWith('--theme=')) {
      options.theme = optionValue(argument, '--theme=');
    } else if (argument.startsWith('--output-dir=')) {
      options.outputDir = optionValue(argument, '--output-dir=');
    } else {
      throw usageError(`Unknown argument "${argument}".`);
    }
  }

  if (options.all && (options.language || options.theme)) {
    throw usageError('--all cannot be combined with --language or --theme.');
  }

  return options;
}

function optionValue(argument, prefix) {
  const value = argument.slice(prefix.length).trim();
  if (!value) {
    throw usageError(`${prefix.slice(0, -1)} requires a non-empty value.`);
  }

  return value;
}

function printHelp() {
  process.stdout.write(`Capture generated Alto documentation cards.

Usage:
  node bin/capture.mjs [--language=<id>] [--theme=<id>] [--output-dir=<path>]
  node bin/capture.mjs --all [--output-dir=<path>]

Options:
  --language=<id>    Capture one language from the current build manifest.
  --theme=<id>       Capture one theme from the current build manifest.
  --all              Require and capture a complete generated matrix.
  --output-dir=<dir> Override the default output directory.
  -h, --help         Show this help.

With no filters, the command captures every card in build/manifest.json.
The default and filtered matrices publish to ../../docs/assets/examples.
A complete --all matrix stays local under build/screenshots unless overridden.
`);
}

function selectCards(manifest, options) {
  assertManifest(manifest);

  if (options.language && !manifest.catalog.languageIds.includes(options.language)) {
    throw usageError(`Unknown language "${options.language}".`);
  }
  if (options.theme && !manifest.catalog.themeIds.includes(options.theme)) {
    throw usageError(`Unknown theme "${options.theme}".`);
  }

  const cards = manifest.cards.filter((card) =>
    (!options.language || card.language.id === options.language) &&
    (!options.theme || card.theme.id === options.theme)
  );

  if (cards.length === 0) {
    throw new Error('No generated cards match the filters. Regenerate the requested selection first.');
  }

  return cards;
}

function assertManifest(manifest) {
  if (!manifest || manifest.schemaVersion !== 1 || !Array.isArray(manifest.cards)) {
    throw new Error('build/manifest.json has an unsupported format. Run the generator again.');
  }
  if (!manifest.catalog || !Array.isArray(manifest.catalog.languageIds) || !Array.isArray(manifest.catalog.themeIds)) {
    throw new Error('build/manifest.json is missing catalog metadata.');
  }
}

async function validateCard(page) {
  return page.evaluate(async ({ width, height }) => {
    await document.fonts.ready;

    const card = document.querySelector('#showcase-card');
    const pre = card?.querySelector('.alto-highlight');
    const code = pre?.querySelector('code');
    const header = card?.querySelector('.showcase-header');
    const sourceElement = document.querySelector('#showcase-source');

    if (!card || !pre || !code || !header || !sourceElement) {
      return { error: 'required card elements are missing' };
    }

    const source = JSON.parse(sourceElement.textContent ?? '""');
    const normalizedSource = source.replaceAll('\r\n', '\n').replaceAll('\r', '\n');
    const withoutFinalNewline = normalizedSource.endsWith('\n')
      ? normalizedSource.slice(0, -1)
      : normalizedSource;
    const lineCount = withoutFinalNewline === '' ? 0 : withoutFinalNewline.split('\n').length;
    const cardRect = card.getBoundingClientRect();
    const preRect = pre.getBoundingClientRect();
    const codeRect = code.getBoundingClientRect();
    const headerRect = header.getBoundingClientRect();
    const computedCode = getComputedStyle(code);
    const embeddedFont = document.body.dataset.fontEmbedded === 'true';

    return {
      viewport: {
        width: window.innerWidth,
        height: window.innerHeight,
        documentWidth: document.documentElement.scrollWidth,
        documentHeight: document.documentElement.scrollHeight,
      },
      card: {
        width: cardRect.width,
        height: cardRect.height,
      },
      pre: {
        width: preRect.width,
        height: preRect.height,
        clientWidth: pre.clientWidth,
        clientHeight: pre.clientHeight,
        scrollWidth: pre.scrollWidth,
        scrollHeight: pre.scrollHeight,
      },
      contentInsideCard:
        codeRect.left >= cardRect.left &&
        codeRect.right <= cardRect.right &&
        codeRect.top > headerRect.bottom &&
        codeRect.bottom <= cardRect.bottom,
      lineCount,
      sourceMatches: code.textContent === source,
      whiteSpace: computedCode.whiteSpace,
      fontFamily: computedCode.fontFamily,
      fontsReady: document.fonts.status === 'loaded',
      embeddedFont,
      embeddedFontReady: !embeddedFont || document.fonts.check('19px "Showcase Mono"'),
      expected: { width, height },
    };
  }, { width: CARD_WIDTH, height: CARD_HEIGHT });
}

function assertCard(card, validation) {
  const prefix = `${card.theme.id}/${card.language.id}`;
  if (validation.error) {
    throw new Error(`${prefix}: ${validation.error}.`);
  }
  if (validation.viewport.width !== CARD_WIDTH || validation.viewport.height !== CARD_HEIGHT) {
    throw new Error(`${prefix}: viewport is not ${CARD_WIDTH}x${CARD_HEIGHT}.`);
  }
  if (validation.card.width !== CARD_WIDTH || validation.card.height !== CARD_HEIGHT) {
    throw new Error(`${prefix}: card is not ${CARD_WIDTH}x${CARD_HEIGHT}.`);
  }
  if (
    validation.viewport.documentWidth > CARD_WIDTH ||
    validation.viewport.documentHeight > CARD_HEIGHT
  ) {
    throw new Error(`${prefix}: document overflows the viewport.`);
  }
  if (
    validation.pre.scrollWidth > validation.pre.clientWidth ||
    validation.pre.scrollHeight > validation.pre.clientHeight ||
    !validation.contentInsideCard
  ) {
    throw new Error(`${prefix}: highlighted code overflows its card.`);
  }
  if (
    validation.lineCount !== card.lineCount ||
    card.lineCount < 8 ||
    card.lineCount > 13
  ) {
    throw new Error(`${prefix}: source must contain between 8 and 13 visible lines.`);
  }
  if (!validation.sourceMatches) {
    throw new Error(`${prefix}: highlighted text does not reconstruct the source.`);
  }
  if (validation.whiteSpace !== 'pre') {
    throw new Error(`${prefix}: highlighted source is allowed to wrap.`);
  }
  if (!validation.fontsReady || !validation.embeddedFontReady) {
    throw new Error(`${prefix}: local fonts are not ready.`);
  }
  if (validation.embeddedFont && !validation.fontFamily.includes('Showcase Mono')) {
    throw new Error(`${prefix}: highlighted code is not using the embedded font.`);
  }
}

async function readJson(filePath) {
  try {
    return JSON.parse(await readFile(filePath, 'utf8'));
  } catch (error) {
    if (error && error.code === 'ENOENT') {
      throw new Error(`Missing ${path.relative(process.cwd(), filePath)}. Run "npm run generate" first.`);
    }
    throw error;
  }
}

async function writeJson(filePath, value) {
  await writeFile(filePath, `${JSON.stringify(value, null, 2)}\n`);
}

function usageError(message) {
  const error = new Error(message);
  error.usage = true;
  return error;
}

function isUsageError(error) {
  return Boolean(error && error.usage);
}

function formatError(error) {
  return error instanceof Error ? error.message : String(error);
}
