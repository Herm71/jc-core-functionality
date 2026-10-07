@AGENTS.md

# CLAUDE.md

This file provides guidance to Claude Code (claude.ai/code) when working with code in this repository.

## What this is

A site-specific WordPress plugin for jasonchafin.com. It holds functionality that should survive a theme change (post types, fields, analytics, security headers, block bindings). It requires **ACF Pro** (declared via `Requires Plugins: advanced-custom-fields-pro`). The plugin was forked from a UCSC/RCID plugin, and some UCSC references remain (CSP domains, `composer.json` description and email, `settings.php` docblock).

## Commands

Neither `vendor/` nor `node_modules/` is committed. Run `composer install` and `npm install` first.

- PHP lint: `composer lint`, and auto-fix with `composer lint-fix`. There is no `phpcs.xml`, so pass the standard and paths yourself: `vendor/bin/phpcs --standard=WordPress plugin.php uninstall.php lib/`.
- Tests:
  - JS/config checks: `npm run test:unit` (Node's built-in `node:test`, files in `tests/*.test.js`). Run one file with `node --test tests/<name>.test.js`.
  - PHP: `composer test` or `npm run test:php` (PHPUnit, files in `tests/php/`). Run one test with `vendor/bin/phpunit --filter <name>`.
  - The PHP tests run without WordPress. `tests/php/bootstrap.php` stubs only the WP functions the loaded feature files call, and records the calls. A new feature file under test needs its WP calls stubbed there and a `require_once` added.
  - `npm test` still runs `lint-staged`, which has no config.
- Release zip: `npm run zip`. It packages only the `files` list in `package.json` into a `jc-core-functionality/` root folder. Run `composer install --no-dev` first, or dev dependencies end up in `vendor/` in the zip.
- Release: `npm run release` (commit-and-tag-version). It bumps `package.json`, `package-lock.json` and the `Version:` header in `plugin.php` (via `wp-plugin-version-updater.js`), updates `CHANGELOG.md` from Conventional Commits, and tags `vX.Y.Z`. Pushing the tag triggers `.github/workflows/release.yml`, which runs `composer install --no-dev` and `plugin-zip`, then attaches `jc-core-functionality.zip` to a GitHub release.
- Release candidates: `npm run release -- --prerelease rc` tags `vX.Y.Z-rc.N`. The workflow publishes `-rc` tags as GitHub prereleases. PUC reads `/releases/latest`, which skips prereleases, so installed sites never see RCs. To test an RC on staging, install its zip by hand. If an RC is ever published without the prerelease flag, every site with the updater is offered it.

## Architecture

- `plugin.php` is the only bootstrap. It defines `JC_DIR`, registers the update checker (below), adds a Settings link on the Plugins screen, and `include_once`s each file in `lib/functions/`. A new feature file needs its own include line added there. Nothing is namespaced or class-based: everything is global functions with a `jc_` prefix, hooked at file load.
- **Post types and custom fields live in `acf-json/`, not in PHP.** `general.php` redirects ACF's JSON load/save point to `JC_DIR . '/acf-json'` (and removes the theme's default load path). The `person` (People) and `quote` (Quotes) CPTs and their field groups are created by ACF from these JSON files. Edit them in the ACF admin UI so the JSON is rewritten, rather than by hand.
- Code depends on these ACF-defined objects by name. The `[quotes]` shortcode (`shortcodes.php`) queries post type `quote`. `general.php` registers the `subtitle` post meta for REST, and that name matches the ACF "Posts" field group.
- Block Bindings sources are registered in `general.php`: `jc/copyright` and `jc/user-data` (args `key` = `name|description|avatar` and `userId`). The settings page in `settings.php` is a static info page that documents features. Update it when you add user-facing features.
- **Updates come from GitHub releases** through plugin-update-checker (PUC), a Composer runtime dependency loaded from `vendor/`. It runs only in admin, cron and WP-CLI, and installs only the `jc-core-functionality.zip` release asset. The slug `jc-core-functionality` must match in `plugin.php`, `uninstall.php` (PUC's option, transient and cron names), `package.json` `name` and the release workflow. `tests/update-checker-config.test.js` enforces this.
- `uninstall.php` removes only PUC's data. By policy, `person` and `quote` content is never deleted.
- `gtm.php` hard-codes GTM container `GTM-WNP9BDSD`. `security-headers.php` sets the CSP via the `wp_headers` filter on the front end only. Any new third-party script or embed domain has to be added to that CSP string.

## Gotchas

- The release zip is built from the `files` list in `package.json`. A new top-level directory won't ship unless it's added there. The list includes a `LICENSE` file that doesn't exist yet (#3).
- If `vendor/` is missing from the zip, the updater does nothing and shows no error, because of the `file_exists()` guard.
- The `.editorconfig` uses tabs, but older files mix in spaces and PEAR-style braces.
- `lib/functions/updater.php` is empty and not included anywhere.
