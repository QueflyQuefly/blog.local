<?php
session_start();
$functions = join(DIRECTORY_SEPARATOR, array('functions', 'functions.php'));
require_once $functions;
$link = "<a class='menu' href='login.php'>Войти</a>";
$label = "<a class='menu' href='login.php'>Вы не авторизованы</a>";
$adminLink = '';
    
if (isset($_GET['exit'])) {
    $_SESSION['user_id'] = false;
    header("Location: /");
} 

if (!empty($_SESSION['user_id'])) {
    $user = getLoginFioRightsById($_SESSION['user_id']);
    $login = $user['login'];
    $fio = $user['fio'];
    $rights = $user['rights'];
    $label = "<a class='menu' href='cabinet.php'>Перейти в личный кабинет</a>";
    $link = "<a class='menu' href='?exit'>Выйти</a>";
    if ($rights == 'superuser') {
        $adminLink = "<a class='menu' href='admin/admin.php'>Админка</a>";
    }
}

$year = date("Y", time());
$ids = getPostIds();
if (!empty($ids)) {
    krsort($ids);
}
if (!empty($_GET['number'])) {
    $number = clearInt($_GET['number']);
} else {
    $number = 10;
}
if (!empty($_GET['page'])) {
    $page = clearInt($_GET['page']);
} else {
    $page = 1;
}
?>



<!DOCTYPE html>
<html>

<head>
    <meta charset='UTF-8'>
    <title>Главная - Просто блог</title>
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
        <div class='singleposttext'>
            <form action='posts.php' method='get'>
                <label for='number'>Кол-во постов: <select id='page' name="number"></label>
                    <?php
                        for ($i = 1; $i < count($ids) / $page; $i++) {
                            echo "<option value='$i'>$i</option>";
                        }
                    ?>
                    <option value='<?=$number?>' selected><?=$number?></option>
                </select>
                <label for='page'>Страница: <select id='page' name="page"></label>
                    <?php
                        for ($i = 1; $i < count($ids) / $number + 1; $i++) { //+1 означает, что иногда нужна еще одна последняя страница
                            echo "<option value='$i'>$i</option>";
                        }
                    ?>
                    <option value='<?=$page?>' selected><?=$page?></option>
                </select>
                <input type='submit' class='submit' value='Применить'>
            </form>
        </div>


        <?php 
            if (empty($ids)) {
                die("<p>Нет постов для отображения</p>");    
            } else {
                foreach ($ids as $id) {
                    $posts[] = getPostForIndexById($id);
                }

                $page = $page * $number - $number + 1;

                $num = count($posts) - $page;
                if ($num < 0) {
                    $num = 0;
                }
                $post = $posts[$num];
                $minId = $num - $number + 1;
                if ($minId < 0) {
                    $minId = 0;
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