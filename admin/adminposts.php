<?php
session_start();
$file_functions = join(DIRECTORY_SEPARATOR, array(dirname(__DIR__), 'functions', 'functions.php'));
require_once $file_functions;

$error = ''; $posts = [];

if (!empty($_SESSION['user_id'])) {
    $user = getUserEmailFioRightsById($_SESSION['user_id']);
    $rights = $user['rights'];
} else {
    $rights = false;
}
if (isset($_GET['deletePostById'])) {
    $deletePostId = clearInt($_GET['deletePostById']);
    if ($deletePostId != '') {
        deletePostById($deletePostId);
        header("Location: adminposts.php");
    }
}
if (isset($_GET['deleteCommentById'])) {
    $deleteCommentId = clearInt($_GET['deleteCommentById']);
    if ($deleteCommentId != '') {
        deleteCommentById($deleteCommentId);
        header("Location: adminposts.php");
    } 
}
if (isset($_GET['page'])) {
    $page = clearInt($_GET['page']);
} else {
    $page = 1;
}
$number = 50;
?>

<!DOCTYPE html>
<html>
<head>
    <meta charset='UTF-8'>
    <title>Управление постами - Просто Блог</title>
    <link rel='stylesheet' href='css/admincss.css'>
</head>
<body>
<div class='view'>
    <div class='viewlist'>
        <p class='logo'><a class="logo" title='На главную' href='/'>Просто Блог</a></p>
        
        <div class='msg'>
            <p class='error'>
                <?php
                    if ($rights !== "superuser") {
                        echo "<p class='error'>Необходимо <a class='link' href='/login.php'>войти</a> как администратор</p>";
                        exit;
                    }
                ?>

                <?=$error?>
            </p>
        </div>


        <p class='label'>Список постов постранично и инвертировано <br> (<?=$number?> постов - одна страница)<a href='adminposts.php'> &#8634</a></p>
        <?php 
            $ids = getPostIds();
            echo "<p style='padding-left:3vh'><span>Страницы:</span></p>";
            echo "<ul class ='list'>";
            for ($i = 1; $i <= count($ids)/ 50 + 1; $i++) {
                echo "<li class='menu'><a class='menu' href='adminposts.php?page=$i'>$i</a></li>";
            }
            echo "</ul><hr>";
            echo "<div class='list'>";
            if (!empty($ids)) {
                $countIds = $number * ($page - 1);
                if ($countIds < 0) {
                    $number += $countIds;
                    $countIds = 0;
                }
                $ids = array_slice($ids, $countIds, $number);
                foreach ($ids as $id) {
                    $posts[] = getPostForIndexById($id);
                }
            }
            if (empty($posts)) {
                echo "<p class='error'>Нет постов для отображения</p>"; 
            } else {
                echo "<ul class='list'>";
                foreach ($posts as $post) {
                    $tags = getTagsToPostById($posts[$i]['id']);
                    $comments = getCommentsByPostId($post['id']);
                    $evaluations = countRatingsByPostId($post['id']);
                    $author = getUserEmailFioRightsById($post['user_id']);
                    if (empty($posts) or $posts == false) {
                        $countComments = 0;
                    } else {
                    $countComments = count($comments);
                    }
            ?>
            <li class='list'>
                <p class='list'><span>ID:</span><?= $post['id'] ?> ::: <span>Название:</span> <?= $post['zag'] ?></p>
                <p class='list'><span>Автор:</span> <?= $author['fio'] ?> </p>
                <p class='list'><span>Рейтинг:</span> <?= $post['rating'] ?> ::: <span>Оценок:</span> <?=$evaluations?> </p>
                <p class='list'> <span>Тэги:</span> 
                    <?php 
                        if ($tags) {
                            foreach ($tags as $tag) {
                                $tagLink = substr($tag['tag'], 1);
                                echo "<a class='menu' href='search.php?search=%23$tagLink'>{$tag['tag']}</a> ";
                            }
                        } else {
                            echo "Нет тэгов";
                        }
                    ?>
                </p>
                <a class='list' href='adminposts.php?deletePostById=<?= $post['id'] ?>'> Удалить пост с ID=<?= $post['id'] ?></a>
                <p class='list'> <span>Комментариев к посту:</span> <?= $countComments ?> </p>
            
                <?php 
                    if ($countComments) {
                        echo "<ul class='list'>";
                        for ($j = 0; $j <= $countComments -1; $j++) {
                            $author = getUserEmailFioRightsById($comments[$j]['user_id']);
                ?>
            <br>
            <li class='list'>
                <p class='list'><span>ID:</span><?= $comments[$j]['id'] ?> ::: <span>Автор:</span> <?= $author['fio'] ?> <br> <span>E-mail автора:</span> <?= $author['email'] ?></p>
                <br>
                <p class='list'>Содержание: <?= $comments[$j]['content'] ?></p>
                <a class='list' href='adminposts.php?deleteCommentById=<?= $comments[$j]['id'] ?>&byPostId=<?= $post['id'] ?>'> Удалить комментарий с ID=<?= $comments[$j]['id'] ?></a>
            </li>
                <?php

                        }
                    echo "</ul>";
                    }
                ?>
                <hr>
            </li>
        <?php 
            } 
        echo "</ul>";
        }
        ?>
        </div>
    </div>
</div>
</body>
</html>