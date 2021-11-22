<?php
session_start();
$file_functions = join(DIRECTORY_SEPARATOR, array(dirname(__DIR__), 'functions', 'functions.php'));
require_once $file_functions;
$error = []; $users = [];

if (!isset($_SESSION['rights'])) {
    $_SESSION['rights'] = '';
}

if (isset($_GET['deleteUserById'])) {
    $deleteId = clearInt($_GET['deleteUserById']);
    if ($deleteId != false) {
        connectToUsers();
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
        <p class='logo'><a class="logo" href='/'>Просто Блог</a></p>
        
        <div class='msg'>
            <p class='error'>
                <?php
                    if($_SESSION['rights'] != "superuser"){
                        echo "<p class='error'>Необходимо <a class='link' href='/login.php'>войти</a> как администратор</p>";
                        exit;
                    }
                ?>
            </p>
        </div>

        <p class='label'>Список всех пользователей <a href='adminusers.php'> &#8634</a></p>

        <div class='list'>
            <?php 
                $users = connectToUsers();
                if (empty($users) or $users == false) {
                    echo "<p class='error'>Нет пользователей</p>"; 
                } else {
                    echo "<ul class='list'>";
                    
                    for ($i= 0; $i <= count($users)-1; $i++) { //здесь не foreach, чтобы в случае чего вывести в обратном порядке
                        $user = $users[$i];
            ?>

            

            <li class='list'>

                <p class='list'>ID:<?= $user['id'] ?> ::: ФИО(псевдоним): <?= $user['fio'] ?>   ::: Категория: <?= $user['rights'] ?>
                <br>Логин: <?= $user['login'] ?></p>
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
