<?php
session_start();
$functions = join(DIRECTORY_SEPARATOR, array('functions', 'functions.php'));
require_once $functions;
$link = "<a class='menu' href='login.php'>Войти</a>";
$label = "<a class='menu' href='login.php'>Вы не авторизованы</a>";
$adminLink = '';
    
if (isset($_GET['exit'])) {
    $_SESSION['log_in'] = false;
    session_destroy();
    header("Location: /");
} 

if (isset($_SESSION['log_in']) && $_SESSION['log_in']) {
    $login = $_SESSION['login'];
    $label = "<a class='menu' href='cabinet.php'>Перейти в личный кабинет</a>";
    $link = "<a class='menu' href='?exit'>Выйти</a>";
    if ($_SESSION['rights'] == 'superuser') {
        $adminLink = "<a class='menu' href='admin/admin.php'>Админка</a>";
    }
} else {
    session_destroy();
}

$year = date("Y", time());
$posts = getPostsForIndex();
?>



<!DOCTYPE html>
<html>

<head>
    <meta charset='UTF-8'>
    <title>Главная - Просто блог</title>
    <link rel='stylesheet' href='css/indexcss.css'>
</head>
<body>
    <nav>
    <div class='top'>
        <div class="logo">
            <a class="logo" title="На главную" href='/'>
            <img id='logo' src='images/logo.jpg' alt='Лого' width='50' height='50'>
            <div id='namelogo'>Просто Блог</div>
            </а>
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

        <?php 
            if (empty($posts) or $posts == false) {
                die("<p>Нет постов для отображения</p>");    
            } else {
                $num = count($posts) - 1;
                $post = $posts[$num];
        ?>

        <a class='onepost' href="viewsinglepost.php?viewPostById=<?=$post['id']?>">
        <div class='viewonepost'>
            
            <div class='oneposttext'>
                <p class='onepostzagolovok'><?=$post['name']?></p>
                <p class='onepostcontent'><?=$post['content']?></p>
                <p class='postdate'><?=$post['date']. " " . $post['author']?></p>

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
            <div class='onepostimage'>
                <img src='images/PostImgId<?=$post['id']?>.jpg' alt='Картинка' class='onepostimage'>
            </div>
        </div>
        </a>

        <?php
            $num--;
            $minId = 0;

            if ($num > 9) {
                $minId = $num - 8;  //Благодаря minId вывожу всего в общем и целом 10 постов
            }

            for ($id = $num; $id >= $minId; $id--) { 
                $post = $posts[$id];
        ?>

        <div class='viewsmallposts'>

            <a class='post' href='viewsinglepost.php?viewPostById=<?=$post['id']?>'>
            <div class='smallpost'>

                 <div class='smallposttext'>
                    <p class='smallpostzagolovok'><?=$post['name_small']?></p>
                    <p class='smallpostcontent'><?=$post['content_small']?></p>
                    <p class='postdate'><?=$post['date']. " " . $post['author']?></p>
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
        <p>Website by Вячеслав Бельский &copy; <?=$year?></p>
    </footer>
</div>
</body>
</html>