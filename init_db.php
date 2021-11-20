<?php

$host = '127.0.0.1';
$dbname = 'myblog';
$username = 'root';
$password = '';

try {

    /* Здесь может ввести в заблуждение dbname=mysql, 
    но этот скрипт запускается лишь в том случае, если нет dbname=myblog,
    точнее нет с ней соединения(что указывает на предыдущий аргумент) */

    $db = new PDO("mysql:host=$host;dbname=mysql", $username, $password); 

    $sql = "CREATE DATABASE $dbname;

        USE myblog;

        CREATE TABLE Users
        (
        Id INT AUTO_INCREMENT,
        Login VARCHAR(20),
        Fio VARCHAR(20),
        Password VARCHAR(20),
        Rights VARCHAR(20),
        PRIMARY KEY (id)
        );

        CREATE TABLE Comments
        (
        Id INT AUTO_INCREMENT,
        Postid INT,
        Author VARCHAR(20),
        Date INT,
        Content TEXT,
        PRIMARY KEY (id)
        );


        CREATE TABLE Posts
        (
        Id INT AUTO_INCREMENT,
        Name TEXT,
        Author VARCHAR(20),
        Date INT,
        Content TEXT,
        PRIMARY KEY (id)
        );
        
        INSERT INTO Users
        (Login, Fio, Password, Rights) 
        VALUES ('12345', 'Администратор', '12345', 'superuser')
        ;";

    $db->exec($sql);
} catch(PDOException $e) {
    echo $error = $e->getMessage();
}