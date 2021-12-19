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
if (isset($_GET['deleteUserById'])) {
    $deleteId = clearInt($_GET['deleteUserById']);
    if ($deleteId != false) {
        deleteUserById($deleteId);
        header("Location: adminusers.php");
    }
}
if (isset($_GET['page'])) {
    $page = clearInt($_GET['page']);
} else {
    $page = 1;
}
$numberOfUsers= 50;
?>

<!DOCTYPE html>
<html>
<head>
    <meta charset='UTF-8'>
    <title>Управление пользователями - Просто Блог</title>
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
        ?>

        <p class='label'>
            Список всех пользователей <br>
            (одна страница - <?= $numberOfUsers?> пользователей)
            <a href='adminusers.php'> &#8634</a>
        </p>

        <div class='list'>
            <?php 
                $users = getUsersByNumber(50, $numberOfUsers* $page - $number);
                echo "<p style='padding-left:3vmin'><span>Страницы:</span></p>";
                echo "<ul class ='list'>";
                for ($i = $page - 3; $i <= $page + 3; $i++) {//обманываю пользователя, что есть ещё страницы
                    if ($i > 0) {
                        echo "<li><a class='menuLink' href='adminusers.php?page=$i'>$i</a></li>";
                    }
                }
                echo "</ul><hr>";
                echo "<div class='list'>";
                echo "<ul class='list'>";
                
                foreach ($users as $user) {
                    $user['date_time'] = date("d.m.Y в H:i", $user['date_time']);
            ?>

            

            <li class='list'>

                <p class='list'>ID:<?= $user['id'] ?> ::: ФИО(псевдоним): <?= $user['fio'] ?>   ::: Категория: <?= $user['rights'] ?>
                    <br>E-mail: <?= $user['email'] ?>
                    <br>Дата регистрации: <?= $user['date_time'] ?>
                    <br><a class='link' href='/cabinet.php?user=<?=$user['id']?>'>Перейти в профиль пользователя</a>
                </p>
                <a class='list' href='adminusers.php?deleteUserById=<?= $user['id'] ?> '> Удалить <?= $user['rights'] ?> -а</a>
                <hr>

            </li>
        

            <?php 
                } echo "</ul>";
            }
            ?>
        </div>  
    </div>
</div>
</body>
</html>