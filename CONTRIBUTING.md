# Contributing to Alto Code Highlight

Contributions should preserve the public API, escaped output, semantic scopes,
and deterministic highlighting.

## Prepare a checkout

```bash
composer install
composer qa
```

`composer qa` runs PHP CS Fixer, PHPStan, and PHPUnit. Run coverage separately
when a change affects executable code:

```bash
composer coverage
```

## Propose a change

Add or update tests for observable behavior. Update `docs/` and `CHANGELOG.md`
when the public contract changes. Keep language aliases, embedded parsing,
semantic scopes, and generated HTML compatibility explicit.

Documentation preview tooling is maintained in
[`tools/docs-showcase/`](tools/docs-showcase/README.md). It is contributor
infrastructure and is not part of the package's public API.

Open a pull request against `main` only after the complete quality gate passes.
Describe the user-visible result and any compatibility, security, or
performance impact.
