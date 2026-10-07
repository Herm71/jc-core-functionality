<?php
/**
 * Security Headers
 *
 * Adds security headers, including the Content Security Policy, to front-end responses.
 *
 * @see https://pantheon.io/docs/wordpress-best-practices#security-headers
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
 * Whether the CSP and its nonces apply to the current request.
 *
 * Admin screens and the Customizer preview print scripts outside the script
 * API, so a nonce-based policy would break them.
 *
 * @return bool
 */
function jc_csp_applies() {
	return ! is_admin() && ! is_customize_preview();
}

/**
 * The CSP nonce for this request.
 *
 * Generated once and reused, so the header and every script tag on the page
 * carry the same value.
 *
 * @return string Base64-encoded 128-bit nonce.
 */
function jc_csp_nonce() {
	static $nonce = null;

	if ( null === $nonce ) {
		$nonce = base64_encode( random_bytes( 16 ) ); // phpcs:ignore WordPress.PHP.DiscouragedPHPFunctions.obfuscation_base64_encode -- CSP nonces are base64 by spec.
	}

	return $nonce;
}

/**
 * Whether to send the policy as Content-Security-Policy-Report-Only.
 *
 * True until violation reports confirm nothing legitimate is blocked. To
 * enforce, return false:
 *
 *     add_filter( 'jc_csp_report_only', '__return_false' );
 *
 * @return bool
 */
function jc_csp_report_only() {
	return (bool) apply_filters( 'jc_csp_report_only', true );
}

/**
 * The Content Security Policy, as directive => sources.
 *
 * Built from an inventory of every page in the sitemap plus runtime capture
 * of what GTM loads (October 2026). Scripts are trusted by nonce, and
 * 'strict-dynamic' extends that trust to what they load (gtm.js, gtag.js,
 * script modules), so script-src needs no host list. 'self' and https: are
 * fallbacks that CSP3 browsers ignore when 'strict-dynamic' is present.
 *
 * Styles keep 'unsafe-inline': block supports emit dozens of inline <style>
 * blocks and style attributes per page, and none of them can carry a nonce.
 *
 * @return array<string, string[]>
 */
function jc_csp_directives() {
	$google_analytics = array(
		'https://*.google-analytics.com',
		'https://*.analytics.google.com',
		'https://*.googletagmanager.com',
		'https://stats.g.doubleclick.net',
		'https://www.google.com',
	);

	$directives = array(
		'default-src'     => array( "'self'" ),
		'script-src'      => array( "'nonce-" . jc_csp_nonce() . "'", "'strict-dynamic'", "'self'", 'https:' ),
		'style-src'       => array( "'self'", "'unsafe-inline'" ),
		'img-src'         => array_merge(
			array( "'self'", 'data:', 'https://secure.gravatar.com', 'https://s.w.org' ),
			$google_analytics
		),
		'font-src'        => array( "'self'", 'data:' ),
		'connect-src'     => array_merge( array( "'self'" ), $google_analytics ),
		'frame-src'       => array( "'self'", 'https://www.googletagmanager.com' ),
		'worker-src'      => array( "'self'", 'blob:' ),
		'object-src'      => array( "'none'" ),
		'base-uri'        => array( "'self'" ),
		'form-action'     => array( "'self'" ),
		'frame-ancestors' => array( "'self'" ),
	);

	/**
	 * Filters the CSP directives before they are serialized.
	 *
	 * @param array<string, string[]> $directives Directive => sources.
	 */
	return apply_filters( 'jc_csp_directives', $directives );
}

/**
 * Serialize directives into a policy string.
 *
 * @param array<string, string[]> $directives Directive => sources.
 * @return string
 */
function jc_csp_build( array $directives ) {
	$parts = array();

	foreach ( $directives as $name => $sources ) {
		$parts[] = trim( $name . ' ' . implode( ' ', array_unique( $sources ) ) );
	}

	return implode( '; ', $parts );
}

/**
 * Add security headers to front-end responses.
 *
 * While the policy is Report-Only, a minimal baseline is still enforced:
 * the parts of the old policy that cannot break page content.
 *
 * @param array<string, string> $headers Headers WordPress is about to send.
 * @return array<string, string>
 */
function jc_additional_securityheaders( $headers ) {
	if ( ! jc_csp_applies() ) {
		return $headers;
	}

	$headers['Referrer-Policy']        = 'strict-origin-when-cross-origin';
	$headers['X-Content-Type-Options'] = 'nosniff';
	$headers['Permissions-Policy']     = 'geolocation=(), microphone=(), camera=()';
	$headers['X-Frame-Options']        = 'SAMEORIGIN';

	$policy = jc_csp_build( jc_csp_directives() );

	if ( jc_csp_report_only() ) {
		$headers['Content-Security-Policy']             = "object-src 'none'; base-uri 'self'; frame-ancestors 'self'";
		$headers['Content-Security-Policy-Report-Only'] = $policy;
	} else {
		$headers['Content-Security-Policy'] = $policy;
	}

	return $headers;
}
add_filter( 'wp_headers', 'jc_additional_securityheaders' );

/**
 * Add the CSP nonce to scripts printed through the script API.
 *
 * Covers classic and module scripts (wp_script_attributes) and inline
 * scripts, the import map and speculation rules (wp_inline_script_attributes).
 * Scripts printed as raw markup do not pass through these filters and are
 * blocked once the policy is enforced.
 *
 * @param array<string, string|bool> $attributes Script tag attributes.
 * @return array<string, string|bool>
 */
function jc_csp_script_nonce( $attributes ) {
	if ( jc_csp_applies() ) {
		$attributes['nonce'] = jc_csp_nonce();
	}

	return $attributes;
}
add_filter( 'wp_script_attributes', 'jc_csp_script_nonce' );
add_filter( 'wp_inline_script_attributes', 'jc_csp_script_nonce' );
