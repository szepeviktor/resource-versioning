=== Resource Versioning ===
Contributors: szepe.viktor
Donate link: https://szepe.net/wp-donate/
Tags: file, resource, apache, nginx, varnish, cache, CSS, JS, JavaScript, CDN, content delivery network, optimization, performance
Requires at least: 4.0
Tested up to: 4.8
Stable tag: 0.2.0
License: GPLv2

Turn Query String Parameters into file revision numbers.

== Description ==

"It’s important to make resources (images, scripts, stylesheets, etc.) cacheable."

[Steve Souders](http://www.stevesouders.com/blog/2008/08/23/revving-filenames-dont-use-querystring/)

It is much easier to use a CDN without Query String Parameters.
This plugins alters only local resources' URL-s.
The `ver` Query String Parameter will be inserted into the filename.

For example `jquery.min.js?ver=1.10` becomes `jquery.min.110.js`.

To reverse this in the web server add this line to your nginx config:

`
server {
    location ~ ^(.+)\.\d\d+\.(js|css|png|jpg|jpeg|gif|ico)$ {
        #try_files $uri $1.$2 /index.php?$args;
        try_files $uri $1.$2 =404;
    }
}
`

Or to your Apache configuration or `.htaccess` file.

`
RewriteEngine On
RewriteCond %{REQUEST_FILENAME} !-f
RewriteCond %{REQUEST_FILENAME} !-d
RewriteRule ^(.+)\.\d\d+\.(js|css|png|jpg|jpeg|gif|ico)$ $1.$2 [NC,L]
`

= Testing the plugin before live usage =

You can test the plugin by replacing the two `add_filter()` calls with this

`
require_once( dirname( dirname( dirname( dirname( __FILE__ ) ) ) ) . '/wp-load.php' );
wp();
echo o1_revving_src( $argv[1] ) . PHP_EOL;
`

Then start it from CLI: `php revving.php <TEST-URL>`

= Links =

Development of this plugin goes on on [GitHub](https://github.com/szepeviktor/resource-versioning).

== Installation ==

This section describes how to install the plugin and get it working.

1. Upload `revving.php` to the `/wp-content/plugins/` directory
1. Activate the plugin through the 'Plugins' menu in WordPress

== Frequently Asked Questions ==

= How to remove all Query String Parameters from resources? =

Poorly written plugins and themes may add unwanted Query String Parameters.
For example `?rev=4.10`.

To drop all these parameters copy this into your `wp-config.php`:

`
define( 'O1_REMOVE_ALL_QARGS', true );
`

== Changelog ==

= 0.2.0 =
* Strengthen stability.
* Support cdn-ified URL-s.

= 0.1.3 =
* FIX: Use proper WordPress function for content URL.

= 0.1.2 =
* FIX: Don't run on admin.

= 0.1.1 =
* FIX: External URL detection.

= 0.1.0 =
* Initial relase without multisite support.
