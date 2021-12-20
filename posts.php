<?php
$start = microtime(true);
session_start();
$functions = 'functions' . DIRECTORY_SEPARATOR . 'functions.php';
require_once $functions;

$_SESSION['referrer'] = "posts.php";

if (isset($_GET['exit'])) {
    $_SESSION['user_id'] = false;
    header("Location:posts.php?");
}

$year = date("Y", time());
if (!empty($_GET['number'])) {
    $numberOfPosts = clearInt($_GET['number']);
    if ($numberOfPosts < 25 || $numberOfPosts > 100) {
        $numberOfPosts = 25;
    }
} else {
    $numberOfPosts = 25;
}
if (!empty($_GET['page'])) {
    $page = clearInt($_GET['page']);
    if ($page <= 0) {
        $page = 1;
    } 
} else {
    $page = 1;
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
$posts = getPostsByNumber($numberOfPosts, ($numberOfPosts * $page) - $numberOfPosts);
?>

<!DOCTYPE html>
<html>

<head>
    <meta charset='UTF-8'>
    <title>Все посты - Просто блог</title>
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
        <div class='singleposttext'>
            <label for='number'>Кол-во постов: <select id='number' class='select' name="number" onchange="window.location.href=this.options[this.selectedIndex].value"></label>
                <?php
                    for ($i = 25; $i <= 100; $i+=25) {
                        if ($i != $numberOfPosts) {
                            echo "<option value='posts.php?number=$i&page=$page'>$i</option>";
                        } else {
                            echo "<option value='$numberOfPosts' selected>$numberOfPosts</option>";
                        }
                    }
                ?>
            </select>
            <label for='page'>Страница: <select id='page' class='select' name="page" onchange="window.location.href=this.options[this.selectedIndex].value"></label>
                <?php
                    count($posts) < $numberOfPosts ? $endPage = $page : $endPage = $page + 3;
                    for ($i = $page - 3; $i <= $endPage; $i++) {
                        if ($i > 0) {
                            if ($i != $page) {
                                echo "<option value='posts.php?number=$numberOfPosts&page=$i'>$i</option>";
                            } else {
                                echo "<option value='$page' selected>$page</option>";
                            }
                        }
                    }
                ?>
            </select>
        </div>
        <?php 
            if (empty($posts)) {
                echo "<p>Нет постов для отображения</p>";    
            } else {
                foreach ($posts as $post) {
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
            }
        ?>
    </div>
</div>
<footer>
    <p>Website by Вячеслав Бельский &copy; <?= $year ?><br> Время загрузки страницы: <?= round(microtime(true) - $start, 4) ?> с.</p>
</footer>
</body>
</html>