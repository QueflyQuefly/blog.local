<?php
session_start();
$functions = 'functions' . DIRECTORY_SEPARATOR . 'functions.php';
require_once $functions;

if (isset($_GET['user'])) {
    $userId = clearInt($_GET['user']);
    $user = getUserEmailFioRightsById($userId);
    $_SESSION['referrer'] = $_SERVER['REQUEST_URI'];
    
    if (!empty($_SESSION['user_id'])) {
        $sessionUser = getUserEmailFioRightsById($_SESSION['user_id']);
        if (isset($_GET['subscribe'])) {
            toSubscribeUser($sessionUser['email'], $user['email']);
            $uri = str_replace('&subscribe', '', $_SERVER['REQUEST_URI']);
            header("Location: $uri");
        }
        if (isset($_GET['unsubscribe'])) {
            toUnsubscribeUser($sessionUser['email'], $user['email']);
            $uri = str_replace('&unsubscribe', '', $_SERVER['REQUEST_URI']);
            header("Location: $uri");
        }
        $link = "<a class='menu' href='{$_SERVER['REQUEST_URI']}&exit'>Выйти</a>";
        if ($userId === $_SESSION['user_id']) {
            header("Location: cabinet.php");
        }
        if ($sessionUser['rights'] === 'superuser') {
            $showInfoAndLinksToDelete = true;
        }
    }
} elseif (!empty($_SESSION['user_id'])) {
    $userId = $_SESSION['user_id'];
    $user = getUserEmailFioRightsById($userId);
    $showInfoAndLinksToDelete = true;
    $linkToChangeUserInfo = true;
    $_SESSION['referrer'] = 'cabinet.php';
} else {
    header("Location: login.php");
}
if (isset($_GET['exit'])) {
    $_SESSION['user_id'] = false;
    $uri = str_replace('exit', '', $_SERVER['REQUEST_URI']);
    header("Location: $uri");
}
if ($showInfoAndLinksToDelete === true) {
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
}
if (isset($_POST['email']) && isset($_POST['fio']) && isset($_POST['password'])) {
    $id = $_SESSION['user_id'];
    $email = clearStr($_POST['email']);
    $fio = clearStr($_POST['fio']);
    $password = clearStr($_POST['password']);
    $regex = '/\A[^@]+@([^@\.]+\.)+[^@\.]+\z/u';
    if (!preg_match($regex, $email)) {
        $msg = "Неверный формат email";
        header("Location: cabinet.php?changeinfo&msg=$msg");
        exit;
    }   
    if ($email && $fio && $password) {
        $password = password_hash($password, PASSWORD_BCRYPT);
        if (!updateUser($id, $email, $fio, $password)) {
            $msg = "Пользователь с таким email уже зарегистрирован";
            header("Location: cabinet.php?changeinfo&msg=$msg"); 
        } else {
            $msg = "Изменения сохранены";
            header("Location: cabinet.php?msg=$msg");
        } 
    } elseif ($email && $fio) {
        if (!updateUser($id, $email, $fio)) {
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
                <?php
                    if (empty($_SESSION['user_id'])) {
                        echo "<li class='menu'><a class='menu' href='login.php'>Войти</a></li>";
                    } else {
                        echo "<li class='menu'><a class='menu' href='?exit'>Выйти</a></li>";
                        if ($user['rights'] === 'superuser') {
                            echo "<li class='menu'><a class='menu' href='admin/admin.php'>Админка</a></li>";
                        }
                    }
                ?>
                <li class='menu'><a class='menu' href='search.php'>Поиск</a></li>
                <li class='menu'><a class='menu' href='addpost.php'>Создать новый пост</a></li>
            </ul>
        </div>
    </div>
</nav>

<div class='allwithoutmenu'>
    <div class='content'>
        <div id='singlepostzagolovok'>
            <p class='singlepostzagolovok'>
                Профиль пользователя <br> 
                ФИО: <?=$user['fio']?><br> 
                <?php
                    if (!empty($showInfoAndLinksToDelete)) {
                        echo "E-mail: " . $user['email'];
                    }
                    if (!empty($linkToChangeUserInfo)) {
                        if (!isset($_GET['changeinfo'])) {
                            echo "<a class='list' style='font-size:13pt; width:40vh' title='Изменить параметры профиля' href='cabinet.php?changeinfo'>Изменить параметры профиля</a>\n";
                        } else {
                            echo "<a class='list' style='font-size:13pt; width:40vh' title='Отмена' href='cabinet.php'>Отмена</a>\n";
                        }
                    }
                    if (isset($_GET['changeinfo'])) {
                    ?>
                        <div class='container'>
                            <div class='center'>
                                <div class='form'>
                                    <form action='cabinet.php' method='post'>
                                        <input type='email' name='email' required autofocus minlength="1" maxlength='50' placeholder='Введите новый email' class='text' value='<?=$user['email']?>'><br>
                                        <input type='login' name='fio' required minlength="1" maxlength='50' autocomplete="true" placeholder='Новый псевдоним' class='text' value='<?=$user['fio']?>'><br>
                                        <input type='password' name='password' minlength="0" maxlength='20' placeholder='Новый пароль; оставьте пустым, если не хотите менять' class='text'><br>
                                <?php
                                    if (!empty($msg)) {
                                        echo "<div class='msg'><p class='error'>$msg</p></div>";
                                    }
                                ?>
                                        <div id='right'><input type='submit' value='Сохранить' class='submit'></div>
                                    </form>
                                </div>
                            </div>
                        </div>
                    <?php
                    } elseif (!empty($msg)) {
                        echo "<p class='list' style='font-size:13pt'>$msg</p>";
                    }
                    if (isset($_GET['user']) && !empty($_SESSION['user_id'])) {
                        if (!isSubscribedUser($sessionUser['email'], $user['email'])) {
                            echo "<a class='list' title='Подписаться' style='font-size:13pt; width:40vh' href='{$_SERVER["REQUEST_URI"]}&subscribe'>Подписаться</a>";
                        } else {
                            echo "<a class='list' title='Отменить подписку' style='font-size:13pt; width:40vh' href='{$_SERVER["REQUEST_URI"]}&unsubscribe'>Отменить подписку</a>";
                        }
                    }
                ?>
            </p>
        </div>
            <?php 
                $posts = getPostsByUserId($userId);
                if (empty($posts) or $posts == false) {
                    $countPosts = 0; 
                } else {
                    $countPosts = count($posts);
                }
                echo "<div class='contentsinglepost'><p class='smallpostzagolovok'>Список постов &copy; ${user['fio']} (всего $countPosts):</p></div>";
                if (empty($posts) or $posts == false) {
                    echo "<div class='contentsinglepost'><p class='center'>Нет постов для отображения</p></div>"; 
                } else {
                    echo "<ul class='list'>";
                    foreach ($posts as $post) {
            ?>
        <div class='viewsmallposts'>
            <a class='post' href='viewsinglepost.php?viewPostById=<?= $post['id'] ?>'>
                <div class='smallpost'>
                    <div class='smallposttext'>
                        <p class='smallpostzagolovok'><?= $post['zag'] ?></p>
                        <p class='postdate'> &copy; <?= $post['date_time'] ?> Рейтинг: <?=$post['rating']?>, оценок: <?= $post['countRatings']?></p>
                        <?php
                            if (!empty($showInfoAndLinksToDelete)) {
                        ?>
                        <object><a class='list' href='cabinet.php?deletePostById=<?= $post['id'] ?>'> Удалить пост с ID=<?= $post['id'] ?></a></object><br>
                        <?php
                            }
                        ?>
                        <p class='postdate'>Комментариев: <?= $post['countComments'] ?> </p>
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
                $comments = getCommentsByUserId($userId);
                if (empty($comments) or $comments == false) {
                    $countComments = 0;
                } else {
                $countComments = count($comments);
                }
                echo "<div class='contentsinglepost'><p class='smallpostzagolovok'>Список комментариев &copy; ${user['fio']} (всего $countComments):</p></div>";
                if ($countComments) {
                    echo "<ul class='list'>";
                    for ($i = 0; $i <= $countComments -1; $i++) {
                        $content = nl2br($comments[$i]['content']);
                        $date = date("d.m.Y",$comments[$i]['date_time']) ." в ". date("H:i", $comments[$i]['date_time']);
            ?>

            <a class='post' href='viewsinglepost.php?viewPostById=<?=$comments[$i]['post_id']?>#comment<?=$comments[$i]['id']?>'>
                <div class='viewcomment' id='comment<?= $comments[$i]['id'] ?>'>
                    <p class='commentauthor'><?=$user['fio']?><div class='commentdate'><?=$date?></div></p>
                    <div class='commentcontent'>
                        <p class='commentcontent'><?=$content?></p>
                        <p class='commentcontent'>
                            <?php
                                if (!empty($showInfoAndLinksToDelete)) {
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
            $postsLikeIds = getLikedPostsIdsByUserId($userId);
            $countPostsLikeIds = count($postsLikeIds);
            echo "<div class='contentsinglepost'><p class='smallpostzagolovok'>Оценённые посты  &copy; ${user['fio']} (всего $countPostsLikeIds):</p></div>";
            if (!empty($postsLikeIds)) {
                foreach ($postsLikeIds as $postLikeId) {
                    $post = getPostForViewById($postLikeId['post_id']);
                    $authorOfPost = getUserEmailFioRightsById($post['user_id']);
                    $fioOfAuthor = $authorOfPost['fio']; 
        ?>

        <div class='viewsmallposts'>
            <a class='post' href='viewsinglepost.php?viewPostById=<?=$post['id']?>'>
                <div class='smallpost'>

                    <div class='smallposttext'>
                        <p class='smallpostzagolovok'><?=$post['zag']?></p>
                        <p class='postdate'><?=$post['date_time']. " &copy; " . $fioOfAuthor?></p>
                        <p class='postrating'>Рейтинг: <?=$post['rating']?>, оценок: <?= $post['countRatings']?></p>
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
                $commentsLikeIds = getLikedCommentsIdsByUserId($userId);
                foreach ($commentsLikeIds as $id) {
                    $commentsLike[] = getCommentById($id);
                }
                if (empty($commentsLike)) {
                    $countComments = 0;
                } else {
                    $countComments = count($commentsLike);
                }
                echo "<div class='contentsinglepost'><p class='smallpostzagolovok'>Понравившиеся комментарии &copy; ${user['fio']} (всего $countComments):</p></div>";
                if ($countComments) {
                    for ($i = 0; $i <= $countComments -1; $i++) {
                        $commentAuthor = getUserEmailFioRightsById($commentsLike[$i]['user_id']);
                        $fioOfCommentAuthor = $commentAuthor['fio'];
                        $content = nl2br($commentsLike[$i]['content']);
                        $date = date("d.m.Y", $commentsLike[$i]['date_time']) ." в ". date("H:i", $commentsLike[$i]['date_time']);
            ?>

            <a class='post' href='viewsinglepost.php?viewPostById=<?=$commentsLike[$i]['post_id']?>#comment<?=$commentsLike[$i]['id']?>'>
                <div class='viewcomment' id='comment'>
                    <p class='commentauthor'><?=$fioOfCommentAuthor?><div class='commentdate'><?=$date?></div></p>
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