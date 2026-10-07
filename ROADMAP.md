# Roadmap

Planned fixes and improvements for the Jason Chafin Core Functionality plugin, from a code review in October 2026 (based on v1.0.1). Items are grouped by priority. Each one names the file it affects and links to its GitHub issue.

| Milestone | Sections | Issues |
| --- | --- | --- |
| [v1.0.2 — Packaging & bug fixes](https://github.com/Herm71/jc-core-functionality/milestone/1) | 0–2 | #17 (first), #2–#8, #21 |
| [v1.1.0 — Security & cleanup](https://github.com/Herm71/jc-core-functionality/milestone/2) | 3–4 | #9–#13 |
| [v1.2.0 — Tooling & enhancements](https://github.com/Herm71/jc-core-functionality/milestone/3) | 5–6 | #14–#16 |

When you close an issue, tick its box here too.

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

- [ ] **Tighten the Content Security Policy** (`lib/functions/security-headers.php`) ([#9](https://github.com/Herm71/jc-core-functionality/issues/9)). The current policy allows `http://*`, `'unsafe-inline'` and `'unsafe-eval'`, and still lists UCSC/RCID domains. First make a list of the outside domains the site actually uses, then rebuild the policy. Ship it as `Report-Only` first. Also drop the deprecated `X-XSS-Protection` header and review `Referrer-Policy`.
- [ ] **Add `defined( 'ABSPATH' ) || exit;`** at the top of every PHP file ([#10](https://github.com/Herm71/jc-core-functionality/issues/10)).
- [ ] **Escape the `description` block-binding value** (`lib/functions/general.php`, `jc_user_data_bindings`) with `wp_kses_post()` ([#11](https://github.com/Herm71/jc-core-functionality/issues/11)).

## 4. Cleanup — v1.1.0

All of these are tracked in [#12](https://github.com/Herm71/jc-core-functionality/issues/12) unless noted.

- [ ] Delete the empty `lib/functions/updater.php`, or implement it.
- [ ] Use one text domain everywhere (`jc-core-functionality`), and add `Text Domain:` to the plugin header.
- [ ] Replace `date( 'Y' )` with `wp_date( 'Y' )` in the copyright binding.
- [ ] Use the same docblock headers in every file.
- [ ] Reformat the older files to match `.editorconfig` (tabs) and the WordPress brace style.
- [ ] Remove the UCSC/RCID leftovers in `composer.json`, `settings.php` and `gtm.php` ([#13](https://github.com/Herm71/jc-core-functionality/issues/13)).
- [ ] Remove the stray "readme edit." line from `README.md`, and list the shortcodes, block bindings and ACF post types in it ([#13](https://github.com/Herm71/jc-core-functionality/issues/13)).

## 5. Tooling — v1.2.0

All tracked in [#14](https://github.com/Herm71/jc-core-functionality/issues/14).

- [ ] **Add a `phpcs.xml.dist`** that sets the `WordPress` standard, the `jc` prefix and the text domain, and excludes `vendor/` and `node_modules/`. Then `composer lint` works with no arguments.
- [ ] Configure `lint-staged`, or point `npm test` at something useful, such as running `composer lint`.
- [ ] Optionally add a CI job that runs PHPCS on pull requests.

## 6. Enhancements to consider — v1.2.0

- [ ] Skip loading GTM for logged-in administrators, and make the container ID a constant or setting instead of hard-coding it ([#15](https://github.com/Herm71/jc-core-functionality/issues/15)).
- [ ] Document the `jc/user-data` binding on the settings page ([#16](https://github.com/Herm71/jc-core-functionality/issues/16)).
- [ ] Decide whether removing the theme's ACF JSON load path (`unset( $paths[0] )` in `general.php`) is intentional, and document why ([#16](https://github.com/Herm71/jc-core-functionality/issues/16)).
