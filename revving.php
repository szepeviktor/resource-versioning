<?php
/*
Plugin Name: Resource Versioning
Description: Turn Query String Parameters into file revision numbers.
Version: 0.2.0
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
 * @param string $src  Original URL
 *
 * @return string      Versioned URL
 */
function o1_revving_src( $src ) {

    if ( is_admin() ) {
        return $src;
    }

    // Check for external or admin URL
    $siteurl_noscheme = str_replace( array( 'http:', 'https:' ), '', site_url() );
    $contenturl_noscheme = str_replace( array( 'http:', 'https:' ), '', content_url() );
    // Support cdn-ified URL-s
    $contenturl_cdn = apply_filters( 'tiny_cdn', content_url() );
    if ( ! o1_revving_starts_with( $src, site_url() )
        && ! o1_revving_starts_with( $src, $siteurl_noscheme )
        && ! o1_revving_starts_with( $src, content_url() )
        && ! o1_revving_starts_with( $src, $contenturl_noscheme )
        && ! o1_revving_starts_with( $src, $contenturl_cdn )
    ) {
        return $src;
    }

    // Separate query string from the URL
    $parts = preg_split( '/\?/', $src, 2 );

    // No query string
    if ( ! isset( $parts[1] ) ) {
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
