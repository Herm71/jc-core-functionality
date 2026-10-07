# Roadmap

Planned fixes and improvements for the Jason Chafin Core Functionality plugin, from a code review in October 2026 (based on v1.0.1). Items are grouped by priority. Each one names the file it affects and links to its GitHub issue.

| Milestone | Sections | Issues |
| --- | --- | --- |
| [v1.0.2 — Packaging & bug fixes](https://github.com/Herm71/jc-core-functionality/milestone/1) | 1–2 | #2–#8 |
| [v1.1.0 — Security & cleanup](https://github.com/Herm71/jc-core-functionality/milestone/2) | 3–4 | #9–#13 |
| [v1.2.0 — Tooling & enhancements](https://github.com/Herm71/jc-core-functionality/milestone/3) | 5–6 | #14–#16 |

When you close an issue, tick its box here too.

## 1. Release packaging — v1.0.2

- [ ] **Ship `acf-json/` in the release zip** ([#2](https://github.com/Herm71/jc-core-functionality/issues/2)). `wp-scripts plugin-zip` packages only the paths in the `files` array of `package.json`, and `acf-json/` isn't in it. Releases therefore install without the `person` and `quote` post types or their field groups. Add `"acf-json"` to `files`.
- [ ] **Add the missing `LICENSE` file**, or remove it from `files` ([#3](https://github.com/Herm71/jc-core-functionality/issues/3)). The plugin header says GPL2, but `package.json` says `ISC`. Pick one and use it in both places.
- [ ] **Update the release workflow** (`.github/workflows/release.yml`) ([#4](https://github.com/Herm71/jc-core-functionality/issues/4)). It pins Node 14 and old action versions. It also runs `npm run build`, but there is no `src/` to build. Either drop the build step or add a `src/` entry point.

## 2. Bugs — v1.0.2

- [ ] **Replace the update-check filter** (`lib/functions/general.php`, `jc_custom_functionality_hidden`) ([#5](https://github.com/Herm71/jc-core-functionality/issues/5)). It never runs. WordPress now checks for updates at an `https://` address and sends JSON, but the filter only matches `http://` and unserializes the body. `plugin_basename(__FILE__)` also resolves to the wrong file. Delete the function and add an `Update URI:` header to `plugin.php`.
- [ ] **Fix `[quotes]` leaving the global post changed** (`lib/functions/shortcodes.php`) ([#6](https://github.com/Herm71/jc-core-functionality/issues/6)). `wp_reset_postdata()` comes after `return`, so it never runs. Move it before the return, and escape the title with `esc_html()`.
- [ ] **Fix the settings page markup** (`lib/functions/settings.php`) ([#7](https://github.com/Herm71/jc-core-functionality/issues/7)). There's a closing `</div>` with no opening `<div class="wrap">`. Escape `Version` and `Description` on output.
- [ ] **Remove the duplicate include** of `security-headers.php` (`plugin.php`) ([#8](https://github.com/Herm71/jc-core-functionality/issues/8)).

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
