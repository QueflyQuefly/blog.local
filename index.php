<?php
$startTime = microtime(true);
spl_autoload_register(function ($class) {
    if (strpos($class, 'Controller') !== false) {
        $pathToClass = 'controllers' . DIRECTORY_SEPARATOR;
    }
    if (strpos($class, 'Service') !== false) {
        $pathToClass = 'services' . DIRECTORY_SEPARATOR;
    }
    if (strpos($class, 'View') !== false) {
        $pathToClass = 'views' . DIRECTORY_SEPARATOR;
    }
    require_once $pathToClass . $class . '.php';
});
function clearInt($int) {
    return abs((int) $int);
}
function clearStr($str) {
    return trim(strip_tags($str));
}
require 'Factory.php';
$frontController = new FrontController(new Factory(), $startTime);