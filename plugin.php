<?php
/**
 * Plugin Name: Jason Chafin Core Functionality
 * Plugin URI: https://github.com/Herm71/jc-core-functionality.git
 * GitHub Plugin URI: https://github.com/Herm71/jc-core-functionality
 * Description: Contains custom functionality. Theme independent.
 * Version: 0.1.2
 * Author: Jason Chafin
 * Author URI: https://github.com/Herm71
 * License: GPL2
 *
 * This program is free software; you can redistribute it and/or modify it under the terms of the GNU
 * General Public License version 2, as published by the Free Software Foundation.  You may NOT assume
 * that you can use any other version of the GPL.
 *
 * This program is distributed in the hope that it will be useful, but WITHOUT ANY WARRANTY; without
 * even the implied warranty of MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.
 */

// Plugin Directory
define('JC_DIR', dirname(__FILE__));

// Include Customization files.

// Post Types.
// if (file_exists(JC_DIR . '/lib/functions/post-types.php') ) {
//     include_once JC_DIR . '/lib/functions/post-types.php';
// }

// Google Tag Manager.
if (file_exists(JC_DIR . '/lib/functions/gtm.php') ) {
    include_once JC_DIR . '/lib/functions/gtm.php';
}

// Shortcodes.
// if ( file_exists( JC_DIR . '/lib/functions/shortcodes.php' ) ) {
//     include_once JC_DIR . '/lib/functions/shortcodes.php';
// }

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
