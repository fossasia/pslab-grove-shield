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
define('DB_NAME', 'wordpress');

/** MySQL database username */
define('DB_USER', 'root');

/** MySQL database password */
define('DB_PASSWORD', '');

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
define('AUTH_KEY',         'hgbuixgxmguqvpy4t9otdj88qkan5fljfen4tzzwacls0vk78qjcco0qwxxcjdjy');
define('SECURE_AUTH_KEY',  'ea5vvrxvglxn49wt63dwu1nutlrzrb2j41yiaxvhbea5xdbput8t1zgakxqu6bj4');
define('LOGGED_IN_KEY',    'kfsbpfjaec7oq2dwx2uq7nd2pev14udjhjxpsf8rb76teylb2etrn7iy7rv7jblc');
define('NONCE_KEY',        'ahy3ub61ixtdgznya15tl43ukrujqdjhidkvzzptb0qcl4uyndkrzwjeek5lxdse');
define('AUTH_SALT',        'dkl13m0it9xmolusjyfmerrgiruidq9nmkkbqwxrhwybyz5bomx6v6vlxrnaivqi');
define('SECURE_AUTH_SALT', 'yrkakqjztnudymovnmzvzgztrhtwh1alby6kxacmlr6qpva4bvnub6upicfiobxq');
define('LOGGED_IN_SALT',   'tmbjn0ozqd7crlagubbwzitntnlmart27mpfw35kst9y6twzbqsfhwpfs1ykv9jb');
define('NONCE_SALT',       'gvb0c3ia9i2jkdt7bvepslyrrs4uz6nnqodvdz7oyfzgbyoaensn36c8tgbyayqy');

/**#@-*/

/**
 * WordPress Database Table prefix.
 *
 * You can have multiple installations in one database if you give each
 * a unique prefix. Only numbers, letters, and underscores please!
 */
$table_prefix  = 'wpjl_';

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
