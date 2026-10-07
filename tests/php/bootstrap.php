<?php
/**
 * PHPUnit bootstrap: minimal WordPress stubs.
 *
 * Just enough of the WordPress API for the plugin's feature files to load and
 * run outside WordPress. Each stub records what it was called with so tests
 * can assert on behavior, not just output.
 *
 * @package jc_Custom_Functionality
 */

define( 'ABSPATH', __DIR__ . '/' );
define( 'JC_DIR', dirname( __DIR__, 2 ) );

require_once dirname( __DIR__, 2 ) . '/vendor/autoload.php';

/**
 * Shared stub state, reset between tests.
 */
final class WP_Stubs {
	/** @var array<string, callable> Registered shortcodes. */
	public static array $shortcodes = array();

	/** @var array<string, string> Header values returned by get_plugin_data(). */
	public static array $plugin_data = array();

	/** @var array<int, array{markup: bool, translate: bool}> get_plugin_data() calls. */
	public static array $plugin_data_calls = array();

	/** @var int Number of wp_reset_postdata() calls. */
	public static int $reset_postdata_calls = 0;

	/** @var array<int, array<string, string>> User meta by user ID. */
	public static array $user_meta = array();

	/** @var string[] Formats passed to wp_date(). */
	public static array $wp_date_calls = array();

	/** @var string What wp_date() returns, standing in for the site-timezone year. */
	public static string $wp_date_year = '2031';

	public static function reset(): void {
		self::$plugin_data          = array();
		self::$plugin_data_calls    = array();
		self::$reset_postdata_calls = 0;
		self::$user_meta            = array();
		self::$wp_date_calls        = array();
		WP_Query::$posts            = array();
		WP_Query::$last_args        = array();
		$GLOBALS['post']            = null;
	}
}

/**
 * Stand-in post object.
 */
final class WP_Post {
	public function __construct( public string $post_title ) {}
}

/**
 * Stand-in WP_Query that iterates a fixed list of posts and, like the real
 * one, overwrites the global $post on the_post().
 */
class WP_Query {
	/** @var WP_Post[] Posts every query returns. */
	public static array $posts = array();

	/** @var array Arguments of the most recent query. */
	public static array $last_args = array();

	private int $index = 0;

	public function __construct( array $args ) {
		self::$last_args = $args;
	}

	public function have_posts(): bool {
		return $this->index < count( self::$posts );
	}

	public function the_post(): void {
		$GLOBALS['post'] = self::$posts[ $this->index++ ];
	}
}

function add_action( ...$args ) {}
function add_filter( ...$args ) {}

function add_shortcode( string $tag, callable $callback ): void {
	WP_Stubs::$shortcodes[ $tag ] = $callback;
}

function esc_html( $text ): string {
	// WordPress does not double-encode existing entities.
	return htmlspecialchars( (string) $text, ENT_QUOTES, 'UTF-8', false );
}

function get_the_title(): string {
	return $GLOBALS['post']->post_title;
}

function wp_reset_postdata(): void {
	++WP_Stubs::$reset_postdata_calls;
	$GLOBALS['post'] = $GLOBALS['jc_test_main_post'] ?? null;
}

function get_plugin_data( string $file, bool $markup = true, bool $translate = true ): array {
	WP_Stubs::$plugin_data_calls[] = array(
		'markup'    => $markup,
		'translate' => $translate,
	);
	return WP_Stubs::$plugin_data;
}

function __( $text, $domain = 'default' ) {
	return $text;
}

function absint( $value ): int {
	return abs( (int) $value );
}

function esc_url( $url ): string {
	return htmlspecialchars( (string) $url, ENT_QUOTES, 'UTF-8', false );
}

/**
 * Approximates wp_kses_post(): drops script/style elements and on* handlers,
 * keeps ordinary post markup such as links and emphasis.
 */
function wp_kses_post( $content ): string {
	$content = preg_replace( '#<(script|style)\b[^>]*>.*?</\1>#is', '', (string) $content );
	return preg_replace( '#\s+on[a-z]+\s*=\s*("[^"]*"|\'[^\']*\'|[^\s>]+)#i', '', $content );
}

function wp_date( string $format ): string {
	WP_Stubs::$wp_date_calls[] = $format;
	return WP_Stubs::$wp_date_year;
}

function get_the_author_meta( string $field, int $user_id ): string {
	return WP_Stubs::$user_meta[ $user_id ][ $field ] ?? '';
}

function get_avatar_url( int $user_id ): string {
	return WP_Stubs::$user_meta[ $user_id ]['avatar'] ?? '';
}

function register_meta( ...$args ) {}
function register_block_bindings_source( ...$args ) {}

require_once JC_DIR . '/lib/functions/general.php';
require_once JC_DIR . '/lib/functions/shortcodes.php';
require_once JC_DIR . '/lib/functions/settings.php';
