<?php
/**
 * Tests for the jc/copyright and jc/user-data block bindings sources.
 *
 * @package jc_Custom_Functionality
 */

use PHPUnit\Framework\TestCase;

final class BlockBindingsTest extends TestCase {

	protected function setUp(): void {
		WP_Stubs::reset();
		WP_Stubs::$user_meta = array(
			7 => array(
				'display_name' => 'Jason <b>Chafin</b>',
				'description'  => 'Writes <a href="https://example.com">here</a>. <script>alert(1)</script>',
				'avatar'       => 'https://example.com/a.png?s=96&d=mm',
			),
		);
	}

	public function test_copyright_uses_the_site_timezone_year(): void {
		// date() would return PHP's (UTC) year; the stub year proves wp_date() ran.
		$this->assertSame( '&copy; 2031', jc_copyright_binding() );
		$this->assertSame( array( 'Y' ), WP_Stubs::$wp_date_calls );
	}

	public function test_description_keeps_links_and_drops_scripts(): void {
		// Regression for #11: description was returned raw.
		$bio = jc_user_data_bindings(
			array(
				'key'    => 'description',
				'userId' => 7,
			)
		);

		$this->assertStringContainsString( '<a href="https://example.com">here</a>', $bio );
		$this->assertStringNotContainsString( '<script', $bio );
	}

	public function test_name_is_escaped(): void {
		$this->assertSame(
			'Jason &lt;b&gt;Chafin&lt;/b&gt;',
			jc_user_data_bindings(
				array(
					'key'    => 'name',
					'userId' => 7,
				)
			)
		);
	}

	public function test_avatar_is_a_url(): void {
		$this->assertSame(
			'https://example.com/a.png?s=96&amp;d=mm',
			jc_user_data_bindings(
				array(
					'key'    => 'avatar',
					'userId' => 7,
				)
			)
		);
	}

	/**
	 * @return array<string, array{0: array}>
	 */
	public static function invalid_args(): array {
		return array(
			'no key'      => array( array( 'userId' => 7 ) ),
			'no user'     => array( array( 'key' => 'name' ) ),
			'user zero'   => array(
				array(
					'key'    => 'name',
					'userId' => 0,
				),
			),
			'unknown key' => array(
				array(
					'key'    => 'email',
					'userId' => 7,
				),
			),
		);
	}

	#[\PHPUnit\Framework\Attributes\DataProvider( 'invalid_args' )]
	public function test_returns_null_for_invalid_args( array $args ): void {
		$this->assertNull( jc_user_data_bindings( $args ) );
	}
}
