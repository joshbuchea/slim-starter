<?php

/**
 * Users Class
 */
class Users {

  /**
   * Create user
   */
  public static function create() {
    global $app;
    $req = $app->getInstance()->request();
    $res = json_decode($req->getBody());

    $sql = "INSERT INTO users (id, email, password, created, enabled) VALUES (NULL, :email, :password, UTC_TIMESTAMP(), 1)";
    try {
      $db = Database::get();
      $stmt = $db->prepare($sql);
      $stmt->bindParam("email", $res->email);
      $stmt->bindParam("password", $res->password);
      $stmt->execute();
      $user->id = $db->lastInsertId();
      $db = null;
      $app->response->setStatus(200);
      echo json_encode($user);
    } catch(PDOException $e) {
      error_log($e->getMessage(), 3, '/var/tmp/phperror.log');
      $app->response->setStatus(400);
      echo '{"error":{"text":'. $e->getMessage() .'}}';
    }
  }

  /**
   * Get all users
   */
  public static function getAll() {
    global $app;
    $sql = "SELECT * FROM users";
    try {
      $db = Database::get();
      $stmt = $db->query($sql);
      $users = $stmt->fetchAll(PDO::FETCH_OBJ);
      $db = null;
      $app->response->setStatus(200);
      echo '{"data": ' . json_encode($users) . '}';
      // echo json_encode($users);
    } catch(PDOException $e) {
      error_log($e->getMessage(), 3, '/var/tmp/phperror.log');
      $app->response->setStatus(400);
      echo '{"error":{"text":'. $e->getMessage() .'}}';
    }
  }

  /**
   * Get user
   */
  public static function get($id) {
    global $app;
    $sql = "SELECT * FROM users WHERE id = :id";
    try {
      $db = Database::get();
      $stmt = $db->prepare($sql);
      $stmt->bindParam("id", $id);
      $stmt->execute();
      $user = $stmt->fetch(PDO::FETCH_OBJ);
      $db = null;
      $app->response->setStatus(200);
      echo '{"data": ' . json_encode($user) . '}';
      // echo json_encode($user);
    } catch(PDOException $e) {
      error_log($e->getMessage(), 3, '/var/tmp/phperror.log');
      $app->response->setStatus(400);
      echo '{"error":{"text":'. $e->getMessage() .'}}';
    }
  }

}
