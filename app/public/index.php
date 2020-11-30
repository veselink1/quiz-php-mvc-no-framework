<?php

define('BASE_URL', '/public');
define('ROUTER_BASE_URL', '/public/index.php');

error_reporting(E_ALL);
ini_set('display_errors', '1');

// Retrieve DB config
$dbConfig = require __DIR__ . '/../config/database.config.php';

$route = \explode('?', $_SERVER["REQUEST_URI"], 2)[0];
if (\strpos($route, ROUTER_BASE_URL) === 0) {
    $route = \substr($route, \strlen(ROUTER_BASE_URL));
}

switch ($route) {
    case '/quiz':
        $controllerName = 'QuizController';
        $action = 'quizAction';
        break;

    case '/login':
        $controllerName = 'UserController';
        $action = 'loginAction';
        break;

    case '':
    case '/':
    default:
        $controllerName = 'QuizController';
        $action = 'listAction';
        break;
}

require '../src/util/SimpleServiceProvider.php';
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

$services = new SimpleServiceProvider();
$services->register($quizManager);
$services->register($userManager);

if (\mysqli_connect_errno()) {
    throw new \Exception(\sprintf("Connect failed: %s\n", \mysqli_connect_error()));
}

$controller = new $controllerName($services);
$view = $controller->{$action}($_REQUEST, $_SERVER['REQUEST_METHOD']);

echo $view->renderView();
