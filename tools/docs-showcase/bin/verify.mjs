#!/usr/bin/env node

import { access, readFile } from 'node:fs/promises';
import path from 'node:path';
import process from 'node:process';
import { fileURLToPath } from 'node:url';

const CARD_WIDTH = 800;
const CARD_HEIGHT = 400;
const PNG_SIGNATURE = Buffer.from([137, 80, 78, 71, 13, 10, 26, 10]);
const toolRoot = path.resolve(path.dirname(fileURLToPath(import.meta.url)), '..');
const buildRoot = path.join(toolRoot, 'build');

try {
  const options = parseOptions(process.argv.slice(2));

  if (options.help) {
    printHelp();
    process.exit(0);
  }

  await verifyPackageContracts();

  const manifest = await readJson(path.join(buildRoot, 'manifest.json'));
  const captures = await readJson(path.join(buildRoot, 'captures.json'));
  verifyManifest(manifest, options);
  verifyCaptures(manifest, captures);

  await access(path.join(buildRoot, 'gallery.html'));

  for (const card of manifest.cards) {
    await verifyHtml(card);
  }

  for (const capture of captures.captures) {
    await verifyPng(capture);
  }

  process.stdout.write(
    `Verified ${manifest.cards.length} HTML card${manifest.cards.length === 1 ? '' : 's'} and ` +
    `${captures.captures.length} PNG asset${captures.captures.length === 1 ? '' : 's'} at 800x400.\n`,
  );
} catch (error) {
  process.stderr.write(`Error: ${formatError(error)}\n`);
  process.exitCode = error && error.usage ? 2 : 1;
}

function parseOptions(arguments_) {
  const options = { all: false, help: false };

  for (const argument of arguments_) {
    if (argument === '-h' || argument === '--help') {
      options.help = true;
    } else if (argument === '--all') {
      options.all = true;
    } else {
      const error = new Error(`Unknown argument "${argument}".`);
      error.usage = true;
      throw error;
    }
  }

  return options;
}

function printHelp() {
  process.stdout.write(`Verify generated Alto documentation cards and PNG assets.

Usage:
  node bin/verify.mjs [--all]

Options:
  --all       Require a complete language/theme matrix.
  -h, --help  Show this help.
`);
}

async function verifyPackageContracts() {
  const packageJson = await readJson(path.join(toolRoot, 'package.json'));
  const requiredScripts = ['generate', 'capture', 'verify', 'refresh'];

  if (packageJson.private !== true) {
    throw new Error('package.json must remain private.');
  }
  for (const script of requiredScripts) {
    if (typeof packageJson.scripts?.[script] !== 'string' || packageJson.scripts[script] === '') {
      throw new Error(`package.json is missing the "${script}" script.`);
    }
  }
  for (const [name, version] of Object.entries(packageJson.devDependencies ?? {})) {
    if (!/^\d+\.\d+\.\d+(?:-[0-9A-Za-z.-]+)?$/u.test(version)) {
      throw new Error(`Development dependency "${name}" must use an exact version.`);
    }
  }

  const composerJson = await readJson(path.join(toolRoot, 'composer.json'));
  const pathRepository = composerJson.repositories?.find(
    (repository) => repository.type === 'path' && repository.url === '../..',
  );
  if (!pathRepository) {
    throw new Error('composer.json must use ../.. as an Alto path repository.');
  }
  if (composerJson.require?.['alto/code-highlight'] !== 'dev-main') {
    throw new Error('composer.json must require the local Alto development package.');
  }
}

function verifyManifest(manifest, options) {
  if (!manifest || manifest.schemaVersion !== 1 || !Array.isArray(manifest.cards)) {
    throw new Error('build/manifest.json has an unsupported format.');
  }
  if (!manifest.catalog || !Array.isArray(manifest.catalog.languageIds) || !Array.isArray(manifest.catalog.themeIds)) {
    throw new Error('build/manifest.json is missing catalog metadata.');
  }
  if (manifest.expectedCount !== manifest.cards.length || manifest.cards.length === 0) {
    throw new Error('build/manifest.json has an invalid card count.');
  }

  const expectedAllCount = manifest.catalog.languageIds.length * manifest.catalog.themeIds.length;
  if (options.all && (manifest.selection !== 'all' || manifest.cards.length !== expectedAllCount)) {
    throw new Error(`Expected the full ${expectedAllCount}-card matrix; regenerate with --all.`);
  }

  const keys = new Set();
  for (const card of manifest.cards) {
    const key = `${card.theme?.id}/${card.language?.id}`;
    if (keys.has(key)) {
      throw new Error(`Duplicate manifest card "${key}".`);
    }
    keys.add(key);

    if (!manifest.catalog.languageIds.includes(card.language?.id)) {
      throw new Error(`Manifest card "${key}" uses an unknown language.`);
    }
    if (!manifest.catalog.themeIds.includes(card.theme?.id)) {
      throw new Error(`Manifest card "${key}" uses an unknown theme.`);
    }
    if (card.lineCount < 8 || card.lineCount > 13) {
      throw new Error(`Manifest card "${key}" does not have between 8 and 13 lines.`);
    }
    if (card.html !== `cards/${card.theme.id}/${card.language.id}.html`) {
      throw new Error(`Manifest card "${key}" has a non-canonical HTML path.`);
    }
    if (!/^[a-f0-9]{64}$/u.test(card.sourceSha256 ?? '')) {
      throw new Error(`Manifest card "${key}" has an invalid source checksum.`);
    }
  }
}

function verifyCaptures(manifest, captures) {
  if (!captures || captures.schemaVersion !== 1 || !Array.isArray(captures.captures)) {
    throw new Error('build/captures.json has an unsupported format. Run the capture command.');
  }
  if (captures.expectedCount !== captures.captures.length || captures.captures.length === 0) {
    throw new Error('build/captures.json has an invalid capture count.');
  }
  if (captures.captures.length !== manifest.cards.length) {
    throw new Error('Captured assets do not cover the current build manifest.');
  }

  const manifestByKey = new Map(
    manifest.cards.map((card) => [`${card.theme.id}/${card.language.id}`, card]),
  );
  const seen = new Set();

  for (const capture of captures.captures) {
    const key = `${capture.theme}/${capture.language}`;
    const card = manifestByKey.get(key);
    if (!card) {
      throw new Error(`Capture "${key}" is not present in the current manifest.`);
    }
    if (seen.has(key)) {
      throw new Error(`Duplicate capture "${key}".`);
    }
    seen.add(key);
    if (capture.sourceSha256 !== card.sourceSha256) {
      throw new Error(`Capture "${key}" was produced from stale source.`);
    }
  }
}

async function verifyHtml(card) {
  const filePath = path.join(buildRoot, card.html);
  const html = await readFile(filePath, 'utf8');
  const key = `${card.theme.id}/${card.language.id}`;

  if (!html.includes('id="showcase-card"')) {
    throw new Error(`${key}: generated HTML is missing the showcase card.`);
  }
  if (!html.includes(`data-language="${escapeHtml(card.language.id)}"`)) {
    throw new Error(`${key}: generated HTML has the wrong language metadata.`);
  }
  if (!html.includes(`data-theme="${escapeHtml(card.theme.id)}"`)) {
    throw new Error(`${key}: generated HTML has the wrong theme metadata.`);
  }
  if (!html.includes(`data-source-sha256="${card.sourceSha256}"`)) {
    throw new Error(`${key}: generated HTML has stale source metadata.`);
  }
  if (
    /<(?:script|img|iframe)\b[^>]+\bsrc\s*=\s*["']https?:/iu.test(html) ||
    /<link\b[^>]+\bhref\s*=\s*["']https?:/iu.test(html) ||
    /@import\s+(?:url\()?["']?https?:/iu.test(html)
  ) {
    throw new Error(`${key}: generated HTML contains an external resource.`);
  }
}

async function verifyPng(capture) {
  const filePath = path.resolve(toolRoot, capture.png);
  const buffer = await readFile(filePath);
  const key = `${capture.theme}/${capture.language}`;

  if (buffer.length < 24 || !buffer.subarray(0, 8).equals(PNG_SIGNATURE)) {
    throw new Error(`${key}: capture is not a valid PNG file.`);
  }

  const width = buffer.readUInt32BE(16);
  const height = buffer.readUInt32BE(20);
  if (width !== CARD_WIDTH || height !== CARD_HEIGHT) {
    throw new Error(`${key}: PNG is ${width}x${height}, expected ${CARD_WIDTH}x${CARD_HEIGHT}.`);
  }
}

async function readJson(filePath) {
  try {
    return JSON.parse(await readFile(filePath, 'utf8'));
  } catch (error) {
    if (error && error.code === 'ENOENT') {
      throw new Error(`Missing ${path.relative(process.cwd(), filePath)}.`);
    }
    throw error;
  }
}

function escapeHtml(value) {
  return value
    .replaceAll('&', '&amp;')
    .replaceAll('"', '&quot;')
    .replaceAll('<', '&lt;')
    .replaceAll('>', '&gt;');
}

function formatError(error) {
  return error instanceof Error ? error.message : String(error);
}
