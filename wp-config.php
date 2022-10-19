<?php
/**
 * The base configurations of the WordPress.
 *
 * This file has the following configurations: MySQL settings, Table Prefix,
 * Secret Keys, WordPress Language, and ABSPATH. You can find more information
 * by visiting {@link http://codex.wordpress.org/Editing_wp-config.php Editing
 * wp-config.php} Codex page. You can get the MySQL settings from your web host.
 *
 * This file is used by the wp-config.php creation script during the
 * installation. You don't have to use the web site, you can just copy this file
 * to "wp-config.php" and fill in the values.
 *
 * @package WordPress
 */

// ** MySQL settings - You can get this info from your web host ** //
/** The name of the database for WordPress */
define('DB_NAME', 'bettawerks_net');

/** MySQL database username */
define('DB_USER', 'bettawerksnet');

/** MySQL database password */
define('DB_PASSWORD', '*dfQYP3X');

/** MySQL hostname */
define('DB_HOST', 'mysql.bettawerks.net');

/** Database Charset to use in creating database tables. */
define('DB_CHARSET', 'utf8');

/** The Database Collate type. Don't change this if in doubt. */
define('DB_COLLATE', '');

/**#@+
 * Authentication Unique Keys and Salts.
 *
 * Change these to different unique phrases!
 * You can generate these using the {@link https://api.wordpress.org/secret-key/1.1/salt/ WordPress.org secret-key service}
 * You can change these at any point in time to invalidate all existing cookies. This will force all users to have to log in again.
 *
 * @since 2.6.0
 */
define('AUTH_KEY',         'Q&Bh`M$E$L5#6RIG5%a)/g#6zLIqn~"e`!P@0YZF7VqnwMYolIANM7/6K8ChF^Ob');
define('SECURE_AUTH_KEY',  '%ji5vrGPfc6g;$|E!b~Xm1Eq)Mi);cb6vFtkRpJFmWBE&LAEIwmqM:NDvm?U~bUL');
define('LOGGED_IN_KEY',    '1j5xS"^a_ry2l1OsRdiSNCrJpGF$?k|3pPC2`l#Bkz/%vxN);"h1$C#SE5kH6wMd');
define('NONCE_KEY',        'I8Du?!s?^p9Mt~QqHcS5I;8HW9;/C@8|j@9T|!bhe1h*h?w6:_3bIi%VoUANKC0)');
define('AUTH_SALT',        'IuO4#|L:jC"PXHPpvqty&5;oH@G@PW52k0;J~Zw)~1kyL9+ul/:BN_qvr$X5a~S5');
define('SECURE_AUTH_SALT', 'w9apgQAV!X~hg~TWy$CRBE`yX$YommIgbVHAX#aW$V?/z7DXyKC`)F0c_O3b41/3');
define('LOGGED_IN_SALT',   '"AvpLqZp2l%/p7Im5v?hPsuho&|EkFl)n3#dZQl@ktcWPuDAAmA0sXW|WSy*GRT@');
define('NONCE_SALT',       'z^PBqv:RemvjFYDv)iG)dJP&;^QKFySl!&gN0c7CpJI;4nc%NSM!a:*2)?t@tD2U');

/**#@-*/

/**
 * WordPress Database Table prefix.
 *
 * You can have multiple installations in one database if you give each a unique
 * prefix. Only numbers, letters, and underscores please!
 */
$table_prefix = '_8EH_';

/**
 * Limits total Post Revisions saved per Post/Page.
 * Change or comment this line out if you would like to increase or remove the limit.
 */
define('WP_POST_REVISIONS',  10);

/**
 * WordPress Localized Language, defaults to English.
 *
 * Change this to localize WordPress. A corresponding MO file for the chosen
 * language must be installed to wp-content/languages. For example, install
 * de_DE.mo to wp-content/languages and set WPLANG to 'de_DE' to enable German
 * language support.
 */


/**
 * For developers: WordPress debugging mode.
 *
 * Change this to true to enable the display of notices during development.
 * It is strongly recommended that plugin and theme developers use WP_DEBUG
 * in their development environments.
 */
define('WP_DEBUG', false);

/**
 * Removing this could cause issues with your experience in the DreamHost panel
 */

if (preg_match("/^(.*)\.dream\.website$/", $_SERVER['HTTP_HOST'])) {
        $proto = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? "https" : "http";
        define('WP_SITEURL', $proto . '://' . $_SERVER['HTTP_HOST']);
        define('WP_HOME',    $proto . '://' . $_SERVER['HTTP_HOST']);
        define('JETPACK_STAGING_MODE', true);
}
define( 'WP_CACHE', true ); // Added by WP Rocket

/* That's all, stop editing! Happy blogging. */

/** Absolute path to the WordPress directory. */
if ( !defined('ABSPATH') )
	define('ABSPATH', dirname(__FILE__) . '/');

/** Sets up WordPress vars and included files. */
require_once(ABSPATH . 'wp-settings.php');
