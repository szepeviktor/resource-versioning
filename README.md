# Sucuri Scanner custom settings

Custom settings for [Sucuri Scanner](https://wordpress.org/plugins/sucuri-scanner/) plugin.

### Restrict the admin interface to a specific user

Copy this to your wp-config.php

```php
define( 'O1_SUCURI_USER', 'your-username' );
```

### Hide Sucuri WAF related UI elements

The WAF menu and tab are not useful for users without WAF subscription.
"Website Firewall protection" postbox on Hardening tab should be also hidden.

### Prevent DNS queries on each page load

Sucuri plugin [looks up](https://plugins.trac.wordpress.org/changeset/1194834)
each visitor's IP address. Defining `NOT_USING_CLOUDPROXY` prevents this behavior.

### Hide Sucuri ads

Ads in the Sucuri Scanner plugin could be hidden manually.
This plugin hides the ads.

### Set data store path.

Sucuri Scanner's default datastore path is in the uploads directory.
This plugin sets the datastore path to `wp-content/sucuri`.
