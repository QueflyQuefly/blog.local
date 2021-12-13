<?php
require_once 'dbconfig.php';
try {
    $db = new PDO("mysql:host=$host", $username, $password);

    $password = password_hash('12345', PASSWORD_BCRYPT);
    $password = $db->quote($password);
    $date = time();

    $sql = "CREATE DATABASE $dbname;

        USE $dbname;

        CREATE TABLE users
        (
        id INT AUTO_INCREMENT,
        email VARCHAR(50),
        fio VARCHAR(50),
        pass_word CHAR(60),
        date_time INT,
        rights VARCHAR(20),
        PRIMARY KEY (id)
        );

        CREATE TABLE comments
        (
        id INT AUTO_INCREMENT,
        post_id INT,
        user_id INT,
        date_time INT,
        content TEXT,
        rating INT,
        PRIMARY KEY (id)
        );


        CREATE TABLE posts
        (
        id INT AUTO_INCREMENT,
        zag TEXT,
        user_id INT,
        date_time INT,
        content TEXT,
        rating DOUBLE,
        PRIMARY KEY (id)
        );


        CREATE TABLE rating_posts
        (
        id INT AUTO_INCREMENT,
        user_id INT,
        post_id INT,
        rating TINYINT,
        PRIMARY KEY (id)
        );
        

        CREATE TABLE rating_comments
        (
        id INT AUTO_INCREMENT,
        user_id INT,
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
        user_id_want_subscribe INT,
        user_id INT,
        PRIMARY KEY (id)
        );


        INSERT INTO users
        (email, fio, pass_word, date_time, rights) 
        VALUES ('12345', 'Администратор', $password, $date, 'superuser')
        ;";

    $db->exec($sql);
} catch(PDOException $e) {
    echo $error = $e->getMessage();
}