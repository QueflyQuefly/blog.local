<?php
$start = microtime(true);
session_start();
$file_functions = dirname(__DIR__) . DIRECTORY_SEPARATOR . 'functions' . DIRECTORY_SEPARATOR . 'functions.php';
require_once $file_functions;

$_SESSION['referrer'] = $_SERVER['REQUEST_URI'];

if (!empty($_SESSION['user_id']) && strpos($_SESSION['user_id'], RIGHTS_SUPERUSER) !== false) {
    $rights = RIGHTS_SUPERUSER;
} else {
    $rights = false;
}
if (isset($_GET['deleteUserById']) && $rights === RIGHTS_SUPERUSER) {
    $deleteId = clearInt($_GET['deleteUserById']);
    if ($deleteId != false) {
        deleteUserById($deleteId);
        header("Location: adminusers.php");
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
$numberOfUsers = 50;
$year = date("Y", time());
?>

<!DOCTYPE html>
<html>
<head>
    <meta charset='UTF-8'>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Управление пользователями - Просто Блог</title>
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
                    if (empty($_SESSION['user_id'])) {
                        echo "<li><a class='menuLink' href='/login.php'>Войти</a></li>";
                    } else {
                        echo "<li><a class='menuLink' href='/index.php?exit'>Выйти</a></li>";
                        if (strpos($_SESSION['user_id'], RIGHTS_SUPERUSER) !== false) {
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
            if ($rights !== "superuser") {
                echo "<p class='error'>Необходимо <a class='link' href='/login.php'>войти</a> как администратор</p>";
            } else {
        ?>

        <div id='desc'>
            <p>Управление пользователями<br>
            (одна страница - <?=  $numberOfUsers  ?> пользователей)
            <a href='adminusers.php'> &#8634</a></p>
        </div>
            <?php 
                $users = getUsersByNumber($numberOfUsers, $numberOfUsers * $page - $numberOfUsers);
                echo "<p style='padding-left:3vmin'><span>Страницы:</span></p>";
                echo "<ul style='display: inline-flex;'>";
                for ($i = $page - 3; $i <= $page + 3; $i++) {//обманываю пользователя, что есть ещё страницы
                    if ($i > 0) {
                        echo "<li style='list-style-type:none'><a class='menuLink' href='adminusers.php?page=$i'>$i</a></li>";
                    }
                }
                echo "</ul><hr>";
                foreach ($users as $user) {
                    $user['date_time'] = date("d.m.Y в H:i", $user['date_time']);
            ?>

        <div class='viewpost'>
            <a class='postLink' href='cabinet.php?user=<?= $user['user_id'] ?>'>
                <div class='posttext'>
                    <p class='postzagolovok'> Просмотр дополнительной информации по нажатию</p>
                    <p class='postzagolovok'> ФИО(псевдоним): <?=  $user['fio']  ?></p>
                    <p class='postzagolovok'> Дата регистрации: <?=  $user['date_time']  ?></p>
                    <p class='postzagolovok'> Категория: <?=  $user['rights']  ?></p>
                    <p class='postzagolovok'>ID: <?=  $user['user_id']  ?> </p>
                    <p class='postzagolovok'>E-mail: <?=  $user['email']  ?></p>
                    <p class='postdate'><object><a class='list' href='adminusers.php?deleteUserById=<?=  $user['user_id']  ?> '> Удалить <?=  $user['rights']  ?>-а</a></object>
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