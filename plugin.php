<?php
/**
 * Plugin Name: Jason Chafin Core Functionality
 * Plugin URI: https://github.com/Herm71/jc-core-functionality.git
 * Description: Contains custom functionality. Theme independent.
 * Version: 1.2.0-rc.0
 * Author: Jason Chafin
 * Author URI: https://github.com/Herm71
 * License: GPL-2.0-or-later
 * License URI: https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain: jc-core-functionality
 * Requires Plugins: advanced-custom-fields-pro
 */

// Block direct access.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

// Plugin Directory
define( 'JC_DIR', __DIR__ );

/**
 * Plugin updates via GitHub releases.
 *
 * Only registered where an update can actually be surfaced or applied: the
 * dashboard, WP-Cron, and WP-CLI (`wp plugin update jc-core-functionality`).
 * Front-end requests skip it.
 *
 * Deliberately not gated on wp_is_auto_update_enabled_for_type('plugin').
 * That governs unattended background auto-updates only; the update notice and
 * the Update Now button both depend on the check itself running, so gating on
 * it would hide updates from any site that has auto-updates switched off.
 *
 * @see https://github.com/YahnisElsts/plugin-update-checker
 */
$jc_update_checker_path = JC_DIR
	. '/vendor/yahnis-elsts/plugin-update-checker/plugin-update-checker.php';

if ( ( is_admin() || wp_doing_cron() || ( defined( 'WP_CLI' ) && WP_CLI ) )
	&& file_exists( $jc_update_checker_path )
) {
	include_once $jc_update_checker_path;

	$jc_update_checker = YahnisElsts\PluginUpdateChecker\v5\PucFactory::buildUpdateChecker(
		'https://github.com/Herm71/jc-core-functionality/',
		__FILE__,
		'jc-core-functionality'
	);

	// Install the release asset (jc-core-functionality.zip) and nothing else.
	// The default preference silently falls back to GitHub's source tarball,
	// which ships without vendor/ — installing that would leave the site with a
	// copy of this plugin that can no longer update itself.
	$jc_vcs_api = $jc_update_checker->getVcsApi();
	$jc_vcs_api->enableReleaseAssets(
		'/jc-core-functionality\.zip/',
		$jc_vcs_api::REQUIRE_RELEASE_ASSETS
	);
}

/**
 * Add link to Settings page from Plugins
 */
add_filter( 'plugin_action_links_' . plugin_basename( __FILE__ ), 'jc_custom_functionality_plugin_action_links' );
function jc_custom_functionality_plugin_action_links( $links ) {
	// Build and escape the URL.
	$url = esc_url(
		add_query_arg(
			'page',
			'jc-custom-functionality-settings',
			get_admin_url() . 'options-general.php'
		)
	);
	// Create the link.
	$settings_link = "<a href='$url'>" . esc_html__( 'Settings', 'jc-core-functionality' ) . '</a>';
	// Adds the link to the end of the array.
	array_push(
		$links,
		$settings_link
	);
	return $links;
}


// Include Customization files.

// Plugin Settings.
if ( file_exists( JC_DIR . '/lib/functions/settings.php' ) ) {
	include_once JC_DIR . '/lib/functions/settings.php';
}

// Google Tag Manager.
if ( file_exists( JC_DIR . '/lib/functions/gtm.php' ) ) {
	include_once JC_DIR . '/lib/functions/gtm.php';
}

// Shortcodes.
if ( file_exists( JC_DIR . '/lib/functions/shortcodes.php' ) ) {
	include_once JC_DIR . '/lib/functions/shortcodes.php';
}

// Disable XMLRP.
if ( file_exists( JC_DIR . '/lib/functions/disable-xmlrpc.php' ) ) {
	include_once JC_DIR . '/lib/functions/disable-xmlrpc.php';
}

// Security Headers.
if ( file_exists( JC_DIR . '/lib/functions/security-headers.php' ) ) {
	include_once JC_DIR . '/lib/functions/security-headers.php';
}

// General.
if ( file_exists( JC_DIR . '/lib/functions/general.php' ) ) {
	include_once JC_DIR . '/lib/functions/general.php';
}
