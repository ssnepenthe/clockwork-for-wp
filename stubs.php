<?php

// Until https://github.com/humanmade/psalm-plugin-wordpress/issues/1 is resolved.
// phpcs:ignoreFile

define( 'WP_CONTENT_DIR', './wp-content' );
define( 'COOKIE_DOMAIN', 'false' );
define( 'COOKIEPATH', '/' );

define( 'COOKIEHASH', '' );

define( 'AUTH_COOKIE', 'wordpress_' . COOKIEHASH );
define( 'SECURE_AUTH_COOKIE', 'wordpress_sec_' . COOKIEHASH );
define( 'LOGGED_IN_COOKIE', 'wordpress_logged_in' . COOKIEHASH );
