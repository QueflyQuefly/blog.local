<?php
/* 
$host = '127.0.0.1';
$dbname = 'myblog';
$username = 'root';
$password = '';

try {
    $db = new PDO("mysql:host=$host;dbname=$dbname", $username, $password);
    $sql = "USE $dbname; 
        CREATE TABLE Users
        (
        Id INT,
        Login VARCHAR(20),
        Fio VARCHAR(20),
        Password VARCHAR(20),
        Rights VARCHAR(20)
    );";
    $db->exec($sql);
    echo "Connected to $dbname at $host successfully.";
} catch (PDOException $e) {
    die("Could not connect to the database $dbname :" . $e->getMessage());
} */

$dir = dirname(__DIR__);
unlink("$dir\images\PostImgId1.jpg");