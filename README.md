# Jason Chafin WordPress Core Functionality Plugin

![GitHub Release](https://img.shields.io/github/v/release/Herm71/jc-core-functionality?logo=github)
 ![GitHub Actions Workflow Status](https://img.shields.io/github/actions/workflow/status/Herm71/jc-core-functionality/release.yml?logo=github)
![GitHub issues](https://img.shields.io/github/issues/Herm71/jc-core-functionality?logo=github)

This is WordPress plugin contains custom functionality for the [Jason Chafin](https://jasonchafin.com) WordPress website. The concept is to keep features of a site that are theme independent, such as custom post-types, taxonomies, and roles separate from the theme code. This will ensure that future theme changes do not affect a site's functionality.

## Features

This plugin can be expanded as use-cases arise. It currently features the following:

* `disable-xmlrpc.php` -- disables `xml-rpc` and removes from `<head>` to prevent brute force attacks on admin usernames and passwords per [WordPress best practices](https://pantheon.io/docs/wordpress-best-practices#avoid-xml-rpc-attacks).

* `general.php` -- general theme-independent functions

* `gtm.php` -- Google Tag Manager and Analytics

* `security-headers.php` -- security headers such as Content Security Policy

## Author

[@Herm71](https://github.com/Herm71/)
