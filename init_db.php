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
        id INT  AUTO_INCREMENT,
        user_id VARCHAR(30),
        email VARCHAR(50),
        fio VARCHAR(50),
        pass_word CHAR(60),
        date_time INT,
        rights VARCHAR(20),
        PRIMARY KEY (id)
        );


        CREATE TABLE posts
        (
        post_id INT AUTO_INCREMENT,
        zag TEXT,
        user_id VARCHAR(30),
        date_time INT,
        content TEXT,
        rating DOUBLE,
        PRIMARY KEY (post_id)
        );


        CREATE TABLE comments
        (
        com_id INT AUTO_INCREMENT,
        post_id INT,
        user_id VARCHAR(30),
        date_time INT,
        content TEXT,
        rating INT,
        PRIMARY KEY (com_id)
        );


        CREATE TABLE rating_posts
        (
        id INT AUTO_INCREMENT,
        user_id VARCHAR(30),
        post_id INT,
        rating TINYINT,
        PRIMARY KEY (id)
        );
        

        CREATE TABLE rating_comments
        (
        id INT AUTO_INCREMENT,
        user_id VARCHAR(30),
        com_id INT,
        post_id INT,
        PRIMARY KEY (id)
        );


        CREATE TABLE tag_posts
        (
        id INT AUTO_INCREMENT,
        tag TINYTEXT,
        post_id INT,
        PRIMARY KEY (id)
        );


        CREATE TABLE subscriptions
        (
        id INT AUTO_INCREMENT,
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