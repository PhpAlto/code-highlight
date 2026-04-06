# Changelog

All notable changes to this project will be documented in this file.

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.1.0/),
and this project adheres to [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

## [1.0.0] - Unreleased

### Added

- Semantic syntax highlighting engine with context-aware PHP parsing
- 27 language parsers: PHP, HTML, SVG, XML, CSS, SCSS, JavaScript, TypeScript, Twig, Markdown, YAML, JSON, SQL, Bash, Go, Rust, Ruby, Swift, Python, Java, C#, Dockerfile, Diff, DotEnv, HTTP, INI, Makefile
- Embedded language support for HTML (`<style>`/`<script>`), SVG, Markdown (fenced code blocks), and Twig (`{% block %}`)
- 7 built-in themes: Alto, GitHub, Polar, Solar, CupertinoDark, Dracula, Noctis
- Theme adapters for Highlight.js (240+), Prism (250+), and TextMate (.tmTheme) themes
- Line numbers and line highlighting support
- Zero runtime dependencies — requires only PHP 8.4+ with `ext-mbstring` and `ext-tokenizer`
