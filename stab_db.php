<?php
require_once 'dbconfig.php';
$functions = join(DIRECTORY_SEPARATOR, array('functions', 'functions.php'));
require_once $functions;

try {
    @$fp = fsockopen("www.google.com", 80, $errno, $errstr, 30);
    if (!$fp) {
        throw new Exception('Отсутствует подключение к интернету');
    }
    unset($fp);
} catch (Exception $e) {
    echo $e->getMessage();
    exit;
}


$arrContextOptions = [
    "ssl" => [
            "verify_peer" => false,
            "verify_peer_name" => false
            ]
];  
$fio = [
    "names" => [0 => "Василий", 1 => "Даниил", 2 => "Иван", 3 => "Павел", 4 => "Александр",
                5 => "Алексей", 6 => "Давид", 7 => "Фёдор"],

    "surnames" => [0 => "Бродский", 1 => "Васильев", 2 => "Пугачев", 3=> "Иванюк", 4 => "Житомирский",
                    5 => "Данилов", 6 => "Крупской", 7 => "Павлов"]
];  

try {
    $db = new PDO("mysql:host=$host;dbname=$dbname", $username, $password);
} catch (PDOException $e) {
    require_once "init_db.php";
}

$j = getLastPostId() + 1;
try {
    for ($i = $j; $i <= $j + 10; $i++) {
        $par = random_int(2, 7);

        $name = random_int(1, 7);
        $surname = random_int(1, 7);
        $author = $fio['names'][$name] . " " . $fio['surnames'][$surname];
        $author = $db->quote($author);
        $login = "'$i@gmail.com'";

        $zag[$i] = file_get_contents("https://fish-text.ru/get?format=html&type=title&number=1", false, stream_context_create($arrContextOptions));
        $zag[$i] = clearStr($zag[$i]);
        $zag[$i] = $db->quote($zag[$i]);

        $text[$i] = file_get_contents("https://fish-text.ru/get?format=html&type=paragraph&number=$par", false, stream_context_create($arrContextOptions));
        $tags[$i] = isNounForTag($text[$i]);
        $text[$i] = $db->quote($text[$i]);

        $com[$i] = file_get_contents("https://fish-text.ru/get?format=html&type=sentence&number=$par", false, stream_context_create($arrContextOptions));
        $com[$i] = clearStr($com[$i]);
        $com[$i] = $db->quote($com[$i]);

        $date = time();
        $sql = "INSERT INTO posts (name, login, author, date, content, rating) 
        VALUES($zag[$i], $login, $author, $date, $text[$i], 0);";

        $db->exec($sql);
        
        if (!empty($tags[$i])) {
            foreach ($tags[$i] as $tag) {
                $tag = $db->quote($tag);

                $sql = "INSERT INTO tag_posts (tag, post_id) 
                VALUES($tag, $i);";

                $db->exec($sql);
            }
        }

        $sql = "INSERT INTO comments (post_id, login, date, content, rating) 
        VALUES($i, $login, $date, $com[$i], 0);";

        $db->exec($sql);

        $password = password_hash($i, PASSWORD_BCRYPT);
        $password = $db->quote($password);
        $sql = "INSERT INTO users (login, fio, password, date, rights) 
        VALUES($login, $author, $password, $date, 'user');";

        $db->exec($sql);
    }
    echo "Подключение к БД: успешно<br>Создано 10 новых постов, 10 новых комментариев и 10 новых пользователей. <a href='/'>На главную</a>";
} catch (PDOException $e) {
    echo $error = $e->getMessage();
}