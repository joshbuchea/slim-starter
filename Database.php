<?php

/**
 * Database Class
 */
class Database {

  /**
   * Get database connection
   *
   * Database constants are defined in config.php.
   */
  public static function get() {
    $dbhost = DB_HOST;
    $dbname = DB_NAME;
    $dbuser = DB_USERNAME;
    $dbpass = DB_PASSWORD;
    $dbConnection = new PDO("mysql:host=$dbhost;dbname=$dbname", $dbuser, $dbpass); 
    $dbConnection->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    return $dbConnection;
  }

}
