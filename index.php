<?php

/**
 * Slim Starter v1
 */

require 'vendor/autoload.php';
require 'config.php';
require 'Database.php';
require 'Debug.php';
require 'Users.php';

// @todo autoload classes

/**
 * Create app
 */
$app = new \Slim\Slim();

/**
 * Routes
 */
$app->get('/', 'hello');
$app->post('/users', 'Users::create');
$app->get('/users', 'Users::getAll');
$app->get('/users/:id', 'Users::get');
$app->group('/user', function() use ($app) {
  $app->get('/:id', 'Users::get');
});

/**
 * Run app
 */
$app->run();

/**
 * Hello
 */
function hello() {
  $template = <<<EOT
<!DOCTYPE html>
<html>
<head>
  <meta charset="utf-8"/>
  <title>Slim Starter v1</title>
  <style>
    body {
      background: #EDEDED;
      color: #A8A8A8;
      text-align: center;
    }
  </style>
</head>
<body>
  <h1>Slim Starter v1</h1>
</body>
</html>
EOT;
  echo $template;
}
