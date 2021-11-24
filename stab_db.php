<?php
session_start();
require_once 'dbconfig.php';

$j = 1; $_SESSION['j'] = 1;



if (isset($_SESSION['bool']) && $_SESSION['bool'] == true) {
    $_SESSION['j'] += 10; 
    $j = $_SESSION['j'];
} else {
    $_SESSION['j'] = 0;
}


try {
    $db = new PDO("mysql:host=$host;dbname=$dbname", $username, $password);
} catch (PDOException $e) {
    require_once "init_db.php";
}

try { 
    for ($i = $j; $i <= $j + 9; $i++) {

        $date = time();
        $sql = "INSERT INTO posts (id, name, author, date, content, rating) 
        VALUES($i, $i, $i, $date, $i, 0);";

        $db->exec($sql);

        $sql = "INSERT INTO comments (post_id, author, date, content, rating) 
        VALUES($i, $i, $date, $i, 0);";

        $db->exec($sql);

        $password = password_hash($i, PASSWORD_BCRYPT);
        $password = $db->quote($password);
        $sql = "INSERT INTO users (login, fio, password, rights) 
        VALUES($i, $i, $password, 'user');";

        $db->exec($sql);
        $_SESSION['bool'] = true;
    }
    echo "Подключение к БД: успешно<br>Создано 10 новых постов, 10 новых комментариев и 10 новых пользователей. <a href='/'>На главную</a>";
} catch (PDOException $e) {
    echo $error = $e->getMessage();
}