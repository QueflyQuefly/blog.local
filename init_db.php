<?php
require_once 'dbconfig.php';
try {
    $db = new PDO("mysql:host=$host", $username, $password);

    $password = password_hash('1', PASSWORD_BCRYPT);
    $password = $db->quote($password);
    $userId = uniqid(RIGHTS_SUPERUSER);
    $userId = $db->quote($userId);
    $date = time();
    $rights = $db->quote(RIGHTS_SUPERUSER);

    $sql = "CREATE DATABASE $dbname;

        USE $dbname;

        CREATE TABLE users
        (
        id INT UNSIGNED AUTO_INCREMENT,
        user_id VARCHAR(30),
        email VARCHAR(50),
        fio VARCHAR(50),
        pass_word CHAR(60),
        date_time INT UNSIGNED,
        rights VARCHAR(20),
        PRIMARY KEY (id)
        );


        CREATE TABLE posts
        (
        post_id INT UNSIGNED AUTO_INCREMENT,
        title TINYTEXT,
        user_id VARCHAR(30),
        date_time INT UNSIGNED,
        content TEXT,
        PRIMARY KEY (post_id)
        );


        CREATE TABLE comments
        (
        com_id INT UNSIGNED AUTO_INCREMENT,
        post_id INT UNSIGNED,
        user_id VARCHAR(30),
        date_time INT UNSIGNED,
        content TEXT,
        rating INT UNSIGNED,
        PRIMARY KEY (com_id)
        );


        CREATE TABLE rating_posts
        (
        id INT UNSIGNED AUTO_INCREMENT,
        user_id VARCHAR(30),
        post_id INT UNSIGNED,
        rating DECIMAL(1),
        PRIMARY KEY (id)
        );
        

        CREATE TABLE additional_info_posts
        (
        id INT UNSIGNED AUTO_INCREMENT,
        post_id INT UNSIGNED,
        rating DECIMAL(2,1),
        count_comments INT UNSIGNED,
        count_ratings INT UNSIGNED,
        PRIMARY KEY (id)
        );


        CREATE TABLE rating_comments
        (
        id INT UNSIGNED AUTO_INCREMENT,
        user_id VARCHAR(30),
        com_id INT UNSIGNED,
        post_id INT UNSIGNED,
        PRIMARY KEY (id)
        );


        CREATE TABLE tag_posts
        (
        id INT UNSIGNED AUTO_INCREMENT,
        tag TINYTEXT,
        post_id INT UNSIGNED,
        PRIMARY KEY (id)
        );


        CREATE TABLE subscriptions
        (
        id INT UNSIGNED AUTO_INCREMENT,
        user_id_want_subscribe VARCHAR(30),
        user_id VARCHAR(30),
        PRIMARY KEY (id)
        );


        INSERT INTO users
        (user_id, email, fio, pass_word, date_time, rights) 
        VALUES ($userId, '1@1.1', 'Администратор', $password, $date, $rights)
        ;";

    if (!$db->exec($sql)) {
        echo $sql;
        echo $error = "База данных не создана";
    }
} catch(PDOException $e) {
    echo $error = $e->getMessage();
}