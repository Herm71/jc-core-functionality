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

	/** @var array<string, callable[]> Registered filters and actions, by hook. */
	public static array $filters = array();

	/** @var array<string, callable[]> Filters registered by the plugin files at load. */
	public static array $plugin_filters = array();

	/** @var array<string, mixed> Transients by key. */
	public static array $transients = array();

	/** @var bool What is_admin() returns. */
	public static bool $is_admin = false;

	/** @var bool What is_customize_preview() returns. */
	public static bool $is_customize_preview = false;

	/** @var array<int, array{0: string, 1: string, 2: array}> register_rest_route() calls. */
	public static array $rest_routes = array();

	public static function reset(): void {
		self::$plugin_data          = array();
		self::$plugin_data_calls    = array();
		self::$reset_postdata_calls = 0;
		self::$user_meta            = array();
		self::$wp_date_calls        = array();
		self::$filters              = self::$plugin_filters;
		self::$transients           = array();
		self::$is_admin             = false;
		self::$is_customize_preview = false;
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

/**
 * Minimal hook system: priority order is ignored, registration order kept.
 */
function add_filter( string $hook, callable $callback ): bool {
	WP_Stubs::$filters[ $hook ][] = $callback;
	return true;
}

function add_action( string $hook, callable $callback ): bool {
	return add_filter( $hook, $callback );
}

function apply_filters( string $hook, $value, ...$args ) {
	foreach ( WP_Stubs::$filters[ $hook ] ?? array() as $callback ) {
		$value = $callback( $value, ...$args );
	}
	return $value;
}

function __return_true(): bool {
	return true;
}

function __return_false(): bool {
	return false;
}

function is_admin(): bool {
	return WP_Stubs::$is_admin;
}

function is_customize_preview(): bool {
	return WP_Stubs::$is_customize_preview;
}

define( 'MINUTE_IN_SECONDS', 60 );

function get_transient( string $key ) {
	return WP_Stubs::$transients[ $key ] ?? false;
}

function set_transient( string $key, $value, int $expiration = 0 ): bool {
	WP_Stubs::$transients[ $key ] = $value;
	return true;
}

function rest_url( string $path = '' ): string {
	return 'https://example.test/wp-json/' . ltrim( $path, '/' );
}

function register_rest_route( string $namespace, string $route, array $args ): bool {
	WP_Stubs::$rest_routes[] = array( $namespace, $route, $args );
	return true;
}

function wp_json_encode( $data ) {
	return json_encode( $data );
}

/**
 * Stand-in REST request carrying a raw body.
 */
class WP_REST_Request {
	public function __construct( private string $body = '' ) {}

	public function get_body(): string {
		return $this->body;
	}
}

/**
 * Stand-in REST response.
 */
class WP_REST_Response {
	public function __construct( public $data = null, public int $status = 200 ) {}

	public function get_status(): int {
		return $this->status;
	}
}

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
require_once JC_DIR . '/lib/functions/security-headers.php';
require_once JC_DIR . '/lib/functions/csp-report.php';

// Fire rest_api_init once so the route registration is recorded.
foreach ( WP_Stubs::$filters['rest_api_init'] ?? array() as $callback ) {
	$callback();
}

WP_Stubs::$plugin_filters = WP_Stubs::$filters;
