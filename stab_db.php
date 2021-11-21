<?php
session_start();
require_once 'dbconfig.php';

if ($_SESSION['bool']) {
    $_SESSION['j'] += 10; 
    $j = $_SESSION['j'];
} else {
    $j = 1;
}


try {
    $db = new PDO("mysql:host=$host;dbname=$dbname", $username, $password);
} catch (PDOException $e) {
    require_once "init_db.php";
}

try { 
    for ($i = $j; $i <= $j + 9; $i++) {

        $date = time();

        $sql = "INSERT INTO posts (id, name, author, date, content) 
        VALUES($i, $i, $i, $date, $i);";

        $db->exec($sql);

        $sql = "INSERT INTO comments (post_id, author, date, content) 
        VALUES($i, $i, $date, $i);";

        $db->exec($sql);

        $password = password_hash($i, PASSWORD_BCRYPT);
        $password = $db->quote($password);
        $sql = "INSERT INTO users (login, fio, password, rights) 
        VALUES($i, $i, $password, 'user');";

        $db->exec($sql);
        $_SESSION['bool'] = true;
    }
    echo "Подключение к БД: успешно<br>Создано 10 новых постов, 10 новых комментариев и 10 новых пользователей";
} catch (PDOException $e) {
    echo $error = $e->getMessage();
}