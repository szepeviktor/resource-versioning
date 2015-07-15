<?php
/*
Plugin Name: Resource Versioning
Description: Turn Query String Parameters into file revision numbers.
Version: 0.1.0
Author: Viktor Szépe
Author URI: https://github.com/szepeviktor?tab=activity
License: GNU General Public License (GPL) version 2
License URI: http://www.gnu.org/licenses/gpl-2.0.html
GitHub Plugin URI: https://github.com/szepeviktor/resource-versioning
Options: O1_REMOVE_ALL_QARGS
*/

/**
 * Filter script and style source URL-s.
 */
add_filter( 'script_loader_src', 'o1_src_revving' );
add_filter( 'style_loader_src', 'o1_src_revving' );

/**
 * Insert version into filename from query string.
 *
 * @param string $src  Original URL
 *
 * @return string      Versioned URL
 */
function o1_src_revving( $src ) {

    // Check for external URL
    if ( 0 !== strpos( $src, site_url() ) ) {
        return $src;
    }

    // Separate query string from the URL
    $parts = preg_split( '/\?/', $src, 2 );

    // Find version in query
    parse_str( $parts[1], $kwargs );
    if ( empty( $kwargs['ver'] ) ) {
        return $src;
    }

    // Sanitize version
    $ver = preg_replace( '/[^0-9]/', '', $kwargs['ver'] );
    // We need at least two digits for the rewrite rule
    if ( strlen( $ver ) < 2 ) {
        $ver = '0' . $ver;
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
