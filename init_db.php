<?php
require_once 'dbconfig.php';
try {

    /* Здесь может ввести в заблуждение dbname=mysql, 
    но этот скрипт запускается лишь в том случае, если нет dbname=myblog,
    точнее нет с ней соединения (что указывает на предыдущий аргумент) */

    $db = new PDO("mysql:host=$host;dbname=mysql", $username, $password); 

    $password = password_hash('12345', PASSWORD_BCRYPT);
    $password = $db->quote($password);

    $sql = "CREATE DATABASE $dbname;

        USE $dbname;

        CREATE TABLE users
        (
        id INT AUTO_INCREMENT,
        login VARCHAR(20),
        fio VARCHAR(20),
        password CHAR(60),
        rights VARCHAR(20),
        PRIMARY KEY (id)
        );

        CREATE TABLE comments
        (
        id INT AUTO_INCREMENT,
        post_id INT,
        author VARCHAR(20),
        date INT,
        content TEXT,
        rating INT,
        PRIMARY KEY (id)
        );


        CREATE TABLE posts
        (
        id INT AUTO_INCREMENT,
        name TEXT,
        author VARCHAR(20),
        date INT,
        content TEXT,
        rating DOUBLE,
        PRIMARY KEY (id)
        );


        CREATE TABLE rating_posts
        (
        id INT AUTO_INCREMENT,
        login VARCHAR(20),
        post_id INT,
        rating TINYINT,
        PRIMARY KEY (id)
        );
        

        CREATE TABLE rating_comments
        (
        id INT AUTO_INCREMENT,
        login VARCHAR(20),
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


        INSERT INTO users
        (login, fio, password, rights) 
        VALUES ('12345', 'Администратор', $password, 'superuser')
        ;";

    $db->exec($sql);
} catch(PDOException $e) {
    echo $error = $e->getMessage();
}