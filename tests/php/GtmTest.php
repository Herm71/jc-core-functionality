<?php
/**
 * Tests for the Google Tag Manager snippets.
 *
 * @package jc_Custom_Functionality
 */

use PHPUnit\Framework\Attributes\RunInSeparateProcess;
use PHPUnit\Framework\TestCase;

final class GtmTest extends TestCase {

	protected function setUp(): void {
		WP_Stubs::reset();
	}

	private function head(): string {
		ob_start();
		jc_google_tag_manager_head();
		return ob_get_clean();
	}

	private function body(): string {
		ob_start();
		jc_google_tag_manager_body();
		return ob_get_clean();
	}

	public function test_defaults_to_the_live_container(): void {
		// Updating must not change what the live site sends.
		$this->assertSame( 'GTM-WNP9BDSD', jc_gtm_container_id() );
		$this->assertStringContainsString( "'dataLayer',\"GTM-WNP9BDSD\");", $this->head() );
		$this->assertStringContainsString(
			'src="https://www.googletagmanager.com/ns.html?id=GTM-WNP9BDSD"',
			$this->body()
		);
	}

	public function test_loader_goes_through_the_script_api_and_gets_the_nonce(): void {
		$this->assertStringContainsString( '<script nonce="' . jc_csp_nonce() . '">', $this->head() );
	}

	public function test_skipped_for_administrators(): void {
		WP_Stubs::$caps = array( 'manage_options' );

		$this->assertSame( '', $this->head() );
		$this->assertSame( '', $this->body() );
	}

	public function test_loads_for_logged_in_non_admins(): void {
		WP_Stubs::$caps = array( 'edit_posts' );

		$this->assertNotSame( '', $this->head() );
	}

	public function test_filter_overrides_the_container(): void {
		add_filter(
			'jc_gtm_container_id',
			static function () {
				return 'GTM-TEST123';
			}
		);

		$this->assertStringContainsString( '"GTM-TEST123"', $this->head() );
		$this->assertStringContainsString( 'id=GTM-TEST123', $this->body() );
	}

	/**
	 * @return array<string, array{0: string}>
	 */
	public static function disabled_ids(): array {
		return array(
			'empty'          => array( '' ),
			'lowercase'      => array( 'gtm-abc123' ),
			'not GTM'        => array( 'G-7JP22J8STH' ),
			'script breakout' => array( "GTM-X');alert(1);//" ),
			'markup'         => array( 'GTM-X"><script>alert(1)</script>' ),
		);
	}

	#[\PHPUnit\Framework\Attributes\DataProvider( 'disabled_ids' )]
	public function test_empty_or_invalid_ids_print_nothing( string $id ): void {
		add_filter(
			'jc_gtm_container_id',
			static function () use ( $id ) {
				return $id;
			}
		);

		$this->assertSame( '', jc_gtm_container_id() );
		$this->assertSame( '', $this->head() );
		$this->assertSame( '', $this->body() );
	}

	#[RunInSeparateProcess]
	public function test_constant_overrides_the_default(): void {
		define( 'JC_GTM_ID', 'GTM-STAGING1' );

		$this->assertSame( 'GTM-STAGING1', jc_gtm_container_id() );
		$this->assertStringContainsString( '"GTM-STAGING1"', $this->head() );
	}

	#[RunInSeparateProcess]
	public function test_empty_constant_disables_gtm(): void {
		define( 'JC_GTM_ID', '' );

		$this->assertSame( '', $this->head() );
		$this->assertSame( '', $this->body() );
	}
}
