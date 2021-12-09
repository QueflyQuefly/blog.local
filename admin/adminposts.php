<?php
session_start();
$file_functions = join(DIRECTORY_SEPARATOR, array(dirname(__DIR__), 'functions', 'functions.php'));
require_once $file_functions;

$error = ''; $posts = [];

if (!isset($_SESSION['rights'])) {
    $_SESSION['rights'] = '';
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
                    if ($_SESSION['rights'] != "superuser") {
                        echo "<p class='error'>Необходимо <a class='link' href='/login.php'>войти</a> как администратор</p>";
                        exit;
                    }
                ?>

                <?=$error?>
            </p>
        </div>


        <p class='label'>Список всех постов <a href='adminposts.php'> &#8634</a></p>

        <div class='list'>
            <?php 
                $posts = getPostsForIndex();
                if (empty($posts) or $posts == false) {
                    echo "<p class='error'>Нет постов для отображения</p>"; 
                } else {
                    echo "<ul class='list'>";
                    $num = count($posts) - 1;
                    for ($i= $num; $i>=0; $i--) {
                        $post = $posts[$i];
                        $tags = getTagsToPostById($posts[$i]['id']);
                        $comments = getCommentsByPostId($post['id']);
                        $evaluations = countRatingsByPostId($post['id']);
                        if (empty($posts) or $posts == false) {
                            $countComments = 0;
                        } else {
                        $countComments = count($comments);
                        }
            ?>

            <li class='list'>

            <p class='list'><span>ID:</span><?= $post['id'] ?> ::: <span>Название:</span> <?= $post['name'] ?></p>
            <p class='list'><span>Автор:</span>  <?= $post['author'] ?> </p>
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
                ?>
            <br>
            <li class='list'>
            <p class='list'><span>ID:</span><?= $comments[$j]['id'] ?> ::: <span>Автор(его логин):</span> <?= $comments[$j]['login'] ?></p>
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
