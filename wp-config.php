<?php
/**
 * The base configuration for WordPress
 *
 * The wp-config.php creation script uses this file during the
 * installation. You don't have to use the web site, you can
 * copy this file to "wp-config.php" and fill in the values.
 *
 * This file contains the following configurations:
 *
 * * MySQL settings
 * * Secret keys
 * * Database table prefix
 * * ABSPATH
 *
 * @link https://codex.wordpress.org/Editing_wp-config.php
 *
 * @package WordPress
 */

// ** MySQL settings - You can get this info from your web host ** //
/** The name of the database for WordPress */
define('DB_NAME', 'shafroll_commerce_beta1.0');

/** MySQL database username */
define('DB_USER', 'root');

/** MySQL database password */
define('DB_PASSWORD', 'admin123');

/** MySQL hostname */
define('DB_HOST', 'localhost');

/** Database Charset to use in creating database tables. */
define('DB_CHARSET', 'utf8mb4');

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
define('AUTH_KEY',         'J2Wcq)AJCJ3InfT78wo_J={(0R=3gr6@(0?<}E9bvIlR1~=UM{}@$|y>(wD__0PQ');
define('SECURE_AUTH_KEY',  '_%AG|:_r|Tz=~i#iT@/ezWI7].kUq:!GufIb^;zX>Ne8|h//gCNTo_!I%6HI&$%I');
define('LOGGED_IN_KEY',    'E6>R$-*<z1@f^ug[~rOR8Qa:iTb4A)m]e,v2VQxOY8dH~v>)}%k~{t6KoSxiSG%{');
define('NONCE_KEY',        'IXJy)@6A%Xw|PT+o]xo/l08z!.B~8$Sh:kIPF[VQ&4-s:;8&WcUvI.=b`c`R|_!^');
define('AUTH_SALT',        'zhQ_ZE5yr~ePn/Y1 m/7GxFau?t9Vvmz{a|=`llW9eb TZ!(*^X?Wmp}-j2:/OcV');
define('SECURE_AUTH_SALT', '*>6mE_a?NBu2S#NVfSu7;n[ogDr6q8?X[_Yhl`}-A-on8fs%H!$BRh>ZAx0t36kD');
define('LOGGED_IN_SALT',   'RWA(fpx1nNP{6b~v7tmf+{y52$-*8,grq;QY&7e()YIV}[N(l}`ETaSNPkmFdI6T');
define('NONCE_SALT',       'M-a/{Np%faU^|9%iJC>If0o-UWvOo})|LH$nH=(XN$Uks=m|hudR%qPg(-!WXJNP');

/**#@-*/

/**
 * WordPress Database Table prefix.
 *
 * You can have multiple installations in one database if you give each
 * a unique prefix. Only numbers, letters, and underscores please!
 */
$table_prefix  = 'wp_';

/**
 * For developers: WordPress debugging mode.
 *
 * Change this to true to enable the display of notices during development.
 * It is strongly recommended that plugin and theme developers use WP_DEBUG
 * in their development environments.
 *
 * For information on other constants that can be used for debugging,
 * visit the Codex.
 *
 * @link https://codex.wordpress.org/Debugging_in_WordPress
 */
define('WP_DEBUG', false);

/* That's all, stop editing! Happy blogging. */

/** Absolute path to the WordPress directory. */
if ( !defined('ABSPATH') )
	define('ABSPATH', dirname(__FILE__) . '/');

/** Sets up WordPress vars and included files. */
require_once(ABSPATH . 'wp-settings.php');
