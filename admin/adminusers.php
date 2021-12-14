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
                if (empty($userIds)) {
                    echo "<p class='error'>Нет пользователей</p>"; 
                } else {
                    echo "<ul class='list'>";
                    
                    foreach ($userIds as $userId) {
                        $user = getUserEmailFioRightsById($userId);
            ?>

            

            <li class='list'>

                <p class='list'>ID:<?= $userId ?> ::: ФИО(псевдоним): <?= $user['fio'] ?>   ::: Категория: <?= $user['rights'] ?>
                <br>Логин: <?= $user['email'] ?></p>
                <a class='list' href='adminusers.php?deleteUserById=<?= $userId ?> '> Удалить <?= $user['rights'] ?> -а</a>
                <hr>

            </li>
        

        <?php 
                } echo "</ul>";
            }
            echo "</div>";
        }
        ?>
    </div>
</div>
</body>
</html>