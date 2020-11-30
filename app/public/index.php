<?php

define('DEBUG', TRUE);
define('BASE_URL', '/public');
define('ROUTER_BASE_URL', '/public/index.php');

if (DEBUG) {
    error_reporting(E_ALL);
    ini_set('display_errors', '1');
}

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

    case '/signup':
        $controllerName = 'UserController';
        $action = 'signupAction';
        break;

    case '/logout':
        $controllerName = 'UserController';
        $action = 'logoutAction';
        break;

    case '':
    case '/':
        $controllerName = 'QuizController';
        $action = 'listAction';
        break;

    default:
        $controllerName = 'ClientErrorController';
        $action = 'notFoundAction';
        break;
}

require '../src/util/SimpleServiceProvider.php';
require '../src/util/DbContext.php';
require '../src/controller/' . $controllerName . '.php';
require '../src/model/UserManager.php';
require '../src/model/QuizManager.php';

$services = new SimpleServiceProvider();

$db = new \mysqli(
    $dbConfig['host'],
    $dbConfig['user'],
    $dbConfig['password'],
    $dbConfig['dbname']
);
$services->register(new DbContext($db));

$quizManager = new DummyQuizManager($services);
$userManager = new UserManager($services);

$services->register($quizManager);
$services->register($userManager);

if (\mysqli_connect_errno()) {
    throw new \Exception(\sprintf("Connect failed: %s\n", \mysqli_connect_error()));
}

$controller = new $controllerName($services);

try {
    $view = $controller->{$action}($_REQUEST, $_SERVER['REQUEST_METHOD']);
} catch (\Throwable $th) {
    if (DEBUG) {
        throw $th;
    } else {
        echo 'An error occurred! If this continues to happen, please report it to the website administrator!';
    }
}

echo $view->renderView();
