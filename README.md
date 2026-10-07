# Jason Chafin WordPress Core Functionality Plugin

![GitHub Release](https://img.shields.io/github/v/release/Herm71/jc-core-functionality?logo=github)
![GitHub Actions Workflow Status](https://img.shields.io/github/actions/workflow/status/Herm71/jc-core-functionality/release.yml?logo=github)
![GitHub issues](https://img.shields.io/github/issues/Herm71/jc-core-functionality?logo=github)

Custom functionality for the [Jason Chafin](https://jasonchafin.com) WordPress site. It holds the parts of the site that don't depend on the theme, such as content types, fields, analytics and security headers, so that changing the theme doesn't change what the site does.

## Requirements

- [Advanced Custom Fields Pro](https://www.advancedcustomfields.com/pro/), declared with `Requires Plugins`. WordPress won't activate this plugin without it.

## Installation

Download `jc-core-functionality.zip` from the [latest release](https://github.com/Herm71/jc-core-functionality/releases/latest) and upload it under **Plugins → Add New → Upload Plugin**.

Use the release zip, not GitHub's **Code → Download ZIP**. The source archive has no `vendor/` directory, so a copy installed from it can't update itself.

## Updates

Once it's installed, updates come from this repository's GitHub releases through [plugin-update-checker](https://github.com/YahnisElsts/plugin-update-checker). They show up on the Plugins screen like any other plugin update, and through `wp plugin update jc-core-functionality`. Release candidates (`-rc` tags) are published as prereleases and are never offered as updates.

## Features

### Content types and fields

Defined as ACF JSON in `acf-json/`, which the plugin registers as ACF's load and save path:

- **People** (`person`), with a Contact Info field group: name, title, phone, email, website, address, bio and photo.
- **Quotes** (`quote`).
- **Posts** get a `subtitle` field. It's also registered as post meta and exposed in the REST API.

### Shortcode

- `[quotes]` shows one random quote title from the Quotes post type.

### Block bindings

| Source | Arguments | Value |
| --- | --- | --- |
| `jc/copyright` | none | `© <current year>` in the site's timezone |
| `jc/user-data` | `key` (`name`, `description` or `avatar`), `userId` | The user's display name, bio, or avatar URL |

Example:

```html
<!-- wp:paragraph {"metadata":{"bindings":{"content":{"source":"jc/copyright"}}}} -->
<p>Copyright Block</p>
<!-- /wp:paragraph -->
```

### Site-wide

- **Google Tag Manager:** container snippets in `<head>` and right after `<body>`.
- **Security headers** on front-end responses: `Referrer-Policy`, `X-Content-Type-Options`, `X-Frame-Options`, `Permissions-Policy` and a Content Security Policy (see below).
- **XML-RPC disabled:** all methods are removed and the RSD link is taken out of `<head>`, to block brute-force login attempts through `/xmlrpc.php`.

## Content Security Policy

Scripts are trusted by a nonce that changes on every request, not by a host list. WordPress adds the nonce to every script printed through its script API (enqueued scripts, inline scripts, the import map and speculation rules). `'strict-dynamic'` extends that trust to the scripts they load, such as GTM and GA4. Styles still allow `'unsafe-inline'`, because block styles are printed inline.

**The policy currently ships as `Content-Security-Policy-Report-Only`.** Browsers report what it *would* block without blocking it. In the meantime a minimal policy is enforced (`object-src 'none'; base-uri 'self'; frame-ancestors 'self'`).

Reports are sent to `/wp-json/jc/v1/csp-report` and written to the PHP error log, one JSON line each, prefixed `[jc-csp]`:

```sh
grep '\[jc-csp\]' /path/to/php-error.log
```

If `WP_DEBUG_LOG` is enabled, WordPress redirects the error log to `wp-content/debug.log` (or the path the constant names), so look there instead.

Chrome sends reports through the Reporting API (`report-to`), which only delivers to `https://` endpoints and batches reports, so they can arrive a minute or more after the violation. Other browsers use `report-uri` and send immediately.

When the log shows nothing legitimate being blocked, enforce the policy:

```php
add_filter( 'jc_csp_report_only', '__return_false' );
```

To allow another source, filter the directives:

```php
add_filter( 'jc_csp_directives', function ( $directives ) {
	$directives['frame-src'][] = 'https://www.youtube-nocookie.com';
	return $directives;
} );
```

Any script printed as a raw `<script>` tag, rather than through `wp_enqueue_script()`, `wp_add_inline_script()` or `wp_print_inline_script_tag()`, gets no nonce and will be blocked once the policy is enforced.

## Uninstalling

Deleting the plugin removes only the update checker's stored data. **People and Quotes content is never deleted.** The post types simply stop being registered until something registers them again.

## Development

```sh
composer install && npm install

composer test          # PHPUnit (tests/php/)
npm run test:unit      # node:test (tests/*.test.js)
npm run release        # bump version, update CHANGELOG.md, tag
```

Pushing a `vX.Y.Z` tag builds the release zip and publishes it as a GitHub release. See [ROADMAP.md](ROADMAP.md) for planned work.

## License

[GPL-2.0-or-later](LICENSE)

## Author

[@Herm71](https://github.com/Herm71/)
