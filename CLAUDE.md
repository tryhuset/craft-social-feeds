# CLAUDE.md

Guidance for Claude Code when working in this repository.

## Local environment: DDEV

This project runs locally in **DDEV**. Do **not** run `composer`, `php`, or
`phpunit` directly on the host — run them inside the DDEV web container so they
use the project's pinned PHP version (8.2) and Composer 2.

```bash
ddev start                       # boot the containers
ddev composer <args>             # run Composer inside the web container
ddev composer audit              # check for dependency security advisories
ddev exec vendor/bin/phpunit     # run the test suite
```

The project URL is https://craft-social-feeds.ddev.site.

## What this is

A Craft CMS 5 plugin (`apt/craft-social-feeds`) that fetches latest social
feeds via a JSON API. Plugin source lives in `src/`; tests in `tests/`.

## Conventions

- Bump the `version` in `composer.json` and add a `CHANGELOG.md` entry for every
  release.
- Tags are prefixed with `v` (e.g. `v4.1.2`) and pushed to the `github` remote
  (`tryhuset/craft-social-feeds`); GitHub releases are cut from those tags.
- Dependabot alerts are on transitive deps in `composer.lock` — resolve them with
  a targeted `ddev composer update <pkg ...> --with-all-dependencies`.
