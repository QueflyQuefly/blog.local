<?php
$startTime = microtime(true);
spl_autoload_register(function ($class) {
    if (strpos($class, 'Controller')) {
        $pathToClass = 'controllers' . DIRECTORY_SEPARATOR;
    }
    if (strpos($class, 'Service')) {
        $pathToClass = 'models' . DIRECTORY_SEPARATOR;
    }
    require $pathToClass . $class . '.php';
});

$pageTitle = 'Главная - просто Блог';
$pageDescription = 'Наилучший источник информации по теме "Путешествия"';
$postController = new PostController;
$posts = $postController->getIndexPosts(10);
$moreTalkedPosts =  $postController->getmoreTalkedPosts(3);
$year = date("Y", time());
$frontController = new FrontController();
$sessionUserId = $frontController->sessionUserId;
$isSuperuser = $frontController->isSuperuser;

if (isset($_GET['deletePostById'])) {
    $postController->deletePostById($_GET['deletePostById']);
}
if (isset($_GET['exit'])) {
    $postController->exitUser();
}

require "layouts/head.layout.php";
require "layouts/menu.layout.php";
require "layouts/startbody.layout.php";

if (empty($posts)) {
        echo "\n<p class='center'>Нет постов для отображения</p>\n";  
} else {
    foreach ($posts as $key => $post) {
        $class = 'viewpost';
        if ($key == 0) {
            $class = 'generalpost';
        }
        $post['date_time'] = date("d.m.Y в H:i", $post['date_time']);
        include 'layouts/post.layout.php';
        }
    echo "\n<p class='center'><a class='submit' href='posts.php'>Посмотреть ещё</a></p>\n";
    }
    if (!empty($moreTalkedPosts)) {
        echo "\n<div class='searchdescription'><div class='singleposttext'>Самые обсуждаемые посты за неделю	&darr;&darr;&darr;</div></div>\n";
        foreach ($moreTalkedPosts as $post) {
            $post['date_time'] = date("d.m.Y в H:i", $post['date_time']);
            $class = 'viewpost';
            include 'layouts/post.layout.php';
    }
}

require "layouts/endbody.layout.php";