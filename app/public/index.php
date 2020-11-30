<?php

define('BASE_URL', '/public');

error_reporting(E_ALL);
ini_set('display_errors', '1');

// Retrieve DB config
$dbConfig = require __DIR__ . '/../config/database.config.php';

$entrypoint = BASE_URL . '/index.php';
$route = $_SERVER["REQUEST_URI"];
if (\strpos($route, $entrypoint) === 0) {
    $route = \substr($route, \strlen($entrypoint));
}

switch ($route) {
    case '':
    case '/':
    default:
        $controllerName = 'QuizController';
        $action = 'listAction';
        break;
}

require '../src/controller/' . $controllerName . '.php';
require '../src/model/UserManager.php';
require '../src/model/QuizManager.php';

$db = new \mysqli(
    $dbConfig['host'],
    $dbConfig['user'],
    $dbConfig['password'],
    $dbConfig['dbname']
);

$quizManager = new QuizManager($db);
$userManager = new UserManager($db);

if (\mysqli_connect_errno()) {
    throw new \Exception(\sprintf("Connect failed: %s\n", \mysqli_connect_error()));
}

$controller = new $controllerName($userManager, $quizManager);
$view = $controller->{$action}($_REQUEST);

echo $view->renderView();
