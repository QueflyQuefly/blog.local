<?php

$host = '127.0.0.1';
$dbname = 'myblog';
$username = 'root';
$password = '';

try {

    /* Здесь может ввести в заблуждение dbname=mysql, 
    но этот скрипт запускается лишь в том случае, если нет dbname=myblog,
    точнее нет с ней соединения (что указывает на предыдущий аргумент) */

    $db = new PDO("mysql:host=$host;dbname=mysql", $username, $password); 

    $sql = "CREATE DATABASE $dbname;

        USE $dbname;

        CREATE TABLE Users
        (
        id INT AUTO_INCREMENT,
        login VARCHAR(20),
        fio VARCHAR(20),
        password VARCHAR(20),
        rights VARCHAR(20),
        PRIMARY KEY (id)
        );

        CREATE TABLE Comments
        (
        id INT AUTO_INCREMENT,
        post_id INT,
        author VARCHAR(20),
        date INT,
        content TEXT,
        PRIMARY KEY (id)
        );


        CREATE TABLE Posts
        (
        id INT AUTO_INCREMENT,
        name TEXT,
        author VARCHAR(20),
        date INT,
        content TEXT,
        PRIMARY KEY (id)
        );
        
        INSERT INTO Users
        (login, fio, password, rights) 
        VALUES ('12345', 'Администратор', '12345', 'superuser')
        ;";

    $db->exec($sql);
} catch(PDOException $e) {
    echo $error = $e->getMessage();
}