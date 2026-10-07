<?php
/**
 * Google Tag Manager
 *
 * Adds the Google Tag Manager snippets (and through them Google Analytics) to the site.
 *
 * @package   jc_Custom_Functionality
 * @since     1.0.0
 * @link      https://github.com/Herm71/jc-core-functionality
 * @author    Jason Chafin
 * @copyright Copyright (c) 2011, Jason Chafin
 * @license   GPL-2.0-or-later
 */

// Block direct access.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

add_action( 'wp_head', 'jc_google_tag_manager_head', -1 );
add_action( 'wp_body_open', 'jc_google_tag_manager_body', -1 );

/**
 * Print the GTM loader in <head>.
 *
 * Printed through wp_print_inline_script_tag() rather than as raw markup so
 * it passes through the wp_inline_script_attributes filter, which is where
 * the CSP nonce is added. A raw <script> here would be blocked by the
 * nonce-based policy in security-headers.php.
 */
function jc_google_tag_manager_head() {
	echo "<!-- Google Tag Manager -->\n";
	wp_print_inline_script_tag(
		"(function(w,d,s,l,i){w[l]=w[l]||[];w[l].push({'gtm.start':\n"
		. "new Date().getTime(),event:'gtm.js'});var f=d.getElementsByTagName(s)[0],\n"
		. "j=d.createElement(s),dl=l!='dataLayer'?'&l='+l:'';j.async=true;j.src=\n"
		. "'https://www.googletagmanager.com/gtm.js?id='+i+dl;f.parentNode.insertBefore(j,f);\n"
		. "})(window,document,'script','dataLayer','GTM-WNP9BDSD');"
	);
	echo "<!-- End Google Tag Manager -->\n";
}

/**
 * Print the GTM <noscript> fallback right after <body>.
 */
function jc_google_tag_manager_body() {
	?>
	<!-- Google Tag Manager (noscript) -->
	<noscript><iframe src="https://www.googletagmanager.com/ns.html?id=GTM-WNP9BDSD"
	height="0" width="0" style="display:none;visibility:hidden"></iframe></noscript>
	<!-- End Google Tag Manager (noscript) -->
	<?php
}
