# Changelog

All notable changes to this project will be documented in this file. See [commit-and-tag-version](https://github.com/absolute-version/commit-and-tag-version) for commit guidelines.

## [1.3.0](https://github.com/Herm71/jc-core-functionality/compare/v1.2.0...v1.3.0) (2026-10-07)

### Features

* :chart_with_upwards_trend: Make the GTM container configurable; skip admins ([3f94c4a](https://github.com/Herm71/jc-core-functionality/commit/3f94c4a5722f6bec5dfe2eba83bfc2f96f5bfc67)), closes [#15](https://github.com/Herm71/jc-core-functionality/issues/15)

### Bug Fixes

* :green_heart: Let the shell expand the node:test glob ([87c4761](https://github.com/Herm71/jc-core-functionality/commit/87c47616ea9c85253eb62dee1df0e33bc6e1b14d)), references [#14](https://github.com/Herm71/jc-core-functionality/issues/14)

## [1.2.0](https://github.com/Herm71/jc-core-functionality/compare/v1.0.1...v1.2.0) (2026-10-07)

### Features

* :arrows_counterclockwise: Update the plugin from GitHub releases ([96b3838](https://github.com/Herm71/jc-core-functionality/commit/96b38382488a772cb4b70db66aa4a8857decd454)), references [#17](https://github.com/Herm71/jc-core-functionality/issues/17)
* :lock: Rebuild the CSP around per-request nonces ([52c00f3](https://github.com/Herm71/jc-core-functionality/commit/52c00f36d2bcb68e4e7c8cc81d0b030a72f7cec8)), references [#9](https://github.com/Herm71/jc-core-functionality/issues/9)
* :mailbox: Collect CSP violation reports at /wp-json/jc/v1/csp-report ([b670682](https://github.com/Herm71/jc-core-functionality/commit/b6706829d0d3b35987f565cd8f2e23eaf8cf8048)), references [#9](https://github.com/Herm71/jc-core-functionality/issues/9)
* :wastebasket: Clear update-checker state on uninstall ([f23bf6e](https://github.com/Herm71/jc-core-functionality/commit/f23bf6ea99983eed6320772fb8c760ec80040670)), references [#17](https://github.com/Herm71/jc-core-functionality/issues/17)

### Bug Fixes

* :bug: Allow GA4's bare analytics.google.com collect host ([da82df6](https://github.com/Herm71/jc-core-functionality/commit/da82df6d9d0a21879dc0590f47e60faa4958014f)), references [#9](https://github.com/Herm71/jc-core-functionality/issues/9)
* :bug: Fix settings page markup and escape header values ([b9dfca4](https://github.com/Herm71/jc-core-functionality/commit/b9dfca45ee9998ee3aafd37374f38c2cf2b8f06e)), closes [#7](https://github.com/Herm71/jc-core-functionality/issues/7)
* :bug: Load security-headers.php once ([15a6f78](https://github.com/Herm71/jc-core-functionality/commit/15a6f7815a1a8dbb9749d13f1c362bb1fc0184ad)), closes [#8](https://github.com/Herm71/jc-core-functionality/issues/8)
* :bug: Reset post data and escape the title in [quotes] ([2b137b8](https://github.com/Herm71/jc-core-functionality/commit/2b137b8e47182a1bcb0372d3e2b81dc566b8605c)), closes [#6](https://github.com/Herm71/jc-core-functionality/issues/6)
* :calendar: Use the site timezone for the copyright year ([c676f98](https://github.com/Herm71/jc-core-functionality/commit/c676f985081f0ecf7dae756c862ed676cfd46bd8)), references [#12](https://github.com/Herm71/jc-core-functionality/issues/12)
* :fire: Delete the dead wordpress.org update filter ([f515c7b](https://github.com/Herm71/jc-core-functionality/commit/f515c7b6e4fc759b41639116e26460aaa1e49e99)), closes [#5](https://github.com/Herm71/jc-core-functionality/issues/5)
* :globe_with_meridians: Use one text domain, jc-core-functionality ([ba78fc4](https://github.com/Herm71/jc-core-functionality/commit/ba78fc4a3476e7f5f6b1fd1fb47838e125404be2)), references [#12](https://github.com/Herm71/jc-core-functionality/issues/12)
* :lock: Block direct access to feature files ([8128956](https://github.com/Herm71/jc-core-functionality/commit/812895656192083cdc8feaab16e339e509ad8861)), closes [#10](https://github.com/Herm71/jc-core-functionality/issues/10), references [#18](https://github.com/Herm71/jc-core-functionality/issues/18)
* :lock: Sanitize the author bio in the jc/user-data binding ([4a9a80b](https://github.com/Herm71/jc-core-functionality/commit/4a9a80bbeb1fed8e2ffdbb2e9e64ba38604c17fc)), closes [#11](https://github.com/Herm71/jc-core-functionality/issues/11)
* :package: Ship vendor/ and acf-json/ in the release zip ([e7c7278](https://github.com/Herm71/jc-core-functionality/commit/e7c72788a6f635da1be88df7cd14b3e3d5d6fe0f)), closes [#2](https://github.com/Herm71/jc-core-functionality/issues/2), references [#17](https://github.com/Herm71/jc-core-functionality/issues/17)
* :rocket: Publish RC tags as prereleases ([8a642d9](https://github.com/Herm71/jc-core-functionality/commit/8a642d947ba382cb9444563e6884cac268312360)), references [#21](https://github.com/Herm71/jc-core-functionality/issues/21)

## [1.2.0-rc.0](https://github.com/Herm71/jc-core-functionality/compare/v1.0.1...v1.2.0-rc.0) (2026-10-07)

### Features

* :arrows_counterclockwise: Update the plugin from GitHub releases ([96b3838](https://github.com/Herm71/jc-core-functionality/commit/96b38382488a772cb4b70db66aa4a8857decd454)), references [#17](https://github.com/Herm71/jc-core-functionality/issues/17)
* :wastebasket: Clear update-checker state on uninstall ([f23bf6e](https://github.com/Herm71/jc-core-functionality/commit/f23bf6ea99983eed6320772fb8c760ec80040670)), references [#17](https://github.com/Herm71/jc-core-functionality/issues/17)

### Bug Fixes

* :package: Ship vendor/ and acf-json/ in the release zip ([e7c7278](https://github.com/Herm71/jc-core-functionality/commit/e7c72788a6f635da1be88df7cd14b3e3d5d6fe0f)), closes [#2](https://github.com/Herm71/jc-core-functionality/issues/2), references [#17](https://github.com/Herm71/jc-core-functionality/issues/17)

## [1.1.0](https://github.com/Herm71/jc-core-functionality/compare/v1.0.1...v1.1.0) (2026-10-07)

### Features

* :arrows_counterclockwise: Update the plugin from GitHub releases ([96b3838](https://github.com/Herm71/jc-core-functionality/commit/96b38382488a772cb4b70db66aa4a8857decd454)), references [#17](https://github.com/Herm71/jc-core-functionality/issues/17)
* :wastebasket: Clear update-checker state on uninstall ([f23bf6e](https://github.com/Herm71/jc-core-functionality/commit/f23bf6ea99983eed6320772fb8c760ec80040670)), references [#17](https://github.com/Herm71/jc-core-functionality/issues/17)

### Bug Fixes

* :package: Ship vendor/ and acf-json/ in the release zip ([e7c7278](https://github.com/Herm71/jc-core-functionality/commit/e7c72788a6f635da1be88df7cd14b3e3d5d6fe0f)), closes [#2](https://github.com/Herm71/jc-core-functionality/issues/2), references [#17](https://github.com/Herm71/jc-core-functionality/issues/17)

### [1.0.1](https://github.com/Herm71/jc-core-functionality/compare/v1.0.0...v1.0.1) (2024-12-18)


### Features

* 🎉 Lots of additions and Refinements ([#1](https://github.com/Herm71/jc-core-functionality/issues/1)) ([8273258](https://github.com/Herm71/jc-core-functionality/commit/82732581d2da4dc5636cfc83dd1582dc494cf3ec))

## [1.0.0](https://github.com/Herm71/jc-core-functionality/compare/v0.1.4...v1.0.0) (2024-01-16)


### Features

* :fire: Remove `shortcodes.ph`, edit README, add badges from Shields.io ([a2d931b](https://github.com/Herm71/jc-core-functionality/commit/a2d931b6f9ed7db76b6460ca17447882faa536f6))

### [0.1.4](https://github.com/Herm71/jc-core-functionality/compare/v0.1.3...v0.1.4) (2024-01-16)


### Bug Fixes

* :art: Replace GTM code ([d067a40](https://github.com/Herm71/jc-core-functionality/commit/d067a40b19e0e01c4afb7f14ae5cd16f1afb884d))

### [0.1.3](https://github.com/Herm71/jc-core-functionality/compare/v0.1.2...v0.1.3) (2024-01-15)


### Features

* :fire: remove custom post types php file, edit plugin.php accordingly ([6c6b9f6](https://github.com/Herm71/jc-core-functionality/commit/6c6b9f683c8551b0bd5738c0213059a871f8cda0))

### [0.1.2](https://github.com/Herm71/jc-core-functionality/compare/v0.1.1...v0.1.2) (2024-01-15)


### Features

* :sparkles: Add GTM and Security Policy ([3252d35](https://github.com/Herm71/jc-core-functionality/commit/3252d35080d029a9c2ca32f5971a43b28a0a6a54))

### [0.1.1](https://github.com/Herm71/jc-core-functionality/compare/v0.1.0...v0.1.1) (2024-01-15)

## 0.1.0 (2024-01-15)
