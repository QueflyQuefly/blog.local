<?php
session_start();
$file_functions = dirname(__DIR__) . DIRECTORY_SEPARATOR . 'functions' . DIRECTORY_SEPARATOR . 'functions.php';
require_once $file_functions;

$_SESSION['referrer'] = $_SERVER['REQUEST_URI'];

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
        
        <?php
        if ($rights !== "superuser") {
        ?>
            <div class='msg'>
                <p class='error'>Необходимо <a class='link' href='/login.php'>войти</a> как администратор</p>
            </div>
        <?php
        } else {
            echo "<p class='label'>Список постов постранично и инвертировано <br> ($number постов - одна страница)<a href='adminposts.php'> &#8634</a></p>";

            $postIds = getPostIds();
            echo "<p style='padding-left:3vh'><span>Страницы:</span></p>";
            echo "<ul class ='list'>";
            for ($i = 1; $i <= count($postIds)/ 50 + 1; $i++) {
                echo "<li class='menu'><a class='menu' href='adminposts.php?page=$i'>$i</a></li>";
            }
            echo "</ul><hr>";
            echo "<div class='list'>";
            if (!empty($postIds)) {
                $countIdsOfPosts = $number * ($page - 1);
                if ($countIdsOfPosts < 0) {
                    $number += $countIdsOfPosts;
                    $countIdsOfPosts = 0;
                }
                $postIds = array_slice($postIds, $countIdsOfPosts, $number);
                if (empty($postIds)) {
                    echo "<p class='error'>Нет постов для отображения</p>"; 
                } else {
                    echo "<ul class='list'>";
                    foreach ($postIds as $postId) {
                        $post = getPostForIndexById($postId);
                        $tags = getTagsToPostById($post['id']);
                        $evaluations = $post['countRatings'];
                        $author = getUserEmailFioRightsById($post['user_id']);
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
                <p class='list'> <span>Комментариев к посту:</span> <?= $post['countComments'] ?> </p>
                <hr>
            </li>
            <?php 
                    }
                    echo "</ul>";
                }
                echo "</div>";
            }
        }
            ?>
        </div>
    </div>
</div>
</body>
</html>