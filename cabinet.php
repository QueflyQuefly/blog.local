<?php
session_start();
$functions = join(DIRECTORY_SEPARATOR, array('functions', 'functions.php'));
require_once $functions;
$link = "<a class='menu' href='login.php'>Войти</a>";
$login = '';
$fio = '';
$adminLink = '';
$show = false;
if (isset($_GET['user'])) {
    $userId = clearInt($_GET['user']);
    $user = getLoginAndFioById($userId);
    $login = $user['login'];
    $fio = $user['fio'];
    $_SESSION['referrer'] = $_SERVER['REQUEST_URI'];
    
    if (!empty($_SESSION['log_in'])) {
        $loginWantSubscribe = $_SESSION['login'];
        if (isset($_GET['subscribe'])) {
            toSubscribeUser($loginWantSubscribe, $login);
            $uri = str_replace('&subscribe', '', $_SERVER['REQUEST_URI']);
            header("Location: $uri");
        }
        if (isset($_GET['unsubscribe'])) {
            toUnsubscribeUser($loginWantSubscribe, $login);
            $uri = str_replace('&unsubscribe', '', $_SERVER['REQUEST_URI']);
            header("Location: $uri");
        }
        $link = "<a class='menu' href='{$_SERVER['REQUEST_URI']}&exit'>Выйти</a>";
        if ($login === $_SESSION['login']) {
            $show = true;
        }
        if (isset($_SESSION['rights']) && $_SESSION['rights'] === 'superuser') {
            $show = true;
        }
    }
} elseif (!empty($_SESSION['log_in'])) {
    $login = $_SESSION['login'];
    $fio = $_SESSION['fio'];
    $rights = $_SESSION['rights'];
    $show = true;
    $link = "<a class='menu' href='index.php?exit'>Выйти</a>";
    $_SESSION['referrer'] = 'cabinet.php';
    if ($_SESSION['rights'] == 'superuser') {
        $adminLink = "<a class='menu' href='admin/admin.php'>Админка</a>";
    }
}
 else {
    header("Location: login.php");
}
if (isset($_GET['exit'])) {
    $_SESSION['log_in'] = false;
    $uri = str_replace('&exit', '', $_SERVER['REQUEST_URI']);
    header("Location: $uri");
}
if (isset($_GET['deletePostById'])) {
    $deletePostId = clearInt($_GET['deletePostById']);
    if ($deletePostId !== '') {
        deletePostById($deletePostId);
        header("Location: {$_SESSION['referrer']}");
    } 
}
if (isset($_GET['deleteCommentById'])) {
    $deleteCommentId = clearInt($_GET['deleteCommentById']);
    if ($deleteCommentId !== '') {
        deleteCommentById($deleteCommentId);
        header("Location: {$_SESSION['referrer']}");
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
                <li class='menu'><a class='menu' href='search.php'>Поиск</a></li>
                <li class='menu'><a class='menu' href='addpost.php'>Создать новый пост</a></li>
                <li class='menu'><?=$adminLink?></li>
            </ul>
        </div>
    </div>
</nav>

<div class='allwithoutmenu'>
    <div class='content'>
        <div id='singlepostzagolovok'>
            <p class='singlepostzagolovok'>
                Личный кабинет пользователя <br> &copy; <?=$fio?>
                <?php
                    if ($show) {
                        echo "::: email: $login";
                    }
                ?>
            </p>
            <?php
                if (!empty($_SESSION['log_in']) && $login !== $_SESSION['login']) {
                    if (!isSubscribedUser($_SESSION['login'], $login)) {
                        echo "<p class='postdate' style='font-size:12pt'><a title='Подписаться' href='{$_SERVER["REQUEST_URI"]}&subscribe'>Подписаться</a></p>";
                    } else {
                        echo "<p class='postdate' style='font-size:12pt'><a title='Отменить подписку' href='{$_SERVER["REQUEST_URI"]}&unsubscribe'>Отменить подписку</a></p>";
                    }
                }
            ?>
        </div>
            <?php 
                $posts = getPostsByLogin($login);
                if (empty($posts) or $posts == false) {
                    $countPosts = 0; 
                } else {
                    $countPosts = count($posts);
                }
                echo "<div class='contentsinglepost'><p class='smallpostzagolovok'>Список постов &copy; $fio (всего $countPosts):</p></div>";
                if (empty($posts) or $posts == false) {
                    echo "<div class='contentsinglepost'><p class='center'>Нет постов для отображения</p></div>"; 
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
                        $post['date'] = date("d.m.Y в H:i", $post['date']);


            ?>

        <div class='viewsmallposts'>

            <a class='post' href='viewsinglepost.php?viewPostById=<?= $post['id'] ?>'>
                <div class='smallpost'>
                    <div class='smallposttext'>
                        <p class='smallpostzagolovok'><?= $post['name'] ?></p>
                        <p class='postdate'> &copy; <?= $post['date'] ?> Рейтинг поста: <?=$post['rating']?></p>
                        <?php
                            if ($show) {
                        ?>
                        <object><a class='list' href='cabinet.php?deletePostById=<?= $post['id'] ?>'> Удалить пост с ID=<?= $post['id'] ?></a></object><br>
                        <?php
                            }
                        ?>
                        <p class='postdate'>Комментариев к посту: <?= $countComments ?> </p>
                    </div>

                    <div class='smallpostimage'>
                        <img src='images/PostImgId<?=$post['id']?>.jpg' alt='Картинка' class='smallpostimage'>
                    </div>
                </div>
            </a>

        </div>

        <?php 
                } 
            }
        ?>

        <div class='viewcomments'>
            
            
            <?php 
                $comments = getCommentsByLogin($login);
                if (empty($comments) or $comments == false) {
                    $countComments = 0;
                } else {
                $countComments = count($comments);
                }
                echo "<div class='contentsinglepost'><p class='smallpostzagolovok'>Список комментариев &copy; $fio (всего $countComments):</p></div>";
                if ($countComments) {
                    echo "<ul class='list'>";
                    for ($i = 0; $i <= $countComments -1; $i++) {
                        $content = nl2br($comments[$i]['content']);
                        $date = date("d.m.Y",$comments[$i]['date']) ." в ". date("H:i", $comments[$i]['date']);
            ?>

            <a class='post' href='viewsinglepost.php?viewPostById=<?=$comments[$i]['post_id']?>#comment<?=$comments[$i]['id']?>'>
                <div class='viewcomment' id='comment<?= $comments[$i]['id'] ?>'>
                    <p class='commentauthor'><?=$fio?><div class='commentdate'><?=$date?></div></p>
                    <div class='commentcontent'>
                        <p class='commentcontent'><?=$content?></p>
                        <p class='commentcontent'>
                            <?php
                                if ($show) {
                            ?> 
                                <object><a class='menu' href='cabinet.php?deleteCommentById=<?= $comments[$i]['id'] ?>'> Удалить комментарий</a></object>
                            <?php
                                }
                            ?>
                        </p>
                    </div>
                </div>
            </a>

            <?php

                    }
                } else {
                    echo "<div class='contentsinglepost'><p class='center'>Нет комментариев для отображения</p></div>";
                }
            ?>
    
        </div>
                        
        <?php 
            $postsLike = getLikedPostsByLogin($login);
            if (empty($postsLike) or $postsLike == false) {
                $countPostsLike = 0;
            } else {
                $countPostsLike = count($postsLike);
            }
            echo "<div class='contentsinglepost'><p class='smallpostzagolovok'>Оценённые посты  &copy; $fio (всего $countPostsLike):</p></div>";
            if ($countPostsLike) {
                for ($j = 0; $j <= $countPostsLike -1; $j++) {
                    $post = getPostForViewById($postsLike[$j]['post_id']);
        ?>

        <div class='viewsmallposts'>
            <a class='post' href='viewsinglepost.php?viewPostById=<?=$post['id']?>'>
                <div class='smallpost'>

                    <div class='smallposttext'>
                        <p class='smallpostzagolovok'><?=$post['name']?></p>
                        <p class='postdate'><?=$post['date']. " &copy; " . $post['author']?></p>
                        <p class='postrating'>Рейтинг поста: <?=$post['rating']?></p>
                    </div>

                    <div class='smallpostimage'>
                        <img src='images/PostImgId<?=$post['id']?>.jpg' alt='Картинка' class='smallpostimage'>
                    </div>
                
                </div>
            </a>
        </div>

        <?php

                }
            } else {
                echo "<div class='contentsinglepost'><p class='center'>Нет постов для отображения</p></div>";
            }
        ?>
            
        <div class='viewcomments'>
            <?php 
                $comments = getLikedCommentsByLogin($login);
                foreach ($comments as $id=>$value) {
                    $comments = getCommentsById($id);
                }
                if (empty($comments) or $comments == false) {
                    $countComments = 0;
                } else {
                    $countComments = count($comments);
                }
                echo "<div class='contentsinglepost'><p class='smallpostzagolovok'>Понравившиеся комментарии &copy; $fio (всего $countComments):</p></div>";
                if ($countComments) {
                    for ($i = 0; $i <= $countComments -1; $i++) {
                        $author = getUserFioByLogin($comments[$i]['login']);
                        $content = nl2br($comments[$i]['content']);
                        $date = date("d.m.Y",$comments[$i]['date']) ." в ". date("H:i", $comments[$i]['date']);
            ?>

            <a class='post' href='viewsinglepost.php?viewPostById=<?=$comments[$i]['post_id']?>#comment<?=$id?>'>
                <div class='viewcomment' id='comment'>
                    <p class='commentauthor'><?=$author['fio']?><div class='commentdate'><?=$date?></div></p>
                    <div class='commentcontent'>
                        <p class='commentcontent'><?=$content?></p> 
                    </div>
                </div>
            </a>

            <?php

                    }
                } else {
                    echo "<div class='contentsinglepost'><p class='center'>Нет комментариев для отображения</p></div>";
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