<?php
$start = microtime(true);
session_start();
$functions = 'functions' . DIRECTORY_SEPARATOR . 'functions.php';
require_once $functions;

$_SESSION['referrer'] = $_SERVER['REQUEST_URI'];

if (isset($_GET['user'])) {
    $userId = clearStr($_GET['user']);
    $user = getUserInfoById($userId);
    
    if (!empty($_SESSION['user_id'])) {
        if (isset($_GET['subscribe'])) {
            toSubscribeUser($_SESSION['user_id'], $userId);
            header("Location: cabinet.php?user=$userId");
        }
        if (isset($_GET['unsubscribe'])) {
            toUnsubscribeUser($_SESSION['user_id'], $userId);
            header("Location: cabinet.php?user=$userId");
        }
        $link = "<a class='menuLink' href='{$_SERVER['REQUEST_URI']}&exit'>Выйти</a>";
        if ($userId == $_SESSION['user_id']) {
            header("Location: cabinet.php");
        }
        if (strpos($_SESSION['user_id'], RIGHTS_SUPERUSER) !== false) {
            $showInfoAndLinksToDelete = true;
        }
    }
} elseif (!empty($_SESSION['user_id'])) {
    $userId = $_SESSION['user_id'];
    $user = getUserInfoById($userId);
    $showInfoAndLinksToDelete = true;
    $linkToChangeUserInfo = true;
    $_SESSION['referrer'] = 'cabinet.php';
} else {
    header("Location: login.php");
}
if (isset($_GET['exit'])) {
    $_SESSION['user_id'] = false;
    header("Location: cabinet.php?user=$userId");
}
if (!empty($showInfoAndLinksToDelete)) {
    if (isset($_GET['deletePostById'])) {
        $deletePostId = clearInt($_GET['deletePostById']);
        if ($deletePostId !== '') {
            deletePostById($deletePostId);
            header("Location: cabinet.php?user=$userId");
        } 
    }
    if (isset($_GET['deleteCommentById'])) {
        $deleteCommentId = clearInt($_GET['deleteCommentById']);
        if ($deleteCommentId !== '') {
            deleteCommentById($deleteCommentId);
            header("Location: cabinet.php?user=$userId");
        } 
    }
}
if (isset($_POST['email']) && isset($_POST['fio']) && isset($_POST['password'])) {
    $id = $_SESSION['user_id'];
    $email = clearStr($_POST['email']);
    $fio = clearStr($_POST['fio']);
    $password = $_POST['password'];
    $regex = '/\A[^@]+@([^@\.]+\.)+[^@\.]+\z/u';
    if (!preg_match($regex, $email)) {
        $msg = "Неверный формат email";
        header("Location: cabinet.php?changeinfo&msg=$msg");
    }   
    if ($email && $fio) {
        if ($password != '') {
            $password = password_hash($password, PASSWORD_BCRYPT);
        } else {
            $password = false;
        }
        if (!updateUser($id, $email, $fio, $password)) {
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
                    if (empty($_SESSION['user_id'])) {
                        echo "<li><a class='menuLink' href='login.php'>Войти</a></li>";
                    } else {
                        if ($userId === $_SESSION['user_id']) {
                            echo "<li><a class='menuLink' href='index.php?exit'>Выйти</a></li>";
                        } else {
                            echo "<li><a class='menuLink' href='cabinet.php?user=$userId&exit'>Выйти</a></li>";
                        }
                        if (strpos($_SESSION['user_id'], RIGHTS_SUPERUSER) !== false) {
                            echo "<li><a class='menuLink' href='admin/admin.php'>Админка</a></li>";
                        }
                    }
                ?>
                <li><a class='menuLink' href='search.php'>Поиск</a></li>
                <li><a class='menuLink' href='addpost.php'>Создать новый пост</a></li>
            </ul>
        </div>
    </div>
</nav>

<div class='allwithoutmenu'>
    <div class='content'>
        <div id='desc'><p>Профиль пользователя - <?=$user['fio']?> </p>
            <?php
                if (!empty($showInfoAndLinksToDelete)) {
                    echo "<p>E-mail: {$user['email']}</p>";
                }
                if ($user['rights'] === RIGHTS_SUPERUSER) {
                    echo "<p style='font-size: 13pt; color: green;'>Является администратором этого сайта</p>";
                }
                if (!empty($linkToChangeUserInfo)) {
                    if (!isset($_GET['changeinfo'])) {
                        echo "<a class='link' style='font-size:13pt; margin-left:30vmin' title='Изменить параметры профиля' 
                                href='cabinet.php?changeinfo'>Изменить параметры профиля</a>\n";
                    } else {
                        echo "<a class='link' style='font-size:13pt; margin-left:30vmin' title='Отмена' 
                                href='cabinet.php'>Отмена</a>\n";
                    }
                }
                if (isset($_GET['user']) && !empty($_SESSION['user_id'])) {
                    if (!isSubscribedUser($_SESSION['user_id'], $userId)) {
                        echo "<p><a class='link' title='Подписаться' style='font-size:14pt' 
                                href='cabinet.php?user=$userId&subscribe'>Подписаться</a></p>";
                    } else {
                        echo "<p><a class='link' title='Отменить подписку' style='font-size:14pt' 
                                href='cabinet.php?user=$userId&unsubscribe'>Отменить подписку</a></p>";
                    }
                }
            ?>
        </div>
        
        <?php
            if (isset($_GET['changeinfo'])) {
            ?>
            <div class='viewcomment'>
                <div class='form'>
                    <form action='cabinet.php' method='post'>
                        <input type='email' name='email' required autofocus minlength="1" maxlength='50' autocomplete="on" placeholder='Введите новый email' class='text' value='<?=$user['email']?>'><br>
                        <input type='login' name='fio' required minlength="1" maxlength='50' autocomplete="on" placeholder='Новый псевдоним' class='text' value='<?=$user['fio']?>'><br>
                        <input type='password' name='password' minlength="0" maxlength='20' autocomplete="new-password" placeholder='Новый пароль; оставьте пустым, если не хотите менять' class='text'><br>
                <?php
                    if (!empty($msg)) {
                        echo "<div class='msg'><p class='error'>$msg</p></div>";
                    }
                ?>
                        <div id='right'><input type='submit' style='margin-left:5vmin' value='Сохранить' class='submit'></div>
                    </form>
                </div>
            </div>
            <?php
                } elseif (!empty($msg)) {
                    echo "<p class='list' style='font-size:13pt'>$msg</p>";
                }
            ?>
            <?php 
                $posts = getPostsByUserId($userId);
                if (empty($posts)) {
                    $countPosts = 0; 
                } else {
                    $countPosts = count($posts);
                }
                echo "<div class='contentsinglepost'><p class='postzagolovok'>Список постов &copy; 
                        {$user['fio']} (всего $countPosts):</p></div>";
                if (empty($posts)) {
                    echo "<div class='contentsinglepost'><p class='center'>Нет постов для отображения</p></div>"; 
                } else {
                    echo "<ul class='list'>";
                    foreach ($posts as $post) {
            ?>

            <div class='viewpost'>
                <a class='postLink' href='viewsinglepost.php?viewPostById=<?=$post['post_id']?>'>
                <div class='posttext'>
                    <p class='postzagolovok'><?=$post['zag']?></p>
                    <p class='postcontent'><?=$post['content']?></p>
                    <p class='postdate'><?=$post['date_time']. " &copy; " . $post['author']?></p>
                    <p class='postrating'>
                    <?php
                        if (!$post['rating']) {
                            echo "Нет оценок. Будьте первым!";
                        } else {
                            echo "Рейтинг поста: " . $post['rating'];
                        }     
                    ?>  
                    </p>
                </div>

                <?php
                    if (!empty($showInfoAndLinksToDelete)) {
                ?>
                    <object>
                        <a class='list' href='cabinet.php?user=<?=$userId?>&deletePostById=<?= $post['post_id'] ?>'>
                            Удалить пост с ID=<?= $post['post_id'] ?>
                        </a>
                    </object>
                <?php
                    } 
                ?>

                <div class='postimage'>
                    <img src='images/PostImgId<?=$post['post_id']?>.jpg' alt='Картинка'>
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
                if (empty($comments) || $comments == false) {
                    $countComments = 0;
                } else {
                $countComments = count($comments);
                }
                echo "<div class='contentsinglepost'><p class='postzagolovok'>Список комментариев &copy; 
                        ${user['fio']} (всего $countComments):</p></div>";
                if ($countComments) {
                    echo "<ul class='list'>";
                    foreach ($comments as $comment) {
                        $content = nl2br($comment['content']);
                        $date = date("d.m.Y", $comment['date_time']) ." в ". date("H:i", $comment['date_time']);
            ?>

            <a class='postLink' href='viewsinglepost.php?viewPostById=<?= $comment['post_id'] ?>#comment<?= $comment['com_id'] ?>'>
                <div class='viewcomment' id='comment<?= $comment['com_id'] ?>'>
                    <p class='commentauthor'><?= $comment['author'] ?><div class='commentdate'><?= $date ?></div></p>
                    <div class='commentcontent'>
                        <p class='commentcontent'><?=$content?></p>
                        <p class='commentcontent'>
                        <?php
                            if (!empty($showInfoAndLinksToDelete)) {
                        ?>
                            <object>
                                <a class='link' href='cabinet.php?user=<?=$userId?>&deleteCommentById=<?= $comment['com_id'] ?>'>
                                    Удалить комментарий
                                </a>
                            </object>
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
            echo "<div class='contentsinglepost'><p class='postzagolovok'>Оценённые посты  &copy; ${user['fio']} (всего $countPostsLikeIds):</p></div>";
            if (!empty($postsLikeIds)) {
                foreach ($postsLikeIds as $postLikeId) {
                    $post = getPostForViewById($postLikeId);
        ?>

            <div class='viewpost'>
                <a class='postLink' href='viewsinglepost.php?viewPostById=<?=$post['post_id']?>'>
                <div class='posttext'>
                    <p class='postzagolovok'><?=$post['zag']?></p>
                    <p class='postcontent'><?=$post['content']?></p>
                    <p class='postdate'><?=$post['date_time']. " &copy; " . $post['author']?></p>
                    <p class='postrating'>
                    <?php
                        if (!$post['rating']) {
                            echo "Нет оценок. Будьте первым!";
                        } else {
                            echo "Рейтинг поста: " . $post['rating'];
                        }     
                    ?>  
                    </p>
                </div>

                <?php
                    if (!empty($showInfoAndLinksToDelete)) {
                ?>
                    <object>
                        <a class='list' href='cabinet.php?user=<?=$userId?>&deletePostById=<?= $post['post_id'] ?>'>
                            Удалить пост с ID=<?= $post['post_id'] ?>
                        </a>
                    </object>
                <?php
                    } 
                ?>

                <div class='postimage'>
                    <img src='images/PostImgId<?=$post['post_id']?>.jpg' alt='Картинка'>
                </div>
                </a>
            </div>
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
                echo "<div class='contentsinglepost'><p class='postzagolovok'>Понравившиеся комментарии &copy; ${user['fio']} (всего $countComments):</p></div>";
                if ($countComments) {
                    foreach ($commentsLike as $commentLike) {
                        $content = nl2br($commentLike['content']);
                        $date = date("d.m.Y", $commentLike['date_time']) ." в ". date("H:i", $commentLike['date_time']);
            ?>

            <a class='postLink' href='viewsinglepost.php?viewPostById=<?=$commentLike['post_id']?>#comment<?=$commentLike['com_id']?>'>
                <div class='viewcomment' id='comment'>
                    <p class='commentauthor'><?=$commentLike['author']?><div class='commentdate'><?=$date?></div></p>
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

    <footer>
        <p>Website by Вячеслав Бельский &copy; <?=$year?><br> Время загрузки страницы: <?=round(microtime(true) - $start, 4)?> с.</p>
    </footer>
</div>
</body>
</html>