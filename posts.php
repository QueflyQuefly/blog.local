<?php
$start = microtime(true);
session_start();
$functions = 'functions' . DIRECTORY_SEPARATOR . 'functions.php';
require_once $functions;
$link = "<a class='menu' href='login.php'>Войти</a>";
$label = "<a class='menu' href='login.php'>Вы не авторизованы</a>";
$adminLink = '';
    
if (isset($_GET['exit'])) {
    $_SESSION['user_id'] = false;
    header("Location: /");
} 

if (!empty($_SESSION['user_id'])) {
    $user = getUserEmailFioRightsById($_SESSION['user_id']);
    $label = "<a class='menu' href='cabinet.php'>Перейти в личный кабинет</a>";
    $link = "<a class='menu' href='?exit'>Выйти</a>";
    if ($user['rights'] === 'superuser') {
        $adminLink = "<a class='menu' href='admin/admin.php'>Админка</a>";
    }
}

$year = date("Y", time());
$ids = getPostIds();
$countIds = count($ids);
if (!empty($_GET['number'])) {
    $number = clearInt($_GET['number']);
    if ($number <= 0 or $number >= $countIds) {
        $number = 10;
    }
} else {
    $number = $countIds;
    if ($number > 10){
        $number = 10;
    }
}
if (!empty($_GET['page'])) {
    $page = clearInt($_GET['page']);
    if ($page <= 0 or $page >= $countIds / $number + 1) {
        $page = 1;
    }
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
                <li class='menu'><?=$link?></li>
                <li class='menu'><a class='menu' href='search.php'>Поиск</a></li>
                <li class='menu'><a class='menu' href='addpost.php'>Создать новый пост</a></li>
                <li class='menu'><?=$label?></li>
                <li class='menu'><?=$adminLink?></li>
            </ul>
        </div>
    </div>
</nav>
<div class='allwithoutmenu'>
    <div class='content'>

        <div id='desc'><p>Наилучший источник информации по теме "Путешествия"</p></div>
        <div class='singleposttext'>
            <label for='number'>Кол-во постов: <select id='number' class='select' name="number" onchange="window.location.href=this.options[this.selectedIndex].value"></label>
                <option value='<?=$number?>' selected><?=$number?></option>
                <?php
                    $i = 1;
                    echo $countIds / $page;
                    while (($i <= $countIds / $page / 25) && ($i <= 4)) {
                        $interval = $i * 25;
                        if ($interval != $number) {
                            echo "<option value='posts.php?number=$interval&page=$page'>$interval</option>";
                        }
                        $i++;
                    }
                ?>
            </select>
            <label for='page'>Страница: <select id='page' class='select' name="page" onchange="window.location.href=this.options[this.selectedIndex].value"></label>
                <option value='<?=$page?>' selected><?=$page?></option>
                <?php
                    for ($i = 1; $i < $countIds / $number + 1; $i++) {
                        if ($i != $page) {
                            echo "<option value='posts.php?number=$number&page=$i'>$i</option>";
                        }
                    }
                ?>
            </select>
        </div>


        <?php 
            if (empty($ids)) {
                die("<p>Нет постов для отображения</p>");    
            } else {
                $countIds = $number * ($page - 1);
                if ($countIds < 0) {
                    $number += $countIds;
                    $countIds = 0;
                }
                $ids = array_slice($ids, $countIds, $number);
                foreach ($ids as $id) {
                    $posts[] = getPostForIndexById($id);
                }
                foreach ($posts as $post) {
                    $authorOfPost = getUserEmailFioRightsById($post['user_id']);
                    $fioOfAuthor = $authorOfPost['fio'];
        ?>

        <div class='viewsmallposts'>

            <a class='post' href='viewsinglepost.php?viewPostById=<?=$post['id']?>'>
            <div class='smallpost'>

                 <div class='smallposttext'>
                    <p class='smallpostzagolovok'><?=$post['zag_small']?></p>
                    <p class='smallpostcontent'><?=$post['content_small']?></p>
                    <p class='postdate'><?=$post['date_time']. " " . $fioOfAuthor?></p>
                    <p class='postrating'>
                        <?php
                            if (!$post['rating']) {
                                echo "Нет оценок. Будьте первым!";
                            } else {
                                echo "Рейтинг поста: " . $post['rating'];
                            }     
                        ?>  
                    </p>
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

    </div>

    <footer class='bottom'>
        <p>Website by Вячеслав Бельский &copy; <?=$year?><br> Время загрузки страницы: <?=round(microtime(true) - $start, 4)?> с.</p>
    </footer>
</div>
</body>
</html>