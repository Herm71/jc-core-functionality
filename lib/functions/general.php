<?php
/**
 * General
 *
 * This file contains any general functions
 *
 * @package   jc_Custom_Functionality
 * @since     1.0.0
 * @link      https://github.com/Herm71/jc-core-functionality.git
 * @author    Jason Chafin
 * @copyright Copyright (c) 2015, Blackbird Consulting
 * @license   http://opensource.org/licenses/gpl-2.0.php GNU Public License
 */

/**
 * Don't Update Plugin
 *
 * @since 1.0.0
 *
 * This prevents you being prompted to update if there's a public plugin
 * with the same name.
 *
 * @author Mark Jaquith
 * @link   http://markjaquith.wordpress.com/2009/12/14/excluding-your-plugin-or-theme-from-update-checks/
 *
 * @param  array  $r,   request arguments
 * @param  string $url, request url
 * @return array request arguments
 */
function jc_custom_functionality_hidden( $r, $url )
{
    if (0 !== strpos($url, 'http://api.wordpress.org/plugins/update-check') ) {
        return $r; // Not a plugin update request. Bail immediately.
    }
    $plugins = unserialize($r['body']['plugins']);
    unset($plugins->plugins[ plugin_basename(__FILE__) ]);
    unset($plugins->active[ array_search(plugin_basename(__FILE__), $plugins->active) ]);
    $r['body']['plugins'] = serialize($plugins);
    return $r;
}
add_filter('http_request_args', 'jc_custom_functionality_hidden', 5, 2);

/**
 * Add new load point for JSON
 */

function jc_add_json_load_point( $paths ) {
    // Remove the original path (optional).
    unset($paths[0]);

    // Append the new path and return it.
    $paths[] = JC_DIR . '/acf-json';

    return $paths;
}
add_filter( 'acf/settings/load_json', 'jc_add_json_load_point' );

/**
 * Add new save point for JSON
 */

function jc_add_json_save_point( $path ) {
    return JC_DIR . '/acf-json';
}
add_filter( 'acf/settings/save_json', 'jc_add_json_save_point' );

// Register Meta Fields

add_action( 'init', 'jc_acf_register_meta' );

function jc_acf_register_meta() {
	$terms = array('subtitle');

	foreach ( $terms as $term ) {
		register_meta(
			'post',
			$term,
			array(
				'show_in_rest'      => true,
				'single'            => true,
				'type'              => 'string',
				'sanitize_callback' => 'wp_strip_all_tags'
			)
		);
	}

}


// Register Custom Block Binding Source

// Copyright
add_action( 'init', 'projectslug_register_block_bindings' );

function projectslug_register_block_bindings() {
	register_block_bindings_source( 'projectslug/copyright', array(
		'label'              => __( 'Copyright', 'projectslug' ),
		'get_value_callback' => 'projectslug_copyright_binding'
	) );
}

function projectslug_copyright_binding() {
	return '&copy; ' . date( 'Y' );
}
