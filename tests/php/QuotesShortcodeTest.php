<?php
/**
 * Tests for the [quotes] shortcode.
 *
 * @package jc_Custom_Functionality
 */

use PHPUnit\Framework\TestCase;

final class QuotesShortcodeTest extends TestCase {

	protected function setUp(): void {
		WP_Stubs::reset();
		$GLOBALS['jc_test_main_post'] = new WP_Post( 'The page being viewed' );
		$GLOBALS['post']              = $GLOBALS['jc_test_main_post'];
	}

	public function test_is_registered_as_quotes(): void {
		$this->assertSame( 'jc_quotes_loop', WP_Stubs::$shortcodes['quotes'] ?? null );
	}

	public function test_queries_one_random_quote(): void {
		jc_quotes_loop();

		$this->assertSame( 'quote', WP_Query::$last_args['post_type'] );
		$this->assertSame( 'rand', WP_Query::$last_args['orderby'] );
		$this->assertSame( 1, WP_Query::$last_args['posts_per_page'] );
	}

	public function test_renders_the_quote_title(): void {
		WP_Query::$posts = array( new WP_Post( 'Be here now' ) );

		$this->assertSame( '<p>Be here now</p>', jc_quotes_loop() );
	}

	public function test_restores_the_global_post(): void {
		// Regression for #6: wp_reset_postdata() sat after the return.
		WP_Query::$posts = array( new WP_Post( 'Be here now' ) );

		jc_quotes_loop();

		$this->assertSame( 1, WP_Stubs::$reset_postdata_calls );
		$this->assertSame( $GLOBALS['jc_test_main_post'], $GLOBALS['post'] );
	}

	public function test_escapes_the_title(): void {
		WP_Query::$posts = array( new WP_Post( '<script>alert(1)</script>' ) );

		$this->assertSame(
			'<p>&lt;script&gt;alert(1)&lt;/script&gt;</p>',
			jc_quotes_loop()
		);
	}

	public function test_keeps_texturized_entities_intact(): void {
		// get_the_title() runs wptexturize; escaping must not double-encode.
		WP_Query::$posts = array( new WP_Post( 'Don&#8217;t panic' ) );

		$this->assertSame( '<p>Don&#8217;t panic</p>', jc_quotes_loop() );
	}

	public function test_renders_nothing_without_quotes(): void {
		$this->assertSame( '', jc_quotes_loop() );
		$this->assertSame( 1, WP_Stubs::$reset_postdata_calls );
	}
}
