<?php
session_start();
$functions = 'functions' . DIRECTORY_SEPARATOR . 'functions.php';
require_once $functions;
$search = '';
$_SESSION['referrer'] = "search.php?search=$search";

if (!empty($_COOKIE['user_id'])) {
    $sessionUserId = $_COOKIE['user_id'];
} elseif (!empty($_SESSION['user_id'])) {
    $sessionUserId = $_SESSION['user_id'];
}
if (!empty($sessionUserId) && getUserInfoById($sessionUserId, 'rights') === RIGHTS_SUPERUSER) {
    $isSuperuser = true;
    $userRights = RIGHTS_SUPERUSER;
} else {
    $userRights = false;
}
if (!empty($_GET['search'])) {
    $search = clearStr($_GET['search']);
    if ($search) {
        if (strpos($search, ' ') !== false && strpos($search, '#') == false) {
            if ($result = searchPostsByContent($search)) {
                $posts = $result;
            }
            if ($result = searchPostsByZagAndAuthor($search)) {
                $posts = $result;
            }
            if ($result = searchUsersByFioAndEmail($search, $userRights)) {
                $users = $result;
            }
        } else {
            if (strpos($search, '#') !== false) {
                if ($result = searchPostsByTag($search)) {
                    $posts = $result;
                }
            } else {
                if ($result = searchPostsByZagAndAuthor($search)) {
                    $posts = $result;
                }
                if ($result = searchUsersByFioAndEmail($search, $userRights)) {
                    $users = $result;
                }
            }
        }
        if (!empty($posts)) {
            $posts = array_slice($posts, -30 , 30);
            krsort($posts);
        } 
        if (!empty($users)) {
            $users = array_slice($users, -30 , 30);
            krsort($users);
        } elseif (empty($posts[0]) && empty($users[0])) {
            $error = "<div class='singleposttext'><p class='error'>Ничего не найдено</p></div>\n";
        }
    } else {
        $error = "<div class='singleposttext'><p class='error'>Введите хоть что-нибудь</p></div>\n";
    }
}
if (!empty($_GET['deletePostById'])) {
    $deletePostId = clearInt($_GET['deletePostById']);
    if ($deletePostId !== '') {
        deletePostById($deletePostId);
        header("Location: search.php?search=$search");
    } 
}
if (!empty($_GET['deleteCommentById'])) {
    $deleteCommentId = clearInt($_GET['deleteCommentById']);
    if ($deleteCommentId !== '') {
        deleteCommentById($deleteCommentId);
        header("Location: search.php?search=$search");
    } 
}
if (!empty($_GET['deleteUserById'])) {
    $deleteUserById = clearInt($_GET['deleteUserById']);
    if ($deleteUserById !== '') {
        deleteUserById($deleteUserById);
        header("Location: search.php?search=$search");
    } 
}
if (isset($_GET['exit']) && !empty($sessionUserId)) {
    $sessionUserId = false;
    setcookie('user_id', '0', 1);
    header("Location: search.php?search=$search");
}
$year = date("Y", time());
?>


<!DOCTYPE html>
<html>

<head>
    <meta charset='UTF-8'>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Поиск - Просто блог</title>
    <link rel='stylesheet' href='css/general.css'>
    <link rel="shortcut icon" href="/images/logo.jpg" type="image/x-icon">
</head>
<body>
<nav>
    <div class='top'>
        <div id="logo">
            <a class="logo" title="На главную" href='/'>
            <img id='imglogo' src='images/logo.jpg' alt='Лого'>
            <div id='namelogo'>Просто Блог</div>
            </а>
        </div>
        <div id="menu">
            <ul class='menuList'>
                <?php
                    if (empty($sessionUserId)) {
                        echo "<li><a class='menuLink' href='login.php'>Войти</a></li>";
                    } else {
                        echo "<li><a class='menuLink' href='search.php?search=$search&exit'>Выйти</a></li>";
                        if (!empty($isSuperuser)) {
                            echo "<li><a class='menuLink' href='admin/admin.php'>Админка</a></li>";
                        }
                    }
                ?>
                <li><a class='menuLink' href='cabinet.php'>Мой профиль</a></li>
                <li><a class='menuLink' href='addpost.php'>Создать новый пост</a></li>
            </ul>
        </div>
    </div>
</nav>

<div class='allsinglepost'>
        <div id='singleposttitle'>
            <p class='singleposttitle'>Поиск поста или автора</p> 
        </div>
    <div class='contentsinglepost'>
        <div class='search'>
            <form class='search' action='search.php' method='get'>
                <input class='text' type='text' id='search' required autofocus autocomplete="on" minlength="1" maxlength="100" placeholder='Найти...' name='search' value='<?= $search ?>'>
                <button type="submit">&#x2315</button>
            </form>
        </div> 
    </div>

    <div class='viewposts'>
        <?php 
            echo "<div class='searchdescription'><div class='posttext'>Поиск поста осуществляется по заголовку, автору или по хештэгу, и по его содержимому, если ищете словосочетание</div>\n"; 
            if (!empty($isSuperuser)) {
                echo "<div class='posttext'>Поиск автора осуществляется по ФИО и логину(email)</div>\n</div>"; 
            } else {
                echo "<div class='posttext'>Поиск автора осуществляется по ФИО</div>\n</div>"; 
            }
            if (!empty($posts)) {
                $countPosts = count($posts);
                echo "<div class='singleposttext'><p class='center'>Результаты поиска (посты, всего $countPosts): </p>\n</div>"; 
                foreach ($posts as $post) {
                    $post['date_time'] = date("d.m.Y в H:i", $post['date_time']);
        ?>

        <div class='viewpost'>
            <a class='postLink' href='viewsinglepost.php?viewPostById=<?=  $post['post_id'] ?>'>
            <div class='posttext'>
                <p class='posttitle'><?=  $post['title'] ?></p>
                <p class='postauthor'><?=  $post['date_time'] ?> &copy; <?=  $post['author'] ?></p>
                <p class='postcontent'><?= $post['content'] ?></p>
                <p class='postdate'>
                    Тэг: <?=  $post['tag'] ?>, комментариев к посту: <?=  ""/* $post['count_comments'] */ ?>
                </p>
                <p class='postrating'>
                <?php
                    if ($post['count_ratings'] == 0) {
                        echo "Нет оценок. Будьте первым! Kомментариев: " . $post['count_comments'];
                    } else {
                        echo "Рейтинг: " . $post['rating'] . ", оценок: " . $post['count_ratings']
                                . ", комментариев: " . $post['count_comments'];
                    }
                ?>  
                </p>
                <?php
                    if (!empty($isSuperuser)) {
                ?>
                    <object>
                        <a class='link' href='search.php?search=<?= $search?>&deletePostById=<?=  $post['post_id'] ?>'> 
                            Удалить пост с ID = <?=  $post['post_id'] ?>
                        </a>
                    </object>
                <?php
                    }
                ?>
            </div>
            <div class='postimage'>
                <img src='images/PostImgId<?= $post['post_id']?>.jpg' alt='Картинка'>
            </div>
            </a>
        </div>

        <?php 
                }
            }
            if (!empty($users)) {
                $countUsers = count($users);
                echo "<div class='singleposttext'><p class='center'>Результаты поиска (пользователи, всего $countUsers): </p>\n</div>"; 
                foreach ($users as $user) {
                    $user['date_time'] = date("d.m.Y в H:i", $user['date_time']);
        ?>

        <div class='post'>
            <a class='postLink' href='cabinet.php?user=<?= $user['user_id']?>'>
                <div class='posttext'>
                    <p class='posttitle'> Просмотр дополнительной информации по нажатию</p>
                    <p class='posttitle'> ФИО(псевдоним): <?=  $user['fio'] ?></p>
                    <p class='posttitle'> Дата регистрации: <?=  $user['date_time'] ?></p>

                    <?php
                        if (!empty($isSuperuser)) {
                    ?>
                        <p class='posttitle'> Категория: <?=  $user['rights'] ?></p>
                        <p class='posttitle'>ID: <?=  $user['user_id'] ?> </p>
                        <p class='posttitle'>E-mail: <?=  $user['email'] ?></p>
                        <p class='postdate'><object><a class='list' href='search.php?search=<?= $search?>&deleteUserById=<?=  $user['user_id'] ?> '> Удалить <?=  $user['rights'] ?>-а</a></object>
                    <?php
                        }
                    ?>
                </div>
            </a>
        </div>
    
        <?php 
                } 
            }
            if (!empty($error)) {
                echo $error;
            }
        ?>
    </div>
</div>
<footer>
    <p>Website by Вячеслав Бельский &copy; <?= $year?></p>
</footer>
</body>
</html>