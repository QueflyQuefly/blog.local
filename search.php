<?php
session_start();
$functions = join(DIRECTORY_SEPARATOR, array('functions', 'functions.php'));
require_once $functions;
$link = "<a class='menu' href='login.php'>Войти</a>";
$label = "<a class='menu' href='login.php'>Вы не авторизованы</a>";
$login = '';
$fio = '';
$search = '';
$rights = '';
$adminLink = '';
$_SESSION['referrer'] = $_SERVER['REQUEST_URI'];

if (isset($_GET['exit'])) {
    $_SESSION['user_id'] = false;
    $uri = str_replace('&exit', '', $_SERVER['REQUEST_URI']);
    header("Location: $uri");
}

if (!empty($_SESSION['user_id'])) {
    $user = getLoginFioRightsById($_SESSION['user_id']);
    $login = $user['login'];
    $fio = $user['fio'];
    $rights = $user['rights'];
    $label = "<a class='menu' href='cabinet.php'>Перейти в личный кабинет</a>";
    $link = "<a class='menu' href='{$_SERVER['REQUEST_URI']}&exit'>Выйти</a>";
    if ($rights == 'superuser') {
        $adminLink = "<a class='menu' href='admin/admin.php'>Админка</a>";
    }
}

if (!empty($_GET['search'])) {
    $search = $_GET['search'];
    $search = clearStr($search);
    if ($search) {
        if (strpos($search, ' ') !== false) {
            if ($result = searchPostsByContent($search)) {
                $posts[] = $result;
            }

            $searchwords = explode(' ', $search);
    
            foreach ($searchwords as $searchword) {
                if (strpos($searchword, '#') !== false) {
                    if ($result = searchPostsByTag($searchword)) {
                        $posts[] = $result;
                    }
                } else {
                    if ($result = searchPostsByNameAndAuthor($searchword)) {
                        $posts[] = $result;
                    }
                    if ($result = searchUsersByFioAndLogin($searchword, $rights)) {
                        $posts[] = $result;
                    }
                }
            }
        } else {
            if (strpos($search, '#') !== false) {
                if ($result = searchPostsByTag($search)) {
                    $posts[] = $result;
                }
            } else {
                if ($result = searchPostsByNameAndAuthor($search)) {
                    $posts[] = $result;
                }
                if ($result = searchUsersByFioAndLogin($search, $rights)) {
                    $posts[] = $result;
                }
            }
        }
        if (!empty($posts[0])) {
            foreach ($posts as $post) {
                foreach ($post as $post_id) {
                    $idsnotsort[] = $post_id;
                }
            }
            $ids = array_unique($idsnotsort);
        } 
        if (!empty($users[0])) {
            foreach ($users as $user) {
                foreach ($user as $u) {
                    $userids[$u['id']] = $u;
                }
            }
        } elseif (empty($posts[0]) && empty($users[0])) {
            $error = "<div class='singleposttext'><p class='error'>Ничего не найдено</p></div>\n";
        }
    } else {
        $error = "<div class='singleposttext'><p class='error'>Введите хоть что-нибудь</p></div>\n";
    }
} 

if (isset($_GET['deletePostById'])) {
    $deletePostId = clearInt($_GET['deletePostById']);
    if ($deletePostId !== '') {
        deletePostById($deletePostId);
        header("Location: search.php?search=$search");
    } 
}
if (isset($_GET['deleteCommentById'])) {
    $deleteCommentId = clearInt($_GET['deleteCommentById']);
    if ($deleteCommentId !== '') {
        deleteCommentById($deleteCommentId);
        header("Location: search.php?search=$search");
    } 
}
if (isset($_GET['deleteUserById'])) {
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
                <li class='menu'><?=$link?></li>
                <li class='menu'><a class='menu' href='addpost.php'>Создать новый пост</a></li>
                <li class='menu'><?=$label?></li>
                <li class='menu'><?=$adminLink?></li>
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
            <form class='search' action='<?=$_SERVER['PHP_SELF']?>' method='get'>
                <input class='text' type='text' id='search' required autofocus minlength="1" maxlength="30" placeholder='Найти...' name='search' value='<?=$search?>'>
                <button type="submit">&#x2315</button>
            </form>
        </div> 
    </div>

    <div class='viewsmallposts'>
        <?php 
            if (empty($ids)) {
                echo "<div class='searchdescription'><div class='smallposttext'>Поиск поста осуществляется по заголовку, автору или по хештэгу, и по его содержимому, если ищете словосочетание</div>\n"; 
                if ($rights === 'superuser') {
                    echo "<div class='smallposttext'>Поиск автора осуществляется по ФИО и логину(email)</div>\n</div>"; 
                } else {
                    echo "<div class='smallposttext'>Поиск автора осуществляется по ФИО</div>\n</div>"; 
                }
            } else {
                echo "<div class='singleposttext'><p class='center'>Результаты поиска (посты): </p>\n</div>"; 
                foreach ($ids as $id) {
                    $post = getPostForIndexById($id);
                    $comments = getCommentsByPostId($id);
                    $tags = getTagsToPostById($id);

                    if (empty($posts)) {
                        $countComments = 0;
                    } else {
                        $countComments = count($comments);
                    }
        ?>

        <a class='post' href='viewsinglepost.php?viewPostById=<?= $post['id'] ?>'>
            <div class='smallpost'>
                <div class='smallposttext'>
                    <p class='smallpostzagolovok'><?= $post['name'] ?></p>
                    <p class='smallpostauthor'><?= $post['date'] ?> &copy; <?= $post['author'] ?></p>
                    <p class='postdate'>Тэги: 
                        <?php
                            if ($tags) {
                                foreach ($tags as $tag) {
                                    $tagLink = substr($tag['tag'], 1);
                                    echo "<object><a class='menu' href='search.php?search=%23$tagLink'> {$tag['tag']}</a> </object>\n  ";
                                }
                            } else {
                                echo "Нет тэгов";
                            }
                        ?>
                        </p>
                    <p class='postdate'> Комментариев к посту: <?= $countComments ?>
                        <?php
                            if ($rights === 'superuser') {
                        ?>

                        <object><a class='list' href='search.php?search=<?=$search?>&deletePostById=<?= $post['id'] ?>'> Удалить пост с ID=<?= $post['id'] ?></a></object>

                        <?php
                            }
                        ?>
                    </p>
                </div>
                <div class='smallpostimage'>
                    <img src='images/PostImgId<?=$post['id']?>.jpg' alt='Картинка' class='smallpostimage'>
                </div>
            </div>
        </a>

        <?php 
                }
            }
            if (!empty($userids)) {
                echo "<div class='singleposttext'><p class='center'>Результаты поиска (пользователи): </p>\n</div>"; 
                foreach ($userids as $user) {
        ?>

        <a class='post' href='cabinet.php?user=<?=$user['id']?>'>
            <div class='smallpost'>
                <div class='smallposttext'>
                    <p class='smallpostzagolovok'> Просмотр дополнительной информации по нажатию</p>
                    <p class='smallpostzagolovok'> ФИО(псевдоним): <?= $user['fio'] ?></p>
                    <p class='smallpostzagolovok'> Категория: <?= $user['rights'] ?></p>
                    <?php
                        if ($rights === 'superuser') {
                    ?>
                    <p class='smallpostzagolovok'>ID:<?= $user['id'] ?> </p>
                    <p class='smallpostzagolovok'>Логин: <?= $user['login'] ?></p>
                    <p class='postdate'><object><a class='list' href='search.php?search=<?=$search?>&deleteUserById=<?= $user['id'] ?> '> Удалить <?= $user['rights'] ?>-а</a></object>
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