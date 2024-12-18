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
add_action( 'init', 'jc_register_block_bindings' );

function jc_register_block_bindings() {
	register_block_bindings_source( 'jc/copyright', array(
		'label'              => __( 'Copyright', 'jc' ),
		'get_value_callback' => 'jc_copyright_binding'
	) );
	register_block_bindings_source( 'jc/user-data', array(
		'label'              => __( 'User Data', 'jc' ),
		'get_value_callback' => 'jc_user_data_bindings'
	) );
}

// Copyright callback
function jc_copyright_binding() {
	return '&copy; ' . date( 'Y' );
}

// User Data callback
function jc_user_data_bindings( $source_args ) {
	// If no key or user ID argument is set, bail early.
	if ( ! isset( $source_args['key'] ) || ! isset( $source_args['userId'] ) ) {
		return null;
	}

	// Get the user ID.
	$user_id = absint( $source_args['userId'] );

	// Return null if there's no user ID at all.
	if ( 0 >= $user_id ) {
		return null;
	}

	// Return the data based on the key argument.
	switch ( $source_args['key'] ) {
		case 'name':
			return esc_html( get_the_author_meta( 'display_name', $user_id ) );
		case 'description':
			return get_the_author_meta( 'description', $user_id );
		case 'avatar':
			return esc_url( get_avatar_url( $user_id ) );
		default:
			return null;
	}
}
