<?php
/**
 * Plugin info page
 *
 * Adds an info page under Settings that lists what this plugin provides.
 *
 * @package   jc_Custom_Functionality
 * @since     1.0.0
 * @link      https://github.com/Herm71/jc-core-functionality
 * @author    Jason Chafin
 * @license   GPL-2.0-or-later
 */

// Block direct access.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! function_exists( 'jc_add_settings_page' ) ) {
	/**
	 * Register the info page under Settings.
	 *
	 * @return void
	 */
	function jc_add_settings_page() {
		add_options_page( 'Jason Chafin Custom Functionality plugin page', 'Jason Chafin Custom Functionality Info', 'manage_options', 'jc-custom-functionality-settings', 'jc_render_plugin_settings_page' );
	}
}
add_action( 'admin_menu', 'jc_add_settings_page' );

if ( ! function_exists( 'jc_render_plugin_settings_page' ) ) {
	/**
	 * Render the info page.
	 *
	 * @return void
	 */
	function jc_render_plugin_settings_page() {
		// Markup off: return plain header values, escaped below.
		$plugin_data = get_plugin_data( JC_DIR . '/plugin.php', false, false );

		// Shown as text, so it must be escaped rather than emitted as an HTML comment.
		$copyright_block = '<!-- wp:paragraph {"metadata":{"bindings":{"content":{"source":"jc/copyright"}}}} -->' . "\n"
			. '<p>Copyright Block</p>' . "\n"
			. '<!-- /wp:paragraph -->';

		// gtm.php is included behind a file_exists() guard, like every feature file.
		$gtm_id = function_exists( 'jc_gtm_container_id' ) ? jc_gtm_container_id() : '';

		$user_data_block = '<!-- wp:paragraph {"metadata":{"bindings":{"content":{"source":"jc/user-data","args":{"key":"name","userId":1}}}}} -->' . "\n"
			. '<p>Author name</p>' . "\n"
			. '<!-- /wp:paragraph -->';
		?><div class="wrap">
		<h1>Jason Chafin Custom Functionality Plugin</h1>
		<h2>Version: <?php echo esc_html( $plugin_data['Version'] ); ?></h2>
		<p><?php echo esc_html( $plugin_data['Description'] ); ?> <a href="https://github.com/Herm71/jc-core-functionality/releases">(release notes)</a></p>
		<hr>
		<h3>Features added by this plugin:</h3>
		<ul>
			<li><strong>Google Tag Manager</strong> and <strong>Google Analytics 4</strong>: container <code><?php echo esc_html( '' !== $gtm_id ? $gtm_id : 'none (disabled)' ); ?></code>. Not loaded for administrators. Set <code>JC_GTM_ID</code> in <code>wp-config.php</code> to change it, or to <code>''</code> to turn it off.</li>
			<li><strong>Security Headers</strong> Content Security Policy, etc.</li>
			<li><strong>Shortcodes:</strong>
				<ul>
					<li><code>[quotes]</code>: Displays a random quote from the Quotes CPT</li>
				</ul>
			</li>
			<li><strong>Block Bindings:</strong>
				<ul>
					<li><code>jc/copyright</code>: &#169; and the current year, in the site's timezone. <blockquote><pre><?php echo esc_html( $copyright_block ); ?></pre></blockquote></li>
					<li><code>jc/user-data</code>: a user's profile data. Arguments: <code>key</code> (<code>name</code>, <code>description</code> or <code>avatar</code>) and <code>userId</code>. <code>name</code> is the display name, <code>description</code> the bio (basic HTML kept), and <code>avatar</code> the avatar image URL, for binding an Image block's <code>url</code>. <blockquote><pre><?php echo esc_html( $user_data_block ); ?></pre></blockquote></li>
				</ul>
			</li>
		</ul>
		</div>
		<?php
	}
}
