<?php
/**
 * Disable XML-RPC
 *
 * Empties the XML-RPC method list and removes the RSD link from <head>.
 * /xmlrpc.php can be used to brute force admin usernames and passwords.
 *
 * @see https://pantheon.io/docs/wordpress-best-practices#avoid-xml-rpc-attacks
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

add_filter(
    'xmlrpc_methods',
    function () {
        return array();
    },
    PHP_INT_MAX
);

// Remove link from <head>.
remove_action('wp_head', 'rsd_link');
