<?php
session_start();
$functions = join(DIRECTORY_SEPARATOR, array('functions', 'functions.php'));
require_once $functions;
$link = '';
$login = '';
$fio = '';
$_SESSION['referrer'] = 'cabinet.php';
if (isset($_GET['user'])) {
    $userId = clearInt($_GET['user']);
    $user = getLoginAndFioById($userId);
    $login = $user['login'];
    $fio = $user['fio'];
    $show = false;
} elseif (isset($_SESSION['log_in']) && $_SESSION['log_in']) {
    $login = $_SESSION['login'];
    $fio = $_SESSION['fio'];
    $show = true;
    $link = "<a class='menu' href='index.php?exit'>Выйти</a>";
} else {
    header("Location: login.php");
}

if (isset($_GET['deletePostById'])) {
    $deletePostId = clearInt($_GET['deletePostById']);
    if ($deletePostId !== '') {
        deletePostById($deletePostId);
        header("Location: cabinet.php?user=$id");
    } 
}
if (isset($_GET['deleteCommentById'])) {
    $deleteCommentId = clearInt($_GET['deleteCommentById']);
    if ($deleteCommentId !== '') {
        deleteCommentById($deleteCommentId);
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
            </ul>
        </div>
    </div>
</nav>

<div class='allwithoutmenu'>
    <div class='content'>

        <div id='singlepostzagolovok'>
            <p class='singlepostzagolovok'>Личный кабинет пользователя</p>
           
        </div>
        <div class='singlepostauthor'>
            <?php
                if ($show) {
            ?>
            <p class='center'>Логин: <?=$login?></p>
            <?php
                }
            ?>
            <p class='center'>ФИО: <?=$fio?></p>
        </div>
        
        <div class='viewsmallposts'>
            <?php 
                $posts = getPostsByFio($fio);
                if (empty($posts) or $posts == false) {
                    $countPosts = 0; 
                } else {
                    $countPosts = count($posts);
                }
                echo "<p class='singlepostcontent'>Список постов &copy; $fio (всего $countPosts):</p>";
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

            <a class='post' href='viewsinglepost.php?viewPostById=<?= $post['id'] ?>'>
                <div class='smallpost'>
                    <div class='smallposttext'>
                        <p class='smallpostzagolovok'><?= $post['name'] ?></p>
                        <p class='postdate'> &copy; <?= $post['date'] ?></p>
                    <?php
                        if ($show) {
                    ?>
                    <a class='list' href='cabinet.php?deletePostById=<?= $post['id'] ?>'> Удалить пост с ID=<?= $post['id'] ?></a><br>
                    <?php
                        }
                    ?>
                    <p class='list'>Комментариев к посту: <?= $countComments ?> </p>
                    </div>
                    <div class='smallpostimage'>
                        <img src='images/PostImgId<?=$post['id']?>.jpg' alt='Картинка' class='smallpostimage'>
                    </div>
                </div>
            </a>
        
            <?php 
                    } 
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
                echo "<p class='singlepostcontent'>Список комментариев &copy; $fio (всего $countComments):</p>";
                if ($countComments) {
                    echo "<ul class='list'>";
                    for ($j = 0; $j <= $countComments -1; $j++) {
            ?>

            <div class='viewcomment' id='comment<?= $comments[$i]['id'] ?>'>
                <p class='commentauthor'><?=$comments[$i]['author']?><div class='commentdate'><?=$date?></div></p>
                <div class='commentcontent'>
                    <p class='commentcontent'><?=$content?></p> 
                </div>
                <div class='like'>
                    <?php
                        $countLikes = $comments[$i]['rating'];
                        if (!isUserChangesComRating($login, $comments[$i]['id'])) {
                            $name = 'like';
                        } else {
                            $name = 'unlike';
                        }
                    ?>
                    
                    <form action='viewsinglepost.php?viewPostById=<?=$id?>#comment<?=$comments[$i]['id']?>' method='post'>
                        <label class='like' title="Нравится" for='like<?=$comments[$i]['id']?>'><span class='like'>&#9825; </span><?=$countLikes?></label>
                        <input type="submit" class='like' id="like<?=$comments[$i]['id']?>" name="<?= $name ?>" value="<?=$comments[$i]['id']?>">
                    </form>
                    <?php
                        if ($show) {
                    ?> 
                        <a class='list' href='cabinet.php?deleteCommentById=<?= $comments[$j]['id'] ?>&byPostId=<?= $post['id'] ?>'> Удалить комментарий</a>
                    <?php
                        }
                    ?>
                </div>
                <hr>
            </div>

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
                echo "<p class='singlepostcontent'>Оценённые посты  &copy; $fio (всего $countPostsLike):</p>";
                if ($countPostsLike) {
                    echo "<ul class='list'>";
                    for ($j = 0; $j <= $countPostsLike -1; $j++) {
                        $post = getPostForViewById($postsLike[$j]['id']);
            ?>

            <li class='list'>
                <p class='list'>Название: <?=$post['name']?></p>  
                <p class='list'>Автор: <?=$post['author']?></p>  
                <p class='list'>Дата публикации: <?=$post['date']?></p>  
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
                echo "<p class='singlepostcontent'>Понравившиеся комментарии &copy; $fio (всего $countComments):</p>";
                if ($countComments) {
                    echo "<ul class='list'>";
                    for ($j = 0; $j <= $countComments -1; $j++) {
            ?>

            <li class='list'>
                <p class='list'>Автор: <?= $comments[$j]['author'] ?></p>  
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