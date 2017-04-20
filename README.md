# Resource Versioning

Turn Query String Parameters into file revision numbers.

> It’s important to make resources (images, scripts, stylesheets, etc.) cacheable.

[Steve Souders](http://www.stevesouders.com/blog/2008/08/23/revving-filenames-dont-use-querystring/)

It is much easier to use a CDN without Query String Parameters.
This plugins alters only local resources' URL-s.
The `ver` Query String Parameter will be inserted into the filename.

`jquery.min.js?ver=1.10` becomes `jquery.min.110.js`

To "revert" that change add this line to your nginx config:

```nginx
server {
    location ~ ^(.+)\.\d\d+\.(js|css|png|jpg|jpeg|gif|ico)$ {
        #try_files $uri $1.$2 /index.php?$args;
        try_files $uri $1.$2 =404;
    }
}
```

Or to your Apache config or `.htaccess` file.

```apache
RewriteEngine On
RewriteCond %{REQUEST_FILENAME} !-f
RewriteCond %{REQUEST_FILENAME} !-d
RewriteRule ^(.+)\.\d\d+\.(js|css|png|jpg|jpeg|gif|ico)$ $1.$2 [NC,L]
```

### Testing the plugin before live usage

You can test the plugin by replacing `add_filter()` calls with these lines

```php
require_once dirname( dirname( dirname( dirname( __FILE__ ) ) ) ) . '/wp-load.php';
wp();
echo o1_src_revving( $argv[1] ) . PHP_EOL;
```

Then start it from CLI: `php revving.php <TEST-URL>`

### How to remove all Query String Parameters from resources?

Poorly written plugins and themes may add unwanted Query String Parameters.
For example `?rev=4.10`.

To drop all these parameters copy this into your wp-config.php

```php
define( 'O1_REMOVE_ALL_QARGS', true );
```
