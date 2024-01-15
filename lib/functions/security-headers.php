<?php
/**
 * Security Headers
 *
 * This file contains the functions necessary to add security headers to the site.
 * see: https://pantheon.io/docs/wordpress-best-practices#security-headers
 *
 * @package   Core_Functionality
 * @since     1.1.0
 * @link      https://github.com/Herm71/jc-core-functionality.git
 * @author    Jason Chafin
 * @copyright Copyright (c) 2011, Jason Chafin
 * @license   http://opensource.org/licenses/gpl-2.0.php GNU Public License
 */
function jc_additional_securityheaders( $headers )
{
    if (! is_admin() ) {
        $headers['Referrer-Policy']         = 'no-referrer-when-downgrade'; // This is the default value, the same as if it were not set.
        $headers['X-Content-Type-Options']  = 'nosniff';
        $headers['X-XSS-Protection']        = '1; mode=block';
        $headers['Permissions-Policy']      = 'geolocation=(self), microphone=(self), camera=(self)';
        $headers['Content-Security-Policy'] = "default-src 'self' 'unsafe-inline' 'unsafe-eval' http://* *.googletagmanager.com *.siteimproveanalytics.com *.pantheonsite.io *.gravatar.com  *.ucsc.edu *.google-analytics.com ajax.googleapis.com use.fontawesome.com *.google.com *.netlify.com *.unpkg.com fonts.googleapis.com fonts.gstatic.com unpkg.com *.fontawesome.com blob data:; worker-src 'self' blob; frame-src 'self' *.youtube.com *.google.com;object-src 'none';frame-ancestors 'self' *.google.com *.youtube.com";
        $headers['X-Frame-Options']         = 'SAMEORIGIN';
    }

    return $headers;
}
add_filter('wp_headers', 'jc_additional_securityheaders');

