<?php
session_start();
$file_functions = join(DIRECTORY_SEPARATOR, array(dirname(__DIR__), 'functions', 'functions.php'));
require_once $file_functions;
$error = []; $users = [];

if (!empty($_SESSION['user_id'])) {
    $user = getLoginFioRightsById($_SESSION['user_id']);
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
        
        <div class='msg'>
            <p class='error'>
                <?php
                    if ($rights !== "superuser") {
                        echo "<p class='error'>Необходимо <a class='link' href='/login.php'>войти</a> как администратор</p>";
                        exit;
                    }
                ?>
            </p>
        </div>

        <p class='label'>Список всех пользователей <a href='adminusers.php'> &#8634</a></p>

        <div class='list'>
            <?php 
                $usersIds = getUsersIds();
                if (empty($usersIds)) {
                    echo "<p class='error'>Нет пользователей</p>"; 
                } else {
                    echo "<ul class='list'>";
                    
                    foreach ($usersIds as $userId) {
                        $user = getLoginFioRightsById($userId);
            ?>

            

            <li class='list'>

                <p class='list'>ID:<?= $userId ?> ::: ФИО(псевдоним): <?= $user['fio'] ?>   ::: Категория: <?= $user['rights'] ?>
                <br>Логин: <?= $user['login'] ?></p>
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