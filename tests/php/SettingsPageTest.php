<?php
/**
 * Tests for the plugin info page.
 *
 * @package jc_Custom_Functionality
 */

use PHPUnit\Framework\TestCase;

final class SettingsPageTest extends TestCase {

	protected function setUp(): void {
		WP_Stubs::reset();
		WP_Stubs::$plugin_data = array(
			'Version'     => '1.0.0',
			'Description' => 'Contains custom functionality.',
		);
	}

	private function render(): string {
		ob_start();
		jc_render_plugin_settings_page();
		return ob_get_clean();
	}

	/**
	 * Parse the page and return its document, failing on malformed markup.
	 */
	private function parse( string $html ): DOMDocument {
		$doc      = new DOMDocument();
		$previous = libxml_use_internal_errors( true );
		$doc->loadXML( '<root>' . str_replace( '<hr>', '<hr/>', $html ) . '</root>' );
		$errors = libxml_get_errors();
		libxml_clear_errors();
		libxml_use_internal_errors( $previous );

		$this->assertSame( array(), $errors, 'Settings page markup is not well-formed' );
		return $doc;
	}

	public function test_markup_is_balanced_inside_a_wrap(): void {
		// Regression for #7: a </div> closed nothing, and an <li> never closed.
		$doc  = $this->parse( $this->render() );
		$root = $doc->documentElement;

		$this->assertSame( 1, $root->childElementCount );
		$this->assertSame( 'div', $root->firstElementChild->tagName );
		$this->assertSame( 'wrap', $root->firstElementChild->getAttribute( 'class' ) );
	}

	public function test_requests_plain_header_values(): void {
		$this->render();

		$this->assertSame( false, WP_Stubs::$plugin_data_calls[0]['markup'] );
	}

	public function test_escapes_header_values(): void {
		WP_Stubs::$plugin_data = array(
			'Version'     => '1.0.0<script>',
			'Description' => 'Tom & "Jerry"',
		);

		$html = $this->render();

		$this->assertStringContainsString( 'Version: 1.0.0&lt;script&gt;', $html );
		$this->assertStringContainsString( 'Tom &amp; &quot;Jerry&quot;', $html );
		$this->assertStringNotContainsString( '<script>', $html );
	}

	public function test_block_binding_example_is_shown_as_text(): void {
		// Regression for #7: the example was emitted as a real HTML comment.
		$doc = $this->parse( $this->render() );
		$pre = $doc->getElementsByTagName( 'pre' )->item( 0 );

		$this->assertStringContainsString(
			'<!-- wp:paragraph {"metadata":{"bindings":{"content":{"source":"jc/copyright"}}}} -->',
			$pre->textContent
		);
		$this->assertSame( 0, $pre->getElementsByTagName( 'p' )->length );
	}
}
