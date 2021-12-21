<?php
$start = microtime(true);
session_start();
$file_functions = dirname(__DIR__) . DIRECTORY_SEPARATOR . 'functions' . DIRECTORY_SEPARATOR . 'functions.php';
require_once $file_functions;

$_SESSION['referrer'] = $_SERVER['REQUEST_URI'];

if (!empty($_COOKIE['user_id'])) {
    $sessionUserId = $_COOKIE['user_id'];
} elseif (!empty($_SESSION['user_id'])) {
    $sessionUserId = $_SESSION['user_id'];
}
if (!empty($sessionUserId) && getUserInfoById($sessionUserId, 'rights') === RIGHTS_SUPERUSER) {
    $isSuperuser = true;
} else {
    $isSuperuser = false;
}
if (isset($_GET['deletePostById']) && $isSuperuser === true) {
    $deletePostId = clearInt($_GET['deletePostById']);
    if ($deletePostId != '') {
        deletePostById($deletePostId);
        header("Location: adminposts.php");
    }
}
if (isset($_GET['page'])) {
    $page = clearInt($_GET['page']);
    if ($page < 1) {
        $page = 1;
    }
} else {
    $page = 1;
}
$numberOfPosts = 25;
$year = date("Y", time());
?>

<!DOCTYPE html>
<html>
<head>
    <meta charset='UTF-8'>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Управление постами - Просто Блог</title>
    <link rel='stylesheet' href='../css/general.css'>
</head>
<body>
<nav>
    <div class='top'>
        <div id="logo">
            <a class="logo" title="На главную" href='/'>
            <img id='imglogo' src='../images/logo.jpg' alt='Лого'>
            <div id='namelogo'>Просто Блог</div>
            </а>
        </div>
        <div id="menu">
            <ul class='menuList'>
                <?php
                    if (empty($sessionUserId)) {
                        echo "<li><a class='menuLink' href='/login.php'>Войти</a></li>";
                    } else {
                        echo "<li><a class='menuLink' href='/index.php?exit'>Выйти</a></li>";
                        if (!empty($isSuperuser)) {
                            echo "<li><a class='menuLink' href='admin.php'>Админка</a></li>";
                        }
                    }
                ?>
                <li><a class='menuLink' href='/cabinet.php'>Мой профиль</a></li>
                <li><a class='menuLink' href='/search.php'>Поиск</a></li>
                <li><a class='menuLink' href='/addpost.php'>Создать новый пост</a></li>
            </ul>
        </div>
    </div>
</nav>
<div class='allwithoutmenu'>
    <div class='content'>
        <?php
        if (empty($isSuperuser)) {
            echo "<p class='error'>Необходимо <a class='link' href='/login.php'>войти</a> как администратор</p>";
        } else {
            echo "<div id='desc'><p>Управление постами <br> ($numberOfPosts постов - одна страница)
                    <a href='adminposts.php'> &#8634</a></p></div>";

            $posts = getPostsByNumber($numberOfPosts, $numberOfPosts * $page - $numberOfPosts);
            echo "<p style='padding-left:3vmin'><span>Страницы:</span></p>";
            echo "<ul style='display: inline-flex;'>";
            count($posts) < $numberOfPosts ? $endPage = $page : $endPage = $page + 3;
            for ($i = $page - 10; $i <= $endPage; $i++) {
                if ($i > 0) {
                    echo "<li style='list-style-type:none'><a class='menuLink' href='adminposts.php?page=$i'>$i</a></li>";
                }
            }
            echo "</ul><hr>";
            if (empty($posts)) {
                echo "<p class='error'>Нет постов для отображения</p>"; 
            } else {
                foreach ($posts as $post) {
            ?>
            <div class='viewpost'>
                <a class='postLink' href='viewsinglepost.php?viewPostById=<?= $post['post_id'] ?>'>
                <div class='posttext'>
                    <p class='posttitle'><?= $post['title'] ?></p>
                    <p class='postcontent'><?= $post['content'] ?></p>
                    <p class='postdate'><?= $post['date_time']. " &copy; " . $post['author'] ?></p>
                    <p class='postrating'>
                    <?php
                        if (!$post['rating']) {
                            echo "Нет оценок. Будьте первым! Kомментариев: " . $post['count_comments'];
                        } else {
                            echo "Рейтинг: " . $post['rating'] . ", оценок: " . $post['count_ratings']
                                    . ", комментариев: " . $post['count_comments'];
                        }  
                    ?>  
                    </p>
                    <object>
                        <a class='link' href='adminposts.php?deletePostById=<?=  $post['post_id']  ?>'>
                            Удалить пост с ID = <?=  $post['post_id']  ?>
                        </a>
                    </object>
                </div>
                <div class='postimage'>
                    <img src='images/PostImgId<?= $post['post_id'] ?>.jpg' alt='Картинка'>
                </div>
                </a>
            </div>
            <?php 
                }
            }
        }
            ?>
    </div>
</div>
<footer>
    <p>Website by Вячеслав Бельский &copy; <?= $year ?><br> Время загрузки страницы: <?= round(microtime(true) - $start, 4) ?> с.</p>
</footer>
</body>
</html>