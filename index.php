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

$pageTitle = 'Главная - просто Блог';
$pageDescription = 'Наилучший источник информации по теме "Путешествия"';
$postController = new PostController();

$year = date("Y", time());
$frontController = new FrontController($_SERVER['REQUEST_URL']);

require "layouts/head.layout.php";
require "layouts/menu.layout.php";
require "layouts/startbody.layout.php";

$postController->showLastPosts(10);
 

$postController->showMoreTalkedPosts(3);

require "layouts/endbody.layout.php";