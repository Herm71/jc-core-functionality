<?php
/**
 * Shortcodes
 *
 * Registers the [quotes] shortcode.
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
 * Render [quotes]: the title of one random Quote.
 *
 * @return string
 */
function jc_quotes_loop() {
	$finalloop = '';
	// One random quote.
	$args  = array(
		'post_type'      => 'quote',
		'orderby'        => 'rand',
		'posts_per_page' => 1,
		'no_found_rows'  => true,
	);
	$quote = new \WP_Query( $args );
	if ( $quote->have_posts() ) :
		while ( $quote->have_posts() ) :
			$quote->the_post();
			$quote_title = get_the_title();
			$finalloop  .= '<p>' . esc_html( $quote_title ) . '</p>';
		endwhile;
	endif;
	// the_post() replaced the global $post; restore it before returning.
	wp_reset_postdata();
	return $finalloop;
}
add_shortcode( 'quotes', 'jc_quotes_loop' );
