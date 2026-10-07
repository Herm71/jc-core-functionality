<?php
/**
 * Tests for the security headers and Content Security Policy.
 *
 * @package jc_Custom_Functionality
 */

use PHPUnit\Framework\TestCase;

final class SecurityHeadersTest extends TestCase {

	protected function setUp(): void {
		WP_Stubs::reset();
	}

	/**
	 * Run the wp_headers filter chain the way WordPress does.
	 *
	 * @return array<string, string>
	 */
	private function headers(): array {
		return apply_filters( 'wp_headers', array() );
	}

	/**
	 * Parse a policy string into directive => sources.
	 *
	 * @return array<string, string[]>
	 */
	private function parse( string $policy ): array {
		$directives = array();
		foreach ( array_filter( array_map( 'trim', explode( ';', $policy ) ) ) as $part ) {
			$tokens                         = preg_split( '/\s+/', $part );
			$directives[ array_shift( $tokens ) ] = $tokens;
		}
		return $directives;
	}

	private function policy(): array {
		return $this->parse( $this->headers()['Content-Security-Policy-Report-Only'] );
	}

	public function test_ships_report_only_with_an_enforced_baseline(): void {
		$headers = $this->headers();

		$this->assertArrayHasKey( 'Content-Security-Policy-Report-Only', $headers );
		$this->assertSame(
			array(
				'object-src'      => array( "'none'" ),
				'base-uri'        => array( "'self'" ),
				'frame-ancestors' => array( "'self'" ),
			),
			$this->parse( $headers['Content-Security-Policy'] )
		);
	}

	public function test_enforces_the_full_policy_when_report_only_is_off(): void {
		add_filter( 'jc_csp_report_only', '__return_false' );

		$headers = $this->headers();

		$this->assertArrayNotHasKey( 'Content-Security-Policy-Report-Only', $headers );
		$this->assertArrayHasKey( 'script-src', $this->parse( $headers['Content-Security-Policy'] ) );
	}

	public function test_scripts_are_trusted_by_nonce_not_inline(): void {
		$script_src = $this->policy()['script-src'];

		$this->assertContains( "'nonce-" . jc_csp_nonce() . "'", $script_src );
		$this->assertContains( "'strict-dynamic'", $script_src );
		$this->assertNotContains( "'unsafe-inline'", $script_src );
		$this->assertNotContains( "'unsafe-eval'", $script_src );
	}

	public function test_nonce_is_128_bits_and_stable_within_a_request(): void {
		$this->assertSame( 16, strlen( base64_decode( jc_csp_nonce(), true ) ) );
		$this->assertSame( jc_csp_nonce(), jc_csp_nonce() );
	}

	public function test_no_wildcard_or_plain_http_sources(): void {
		// Regression for #9: default-src allowed http://* and any host.
		foreach ( $this->policy() as $directive => $sources ) {
			foreach ( $sources as $source ) {
				$this->assertStringStartsNotWith( 'http:', $source, $directive );
				$this->assertNotSame( '*', $source, $directive );
			}
		}
		$this->assertSame( array( "'self'" ), $this->policy()['default-src'] );
	}

	public function test_no_hosts_inherited_from_the_ucsc_plugin(): void {
		$policy = $this->headers()['Content-Security-Policy-Report-Only'];

		foreach ( array( 'ucsc', 'siteimprove', 'pantheonsite', 'netlify', 'unpkg', 'fontawesome', 'youtube' ) as $host ) {
			$this->assertStringNotContainsStringIgnoringCase( $host, $policy );
		}
	}

	public function test_worker_src_allows_blob_urls(): void {
		// Regression: the old policy read "blob" without the colon.
		$this->assertContains( 'blob:', $this->policy()['worker-src'] );
	}

	public function test_allows_what_the_site_loads(): void {
		$policy = $this->policy();

		$this->assertContains( 'https://www.googletagmanager.com', $policy['frame-src'] );
		$this->assertContains( 'https://secure.gravatar.com', $policy['img-src'] );
		$this->assertContains( 'https://s.w.org', $policy['img-src'] );
		$this->assertContains( 'https://*.google-analytics.com', $policy['connect-src'] );
		$this->assertContains( 'https://*.analytics.google.com', $policy['connect-src'] );
		// Regression: *.analytics.google.com does not match the bare host GA4 posts to.
		$this->assertContains( 'https://analytics.google.com', $policy['connect-src'] );
		$this->assertSame( array( "'none'" ), $policy['object-src'] );
	}

	public function test_sends_reports_to_the_endpoint(): void {
		$headers = $this->headers();
		$policy  = $this->policy();

		$this->assertSame( array( 'https://example.test/wp-json/jc/v1/csp-report' ), $policy['report-uri'] );
		$this->assertSame( array( 'csp-endpoint' ), $policy['report-to'] );
		$this->assertSame(
			'csp-endpoint="https://example.test/wp-json/jc/v1/csp-report"',
			$headers['Reporting-Endpoints']
		);
	}

	public function test_other_security_headers(): void {
		$headers = $this->headers();

		$this->assertSame( 'strict-origin-when-cross-origin', $headers['Referrer-Policy'] );
		$this->assertSame( 'nosniff', $headers['X-Content-Type-Options'] );
		$this->assertSame( 'SAMEORIGIN', $headers['X-Frame-Options'] );
		$this->assertSame( 'geolocation=(), microphone=(), camera=()', $headers['Permissions-Policy'] );
		$this->assertArrayNotHasKey( 'X-XSS-Protection', $headers );
	}

	public function test_script_api_tags_get_the_nonce(): void {
		$this->assertSame(
			array(
				'id'    => 'gtm',
				'nonce' => jc_csp_nonce(),
			),
			apply_filters( 'wp_inline_script_attributes', array( 'id' => 'gtm' ) )
		);
		$this->assertSame( jc_csp_nonce(), apply_filters( 'wp_script_attributes', array() )['nonce'] );
	}

	/**
	 * @return array<string, array{0: string}>
	 */
	public static function excluded_contexts(): array {
		return array(
			'admin'              => array( 'is_admin' ),
			'customizer preview' => array( 'is_customize_preview' ),
		);
	}

	#[\PHPUnit\Framework\Attributes\DataProvider( 'excluded_contexts' )]
	public function test_excluded_contexts_get_no_headers_or_nonces( string $flag ): void {
		WP_Stubs::${$flag} = true;

		$this->assertSame( array(), $this->headers() );
		$this->assertSame( array(), apply_filters( 'wp_inline_script_attributes', array() ) );
		$this->assertSame( array(), apply_filters( 'wp_script_attributes', array() ) );
	}
}
