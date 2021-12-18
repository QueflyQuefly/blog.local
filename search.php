<?php
session_start();
$functions = 'functions' . DIRECTORY_SEPARATOR . 'functions.php';
require_once $functions;

$_SESSION['referrer'] = $_SERVER['REQUEST_URI'];

$search = '';

if (!empty($_SESSION['user_id']) && (strpos($_SESSION['user_id'], RIGHTS_SUPERUSER) !== false)) {
    $isSuperuser = true;
    $userRights = RIGHTS_SUPERUSER;
} else {
    $userRights = false;
}
if (isset($_GET['exit'])) {
    $_SESSION['user_id'] = false;
    $uri = str_replace('&exit', '', $_SERVER['REQUEST_URI']);
    header("Location: $uri");
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

$year = date("Y", time());
?>


<!DOCTYPE html>
<html>

<head>
    <meta charset='UTF-8'>
    <title>Поиск - Просто блог</title>
    <link rel='stylesheet' href='css/indexcss.css'>
    <link rel="shortcut icon" href="/images/logo.jpg" type="image/x-icon">
</head>
<body>
<nav>
    <div class='top'>
        <div class="logo">
            <a class="logo" title="На главную" href='/'><img id='logo' src='images/logo.jpg' alt='Лого' width='50' height='50'>
            <div id='namelogo'>Просто Блог</div></а>
        </div>
        <div class="menu">
            <ul class='menu'>
                <?php
                    if (empty($_SESSION['user_id'])) {
                        echo "<li class='menu'><a class='menu' href='login.php'>Войти</a></li>";
                    } else {
                        echo "<li class='menu'><a class='menu' href='{$_SERVER['REQUEST_URI']}&exit'>Выйти</a></li>";
                        if (strpos($_SESSION['user_id'], RIGHTS_SUPERUSER) !== false) {
                            echo "<li class='menu'><a class='menu' href='admin/admin.php'>Админка</a></li>";
                        }
                    }
                ?>
                <li class='menu'><a class='menu' href='cabinet.php'>Мой профиль</a></li>
                <li class='menu'><a class='menu' href='addpost.php'>Создать новый пост</a></li>
            </ul>
        </div>
    </div>
</nav>

<div class='allsinglepost'>
    
    <div class='contentsinglepost'>
        <div id='singlepostzagolovok'>
            <p class='singlepostzagolovok'>Поиск поста или автора</p> 
        </div>

        <div class='search'>
            <form class='search' action='search.php' method='get'>
                <input class='text' type='text' id='search' required autofocus autocomplete="on" minlength="1" maxlength="100" placeholder='Найти...' name='search' value='<?=$search?>'>
                <button type="submit">&#x2315</button>
            </form>
        </div> 
    </div>

    <div class='viewsmallposts'>
        <?php 
            echo "<div class='searchdescription'><div class='smallposttext'>Поиск поста осуществляется по заголовку, автору или по хештэгу, и по его содержимому, если ищете словосочетание</div>\n"; 
            if (!empty($isSuperuser)) {
                echo "<div class='smallposttext'>Поиск автора осуществляется по ФИО и логину(email)</div>\n</div>"; 
            } else {
                echo "<div class='smallposttext'>Поиск автора осуществляется по ФИО</div>\n</div>"; 
            }
            if (!empty($posts)) {
                $countPosts = count($posts);
                echo "<div class='singleposttext'><p class='center'>Результаты поиска (посты, всего $countPosts): </p>\n</div>"; 
                foreach ($posts as $post) {
                    $post['date_time'] = date("d.m.Y в H:i", $post['date_time']);
        ?>

        <a class='post' href='viewsinglepost.php?viewPostById=<?= $post['post_id'] ?>'>
            <div class='smallpost'>
                <div class='smallposttext'>
                    <p class='smallpostzagolovok'><?= $post['zag'] ?></p>
                    <p class='smallpostauthor'><?= $post['date_time'] ?> &copy; <?= $post['author'] ?></p>
                    <p class='postdate'>Тэги: <?= $post['tag'] ?></p>
                    <p class='postdate'> Комментариев к посту: <?= ""/* $post['count_comments'] */ ?>
                        <?php
                            if (!empty($isSuperuser)) {
                        ?>
                            <object><a class='list' href='search.php?search=<?=$search?>&deletePostById=<?= $post['post_id'] ?>'> Удалить пост с ID=<?= $post['post_id'] ?></a></object>
                        <?php
                            }
                        ?>
                    </p>
                </div>
                <div class='smallpostimage'>
                    <img src='images/PostImgId<?=$post['post_id']?>.jpg' alt='Картинка' class='smallpostimage'>
                </div>
            </div>
        </a>

        <?php 
                }
            }
            if (!empty($users)) {
                $countUsers = count($users);
                echo "<div class='singleposttext'><p class='center'>Результаты поиска (пользователи, всего $countUsers): </p>\n</div>"; 
                foreach ($users as $user) {
                    $user['date_time'] = date("d.m.Y в H:i", $user['date_time']);
        ?>

        <a class='post' href='cabinet.php?user=<?=$user['user_id']?>'>
            <div class='smallpost'>
                <div class='smallposttext'>
                    <p class='smallpostzagolovok'> Просмотр дополнительной информации по нажатию</p>
                    <p class='smallpostzagolovok'> ФИО(псевдоним): <?= $user['fio'] ?></p>
                    <p class='smallpostzagolovok'> Дата регистрации: <?= $user['date_time'] ?></p>

                    <?php
                        if (!empty($isSuperuser)) {
                    ?>
                        <p class='smallpostzagolovok'> Категория: <?= $user['rights'] ?></p>
                        <p class='smallpostzagolovok'>ID: <?= $user['user_id'] ?> </p>
                        <p class='smallpostzagolovok'>E-mail: <?= $user['email'] ?></p>
                        <p class='postdate'><object><a class='list' href='search.php?search=<?=$search?>&deleteUserById=<?= $user['user_id'] ?> '> Удалить <?= $user['rights'] ?>-а</a></object>
                    <?php
                        }
                    ?>
                </div>
            </div>
        </a>
    
        <?php 
                } 
            }
            if (!empty($error)) {
                echo $error;
            }
        ?>
    </div>

    <footer class='bottom'>
        <p>Website by Вячеслав Бельский &copy; <?=$year?></p>
    </footer>
</div>
</body>
</html>