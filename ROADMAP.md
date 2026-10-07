# Roadmap

Planned fixes and improvements for the Jason Chafin Core Functionality plugin, from a code review in October 2026 (based on v1.0.1). Items are grouped by priority. Each one names the file it affects and links to its GitHub issue.

| Milestone                                                                                      | Sections | Issues                  |
| ---------------------------------------------------------------------------------------------- | -------- | ----------------------- |
| [v1.0.2 — Packaging & bug fixes](https://github.com/Herm71/jc-core-functionality/milestone/1)  | 0–2      | #17 (first), #2–#8, #21 |
| [v1.1.0 — Security & cleanup](https://github.com/Herm71/jc-core-functionality/milestone/2)     | 3–4      | #9–#13                  |
| [v1.2.0 — Tooling & enhancements](https://github.com/Herm71/jc-core-functionality/milestone/3) | 5–6      | #14–#16                 |
| [v1.3.0 — Maintenance](https://github.com/Herm71/jc-core-functionality/milestone/4)            | 7        | #26, #27                |

When you close an issue, tick its box here too.

### Done on the `26-27-node-and-npm-audit` branch

- Node 24 everywhere through `.nvmrc`, with the actions on their Node 24 majors (#27).
- npm audit from 96 to 37 findings, 0 critical, via `@wordpress/scripts` 36, `lint-staged` 17 and the `wp-prettier` alias (#26).

### Done on the `v1.2.0-tooling-gtm-docs` branch

- Tooling (#14): `phpcs.xml.dist` so `composer lint` works, with the 23 remaining errors fixed; lint tools updated past their advisories; a husky + lint-staged pre-commit hook; `npm test` runs both suites; CI on every PR. Added the `Requires at least: 6.5` and `Requires PHP: 8.0` headers.
- GTM (#15): the container is configurable (`JC_GTM_ID` or a filter, live ID by default) and is skipped for administrators.
- Docs (#16): `jc/user-data` is on the settings page, and the ACF load-path decision is recorded in code.

### Done on the `9-csp-rebuild` branch

- Rebuilt the CSP (#9): per-request nonces with `'strict-dynamic'` and no `'unsafe-inline'`/`'unsafe-eval'` for scripts, only the hosts the site actually uses, and the `blob:` typo fixed. It ships as Report-Only, with violation reports logged through a new REST endpoint.
- The GTM loader now goes through `wp_print_inline_script_tag()` so it gets the nonce.
- `Referrer-Policy` is now `strict-origin-when-cross-origin`, `X-XSS-Protection` is gone, and `Permissions-Policy` now denies geolocation, microphone and camera.
- **Content to fix in WordPress (not the plugin):** Roboto and one other font are registered with `http://localhost:8888` URLs, so they never load. Two images on _Build menus with the WordPress navigation block_ load from `test-jchafin.wordpress.ucsc.edu`, which redirects.
- **Next:** watch the `[jc-csp]` log for a week or two, then return false from `jc_csp_report_only` to enforce.

### Done on the `v1.1.0-cleanup` branch

- Hardening: `ABSPATH` guards in every feature file (#10), and the `jc/user-data` bio sanitized with `wp_kses_post()` (#11).
- Cleanup (#12): deleted `updater.php`; one text domain (`jc-core-functionality`); `wp_date()` for the copyright year; consistent docblocks; WordPress Coding Standards formatting.
- Leftovers (#13): fixed the `composer.json` and `package.json` metadata, and rewrote the README.
- Tests: PHPUnit for the block bindings, and `node:test` rules for guards, text domain, `date()` and leftovers.

### Done on the `21-rc-prerelease` branch

- RC tags now publish as GitHub prereleases, so the updater never offers them to installed sites (#21).

### Done on the `v1.0.2-remaining-fixes` branch

- Fixed #3 (license), #6 (`[quotes]`), #7 (settings page) and #8 (duplicate include). This closes the v1.0.2 milestone.
- Added PHPUnit with minimal WordPress stubs (`composer test`, `tests/php/`), with behaviour tests for `[quotes]` and the settings page.

### Done on the `5-remove-update-filter` branch

- Deleted the dead WordPress.org update filter (#5) and added a test that keeps PUC as the only update path.

### Done on the `17-plugin-update-checker` branch

- Added plugin-update-checker 5.7 (#17). Release assets are required, and the checker runs in admin, cron and WP-CLI.
- Replaced `standard-version` with `commit-and-tag-version`. The new `wp-plugin-version-updater.js` handles multi-digit and `-rc.N` versions.
- Upgraded wp-scripts 26 → 34, so the zip has a `jc-core-functionality/` root folder.
- The release zip now ships `vendor/` and `acf-json/` (#2), and the workflow was modernized (#4).
- Added `uninstall.php`, which clears PUC's data and leaves content alone.
- Added `npm run test:unit` with `node:test` tests for the version updater and the updater slug.

## 0. GitHub-release updater — v1.0.2 (do first)

- [x] **Install updates from GitHub releases using [plugin-update-checker](https://github.com/YahnisElsts/plugin-update-checker)** ([#17](https://github.com/Herm71/jc-core-functionality/issues/17)), built the same way as in [rcid-core-functionality](https://github.com/Herm71/rcid-core-functionality/pull/17). PUC is a Composer runtime dependency, and CI ships it in `vendor/`. It's registered only for the dashboard, cron and WP-CLI, and only accepts the `jc-core-functionality.zip` release asset (`REQUIRE_RELEASE_ASSETS`). The work also removes the `GitHub Plugin URI` header, moves to wp-scripts 34 and `commit-and-tag-version` with a fixed version updater, and adds an `uninstall.php` that clears PUC's stored data. Doing this first also takes care of most of #2 and #4, and turns #5 into a simple delete.

## 1. Release packaging — v1.0.2

- [x] **Ship `acf-json/` in the release zip** ([#2](https://github.com/Herm71/jc-core-functionality/issues/2), done as part of #17). `wp-scripts plugin-zip` packages only the paths in the `files` array of `package.json`, and `acf-json/` isn't in it. Releases therefore install without the `person` and `quote` post types or their field groups. Add `"acf-json"` to `files`.
- [x] **Add the missing `LICENSE` file** ([#3](https://github.com/Herm71/jc-core-functionality/issues/3)). The plugin is now GPL-2.0-or-later everywhere: plugin header (plus `License URI`), `package.json`, `package-lock.json` and `composer.json`. The full GPL-2.0 text is in `LICENSE`.
- [x] **Update the release workflow** (`.github/workflows/release.yml`) ([#4](https://github.com/Herm71/jc-core-functionality/issues/4), done as part of #17). It pinned Node 14 and old action versions, and ran `npm run build` with no `src/`. It now uses Node 20, v4 actions, `npm ci` and `action-gh-release@v2`, and the build step and the dead `build`/`start` scripts are gone.
- [x] **Publish RC tags as prereleases** (`.github/workflows/release.yml`) ([#21](https://github.com/Herm71/jc-core-functionality/issues/21)). The workflow ran on `-rc` tags but never set `prerelease`, so `v1.2.0-rc.0` became the Latest release, and the updater offers Latest to every installed site. The Release step now sets `prerelease` from the tag, and a test fails if it's removed.

## 2. Bugs — v1.0.2

- [x] **Delete the dead update-check filter** (`lib/functions/general.php`, `jc_custom_functionality_hidden`) ([#5](https://github.com/Herm71/jc-core-functionality/issues/5)). It never ran: it matched `http://`, but WordPress calls the endpoint over `https://`. Had it matched, `unserialize()` on the JSON body would have caused a fatal under PHP 8. Deleted rather than repaired, because `jc-core-functionality` doesn't exist on WordPress.org, and plugin-update-checker now handles updates. A test now fails if any plugin file filters `http_request_args`.
- [x] **Fix `[quotes]` leaving the global post changed** (`lib/functions/shortcodes.php`) ([#6](https://github.com/Herm71/jc-core-functionality/issues/6)). `wp_reset_postdata()` now runs before the return, the title is escaped with `esc_html()`, and the query skips the row count with `no_found_rows`.
- [x] **Fix the settings page markup** (`lib/functions/settings.php`) ([#7](https://github.com/Herm71/jc-core-functionality/issues/7)). The page is wrapped in `<div class="wrap">`, the unclosed `<li>` is closed, and the header values are fetched as plain text and escaped. The block-binding example was being hidden as an HTML comment; it's now escaped so it shows as text.
- [x] **Remove the duplicate include** of `security-headers.php` (`plugin.php`) ([#8](https://github.com/Herm71/jc-core-functionality/issues/8)).

## 3. Security and hardening — v1.1.0

- [x] **Tighten the Content Security Policy** (`lib/functions/security-headers.php`) ([#9](https://github.com/Herm71/jc-core-functionality/issues/9)). Rebuilt from an inventory of all 48 sitemap pages plus runtime capture of what GTM loads. It now uses per-request nonces with `'strict-dynamic'`, ships as Report-Only with an enforced baseline, and sends reports to `/wp-json/jc/v1/csp-report`. Enforcing is a one-line filter once the logs are clean. Original notes: The current policy allows `http://*`, `'unsafe-inline'` and `'unsafe-eval'`, and still lists UCSC/RCID domains. First make a list of the outside domains the site actually uses, then rebuild the policy. Ship it as `Report-Only` first. Also drop the deprecated `X-XSS-Protection` header and review `Referrer-Policy`.
- [x] **Add an `ABSPATH` guard** at the top of every PHP file ([#10](https://github.com/Herm71/jc-core-functionality/issues/10)).
- [x] **Escape the `description` block-binding value** (`lib/functions/general.php`, `jc_user_data_bindings`) with `wp_kses_post()` ([#11](https://github.com/Herm71/jc-core-functionality/issues/11)).

## 4. Cleanup — v1.1.0

All of these are tracked in [#12](https://github.com/Herm71/jc-core-functionality/issues/12) unless noted.

- [x] Delete the empty `lib/functions/updater.php`.
- [x] Use one text domain everywhere (`jc-core-functionality`), and add `Text Domain:` to the plugin header.
- [x] Replace `date( 'Y' )` with `wp_date( 'Y' )` in the copyright binding.
- [x] Use the same docblock headers in every file. Existing `@copyright` lines were left as they were.
- [x] Reformat the older files to match `.editorconfig` (tabs) and the WordPress brace style (`phpcbf`). PHPCS errors went from 118 to 23; the rest need judgment and are left for #14.
- [x] Remove the UCSC/RCID leftovers in `composer.json`, `package.json`, `settings.php` and `gtm.php`. The CSP's UCSC hosts were removed in #9 ([#13](https://github.com/Herm71/jc-core-functionality/issues/13)).
- [x] Rewrite `README.md`: remove the stray line, and document the shortcodes, block bindings, ACF post types, installing from the release zip, updates and the uninstall policy ([#13](https://github.com/Herm71/jc-core-functionality/issues/13)).

## 5. Tooling — v1.2.0

All tracked in [#14](https://github.com/Herm71/jc-core-functionality/issues/14).

- [x] **Add a `phpcs.xml.dist`** that sets the `WordPress` standard, the `jc` prefix and the text domain, and excludes `vendor/` and `node_modules/`. Then `composer lint` works with no arguments.
- [x] Configure `lint-staged` (with a husky pre-commit hook), and make `npm test` run both test suites.
- [x] Add CI on every pull request (`ci.yml`): PHP 8.0 syntax and PHPCS, tests on PHP 8.3, and a formatting check. The remaining 23 PHPCS errors are fixed, and the lint tools are updated past their security advisories.

## 6. Enhancements to consider — v1.2.0

- [x] Skip loading GTM for logged-in administrators, and make the container ID configurable: the live ID by default, overridable with `JC_GTM_ID` or a filter, and `''` turns it off ([#15](https://github.com/Herm71/jc-core-functionality/issues/15)).
- [x] Document the `jc/user-data` binding on the settings page ([#16](https://github.com/Herm71/jc-core-functionality/issues/16)).
- [x] Decide whether removing the theme's ACF JSON load path (`unset( $paths[0] )` in `general.php`) is intentional, and document why. It is: the plugin is the single source of the content model ([#16](https://github.com/Herm71/jc-core-functionality/issues/16)).

## 7. Maintenance — v1.3.0

- [x] **Move CI and releases off Node 20** ([#27](https://github.com/Herm71/jc-core-functionality/issues/27)). Node 20 reached end of life in April 2026, and GitHub warned on every run. All workflows now read Node 24 from `.nvmrc`. The actions moved to their Node 24 majors: checkout v7, setup-node v7, cache v6, action-gh-release v3.
- [x] **Clear npm audit findings** ([#26](https://github.com/Herm71/jc-core-functionality/issues/26)). Down from 96 to 37, with 0 critical:
    - `npm audit fix`.
    - `@wordpress/scripts` 34 → 36, with a regenerated lockfile.
    - `lint-staged` 15 → 17.
    - `prettier` aliased to `wp-prettier`, which wp-scripts 36's `format` requires.

    The remaining 20 high findings are inside `@wordpress/scripts` itself: `braces` has no patched release, and `serialize-javascript` is pinned by webpack plugins this project never runs.
