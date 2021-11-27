<?php
require_once 'dbconfig.php';
$functions = join(DIRECTORY_SEPARATOR, array('functions', 'functions.php'));
require_once $functions;

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
                    5 => "Данилов", 6 => "Ничейный", 7 => "Павлов"]
];  

try {
    $db = new PDO("mysql:host=$host;dbname=$dbname", $username, $password);
} catch (PDOException $e) {
    require_once "init_db.php";
}

$j = getLastPostId() + 1;
try { 
    for ($i = $j; $i <= $j + 9; $i++) {
        $par = random_int(2, 7);

        $name = random_int(1, 7);
        $surname = random_int(1, 7);
        $author = $fio['names'][$name] . " " . $fio['surnames'][$surname];
        $author = $db->quote($author);

        $zag[$i] = file_get_contents("https://fish-text.ru/get?format=html&type=title&number=1", false, stream_context_create($arrContextOptions));
        $zag[$i] = clearStr($zag[$i]);
        $zag[$i] = $db->quote($zag[$i]);

        $text[$i] = file_get_contents("https://fish-text.ru/get?format=html&type=paragraph&number=$par", false, stream_context_create($arrContextOptions));
        $text[$i] = $db->quote($text[$i]);

        $date = time();
        $sql = "INSERT INTO posts (id, name, author, date, content, rating) 
        VALUES($i, $zag[$i], $author, $date, $text[$i], 0);";

        $db->exec($sql);

        $sql = "INSERT INTO comments (post_id, author, date, content, rating) 
        VALUES($i, $author, $date, $i, 0);";

        $db->exec($sql);

        $password = password_hash($i, PASSWORD_BCRYPT);
        $password = $db->quote($password);
        $sql = "INSERT INTO users (login, fio, password, rights) 
        VALUES($i, $author, $password, 'user');";

        $db->exec($sql);
    }
    echo "Подключение к БД: успешно<br>Создано 10 новых постов, 10 новых комментариев и 10 новых пользователей. <a href='/'>На главную</a>";
} catch (PDOException $e) {
    echo $error = $e->getMessage();
}