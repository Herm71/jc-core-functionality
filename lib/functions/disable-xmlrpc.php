<?php

/**
 * Disable XMLRPC

 * /xmlrpc.php can be used to brute force admin usernames and passwords.

 * see: https://pantheon.io/docs/wordpress-best-practices#avoid-xml-rpc-attacks
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
