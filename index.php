<?php
$startTime = microtime(true);
spl_autoload_register(function ($class) {
    $pathToControllers = 'controllers' . DIRECTORY_SEPARATOR;
    require $pathToControllers . $class . '.php';
});
$pageTitle = 'Главная - просто Блог';
$pageDescription = 'Наилучший источник информации по теме "Путешествия"';
$postController = new PostController;
$posts = $postController->getIndexPosts(10);
$moreTalkedPosts =  $postController->getmoreTalkedPosts(3);
$year = date("Y", time());
$postLayout = file_get_contents("layouts/post.layout.php");

require "layouts/head.layout.php";
require "layouts/menu.layout.php";
require "layouts/startbody.layout.php";

if (empty($posts)) {
        echo "<p class='center'>Нет постов для отображения</p>";    
} else {
    foreach ($posts as $key => $post) {
        $class = 'viewpost';
        if ($key == 0) {
            $class = 'generalpost';
        }
        $post['date_time'] = date("d.m.Y в H:i", $post['date_time']);
        include 'layouts/post.layout.php';
        }
    echo "<p class='center'><a class='submit' href='posts.php'>Посмотреть ещё</a></p>";
    }
    if (!empty($moreTalkedPosts)) {
        echo "<div class='searchdescription'><div class='singleposttext'>Самые обсуждаемые посты за неделю	&darr;&darr;&darr;</div></div>";
        foreach ($moreTalkedPosts as $post) {
            $post['date_time'] = date("d.m.Y в H:i", $post['date_time']);
            $class = 'viewpost';
            include 'layouts/post.layout.php';
    }
}

require "layouts/endbody.layout.php";