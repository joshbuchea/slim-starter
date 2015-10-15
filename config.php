<?php
/**
 * Slim Starter Config
 */

define('DEV', true);
define('DEBUG', true);

/**
 * Database Configuration
 */
if (DEV) {
  define('DB_HOST', 'localhost');
  define('DB_NAME', 'slimstarter_dev');
  define('DB_USERNAME', 'root');
  define('DB_PASSWORD', 'root');
}
else {
  define('DB_HOST', 'localhost');
  define('DB_NAME', 'slimstarter');
  define('DB_USERNAME', 'root');
  define('DB_PASSWORD', 'root');
}
