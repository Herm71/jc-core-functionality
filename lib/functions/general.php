<?php
/**
 * General
 *
 * ACF JSON load/save paths, registered post meta and block bindings sources.
 *
 * @package   jc_Custom_Functionality
 * @since     1.0.0
 * @link      https://github.com/Herm71/jc-core-functionality
 * @author    Jason Chafin
 * @copyright Copyright (c) 2015, Blackbird Consulting
 * @license   GPL-2.0-or-later
 */

// Block direct access.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Load ACF JSON (field groups, post types) from the plugin.
 *
 * @param string[] $paths ACF JSON load paths.
 * @return string[]
 */
function jc_add_json_load_point( $paths ) {
	// Remove the original path (optional).
	unset( $paths[0] );

	// Append the new path and return it.
	$paths[] = JC_DIR . '/acf-json';

	return $paths;
}
add_filter( 'acf/settings/load_json', 'jc_add_json_load_point' );

/**
 * Save ACF JSON edits into the plugin, so they ship with it.
 *
 * WordPress passes the current save path; it is replaced, not used.
 *
 * @return string
 */
function jc_add_json_save_point() {
	return JC_DIR . '/acf-json';
}
add_filter( 'acf/settings/save_json', 'jc_add_json_save_point' );

/**
 * Register ACF-backed post meta so it is readable through the REST API.
 *
 * @return void
 */
function jc_acf_register_meta() {
	$terms = array( 'subtitle' );

	foreach ( $terms as $term ) {
		register_meta(
			'post',
			$term,
			array(
				'show_in_rest'      => true,
				'single'            => true,
				'type'              => 'string',
				'sanitize_callback' => 'wp_strip_all_tags',
			)
		);
	}
}
add_action( 'init', 'jc_acf_register_meta' );

/**
 * Register the plugin's block bindings sources.
 *
 * @return void
 */
function jc_register_block_bindings() {
	register_block_bindings_source(
		'jc/copyright',
		array(
			'label'              => __( 'Copyright', 'jc-core-functionality' ),
			'get_value_callback' => 'jc_copyright_binding',
		)
	);
	register_block_bindings_source(
		'jc/user-data',
		array(
			'label'              => __( 'User Data', 'jc-core-functionality' ),
			'get_value_callback' => 'jc_user_data_bindings',
		)
	);
}
add_action( 'init', 'jc_register_block_bindings' );

/**
 * Value for the jc/copyright binding.
 *
 * @return string "© <year>" in the site's timezone.
 */
function jc_copyright_binding() {
	return '&copy; ' . wp_date( 'Y' );
}

/**
 * Value for the jc/user-data binding.
 *
 * @param array $source_args Binding args: `key` (name, description or avatar) and `userId`.
 * @return string|null Null when the args are missing or invalid.
 */
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
			// Bios may carry basic formatting (links, emphasis); allow post-safe HTML only.
			return wp_kses_post( get_the_author_meta( 'description', $user_id ) );
		case 'avatar':
			return esc_url( get_avatar_url( $user_id ) );
		default:
			return null;
	}
}
