<?php
$start = microtime(true);
session_start();
$functions = 'functions' . DIRECTORY_SEPARATOR . 'functions.php';
require_once $functions;

$_SESSION['referrer'] = "posts.php";

if (isset($_GET['exit'])) {
    $_SESSION['user_id'] = false;
    header("Location: posts.php");
}

$year = date("Y", time());
$postIds = getPostIds();
$countIdsOfPosts = count($postIds);
if (!empty($_GET['number'])) {
    $numberOfPosts = clearInt($_GET['number']);
    if ($numberOfPosts < 1 || $numberOfPosts >= $countIdsOfPosts) {
        $numberOfPosts = 25;
    }
} else {
    $numberOfPosts = $countIdsOfPosts;
    if ($numberOfPosts < 1 || $numberOfPosts > 25){
        $numberOfPosts = 25;
    }
}
if (!empty($_GET['page']) && $_GET['page'] >= 0 && $countIdsOfPosts != 0 && $_GET['page'] <= $countIdsOfPosts / $numberOfPosts + 1) {
    $page = clearInt($_GET['page']);
} else {
    $page = 1;
}
?>



<!DOCTYPE html>
<html>

<head>
    <meta charset='UTF-8'>
    <title>Все посты - Просто блог</title>
    <link rel='stylesheet' href='css/indexcss.css'>
    <link rel="shortcut icon" href="/images/logo.jpg" type="image/x-icon">
</head>
<body>
    <nav>
    <div class='top'>
        <div class="logo">
            <a class="logo" title="На главную" href='/'>
                <img id='logo' src='images/logo.jpg' alt='Лого' width='50' height='50'>
                <div id='namelogo'>Просто Блог</div>
            </a>    
        </div>
        <div class="menu">
            <ul class='menu'>
                <?php
                    if (empty($_SESSION['user_id'])) {
                        echo "<li class='menu'><a class='menu' href='login.php'>Войти</a></li>";
                    } else {
                        echo "<li class='menu'><a class='menu' href='?exit'>Выйти</a></li>";
                        if (getUserInfoById($_SESSION['user_id'], 'rights') === 'superuser') {
                            echo "<li class='menu'><a class='menu' href='admin/admin.php'>Админка</a></li>";
                        }
                    }
                ?>
                <li class='menu'><a class='menu' href='cabinet.php'>Мой профиль</a></li>
                <li class='menu'><a class='menu' href='search.php'>Поиск</a></li>
                <li class='menu'><a class='menu' href='addpost.php'>Создать новый пост</a></li>
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
                    $i = 1;
                    while (($i <= $countIdsOfPosts / $page / 25 + 1) && ($i <= 4)) {
                        $interval = $i * 25;
                        if ($interval != $numberOfPosts) {
                            echo "<option value='posts.php?number=$interval&page=$page'>$interval</option>";
                        } else {
                            echo "<option value='$numberOfPosts' selected>$numberOfPosts</option>";
                        }
                        $i++;
                    }
                ?>
            </select>
            <label for='page'>Страница: <select id='page' class='select' name="page" onchange="window.location.href=this.options[this.selectedIndex].value"></label>
                <?php
                    if ($page > 3) {
                        $j = $page - 3;
                    } else {
                        $j = 1;
                    }
                    for ($i = $j; $i < $countIdsOfPosts / $numberOfPosts + 1 && $i <= $page + 3; $i++) {
                        if ($i != $page) {
                            echo "<option value='posts.php?number=$numberOfPosts&page=$i'>$i</option>";
                        } else {
                            echo "<option value='$page' selected>$page</option>";
                        }
                    }
                ?>
            </select>
        </div>


        <?php 
            if (empty($postIds)) {
                echo "<p>Нет постов для отображения</p>";    
            } else {
                $postIds = array_slice($postIds, $page * $numberOfPosts - $numberOfPosts, $numberOfPosts);
                foreach ($postIds as $postId) {
                    $post = getPostForIndexById($postId);
        ?>

        <div class='viewsmallposts'>

            <a class='post' href='viewsinglepost.php?viewPostById=<?=$post['post_id']?>'>
            <div class='smallpost'>

                 <div class='smallposttext'>
                    <p class='smallpostzagolovok'><?=$post['zag_small']?></p>
                    <p class='smallpostcontent'><?=$post['content_small']?></p>
                    <p class='postdate'><?=$post['date_time']. " " . $post['author']?></p>
                    <p class='postrating'>
                        <?php
                            if (!$post['rating']) {
                                echo "Нет оценок. Будьте первым!";
                            } else {
                                echo "Рейтинг: " . $post['rating'] . ", оценок: " . $post['countRatings'];
                            }     
                        ?>  
                    </p>
                </div>

                <div class='smallpostimage'>
                    <img src='images/PostImgId<?=$post['post_id']?>.jpg' alt='Картинка' class='smallpostimage'>
                </div>
               
            </div>
            </a>

        </div>

        <?php
                }
            }
        ?>

    </div>

    <footer class='bottom'>
        <p>Website by Вячеслав Бельский &copy; <?=$year?><br> Время загрузки страницы: <?=round(microtime(true) - $start, 4)?> с.</p>
    </footer>
</div>
</body>
</html>