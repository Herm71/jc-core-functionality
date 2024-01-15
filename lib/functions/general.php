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

