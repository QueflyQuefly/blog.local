<?php
session_start();
$file_functions = dirname(__DIR__) . "/functions/functions.php";
require_once $file_functions;

$error = ''; $posts = [];

if (isset($_GET['deletePostById'])) {
    $deletePostId = clearInt($_GET['deletePostById']);
    if ($deletePostId != '') {
        deletePostById($deletePostId);
        header("Location: adminposts.php");
    } 
}
if (isset($_GET['deleteCommentById']) && isset($_GET['byPostId'])) {
    $deleteCommentId = clearInt($_GET['deleteCommentById']);
    $postId = clearInt($_GET['byPostId']);
    if ($deleteCommentId != '' && $postId != '') {
        deleteCommentByIdAndPostId($deleteCommentId, $postId);
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
        <p class='logo'><a class="logo" href='/'>Просто Блог</a></p>
        
        <div class='msg'>
            <p class='error'>
                <?php
                    if ($_SESSION['rights'] != "superuser") {
                        echo "<p class='error'>Необходимо <a class='link' href='/login.php'>войти</a> как администратор</p>";
                        exit;
                    }
                ?>

                <?=$php_errormsg , $error?>
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
                        $comments = getCommentsByPostId($post['id']);
                        $countComments = count($comments);

            ?>

            <li class='list'>

            <p class='list'>ID:<?= $post['id'] ?> ::: Название: <?= $post['name'] ?> <br> Автор: <?= $post['author'] ?> </p>
            <a class='list' href='adminposts.php?deletePostById=<?= $post['id'] ?>'> Удалить пост с ID=<?= $post['id'] ?>-й</a>
            <p class='list'> Комментариев к посту: <?= $countComments ?> </p>
            
            <?php 
                if ($countComments) {
                    echo "<ul class='list'>";
                    for ($j = 0; $j <= $countComments -1; $j++) {
            ?>

            <li class='list'>
            <p class='list'>ID:<?= $comments[$j]['id'] ?> ::: Автор: <?= $comments[$j]['author'] ?></p>  
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