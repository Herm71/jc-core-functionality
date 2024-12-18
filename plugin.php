<?php
/**
 * Plugin Name: Jason Chafin Core Functionality
 * Plugin URI: https://github.com/Herm71/jc-core-functionality.git
 * GitHub Plugin URI: https://github.com/Herm71/jc-core-functionality
 * Description: Contains custom functionality. Theme independent.
 * Version: 1.0.0
 * Author: Jason Chafin
 * Author URI: https://github.com/Herm71
 * License: GPL2
 * Requires Plugins: advanced-custom-fields-pro
 */

// Plugin Directory
define('JC_DIR', dirname(__FILE__));

/**
 * Add link to Settings page from Plugins
 */
add_filter( 'plugin_action_links_' . plugin_basename(__FILE__), 'jc_custom_functionality_plugin_action_links' );
function jc_custom_functionality_plugin_action_links( $links ) {
	// Build and escape the URL.
	$url = esc_url( add_query_arg(
		'page',
		'jc-custom-functionality-settings',
		get_admin_url() . 'options-general.php'
	) );
	// Create the link.
	$settings_link = "<a href='$url'>" . __( 'Settings' ) . '</a>';
	// Adds the link to the end of the array.
	array_push(
		$links,
		$settings_link
	);
	return $links;
}


// Include Customization files.

// Plugin Settings.
if (file_exists(JC_DIR . '/lib/functions/settings.php') ) {
    include_once JC_DIR . '/lib/functions/settings.php';
}

// Google Tag Manager.
if (file_exists(JC_DIR . '/lib/functions/gtm.php') ) {
    include_once JC_DIR . '/lib/functions/gtm.php';
}

// Shortcodes.
if ( file_exists( JC_DIR . '/lib/functions/shortcodes.php' ) ) {
    include_once JC_DIR . '/lib/functions/shortcodes.php';
}

// Disable XMLRP.
if (file_exists(JC_DIR . '/lib/functions/disable-xmlrpc.php') ) {
    include_once JC_DIR . '/lib/functions/disable-xmlrpc.php';
}

// Security Headers.
require_once JC_DIR . '/lib/functions/security-headers.php';
if (file_exists(JC_DIR . '/lib/functions/security-headers.php') ) {
    include_once JC_DIR . '/lib/functions/security-headers.php';
}

// General.
if (file_exists(JC_DIR . '/lib/functions/general.php') ) {
    include_once JC_DIR . '/lib/functions/general.php';
}
