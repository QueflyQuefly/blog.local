<?php
session_start();
$file_functions = dirname(__DIR__) . DIRECTORY_SEPARATOR . 'functions' . DIRECTORY_SEPARATOR . 'functions.php';
require_once $file_functions;

$_SESSION['referrer'] = $_SERVER['REQUEST_URI'];

if (!empty($_SESSION['user_id']) && strpos($_SESSION['user_id'], RIGHTS_SUPERUSER) !== false) {
    $rights = RIGHTS_SUPERUSER;
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
$numberOfPosts= 50;
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
            echo "<p class='label'>Список постов постранично и инвертировано <br> ($numberOfPostsпостов - одна страница)
                    <a href='adminposts.php'> &#8634</a></p>";

            $posts = getPostsByNumber(50, $numberOfPosts* $page - $number);
            echo "<p style='padding-left:3vmin'><span>Страницы:</span></p>";
            echo "<ul class ='list'>";
            for ($i = $page - 3; $i <= $page + 3; $i++) {//обманываю пользователя, что есть ещё страницы
                if ($i > 0) {
                    echo "<li  class='menuLink'><a class='menuLink' href='adminposts.php?page=$i'>$i</a></li>";
                }
            }
            echo "</ul><hr>";
            echo "<div class='list'>";
            if (!empty($posts)) {
                if (empty($posts)) {
                    echo "<p class='error'>Нет постов для отображения</p>"; 
                } else {
                    echo "<ul class='list'>";
                    foreach ($posts as $post) {
                        $tags = getTagsToPostById($post['post_id']);
            ?>
            <li class='list'>
                <p class='list'><span>ID:</span><?= $post['post_id'] ?> ::: <span>Название:</span> <?= $post['zag'] ?></p>
                <p class='list'><span>Автор:</span> <?= $post['author'] ?> </p>
                <p class='list'><span>Рейтинг:</span> <?= $post['rating'] ?> ::: <span>Оценок:</span> <?= ''//$evaluations?> </p>
                <p class='list'> <span>Тэги:</span> 
                    <?php 
                        if ($tags) {
                            foreach ($tags as $tag) {
                                $tagLink = substr($tag['tag'], 1);
                                echo "<a class='menuLink' href='search.php?search=%23$tagLink'>{$tag['tag']}</a> ";
                            }
                        } else {
                            echo "Нет тэгов";
                        }
                    ?>
                </p>
                <a class='list' href='adminposts.php?deletePostById=<?= $post['post_id'] ?>'>
                    Удалить пост с ID=<?= $post['post_id'] ?>
                </a>
                <p class='list'> <span>Комментариев к посту:</span> <?= ''//$post['countComments'] ?> </p>
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