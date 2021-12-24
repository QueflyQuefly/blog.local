<?php
$startTime = microtime(true);
spl_autoload_register(function ($class) {
    if (strpos($class, 'Controller') !== false) {
        $pathToClass = 'controllers' . DIRECTORY_SEPARATOR;
    }
    if (strpos($class, 'Service') !== false) {
        $pathToClass = 'models' . DIRECTORY_SEPARATOR;
    }
    if (strpos($class, 'View') !== false) {
        $pathToClass = 'views' . DIRECTORY_SEPARATOR;
    }
    require_once $pathToClass . $class . '.php';
});

$frontController = new FrontController($_SERVER['REQUEST_URI'], $_REQUEST);