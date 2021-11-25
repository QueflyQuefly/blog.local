<?php
session_start();
$file_functions = join(DIRECTORY_SEPARATOR, array(dirname(__DIR__), 'functions', 'functions.php'));
require_once $file_functions;


/* $host = '127.0.0.1';
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
/* $content = "najfsnkdflakdnlfk lkfsnfkgnr fensfklesn #1223 #djfjf #f11ff #1ddfs1#gjgjgj";

$regex = "/#\w+/";
preg_match_all($regex, $content, $post['tags']);
$post['tags'] = $post['tags'][0];

var_dump($post); */

$search = '#4';
if ($search) {
    if (strpos($search, ' ') !== false) {
        $searchwords = explode(' ', $search);

        foreach ($searchwords as $searchword) {
            if (strpos($searchword, '#') !== false) {
                $posts[] = searchPostsByTag($searchword);
            } else {
                $posts[] = searchPostsByName($searchword);
            }
        }
    } else {
        if (strpos($search, '#') !== false) {
            $posts[] = searchPostsByTag($search);
        } else {
            $posts[] = searchPostsByName($search);
        }
    }
} else {
    echo "<p class='error'>Введите хоть что-нибудь</p>";
}
echo $posts[0][0];