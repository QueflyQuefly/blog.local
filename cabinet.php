<?php
session_start();
$functions = join(DIRECTORY_SEPARATOR, array('functions', 'functions.php'));
require_once $functions;
$link = "<a class='menu' href='login.php'>Войти</a>";
$label = "<a class='menu' href='login.php'>Вы не авторизованы</a>";
$login = '';
$fio = '';
$_SESSION['referrer'] = $_SERVER['REQUEST_URI'];


if (isset($_GET['user'])) {
    $userId = $_GET['user'];
} else {
    header("Location: /");
}  

if (isset($_GET['exit'])) {
    $_SESSION['log_in'] = false;
    session_destroy();
    header("Location: /");
} 

if (isset($_SESSION['log_in']) && $_SESSION['log_in']) {
    $login = $_SESSION['login'];
    $fio = $_SESSION['fio'];
    $link = "<a class='menu' href='?exit'>Выйти</a>";
}

if (isset($_GET['deletePostById'])) {
    $deletePostId = clearInt($_GET['deletePostById']);
    if ($deletePostId !== '') {
        deletePostById($deletePostId);
        header("Location: cabinet.php?user=$id");
    } 
}
if (isset($_GET['deleteCommentById']) && isset($_GET['byPostId'])) {
    $deleteCommentId = clearInt($_GET['deleteCommentById']);
    $postId = clearInt($_GET['byPostId']);
    if ($deleteCommentId !== '' && $postId !== '') {
        deleteCommentByIdAndPostId($deleteCommentId, $postId);
        header("Location: cabinet.php?user=$id");
    } 
}

$year = date("Y", time());
?>


<!DOCTYPE html>
<html>

<head>
    <meta charset='UTF-8'>
    <title>Кабинет - Просто блог</title>
    <link rel='stylesheet' href='css/indexcss.css'>
</head>
<body>
<nav>
    <div class='top'>
        <div class="logo">
            <a class="logo" title="На главную" href='/'><img id='logo' src='images/logo.jpg' alt='Лого' width='50' height='50'>
            <div id='namelogo'>Просто Блог</div></а>
        </div>
        <div class="menu">
            <ul class='menu'>
                <li class='menu'><?=$link?></li>
                <li class='menu'><a class='menu' href='search.php'>Поиск поста</a></li>
                <li class='menu'><a class='menu' href='addpost.php'>Создать новый пост</a></li>
                <li class='menu'><?=$label?></li>
            </ul>
        </div>
    </div>
</nav>

<div class='allsinglepost'>
    <div class='contentsinglepost'>

        <div id='singlepostzagolovok'>
            <p class='singlepostzagolovok'>Личный кабинет пользователя</p>
           
        </div>
        <div class='singlepostauthor'>
            <p class='center'>Логин: <?=$login?></p>
            <p class='center'>ФИО: <?=$fio?></p>
        </div>
        
        <div class='singleposttext'>
            <?php 
                $posts = getPostsByFio($fio);
                if (empty($posts) or $posts == false) {
                    $countPosts = 0; 
                } else {
                    $countPosts = count($posts);
                }
                echo "<p class='singlepostcontent'>Список ваших постов (всего $countPosts):</p>";
                if (empty($posts) or $posts == false) {
                    echo "<p class='center'>Нет постов для отображения</p>"; 
                } else {
                    echo "<ul class='list'>";
                    $num = count($posts) - 1;
                    for ($i= $num; $i>=0; $i--) {
                        $post = $posts[$i];
                        $comments = getCommentsByPostId($post['id']);
                        if (empty($posts) or $posts == false) {
                            $countComments = 0;
                        } else {
                        $countComments = count($comments);
                        }

            ?>

            <li class='list'>

                <p class='list'><a class='menu' href='viewsinglepost.php?viewPostById=<?= $post['id'] ?>'>ID:<?= $post['id'] ?> ::: Название: <?= $post['name'] ?></a></p>
                <a class='list' href='cabinet.php?deletePostById=<?= $post['id'] ?>'> Удалить пост с ID=<?= $post['id'] ?></a>
                <p class='list'> Комментариев к посту: <?= $countComments ?> </p>
                <hr>
            </li>
        
            <?php 
                    } 
                echo "</ul>";
                }
            ?>
            
        </div>

        <div class='singleposttext'>
            
            
            <?php 
                $comments = getCommentsByFio($fio);
                if (empty($comments) or $comments == false) {
                    $countComments = 0;
                } else {
                $countComments = count($comments);
                }
                echo "<p class='singlepostcontent'>Список ваших комментариев (всего $countComments):</p>";
                if ($countComments) {
                    echo "<ul class='list'>";
                    for ($j = 0; $j <= $countComments -1; $j++) {
            ?>

            <li class='list'>
                <p class='list'><a class='menu' href='viewsinglepost.php?viewPostById=<?= $comments[$j]['post_id'] ?>'>ID:<?= $comments[$j]['id'] ?></a></p>  
                <p class='list'>Содержание: <?= $comments[$j]['content'] ?></p>
                <a class='list' href='cabinet.php?deleteCommentById=<?= $comments[$j]['id'] ?>&byPostId=<?= $post['id'] ?>'> Удалить комментарий с ID=<?= $comments[$j]['id'] ?></a>
                <hr>
            </li>

            <?php

                    }
                echo "</ul>";
                } else {
                    echo "<p class='center'>Нет комментариев для отображения</p>";
                }
            ?>
    
        </div>
        <div class='singleposttext'>
                        
        <?php 
                $postsLike = getLikedPostsByLogin($login);
                if (empty($postsLike) or $postsLike == false) {
                    $countPostsLike = 0;
                } else {
                    $countPostsLike = count($postsLike);
                }
                echo "<p class='singlepostcontent'>Посты, которые вы оценили (всего $countPostsLike):</p>";
                if ($countPostsLike) {
                    echo "<ul class='list'>";
                    for ($j = 0; $j <= $countPostsLike -1; $j++) {
                        $post = getPostForViewById($postsLike[$j]['id']);
            ?>

            <li class='list'>
                <p class='list'><a class='menu' href='viewsinglepost.php?viewPostById=<?= $postsLike[$j]['id'] ?>'>ID:<?= $postsLike[$j]['id'] ?></a></p>
                <p class='list'>Автор: <?=$post['author']?></p>  
                <p class='list'>Содержание: <a class='menu' href='viewsinglepost.php?viewPostById=<?= $postsLike[$j]['id'] ?>'>Перейти</a></p>
               <hr>
            </li>

            <?php

                    }
                echo "</ul>";
                } else {
                    echo "<p class='center'>Нет постов для отображения</p>";
                }
            ?>
            
        </div>
        <div class='singleposttext'>
                        
        <?php 
                $comments = getLikedCommentsByLogin($login);
                if (empty($comments) or $comments == false) {
                    $countComments = 0;
                } else {
                $countComments = count($comments);
                }
                echo "<p class='singlepostcontent'>Комментарии, которые вы оценили (всего $countComments):</p>";
                if ($countComments) {
                    echo "<ul class='list'>";
                    for ($j = 0; $j <= $countComments -1; $j++) {
            ?>

            <li class='list'>
                <p class='list'><a class='menu' href='viewsinglepost.php?viewPostById=<?= $comments[$j]['post_id'] ?>#comment<?= $comments[$j]['com_id'] ?>'>ID:<?= $comments[$j]['com_id'] ?></a></p>  
                <p class='list'>Содержание: <a class='menu' href='viewsinglepost.php?viewPostById=<?= $comments[$j]['post_id'] ?>#comment<?= $comments[$j]['com_id'] ?>'>Перейти</a></p>
               <hr>
            </li>

            <?php

                    }
                echo "</ul>";
                } else {
                    echo "<p class='center'>Нет комментариев для отображения</p>";
                }
            ?>

        </div>
    </div>

    <footer class='bottom'>
        <p>Website by Вячеслав Бельский &copy; <?=$year?></p>
    </footer>
</div>
</body>
</html>