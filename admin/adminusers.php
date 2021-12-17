<?php
session_start();
$file_functions = dirname(__DIR__) . DIRECTORY_SEPARATOR . 'functions' . DIRECTORY_SEPARATOR . 'functions.php';
require_once $file_functions;

$_SESSION['referrer'] = $_SERVER['REQUEST_URI'];

if (!empty($_SESSION['user_id'])) {
    $user = getUserEmailFioRightsById($_SESSION['user_id']);
    $rights = $user['rights'];
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
$number = 50;
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

        <p class='label'>Список всех пользователей <a href='adminusers.php'> &#8634</a></p>

        <div class='list'>
            <?php 
                $userIds = getUsersIds();
                echo "<p style='padding-left:3vh'><span>Страницы:</span></p>";
                echo "<ul class ='list'>";
                for ($i = 1; $i <= count($userIds)/ 50 + 1; $i++) {
                    echo "<li class='menu'><a class='menu' href='adminusers.php?page=$i'>$i</a></li>";
                }
                echo "</ul><hr>";

                $countIdsOfUsers = $number * ($page - 1);
                if ($countIdsOfUsers < 0) {
                    $number += $countIdsOfUsers;
                    $countIdsOfUsers = 0;
                }
                $userIds = array_slice($userIds, $countIdsOfUsers, $number);

                echo "<div class='list'>";
                echo "<ul class='list'>";
                
                foreach ($userIds as $userId) {
                    $user = getUserEmailFioRightsById($userId);
                    $comments = getCommentsByUserId($userId);
                    $countComments = count($comments);
            ?>

            

            <li class='list'>

                <p class='list'>ID:<?= $userId ?> ::: ФИО(псевдоним): <?= $user['fio'] ?>   ::: Категория: <?= $user['rights'] ?>
                    <br>E-mail: <?= $user['email'] ?>
                    <br>Комментариев: <?= $countComments ?>
                    <br><a class='link' href='/cabinet.php?user=<?=$userId?>'>Перейти в профиль пользователя</a>
                </p>
                <a class='list' href='adminusers.php?deleteUserById=<?= $userId ?> '> Удалить <?= $user['rights'] ?> -а</a>
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