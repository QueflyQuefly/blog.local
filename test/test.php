<?php
/* 
$host = '127.0.0.1';
$dbname = 'myblog';
$username = 'root';
$password = '';

try {
    $db = new PDO("mysql:host=$host;dbname=$dbname", $username, $password);
    $sql = "INSERT INTO ratingcom (login, com_id, rating) 
            VALUES(1, 1, 1)";
    $db->exec($sql);
    echo "Connected to $dbname at $host successfully.";
} catch (PDOException $e) {
    die("Could not connect to the database $dbname :" . $e->getMessage());
} */
$content = "najfsnkdflakdnlfk lkfsnfkgnr fensfklesn #1223 #djfjf #f11ff #1ddfs1#gjgjgj";

$regex = "/#\w+/";
preg_match_all($regex, $content, $post['tags']);
$post['tags'] = $post['tags'][0];

var_dump($post);