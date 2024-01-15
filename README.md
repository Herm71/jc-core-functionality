# Jason Chafin WordPress Core Functionality Plugin

![GitHub Release](https://img.shields.io/github/v/release/Herm71/jc-core-functionality?display_name=release&logo=github&labelColor=%23362422&color=%23B95B09) ![GitHub Actions Workflow Status](https://img.shields.io/github/actions/workflow/status/Herm71/jc-core-functionality/release.yml?style=flat&logo=github&labelColor=%23362422&color=%23B95B09) ![GitHub issues](https://img.shields.io/github/issues/Herm71/jc-core-functionality?logo=github&labelColor=%23362422&color=%23B95B09)

This is WordPress plugin contains custom functionality for the [Jason Chafin](https://ruthchafininteriordesign.com) [WordPress Block Theme](https://github.com/Herm71/rcid-block-theme). The concept is to keep features of a site that are theme independent, such as custom post-types, taxonomies, and roles separate from the UCSC theme code. This will ensure that future theme changes do not affect a site's functionality.

## Features

This plugin can be expanded as use-cases arise. It currently features the following:

* `disable-xmlrpc.php` -- disables `xml-rpc` and removes from `<head>` to prevent brute force attacks on admin usernames and passwords per [WordPress best practices](https://pantheon.io/docs/wordpress-best-practices#avoid-xml-rpc-attacks).

* `general.php` -- for any general general theme-independent functions

* `gtm.php` -- adds Google Tag Manager and Analytics

* `post-types.php` -- for registering custom post types

* `security-headers.php` -- adds security headers such as Content Security Policy

* `shortcodes.php` -- for custom custom shortcodes
