<?php
$startTime = microtime(true);
session_start();
$functions = 'functions' . DIRECTORY_SEPARATOR . 'functions.php';
require_once $functions;

$_SESSION['referrer'] = $_SERVER['REQUEST_URI'];
if (!empty($_COOKIE['user_id'])) {
    $sessionUserId = $_COOKIE['user_id'];
} elseif (!empty($_SESSION['user_id'])) {
    $sessionUserId = $_SESSION['user_id'];
}
if (!empty($sessionUserId) && getUserInfoById($sessionUserId, 'rights') === RIGHTS_SUPERUSER) {
    $isSuperuser = true;
}
$twoDaysInSeconds = 60*60*24*2;
header("Cache-Control: max-age=$twoDaysInSeconds");
header("Cache-Control: must-revalidate");

if (isset($_GET['user'])) {
    $userId = clearInt($_GET['user']);
    $user = getUserInfoById($userId);
    
    if (!empty($sessionUserId)) {
        $link = "<a class='menuLink' href='{$_SERVER['REQUEST_URI']}&exit'>Выйти</a>";
        if ($userId == $sessionUserId) {
            header("Location: cabinet.php");
        }
        if (!empty($isSuperuser)) {
            $showEmailAndLinksToDelete = true;
        }
    }
} elseif (!empty($sessionUserId)) {
    $userId = $sessionUserId;
    $user = getUserInfoById($userId);
    $showEmailAndLinksToDelete = true;
    $linkToChangeUserInfo = true;
    $_SESSION['referrer'] = 'cabinet.php';
} else {
    header("Location: login.php");
}
if (isset($_GET['exit']) && !empty($sessionUserId)) {
    $sessionUserId = false;
    setcookie('user_id', '0', 1);
    header("Location: cabinet.php?user=$userId");
}
if (!empty($showEmailAndLinksToDelete)) {
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
if (!empty($linkToChangeUserInfo)) {
    if (isset($_POST['email']) && isset($_POST['fio']) && isset($_POST['password'])) {
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
            if (!updateUser($sessionUserId, $email, $fio, $password)) {
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
}
if (isset($_GET['msg'])) {
    $msg = clearStr($_GET['msg']);
}
if (isset($_GET['subscribe'])) {
    if (!empty($sessionUserId)) {
        toSubscribeUser($sessionUserId, $userId);
        header("Location: cabinet.php?user=$userId");
    } else {
        header("Location: login.php");
    }
}
if (isset($_GET['unsubscribe'])) {
    if (!empty($sessionUserId)) {
        toUnsubscribeUser($sessionUserId, $userId);
        header("Location: cabinet.php?user=$userId");
    } else {
        header("Location: login.php");
    }
}

$year = date("Y", time());
?>


<!DOCTYPE html>
<html>

<head>
    <meta charset='UTF-8'>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?= $user['fio'] ?>. Профиль - Просто блог</title>
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
                        if ($userId === $sessionUserId) {
                            echo "<li><a class='menuLink' href='index.php?exit'>Выйти</a></li>";
                        } else {
                            echo "<li><a class='menuLink' href='cabinet.php?user=$userId&exit'>Выйти</a></li>";
                        }
                        if (!empty($isSuperuser)) {
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
        <div id='desc'><p><?= $user['fio'] ?> </p>
            <?php
                if (!empty($showEmailAndLinksToDelete)) {
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
                if (isset($_GET['user']) && !empty($sessionUserId)) {
                    if (!isSubscribedUser($sessionUserId, $userId)) {
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
            if (isset($_GET['changeinfo']) && !empty($linkToChangeUserInfo)) {
            ?>
            <div class='viewcomment'>
                <div class='form'>
                    <form action='cabinet.php' method='post'>
                        <input type='email' name='email' required autofocus minlength="1" maxlength='50' autocomplete="on" placeholder='Введите новый email' class='text' value='<?= $user['email'] ?>'><br>
                        <input type='login' name='fio' required minlength="1" maxlength='50' autocomplete="on" placeholder='Новый псевдоним' class='text' value='<?= $user['fio'] ?>'><br>
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
                echo "<div class='contentsinglepost'><p class='posttitle'>Посты от автора &copy; 
                        {$user['fio']} (всего $countPosts):</p></div>";
                if (empty($posts)) {
                    echo "<div class='contentsinglepost'><p class='center'>Нет постов для отображения</p></div>"; 
                } else {
                    foreach ($posts as $post) {
            ?>

            <div class='viewpost'>
                <a class='postLink' href='viewsinglepost.php?viewPostById=<?= $post['post_id'] ?>'>
                <div class='posttext'>
                    <p class='posttitle'><?= $post['title'] ?></p>
                    <p class='postcontent'><?= $post['content'] ?></p>
                    <p class='postdate'><?= $post['date_time']. " &copy; " . $post['author'] ?></p>
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
                        if (!empty($showEmailAndLinksToDelete)) {
                    ?>
                        <object>
                            <a class='link' href='cabinet.php?user=<?= $userId ?>&deletePostById=<?=  $post['post_id']  ?>'>
                                Удалить пост с ID = <?=  $post['post_id']  ?>
                            </a>
                        </object>
                    <?php
                        } 
                    ?>
                </div>
                <div class='postimage'>
                    <img src='images/PostImgId<?= $post['post_id'] ?>.jpg' alt='Картинка'>
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
                echo "<div class='contentsinglepost'><p class='posttitle'>Комментарии автора &copy; 
                        ${user['fio']} (всего $countComments):</p></div>";
                if ($countComments) {
                    foreach ($comments as $comment) {
                        $comment['content'] = nl2br($comment['content']);
                        $comment['date_time'] = date("d.m.Y в H:i", $comment['date_time']);
            ?>

            <div class='viewcomment' id='comment<?=  $comment['comment_id']  ?>'>
                <a class='postLink' href='viewsinglepost.php?viewPostById=<?=  $comment['post_id']  ?>#comment<?=  $comment['comment_id']  ?>'>
                    <p class='commentauthor'><?=  $comment['author']  ?><div class='commentdate'><?=  $comment['date_time']  ?></div></p>
                    <div class='commentcontent'>
                        <p class='commentcontent'><?= $comment['content'] ?></p>
                        <p class='commentcontent'>
                        <?php
                            if (!empty($showEmailAndLinksToDelete)) {
                        ?>
                            <object>
                                <a class='link' href='cabinet.php?user=<?= $userId ?>&deleteCommentById=<?=  $comment['comment_id']  ?>'>
                                    Удалить комментарий
                                </a>
                            </object>
                        <?php
                            } 
                        ?>
                        </p>
                    </div>
                </a>
            </div>

            <?php

                    }
                } else {
                    echo "<div class='contentsinglepost'><p class='center'>Нет комментариев для отображения</p></div>";
                }
            ?>
        </div>         
        <?php 
            $postsLike = getLikedPostsByUserId($userId);
            $countPostsLike = count($postsLike);
            echo "<div class='contentsinglepost'><p class='posttitle'>Оценённые посты &copy; ${user['fio']} (всего $countPostsLike):</p></div>";
            if (!empty($postsLike)) {
                foreach ($postsLike as $post) {
                    $post['date_time'] = date("d.m.Y в H:i", $post['date_time']);
        ?>
            <div class='viewpost'>
                <a class='postLink' href='viewsinglepost.php?viewPostById=<?= $post['post_id'] ?>'>
                <div class='posttext'>
                    <p class='posttitle'><?= $post['title'] ?></p>
                    <p class='postcontent'><?= $post['content'] ?></p>
                    <p class='postdate'><?= $post['date_time']. " &copy; " . $post['author'] ?></p>
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
                        if (!empty($showEmailAndLinksToDelete)) {
                    ?>
                        <object>
                            <a class='link' href='cabinet.php?user=<?= $userId ?>&deletePostById=<?=  $post['post_id']  ?>'>
                                Удалить пост с ID = <?=  $post['post_id']  ?>
                            </a>
                        </object>
                    <?php
                        } 
                    ?>
                </div>
                <div class='postimage'>
                    <img src='images/PostImgId<?= $post['post_id'] ?>.jpg' alt='Картинка'>
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
                $commentsLike = getLikedCommentsByUserId($userId);
                if (empty($commentsLike)) {
                    $countComments = 0;
                } else {
                    $countComments = count($commentsLike);
                }
                echo "<div class='contentsinglepost'><p class='posttitle'>Понравившиеся комментарии &copy; ${user['fio']} (всего $countComments):</p></div>";
                if ($countComments) {
                    foreach ($commentsLike as $comment) {
                        $comment['content'] = nl2br($comment['content']);
                        $comment['date_time'] = date("d.m.Y в H:i", $comment['date_time']);
            ?>

            <div class='viewcomment' id='comment<?=  $comment['comment_id']  ?>'>
                <a class='postLink' href='viewsinglepost.php?viewPostById=<?=  $comment['post_id']  ?>#comment<?=  $comment['comment_id']  ?>'>
                    <p class='commentauthor'><?=  $comment['author']  ?><div class='commentdate'><?=  $comment['date_time']  ?></div></p>
                    <div class='commentcontent'>
                        <p class='commentcontent'><?=  $comment['content']  ?></p>
                        <p class='commentcontent'>
                        <?php
                            if (!empty($showEmailAndLinksToDelete)) {
                        ?>
                            <object>
                                <a class='link' href='cabinet.php?user=<?= $userId ?>&deleteCommentById=<?=  $comment['comment_id']  ?>'>
                                    Удалить комментарий
                                </a>
                            </object>
                        <?php
                            } 
                        ?>
                        </p>
                    </div>
                </a>
            </div>

            <?php

                    }
                } else {
                    echo "<div class='contentsinglepost'><p class='center'>Нет комментариев для отображения</p></div>";
                }
            ?>
        </div>
    </div>
</div>
<footer>
    <p>Website by Вячеслав Бельский &copy; <?= $year ?><br> Время загрузки страницы: <?= round(microtime(true) - $startTime, 4) ?> с.</p>
</footer>
</body>
</html>