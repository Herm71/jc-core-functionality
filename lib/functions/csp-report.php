<?php
/**
 * CSP violation reports
 *
 * Receives Content Security Policy violation reports at
 * /wp-json/jc/v1/csp-report and writes a one-line summary of each to the PHP
 * error log, so a Report-Only policy can be checked against real traffic
 * before it is enforced.
 *
 * Accepts both formats browsers send: the legacy report-uri body
 * (application/csp-report, {"csp-report": {...}}) and the Reporting API
 * (application/reports+json, [{"type": "csp-violation", "body": {...}}]).
 *
 * @package   jc_Custom_Functionality
 * @since     1.1.0
 * @link      https://github.com/Herm71/jc-core-functionality
 * @author    Jason Chafin
 * @license   GPL-2.0-or-later
 */

// Block direct access.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Reports logged per minute, across all visitors.
 *
 * The endpoint is necessarily public, so this caps how fast anyone can write
 * to the error log through it.
 */
const JC_CSP_REPORTS_PER_MINUTE = 30;

/**
 * Largest request body accepted, in bytes. Real reports are well under 4 KB.
 */
const JC_CSP_REPORT_MAX_BYTES = 16384;

/**
 * Absolute URL of the report endpoint.
 *
 * @return string
 */
function jc_csp_report_url() {
	return rest_url( 'jc/v1/csp-report' );
}

/**
 * Register the report endpoint.
 *
 * @return void
 */
function jc_csp_register_report_route() {
	register_rest_route(
		'jc/v1',
		'/csp-report',
		array(
			'methods'             => 'POST',
			'callback'            => 'jc_csp_receive_report',
			// Browsers send reports without credentials; the endpoint must be public.
			'permission_callback' => '__return_true',
		)
	);
}
add_action( 'rest_api_init', 'jc_csp_register_report_route' );

/**
 * Point the policy at the endpoint, in both reporting formats.
 *
 * @param array<string, string[]> $directives Directive => sources.
 * @return array<string, string[]>
 */
function jc_csp_report_directives( $directives ) {
	$directives['report-uri'] = array( jc_csp_report_url() );
	$directives['report-to']  = array( 'csp-endpoint' );

	return $directives;
}
add_filter( 'jc_csp_directives', 'jc_csp_report_directives' );

/**
 * Declare the Reporting API endpoint named by report-to.
 *
 * @param array<string, string> $headers Headers WordPress is about to send.
 * @return array<string, string>
 */
function jc_csp_reporting_endpoints_header( $headers ) {
	if ( function_exists( 'jc_csp_applies' ) && jc_csp_applies() ) {
		$headers['Reporting-Endpoints'] = 'csp-endpoint="' . jc_csp_report_url() . '"';
	}

	return $headers;
}
add_filter( 'wp_headers', 'jc_csp_reporting_endpoints_header' );

/**
 * Extract violation bodies from either report format.
 *
 * @param mixed $payload Decoded JSON request body.
 * @return array<int, array<string, mixed>>
 */
function jc_csp_extract_violations( $payload ) {
	if ( ! is_array( $payload ) ) {
		return array();
	}

	// report-uri: a single object wrapped in "csp-report".
	if ( isset( $payload['csp-report'] ) && is_array( $payload['csp-report'] ) ) {
		return array( $payload['csp-report'] );
	}

	// Reporting API: a list of typed reports.
	$violations = array();
	foreach ( $payload as $report ) {
		if (
			is_array( $report )
			&& 'csp-violation' === ( $report['type'] ?? '' )
			&& isset( $report['body'] ) && is_array( $report['body'] )
		) {
			$violations[] = $report['body'];
		}
	}

	return $violations;
}

/**
 * Reduce a violation to the fields worth logging, under one naming scheme.
 *
 * The report-uri format uses kebab-case keys; the Reporting API uses camelCase.
 *
 * @param array<string, mixed> $violation Raw violation body.
 * @return array<string, string>
 */
function jc_csp_summarize_violation( array $violation ) {
	$fields = array(
		'page'        => array( 'document-uri', 'documentURL' ),
		'directive'   => array( 'effective-directive', 'effectiveDirective', 'violated-directive' ),
		'blocked'     => array( 'blocked-uri', 'blockedURL' ),
		'source'      => array( 'source-file', 'sourceFile' ),
		'line'        => array( 'line-number', 'lineNumber' ),
		'sample'      => array( 'script-sample', 'sample' ),
		'disposition' => array( 'disposition' ),
	);

	$summary = array();
	foreach ( $fields as $name => $keys ) {
		foreach ( $keys as $key ) {
			if ( isset( $violation[ $key ] ) && is_scalar( $violation[ $key ] ) && '' !== (string) $violation[ $key ] ) {
				// Visitor-supplied: cap length. JSON encoding at log time escapes newlines.
				$summary[ $name ] = substr( (string) $violation[ $key ], 0, 300 );
				break;
			}
		}
	}

	return $summary;
}

/**
 * Claim one slot in this minute's logging budget.
 *
 * @return bool False once the per-minute cap is reached.
 */
function jc_csp_take_report_slot() {
	$key   = 'jc_csp_reports_' . gmdate( 'YmdHi' );
	$count = (int) get_transient( $key );

	if ( $count >= JC_CSP_REPORTS_PER_MINUTE ) {
		return false;
	}

	set_transient( $key, $count + 1, 2 * MINUTE_IN_SECONDS );
	return true;
}

/**
 * Handle a report POST.
 *
 * Always answers 204: browsers ignore the response, and a uniform reply
 * gives nothing away about rate limiting or parsing.
 *
 * @param WP_REST_Request $request Incoming request.
 * @return WP_REST_Response
 */
function jc_csp_receive_report( $request ) {
	$body = $request->get_body();

	if ( '' !== $body && strlen( $body ) <= JC_CSP_REPORT_MAX_BYTES ) {
		foreach ( jc_csp_extract_violations( json_decode( $body, true ) ) as $violation ) {
			if ( ! jc_csp_take_report_slot() ) {
				break;
			}
			// phpcs:ignore WordPress.PHP.DevelopmentFunctions.error_log_error_log -- Logging reports is this endpoint's purpose.
			error_log( '[jc-csp] ' . wp_json_encode( jc_csp_summarize_violation( $violation ) ) );
		}
	}

	return new WP_REST_Response( null, 204 );
}
