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

/**
 * Container used when nothing else is configured: the live site's.
 */
const JC_GTM_DEFAULT_CONTAINER_ID = 'GTM-WNP9BDSD';

/**
 * The GTM container ID to load, or '' to load nothing.
 *
 * Defaults to the live site's container. Override in wp-config.php:
 *
 *     define( 'JC_GTM_ID', 'GTM-XXXXXXX' ); // A different container.
 *     define( 'JC_GTM_ID', '' );            // No GTM, e.g. on staging.
 *
 * or with the jc_gtm_container_id filter. Anything that is not a valid
 * container ID is treated as '', so a typo disables GTM rather than
 * printing an arbitrary string into a script.
 *
 * @return string
 */
function jc_gtm_container_id() {
	$id = defined( 'JC_GTM_ID' ) ? (string) JC_GTM_ID : JC_GTM_DEFAULT_CONTAINER_ID;

	/**
	 * Filters the GTM container ID. Return '' to disable GTM.
	 *
	 * @param string $id Container ID, e.g. GTM-WNP9BDSD.
	 */
	$id = (string) apply_filters( 'jc_gtm_container_id', $id );

	return 1 === preg_match( '/^GTM-[A-Z0-9]+$/', $id ) ? $id : '';
}

/**
 * Whether to print the GTM snippets on this request.
 *
 * Skipped for users who can manage options, so the site owner's own
 * visits stay out of analytics.
 *
 * @return bool
 */
function jc_gtm_should_load() {
	return '' !== jc_gtm_container_id() && ! current_user_can( 'manage_options' );
}

/**
 * Print the GTM loader in <head>.
 *
 * Printed through wp_print_inline_script_tag() rather than as raw markup so
 * it passes through the wp_inline_script_attributes filter, which is where
 * the CSP nonce is added. A raw script tag here would be blocked by the
 * nonce-based policy in security-headers.php.
 *
 * @return void
 */
function jc_google_tag_manager_head() {
	if ( ! jc_gtm_should_load() ) {
		return;
	}

	echo "<!-- Google Tag Manager -->\n";
	wp_print_inline_script_tag(
		"(function(w,d,s,l,i){w[l]=w[l]||[];w[l].push({'gtm.start':\n"
		. "new Date().getTime(),event:'gtm.js'});var f=d.getElementsByTagName(s)[0],\n"
		. "j=d.createElement(s),dl=l!='dataLayer'?'&l='+l:'';j.async=true;j.src=\n"
		. "'https://www.googletagmanager.com/gtm.js?id='+i+dl;f.parentNode.insertBefore(j,f);\n"
		// wp_json_encode() quotes and escapes the ID for a JS string context.
		. "})(window,document,'script','dataLayer'," . wp_json_encode( jc_gtm_container_id() ) . ');'
	);
	echo "<!-- End Google Tag Manager -->\n";
}
add_action( 'wp_head', 'jc_google_tag_manager_head', -1 );

/**
 * Print the GTM <noscript> fallback right after <body>.
 *
 * @return void
 */
function jc_google_tag_manager_body() {
	if ( ! jc_gtm_should_load() ) {
		return;
	}

	$src = add_query_arg( 'id', jc_gtm_container_id(), 'https://www.googletagmanager.com/ns.html' );
	?>
	<!-- Google Tag Manager (noscript) -->
	<noscript><iframe src="<?php echo esc_url( $src ); ?>"
	height="0" width="0" style="display:none;visibility:hidden"></iframe></noscript>
	<!-- End Google Tag Manager (noscript) -->
	<?php
}
add_action( 'wp_body_open', 'jc_google_tag_manager_body', -1 );
