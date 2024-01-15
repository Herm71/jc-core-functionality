<?
/**
 * Custom Shortcodes
 *
 * This file contains any custom shortcodes
 *
 * @package   Core_Functionality
 * @since     1.0.0
 * @link      https://github.com/Herm71/jc-core-functionality.git
 * @author    Jason Chafin
 * @copyright Copyright (c) 2011, Jason Chafin
 * @license   http://opensource.org/licenses/gpl-2.0.php GNU Public License
 */

/**
 * Show Current Year
 *
 * @return void
 * Description
 * @package
 * @since
 * @author Jason Chafin
 * @link http://www.blackbirdconsult.com
 * @license GNU General Public License 2.0+
 */

function jc_show_current_year(){

	return date('Y');

}

add_shortcode('show-current-year', 'jc_show_current_year');
