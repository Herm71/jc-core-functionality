<?php
/**
 * Add Plugin settings and info page
 *
 * This file contains functions to add a settings/info page below WordPress Settings menu
 *
 * @package      ucsc
 * @since        1.7.0
 * @link         https://github.com/ucsc/jc-custom-functionality.git
 * @author       UC Santa Cruz
 * @license      http://opensource.org/licenses/gpl-2.0.php GNU Public License
 */
/** Register new menu and page */
if ( ! function_exists( 'jc_add_settings_page' ) ) {
	function jc_add_settings_page() {
		add_options_page( 'Jason Chafin Custom Functionality plugin page', 'Jason Chafin Custom Functionality Info', 'manage_options', 'jc-custom-functionality-settings', 'jc_render_plugin_settings_page' );
	}
}
add_action( 'admin_menu', 'jc_add_settings_page' );

/**
 * HTML output of Settings page
 *
 */
if ( ! function_exists( 'jc_render_plugin_settings_page' ) ) {
	function jc_render_plugin_settings_page() {
		$plugin_data = get_plugin_data( JC_DIR . '/plugin.php');
		?><h1>Jason Chafin Custom Functionality Plugin</h1>
		<h2>Version: <?php echo $plugin_data['Version']; ?></h2>
		<p><?php echo $plugin_data['Description']; ?> <a href="https://github.com/Herm71/jc-core-functionality/releases">(release notes)</a></p>
		<hr>
		<h3>Features added by this plugin:</h3>
		<ul>
			<li><strong>Google Tag Manager</strong> and <strong>Google Analytics 4</strong></li>
			<li><strong>Security Headers</strong> Content Security Policy, etc.</li>
			<li><strong>Shortcodes:</strong>
				<ul>
					<li><code>[quotes]</code>: Displays a random quote from the Quotes CPT</li>
				</ul>
			</li>
			<li><strong>Block Bindings:</strong>
				<ul>
					<li>Copyright <blockquote><pre><!-- wp:paragraph {"metadata":{"bindings":{"content":{"source":"jc/copyright"}}}} -->
<p>Copyright Block</p>
<!-- /wp:paragraph --></pre></blockquote>
				</ul>
			</li>
		</ul>
		</div><?php
	}
}

