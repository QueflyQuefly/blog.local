<?php
session_start();
$functions = join(DIRECTORY_SEPARATOR, array('functions', 'functions.php'));
require_once $functions;
$link = "<a class='menu' href='login.php'>Войти</a>";
$login = '';
$fio = '';
$adminLink = '';
$msg = '';
$show = false;
$linkToChange = false;
if (isset($_GET['user'])) {
    $userId = clearInt($_GET['user']);
    $user = getLoginFioRightsById($userId);
    $login = $user['login'];
    $fio = $user['fio'];
    $_SESSION['referrer'] = $_SERVER['REQUEST_URI'];
    
    if (!empty($_SESSION['user_id'])) {
        $user = getLoginFioRightsById($_SESSION['user_id']);
        $userLogin = $user['login'];
        $userFio = $user['fio'];
        $userRights = $user['rights'];
        $loginWantSubscribe = $userLogin;
        if (isset($_GET['subscribe'])) {
            toSubscribeUser($loginWantSubscribe, $login);
            $uri = str_replace('&subscribe', '', $_SERVER['REQUEST_URI']);
            header("Location: $uri");
        }
        if (isset($_GET['unsubscribe'])) {
            toUnsubscribeUser($loginWantSubscribe, $login);
            $uri = str_replace('&unsubscribe', '', $_SERVER['REQUEST_URI']);
            header("Location: $uri");
        }
        $link = "<a class='menu' href='{$_SERVER['REQUEST_URI']}&exit'>Выйти</a>";
        if ($login === $userLogin) {
            header("Location: cabinet.php");
        }
        if ($userRights === 'superuser') {
            $show = true;
        }
    }
} elseif (!empty($_SESSION['user_id'])) {
    $user = getLoginFioRightsById($_SESSION['user_id']);
    $login = $user['login'];
    $fio = $user['fio'];
    $rights = $user['rights'];
    $show = true;
    $linkToChange = true;
    $link = "<a class='menu' href='index.php?exit'>Выйти</a>";
    $_SESSION['referrer'] = 'cabinet.php';
    if ($rights == 'superuser') {
        $adminLink = "<a class='menu' href='admin/admin.php'>Админка</a>";
    }
}
 else {
    header("Location: login.php");
}
if (isset($_GET['exit'])) {
    $_SESSION['user_id'] = false;
    $uri = str_replace('&exit', '', $_SERVER['REQUEST_URI']);
    header("Location: $uri");
}
if (isset($_GET['deletePostById'])) {
    $deletePostId = clearInt($_GET['deletePostById']);
    if ($deletePostId !== '') {
        deletePostById($deletePostId);
        header("Location: {$_SESSION['referrer']}");
    } 
}
if (isset($_GET['deleteCommentById'])) {
    $deleteCommentId = clearInt($_GET['deleteCommentById']);
    if ($deleteCommentId !== '') {
        deleteCommentById($deleteCommentId);
        header("Location: {$_SESSION['referrer']}");
    } 
}
if (isset($_POST['login']) && isset($_POST['fio']) && isset($_POST['password'])) {
    $id = $_SESSION['user_id'];
    $login = clearStr($_POST['login']);
    $fio = clearStr($_POST['fio']);
    $password = clearStr($_POST['password']);
    $regex = '/\A[^@]+@([^@\.]+\.)+[^@\.]+\z/u';
    if (!preg_match($regex, $login)) {
        $msg = "Неверный формат email";
        header("Location: cabinet.php?changeinfo&msg=$msg");
        exit;
    }   
    if ($login && $fio && $password) {
        $password = password_hash($password, PASSWORD_BCRYPT);
        if (!updateUser($id, $login, $fio, $password)) {
            $msg = "Пользователь с таким email уже зарегистрирован";
            header("Location: cabinet.php?changeinfo&msg=$msg"); 
        } else {
            $msg = "Изменения сохранены";
            header("Location: cabinet.php?msg=$msg");
        } 
    } elseif ($login && $fio) {
        if (!updateUser($id, $login, $fio)) {
            $msg = "Пользователь с таким email уже зарегистрирован";
            header("Location: cabinet.php?changeinfo&msg=$msg"); 
        } else {
            $msg = "Изменения сохранены";
            header("Location: cabinet.php?msg=$msg");
        } 
    } else { 
        $msg = "Заполните все поля";
        header("Location: cabinet.php?changeinfo&msg=$msg");
    }
}
if (isset($_GET['msg'])) {
    $msg = clearStr($_GET['msg']);
}

$year = date("Y", time());
?>


<!DOCTYPE html>
<html>

<head>
    <meta charset='UTF-8'>
    <title>Кабинет - Просто блог</title>
    <link rel='stylesheet' href='css/indexcss.css'>
    <link rel="shortcut icon" href="/images/logo.jpg" type="image/x-icon">
</head>
<body>
<nav>
    <div class='top'>
        <div class="logo">
            <a class="logo" title="На главную" href='/'><img id='logo' src='images/logo.jpg' alt='Лого' width='50' height='50'>
            <div id='namelogo'>Просто Блог</div></a>
        </div>
        <div class="menu">
            <ul class='menu'>
                <li class='menu'><?=$link?></li>
                <li class='menu'><a class='menu' href='search.php'>Поиск</a></li>
                <li class='menu'><a class='menu' href='addpost.php'>Создать новый пост</a></li>
                <li class='menu'><?=$adminLink?></li>
            </ul>
        </div>
    </div>
</nav>

<div class='allwithoutmenu'>
    <div class='content'>
        <div id='singlepostzagolovok'>
            <p class='singlepostzagolovok'>
                Личный кабинет пользователя <br> 
                ФИО: <?=$fio?><br> 
                <?php
                    if ($show) {
                        echo "E-mail: $login";
                    }
                    if ($linkToChange) {
                        echo "<a class='list' style='font-size:13pt' title='Изменить параметры профиля' href='cabinet.php?changeinfo'>Изменить параметры профиля</a>\n";
                    }
                    if (isset($_GET['changeinfo'])) {
                        echo <<< EOD
                        <div class='container'>
                            <div class='center'>
                                <div class='form'>
                                    <form action='cabinet.php' method='post'>
                                        <input type='login' name='login' required autofocus minlength="1" maxlength='50' placeholder='Введите новый email' class='text' value='$login'><br>
                                        <input type='login' name='fio' required minlength="1" maxlength='50' autocomplete="true" placeholder='Новый псевдоним' class='text' value='$fio'><br>
                                        <input type='password' name='password' minlength="0" maxlength='20' placeholder='Новый пароль; оставьте пустым, если не хотите менять' class='text'><br>
                        
                                        <div class='msg'>
                                            <p class='error'>$msg</p>
                                        </div>

                                        <div id='right'><input type='submit' value='Сохранить' class='submit'></div>
                                    </form>
                                </div>
                            </div>
                        </div>
EOD;
                    } elseif (!empty($msg)) {
                        echo "<p class='list' style='font-size:13pt'>$msg</p>";
                    }
                    if (isset($_GET['user']) && !empty($_SESSION['user_id']) && $login !== $userLogin) {
                        if (!isSubscribedUser($userLogin, $login)) {
                            echo "<a class='list' title='Подписаться' style='font-size:13pt' href='{$_SERVER["REQUEST_URI"]}&subscribe'>Подписаться</a>";
                        } else {
                            echo "<a class='list' title='Отменить подписку' style='font-size:13pt' href='{$_SERVER["REQUEST_URI"]}&unsubscribe'>Отменить подписку</a>";
                        }
                    }
                ?>
            </p>
        </div>
            <?php 
                $posts = getPostsByLogin($login);
                if (empty($posts) or $posts == false) {
                    $countPosts = 0; 
                } else {
                    $countPosts = count($posts);
                }
                echo "<div class='contentsinglepost'><p class='smallpostzagolovok'>Список постов &copy; $fio (всего $countPosts):</p></div>";
                if (empty($posts) or $posts == false) {
                    echo "<div class='contentsinglepost'><p class='center'>Нет постов для отображения</p></div>"; 
                } else {
                    echo "<ul class='list'>";
                    $num = count($posts) - 1;
                    for ($i= $num; $i>=0; $i--) {
                        $post = $posts[$i];
                        $comments = getCommentsByPostId($post['id']);
                        if (empty($posts) or $posts == false) {
                            $countComments = 0;
                        } else {
                        $countComments = count($comments);
                        }
                        $post['date'] = date("d.m.Y в H:i", $post['date']);


            ?>

        <div class='viewsmallposts'>

            <a class='post' href='viewsinglepost.php?viewPostById=<?= $post['id'] ?>'>
                <div class='smallpost'>
                    <div class='smallposttext'>
                        <p class='smallpostzagolovok'><?= $post['name'] ?></p>
                        <p class='postdate'> &copy; <?= $post['date'] ?> Рейтинг поста: <?=$post['rating']?></p>
                        <?php
                            if ($show) {
                        ?>
                        <object><a class='list' href='cabinet.php?deletePostById=<?= $post['id'] ?>'> Удалить пост с ID=<?= $post['id'] ?></a></object><br>
                        <?php
                            }
                        ?>
                        <p class='postdate'>Комментариев к посту: <?= $countComments ?> </p>
                    </div>

                    <div class='smallpostimage'>
                        <img src='images/PostImgId<?=$post['id']?>.jpg' alt='Картинка' class='smallpostimage'>
                    </div>
                </div>
            </a>

        </div>

        <?php 
                } 
            }
        ?>

        <div class='viewcomments'>
            
            
            <?php 
                $comments = getCommentsByLogin($login);
                if (empty($comments) or $comments == false) {
                    $countComments = 0;
                } else {
                $countComments = count($comments);
                }
                echo "<div class='contentsinglepost'><p class='smallpostzagolovok'>Список комментариев &copy; $fio (всего $countComments):</p></div>";
                if ($countComments) {
                    echo "<ul class='list'>";
                    for ($i = 0; $i <= $countComments -1; $i++) {
                        $content = nl2br($comments[$i]['content']);
                        $date = date("d.m.Y",$comments[$i]['date']) ." в ". date("H:i", $comments[$i]['date']);
            ?>

            <a class='post' href='viewsinglepost.php?viewPostById=<?=$comments[$i]['post_id']?>#comment<?=$comments[$i]['id']?>'>
                <div class='viewcomment' id='comment<?= $comments[$i]['id'] ?>'>
                    <p class='commentauthor'><?=$fio?><div class='commentdate'><?=$date?></div></p>
                    <div class='commentcontent'>
                        <p class='commentcontent'><?=$content?></p>
                        <p class='commentcontent'>
                            <?php
                                if ($show) {
                            ?> 
                                <object><a class='menu' href='cabinet.php?deleteCommentById=<?= $comments[$i]['id'] ?>'> Удалить комментарий</a></object>
                            <?php
                                }
                            ?>
                        </p>
                    </div>
                </div>
            </a>

            <?php

                    }
                } else {
                    echo "<div class='contentsinglepost'><p class='center'>Нет комментариев для отображения</p></div>";
                }
            ?>
    
        </div>
                        
        <?php 
            $postsLike = getLikedPostsByLogin($login);
            if (empty($postsLike) or $postsLike == false) {
                $countPostsLike = 0;
            } else {
                $countPostsLike = count($postsLike);
            }
            echo "<div class='contentsinglepost'><p class='smallpostzagolovok'>Оценённые посты  &copy; $fio (всего $countPostsLike):</p></div>";
            if ($countPostsLike) {
                for ($j = 0; $j <= $countPostsLike -1; $j++) {
                    $post = getPostForViewById($postsLike[$j]['post_id']);
        ?>

        <div class='viewsmallposts'>
            <a class='post' href='viewsinglepost.php?viewPostById=<?=$post['id']?>'>
                <div class='smallpost'>

                    <div class='smallposttext'>
                        <p class='smallpostzagolovok'><?=$post['name']?></p>
                        <p class='postdate'><?=$post['date']. " &copy; " . $post['author']?></p>
                        <p class='postrating'>Рейтинг поста: <?=$post['rating']?></p>
                    </div>

                    <div class='smallpostimage'>
                        <img src='images/PostImgId<?=$post['id']?>.jpg' alt='Картинка' class='smallpostimage'>
                    </div>
                
                </div>
            </a>
        </div>

        <?php

                }
            } else {
                echo "<div class='contentsinglepost'><p class='center'>Нет постов для отображения</p></div>";
            }
        ?>
            
        <div class='viewcomments'>
            <?php 
                $comments = getLikedCommentsByLogin($login);
                foreach ($comments as $id=>$value) {
                    $comments = getCommentsById($id);
                }
                if (empty($comments) or $comments == false) {
                    $countComments = 0;
                } else {
                    $countComments = count($comments);
                }
                echo "<div class='contentsinglepost'><p class='smallpostzagolovok'>Понравившиеся комментарии &copy; $fio (всего $countComments):</p></div>";
                if ($countComments) {
                    for ($i = 0; $i <= $countComments -1; $i++) {
                        $author = getUserIdAndFioByLogin($comments[$i]['login']);
                        $content = nl2br($comments[$i]['content']);
                        $date = date("d.m.Y",$comments[$i]['date']) ." в ". date("H:i", $comments[$i]['date']);
            ?>

            <a class='post' href='viewsinglepost.php?viewPostById=<?=$comments[$i]['post_id']?>#comment<?=$id?>'>
                <div class='viewcomment' id='comment'>
                    <p class='commentauthor'><?=$author['fio']?><div class='commentdate'><?=$date?></div></p>
                    <div class='commentcontent'>
                        <p class='commentcontent'><?=$content?></p> 
                    </div>
                </div>
            </a>

            <?php

                    }
                } else {
                    echo "<div class='contentsinglepost'><p class='center'>Нет комментариев для отображения</p></div>";
                }
            ?>

        </div>
    </div>

    <footer class='bottom'>
        <p>Website by Вячеслав Бельский &copy; <?=$year?></p>
    </footer>
</div>
</body>
</html>