<?php
/**
 * Tests for the CSP violation report endpoint.
 *
 * @package jc_Custom_Functionality
 */

use PHPUnit\Framework\TestCase;

final class CspReportTest extends TestCase {

	protected function setUp(): void {
		WP_Stubs::reset();
	}

	/**
	 * PHPUnit points error_log at its own temp file for each test.
	 */
	private function log_lines(): array {
		$file = (string) ini_get( 'error_log' );
		return is_file( $file ) ? file( $file, FILE_IGNORE_NEW_LINES ) : array();
	}

	/**
	 * @return array<int, array<string, string>> Logged summaries, decoded.
	 */
	private function logged(): array {
		$entries = array();
		foreach ( $this->log_lines() as $line ) {
			$pos = strpos( $line, '[jc-csp] ' );
			if ( false !== $pos ) {
				$entries[] = json_decode( substr( $line, $pos + 9 ), true );
			}
		}
		return $entries;
	}

	private function post( string $body ): WP_REST_Response {
		return jc_csp_receive_report( new WP_REST_Request( $body ) );
	}

	private static function legacy( array $overrides = array() ): string {
		return json_encode(
			array(
				'csp-report' => $overrides + array(
					'document-uri'        => 'https://jasonchafin.com/about/',
					'effective-directive' => 'script-src-elem',
					'blocked-uri'         => 'https://evil.example/x.js',
					'source-file'         => 'https://jasonchafin.com/about/',
					'line-number'         => 12,
					'disposition'         => 'report',
				),
			)
		);
	}

	public function test_route_is_public_post_only(): void {
		$this->assertCount( 1, WP_Stubs::$rest_routes );
		list( $namespace, $route, $args ) = WP_Stubs::$rest_routes[0];

		$this->assertSame( 'jc/v1', $namespace );
		$this->assertSame( '/csp-report', $route );
		$this->assertSame( 'POST', $args['methods'] );
		$this->assertSame( '__return_true', $args['permission_callback'] );
	}

	public function test_logs_a_report_uri_violation(): void {
		$this->expectErrorLog();
		$response = $this->post( self::legacy() );

		$this->assertSame( 204, $response->get_status() );
		$this->assertSame(
			array(
				array(
					'page'        => 'https://jasonchafin.com/about/',
					'directive'   => 'script-src-elem',
					'blocked'     => 'https://evil.example/x.js',
					'source'      => 'https://jasonchafin.com/about/',
					'line'        => '12',
					'disposition' => 'report',
				),
			),
			$this->logged()
		);
	}

	public function test_logs_reporting_api_violations_and_skips_other_types(): void {
		$this->expectErrorLog();
		$this->post(
			json_encode(
				array(
					array(
						'type' => 'deprecation',
						'body' => array( 'id' => 'x' ),
					),
					array(
						'type' => 'csp-violation',
						'body' => array(
							'documentURL'        => 'https://jasonchafin.com/',
							'effectiveDirective' => 'img-src',
							'blockedURL'         => 'https://tracker.example/p.gif',
						),
					),
				)
			)
		);

		$this->assertSame(
			array(
				array(
					'page'      => 'https://jasonchafin.com/',
					'directive' => 'img-src',
					'blocked'   => 'https://tracker.example/p.gif',
				),
			),
			$this->logged()
		);
	}

	public function test_caps_reports_per_minute(): void {
		$this->expectErrorLog();
		for ( $i = 0; $i < JC_CSP_REPORTS_PER_MINUTE + 5; $i++ ) {
			$this->assertSame( 204, $this->post( self::legacy() )->get_status() );
		}

		$this->assertCount( JC_CSP_REPORTS_PER_MINUTE, $this->logged() );
	}

	public function test_a_report_cannot_inject_log_lines(): void {
		$this->expectErrorLog();
		$this->post( self::legacy( array( 'blocked-uri' => "x\n[jc-csp] {\"forged\":true}" ) ) );

		$this->assertCount( 1, $this->log_lines() );
		$this->assertSame( "x\n[jc-csp] {\"forged\":true}", $this->logged()[0]['blocked'] );
	}

	public function test_long_fields_are_truncated(): void {
		$this->expectErrorLog();
		$this->post( self::legacy( array( 'blocked-uri' => str_repeat( 'a', 5000 ) ) ) );

		$this->assertSame( 300, strlen( $this->logged()[0]['blocked'] ) );
	}

	/**
	 * @return array<string, array{0: string}>
	 */
	public static function ignored_bodies(): array {
		return array(
			'empty'         => array( '' ),
			'not json'      => array( 'csp-report=1' ),
			'json scalar'   => array( '42' ),
			'no violations' => array( '{"hello":"world"}' ),
			'oversized'     => array( self::legacy( array( 'script-sample' => str_repeat( 'a', JC_CSP_REPORT_MAX_BYTES ) ) ) ),
		);
	}

	#[\PHPUnit\Framework\Attributes\DataProvider( 'ignored_bodies' )]
	public function test_ignores_bodies_that_are_not_reports( string $body ): void {
		$this->assertSame( 204, $this->post( $body )->get_status() );
		$this->assertSame( array(), $this->logged() );
	}
}
