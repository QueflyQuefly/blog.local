<?php
$start = microtime(true);
session_start();
$functions = 'functions' . DIRECTORY_SEPARATOR . 'functions.php';
require_once $functions;

$twoDaysInseconds = 60*60*24*2;
header("Cache-Control: max-age=$twoDaysInseconds");
header("Cache-Control: must-revalidate");
    
if (!empty($_COOKIE['user_id'])) {
    $_SESSION['user_id'] = $_COOKIE['user_id'];
}
if (!empty($_SESSION['user_id']) && strpos($_SESSION['user_id'], RIGHTS_SUPERUSER) !== false) {
    if (isset($_GET['deletePostById'])) {
        $deletePostId = clearInt($_GET['deletePostById']);
        if ($deletePostId !== '') {
            deletePostById($deletePostId);
            header("Location: cabinet.php?user=$userId");
        } 
    }
}
if (isset($_GET['exit']) && !empty($_SESSION['user_id'])) {
    $_SESSION['user_id'] = false;
    setcookie('user_id', '0', 1);
    header("Location: /");
}
$year = date("Y", time());
$posts = getPostsByNumber(10);
?>

<!DOCTYPE html>
<html>

<head>
    <meta charset='UTF-8'>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Главная - Просто блог</title>
    <link rel='stylesheet' href='css/general.css'>
    <link rel="shortcut icon" href="/images/logo.jpg" type="image/x-icon">
</head>
<body>
<nav>
    <div class='top'>
        <div id="logo">
            <a class="logo" title="На главную" href='/'>
            <img id='imglogo' src='images/logo.jpg' alt='Лого'>
            <div id='namelogo'>Просто Блог</div>
            </а>
        </div>
        <div id="menu">
            <ul class='menuList'>
                <?php
                    if (empty($_SESSION['user_id'])) {
                        echo "<li><a class='menuLink' href='login.php'>Войти</a></li>";
                    } else {
                        echo "<li><a class='menuLink' href='?exit'>Выйти</a></li>";
                        if (strpos($_SESSION['user_id'], RIGHTS_SUPERUSER) !== false) {
                            echo "<li><a class='menuLink' href='admin/admin.php'>Админка</a></li>";
                        }
                    }
                ?>
                <li><a class='menuLink' href='cabinet.php'>Мой профиль</a></li>
                <li><a class='menuLink' href='search.php'>Поиск</a></li>
                <li><a class='menuLink' href='addpost.php'>Создать новый пост</a></li>
            </ul>
        </div>
    </div>
</nav>
<div class='allwithoutmenu'>
    <div class='content'>

        <div id='desc'><p>Наилучший источник информации по теме "Путешествия"</p></div>

        <?php 
            if (empty($posts)) {
                echo "<p>Нет постов для отображения</p>";    
            } else {
                foreach ($posts as $key => $post) {
                    $class = 'viewpost';
                    if ($key == 0) {
                        $class = 'generalpost';
                    }
                    $post['date_time'] = date("d.m.Y в H:i", $post['date_time']);
        ?>
            <div class='<?=  $class  ?>'>
                <a class='postLink' href='viewsinglepost.php?viewPostById=<?= $post['post_id'] ?>'>
                 <div class='posttext'>
                    <p class='postzagolovok'><?= $post['zag'] ?></p>
                    <p class='postcontent'><?= $post['content'] ?></p>
                    <p class='postdate'><?= $post['date_time']. " &copy; " . $post['author'] ?></p>
                    <p class='postrating'>
                    <?php
                        if (!$post['rating']) {
                            echo "Нет оценок. Будьте первым!";
                        } else {
                            echo "Рейтинг: " . $post['rating'] . ", оценок: " ; //. $post['countRatings'];
                        }     
                    ?>  
                    </p>
                    <?php
                        if (!empty($_SESSION['user_id']) && strpos($_SESSION['user_id'], RIGHTS_SUPERUSER) !== false) {
                    ?>
                        <object>
                            <a class='link' href='posts.php?deletePostById=<?=  $post['post_id']  ?>'>
                                Удалить пост с ID = <?=  $post['post_id']  ?>
                            </a>
                        </object>
                    <?php
                        } 
                    ?>
                </div>
                <div class='postimage'>
                    <img src='images/PostImgId<?= $post['post_id'] ?>.jpg' alt='Картинка'>
                </div>
                </a>
            </div>

            <?php
                    }
                echo "<p class='center'><a class='submit' href='posts.php'>Посмотреть ещё</a></p>";
                }
                $moreTalkedPosts = getMoreTalkedPosts(3);
                if (!empty($moreTalkedPosts)) {
                    echo "<div class='searchdescription'><div class='singleposttext'>Самые обсуждаемые посты за неделю	&darr;&darr;&darr;</div></div>";
                    foreach ($moreTalkedPosts as $post) {
                        $post['date_time'] = date("d.m.Y в H:i", $post['date_time']);
            ?>

            <div class='viewpost'>
                <a class='postLink' href='viewsinglepost.php?viewPostById=<?= $post['post_id'] ?>'>
                <div class='posttext'>
                    <p class='postzagolovok'><?= $post['zag'] ?></p>
                    <p class='postcontent'><?= $post['content'] ?></p>
                    <p class='postdate'><?= $post['date_time']. " &copy; " . $post['author'] ?></p>
                    <p class='postrating'>
                    <?php
                        if (!$post['rating']) {
                            echo "Нет оценок. Будьте первым!";
                        } else {
                            echo "Рейтинг поста: " . $post['rating'];
                        }     
                    ?>  
                    </p>
                    <?php
                        if (!empty($_SESSION['user_id']) && strpos($_SESSION['user_id'], RIGHTS_SUPERUSER) !== false) {
                    ?>
                        <object>
                            <a class='link' href='posts.php?deletePostById=<?=  $post['post_id']  ?>'>
                                Удалить пост с ID = <?=  $post['post_id']  ?>
                            </a>
                        </object>
                    <?php
                        } 
                    ?>
                </div>
                <div class='postimage'>
                    <img src='images/PostImgId<?= $post['post_id'] ?>.jpg' alt='Картинка'>
                </div>
                </a>
            </div>

        <?php
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