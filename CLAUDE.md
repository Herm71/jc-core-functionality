@AGENTS.md

# CLAUDE.md

This file provides guidance to Claude Code (claude.ai/code) when working with code in this repository.

## What this is

A site-specific WordPress plugin for jasonchafin.com. It holds functionality that should survive a theme change (post types, fields, analytics, security headers, block bindings). It requires **ACF Pro** (declared via `Requires Plugins: advanced-custom-fields-pro`). The plugin was forked from a UCSC/RCID plugin, and some UCSC references remain (CSP domains, `composer.json` description and email, `settings.php` docblock).

## Commands

Neither `vendor/` nor `node_modules/` is committed. Run `composer install` and `npm install` first.

- PHP lint: `composer lint`, and auto-fix with `composer lint-fix`. There is no `phpcs.xml`, so pass the standard and paths yourself: `vendor/bin/phpcs --standard=WordPress plugin.php lib/`.
- Release: `npm run release` (standard-version). It bumps the version in `package.json`, `package-lock.json` and the `Version:` header in `plugin.php`, using the regex in `standard-version-updater.js`, which only matches single-digit `X.Y.Z`. It also updates `CHANGELOG.md` from Conventional Commits and creates a `vX.Y.Z` tag. Pushing the tag triggers `.github/workflows/release.yml`, which runs `npm run build` and `wp-scripts plugin-zip`, then attaches `jc-core-functionality.zip` to a GitHub release.
- There are no tests. `npm test` runs `lint-staged`, which has no config.
- The `@wordpress/scripts` build/start/lint-js scripts exist, but there is no `src/` directory and no JS/CSS assets yet.

## Architecture

- `plugin.php` is the only bootstrap. It defines `JC_DIR`, adds a Settings link on the Plugins screen, and `include_once`s each file in `lib/functions/`. A new feature file needs its own include line added there. Nothing is namespaced or class-based: everything is global functions with a `jc_` prefix, hooked at file load.
- **Post types and custom fields live in `acf-json/`, not in PHP.** `general.php` redirects ACF's JSON load/save point to `JC_DIR . '/acf-json'` (and removes the theme's default load path). The `person` (People) and `quote` (Quotes) CPTs and their field groups are created by ACF from these JSON files. Edit them in the ACF admin UI so the JSON is rewritten, rather than by hand.
- Code depends on these ACF-defined objects by name. The `[quotes]` shortcode (`shortcodes.php`) queries post type `quote`. `general.php` registers the `subtitle` post meta for REST, and that name matches the ACF "Posts" field group.
- Block Bindings sources are registered in `general.php`: `jc/copyright` and `jc/user-data` (args `key` = `name|description|avatar` and `userId`). The settings page in `settings.php` is a static info page that documents features. Update it when you add user-facing features.
- `gtm.php` hard-codes GTM container `GTM-WNP9BDSD`. `security-headers.php` sets the CSP via the `wp_headers` filter on the front end only. Any new third-party script or embed domain has to be added to that CSP string.

## Gotchas

- The release zip is built from the `files` list in `package.json`. That list does **not** include `acf-json/`, so released builds currently ship without the CPT and field definitions. It also lists a `LICENSE` file that doesn't exist.
- The `.editorconfig` uses tabs, but older files mix in spaces and PEAR-style braces.
- `lib/functions/updater.php` is empty and not included anywhere.
