<?php
/**
 * Shortcodes
 *
 * This file contains Shortcode functions
 *
 * @package   jc_Custom_Functionality
 * @since     1.0.0
 * @link      https://github.com/Herm71/jc-core-functionality.git
 * @author    Jason Chafin
 * @copyright Copyright (c) 2015, Blackbird Consulting
 * @license   http://opensource.org/licenses/gpl-2.0.php GNU Public License
 */

add_shortcode( 'quotes','jc_quotes_loop' );
function jc_quotes_loop() {
	$finalloop = '';
	// Call Post
	$args = array (
	'post_type' => 'quote',
	'orderby' => 'rand',
	'posts_per_page' => 1,

	);
	$quote = new \WP_Query( $args );
	if ($quote->have_posts()) :
		while ($quote->have_posts()) :
			$quote->the_post();
			$quoteTitle = get_the_title();
			$finalloop .= '<p>'.$quoteTitle.'</p>';
		endwhile;
	endif;
	return $finalloop;
	wp_reset_postdata();
}

