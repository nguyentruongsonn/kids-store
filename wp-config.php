<?php
/**
 * The base configuration for WordPress
 *
 * The wp-config.php creation script uses this file during the installation.
 * You don't have to use the website, you can copy this file to "wp-config.php"
 * and fill in the values.
 *
 * This file contains the following configurations:
 *
 * * Database settings
 * * Secret keys
 * * Database table prefix
 * * ABSPATH
 *
 * @link https://developer.wordpress.org/advanced-administration/wordpress/wp-config/
 *
 * @package WordPress
 */

// ** Database settings - You can get this info from your web host ** //
/** The name of the database for WordPress */
define( 'DB_NAME', 'shop' );

/** Database username */
define( 'DB_USER', 'root' );

/** Database password */
define( 'DB_PASSWORD', '' );

/** Database hostname */
define( 'DB_HOST', '127.0.0.1' );

/** Database charset to use in creating database tables. */
define( 'DB_CHARSET', 'utf8mb4' );

/** The database collate type. Don't change this if in doubt. */
define( 'DB_COLLATE', '' );

/**#@+
 * Authentication unique keys and salts.
 *
 * Change these to different unique phrases! You can generate these using
 * the {@link https://api.wordpress.org/secret-key/1.1/salt/ WordPress.org secret-key service}.
 *
 * You can change these at any point in time to invalidate all existing cookies.
 * This will force all users to have to log in again.
 *
 * @since 2.6.0
 */
define( 'AUTH_KEY',         'ysUAOg9Ex-`:!0pN8#gV0G{Xm&A+aFPyY~Di9ae3w;ysEk+Gc&HWu(oJ*hzg62Ju' );
define( 'SECURE_AUTH_KEY',  ' qjKxkgPMC)1GCV+[I_GC$(6!*5RbPwb{~# ,?LsqpL_cFtCoPsgt+t3G0k ]I.)' );
define( 'LOGGED_IN_KEY',    'rYJFd!9Os10-?9Fa<[l`uO)kYRI>7[YYNYmT@% 9c/+[6Ef`,bnJ/C5j9&cs2::3' );
define( 'NONCE_KEY',        '/6)b!EXEwzsCTiS:8Y^ ,DSr:qITep:Epg-]f]RG;r{~+QNz:l+bZ^_%8@.UdlhQ' );
define( 'AUTH_SALT',        'rm@|D,anxc3_lx?!@rPdBHIg`F!nTES]8N~3Cz0cYo]<O*Q#}M?40/?L=1t#pwG3' );
define( 'SECURE_AUTH_SALT', '!8-W5#A?4l(&,NH;~[#yt|q)}*L4+2B:B,it+5/7&C8SOLuZu0=1 x=MG3>AJ<u&' );
define( 'LOGGED_IN_SALT',   '&RdOev5J<`v_f1j|GsT.Apk-Hs[`OvzP}! |d6qsD-DD!mB%QO!I+,:c,ri7p:o.' );
define( 'NONCE_SALT',       'aWX1QV4<YnLG5/i>i)$PX<@<-lHmAbq%+neTWmrg)HM~</Mrldsi]y96yINb.j%f' );

/**#@-*/

/**
 * WordPress database table prefix.
 *
 * You can have multiple installations in one database if you give each
 * a unique prefix. Only numbers, letters, and underscores please!
 *
 * At the installation time, database tables are created with the specified prefix.
 * Changing this value after WordPress is installed will make your site think
 * it has not been installed.
 *
 * @link https://developer.wordpress.org/advanced-administration/wordpress/wp-config/#table-prefix
 */
$table_prefix = 'wp_';

/**
 * For developers: WordPress debugging mode.
 *
 * Change this to true to enable the display of notices during development.
 * It is strongly recommended that plugin and theme developers use WP_DEBUG
 * in their development environments.
 *
 * For information on other constants that can be used for debugging,
 * visit the documentation.
 *
 * @link https://developer.wordpress.org/advanced-administration/debug/debug-wordpress/
 */
define( 'WP_DEBUG', false );

/**
 * This local site has a backlog of WooCommerce scheduled actions that includes
 * remote requests. Do not execute the queue during a visitor request.
 */
define( 'DISABLE_WP_CRON', true );

/* Add any custom values between this line and the "stop editing" line. */

// Hỗ trợ truy cập qua Cloudflare Tunnel / ngrok để test webhook payOS trên Localhost
if (isset($_SERVER['HTTP_X_FORWARDED_PROTO']) && $_SERVER['HTTP_X_FORWARDED_PROTO'] === 'https') {
    $_SERVER['HTTPS'] = 'on';
}
if (isset($_SERVER['HTTP_HOST']) && $_SERVER['HTTP_HOST'] !== 'localhost' && $_SERVER['HTTP_HOST'] !== '127.0.0.1') {
    $scheme = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on') ? 'https' : 'http';
    define('WP_HOME', $scheme . '://' . $_SERVER['HTTP_HOST'] . '/cung');
    define('WP_SITEURL', $scheme . '://' . $_SERVER['HTTP_HOST'] . '/cung');
}

/* That's all, stop editing! Happy publishing. */

/** Absolute path to the WordPress directory. */
if ( ! defined( 'ABSPATH' ) ) {
	define( 'ABSPATH', __DIR__ . '/' );
}

/** Sets up WordPress vars and included files. */
require_once ABSPATH . 'wp-settings.php';
