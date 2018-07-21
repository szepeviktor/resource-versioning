<?php
/*
Plugin Name: Resource Versioning
Description: Turn Query String Parameters into file revision numbers.
Version: 0.3.0
Author: Viktor Szépe
License: GNU General Public License (GPL) version 2
License URI: http://www.gnu.org/licenses/gpl-2.0.html
GitHub Plugin URI: https://github.com/szepeviktor/resource-versioning
Constants: O1_REMOVE_ALL_QARGS
*/

/**
 * Trigger fail2ban
 */
if ( ! function_exists( 'add_filter' ) ) {
    error_log( 'Break-in attempt detected: revving_direct_access '
        . addslashes( isset( $_SERVER['REQUEST_URI'] ) ? $_SERVER['REQUEST_URI'] : '' )
    );
    ob_get_level() && ob_end_clean();
    if ( ! headers_sent() ) {
        header( 'Status: 403 Forbidden' );
        header( 'HTTP/1.1 403 Forbidden', true, 403 );
        header( 'Connection: Close' );
    }
    exit;
}

/**
 * Filter script and style source URL-s.
 */
add_filter( 'script_loader_src', 'o1_revving_src' );
add_filter( 'style_loader_src', 'o1_revving_src' );

/**
 * Insert version into filename from query string.
 *
 * @param string $src Original URL
 *
 * @return string     Versioned URL
 */
function o1_revving_src( $src ) {

    if ( is_admin() ) {
        return $src;
    }

    // Only operate on known URL-s
    $siteurl_noscheme = str_replace( array( 'http:', 'https:' ), '', site_url() );
    $contenturl_noscheme = str_replace( array( 'http:', 'https:' ), '', content_url() );
    // Support cdn-ified URL-s
    $contenturl_cdn = apply_filters( 'tiny_cdn', content_url() );
    $contenturl_cdn_noscheme = str_replace( array( 'http:', 'https:' ), '', $contenturl_cdn );

    $urls = array(
        site_url(),
        $siteurl_noscheme,
        content_url(),
        $contenturl_noscheme,
        $contenturl_cdn,
        $contenturl_cdn_noscheme,
    );
    if ( ! o1_revving_starts_with_array( $src, $urls ) ) {
        return $src;
    }

    // Separate query string from the URL
    $parts = preg_split( '/\?/', $src, 2 );

    // No query string
    if ( empty( $parts[1] ) ) {
        return $src;
    }

    // Find version in query string
    $kwargs = array();
    parse_str( $parts[1], $kwargs );
    if ( empty( $kwargs['ver'] ) ) {
        return $src;
    }

    // Sanitize version
    $ver = preg_replace( '/[^0-9]/', '', $kwargs['ver'] );
    // We need at least two digits for the rewrite rule
    if ( strlen( $ver ) < 2 ) {
        $ver = '00' . $ver;
    }

    // Find where to insert version
    $pos = strrpos( $parts[0], '.' );

    // No dot in URL
    if ( false === $pos ) {
        return $src;
    }

    // Remove all query arguments
    if ( defined( 'O1_REMOVE_ALL_QARGS' ) && O1_REMOVE_ALL_QARGS ) {
        $new_query = '';
    } else {
        unset( $kwargs['ver'] );
        $new_query = build_query( $kwargs );
    }

    // Return the new URL
    return sprintf( '%s.%s.%s%s',
        substr( $parts[0], 0, $pos ),
        $ver,
        substr( $parts[0], $pos + 1 ),
        $new_query ? '?' . $new_query : ''
    );
}

/**
 * Return if haystack starts with needle.
 *
 * @param string $haystack The haystack.
 * @param string $needle   The needle.
 *
 * @return boolean Whether starts with or not.
 */
function o1_revving_starts_with( $haystack, $needle ) {

     $length = strlen( $needle );

     return ( substr( $haystack, 0, $length ) === $needle );
}

/**
 * Return if haystack starts with any needle.
 *
 * @param string $haystack     The haystack.
 * @param array  $needle_array The needles.
 *
 * @return boolean Whether any element starts with or not.
 */
function o1_revving_starts_with_array( $haystack, $needle_array ) {

    foreach( $needle_array as $needle ) {
        if ( o1_revving_starts_with( $haystack, $needle ) ) {

            return true;
        }
    }

    return false;
}
