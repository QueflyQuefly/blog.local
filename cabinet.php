<?php
session_start();
$functions = join(DIRECTORY_SEPARATOR, array('functions', 'functions.php'));
require_once $functions;
$link = "<a class='menu' href='login.php'>Войти</a>";
$label = "<a class='menu' href='login.php'>Вы не авторизованы</a>";
$login = '';
$fio = '';
    
if (isset($_GET['exit'])) {
    $_SESSION['log_in'] = false;
    session_destroy();
    header("Location: {$_SERVER['REQUEST_URI']}");
} 

if (isset($_SESSION['log_in']) && $_SESSION['log_in']) {
    $login = $_SESSION['login'];
    $fio = $_SESSION['fio'];

    $link = "<a class='menu' href='?exit'>Выйти</a>";
    if ($_SESSION['rights'] == 'superuser') {
        $label = "<a class='menu' href='admin/admin.php'>Вы вошли как администратор</a>";
    } else {
        $label = "<a class='menu' href='cabinet.php?user=$login'>Перейти в личный кабинет</a>";
    }
} else {
    session_destroy();
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (isset($_SESSION['log_in']) && $_SESSION['log_in']) {
        $commentAuthor = $_POST['addCommentAuthor'];
        $commentContent = $_POST['addCommentContent'];
        if ($commentAuthor && $commentContent) {
            insertComments($id, $commentAuthor, time(), $commentContent);
            header("Location: viewsinglepost.php?viewPostById=$id");
        } else {
            $error = 'Комментарий не может быть пустым';
        }
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
    <title>Пост - Просто блог</title>
    <link rel='stylesheet' href='css/indexcss.css'>
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
            </ul>
        </div>
    </div>
</nav>

<div class='allsinglepost'>
    <div class='contentsinglepost'>

        <div id='singlepostzagolovok'>
            <p class='singlepostzagolovok'>Личный кабинет пользователя</p>
           
        </div>
        <div class='singlepostauthor'>
            <p class='center'>Логин: <?=$login?></p>
            <p class='center'>ФИО: <?=$fio?></p>
        </div>
        
        <div class='singleposttext'>
            
            <p class='singlepostcontent'>Ваши посты:</p>
            <p class='label'>Список всех постов <a href='adminposts.php'> &#8634</a></p>

        <div class='list'>
            <?php 
                $posts = getPostsByFio($fio);
                if (empty($posts) or $posts == false) {
                    echo "<p class='error'>Нет постов для отображения</p>"; 
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

            ?>

            <li class='list'>

            <p class='list'>ID:<?= $post['id'] ?> ::: Название: <?= $post['name'] ?></p>
            <a class='list' href='adminposts.php?deletePostById=<?= $post['id'] ?>'> Удалить пост с ID=<?= $post['id'] ?>-й</a>
            <p class='list'> Комментариев к посту: <?= $countComments ?> </p>
            <hr>
            </li>
        
            <?php 
                } 
            echo "</ul>";
            }
            ?>

        </div>

            
        </div>
        <div class='singleposttext'>
            
            <p class='singlepostcontent'>Ваши комментарии:</p>
            <?php 
                $comments = getCommentsByFio($fio);
                $countComments = count($comments);
                if ($countComments) {
                    echo "<ul class='list'>";
                    for ($j = 0; $j <= $countComments -1; $j++) {
            ?>

            <li class='list'>
            <p class='list'>ID:<?= $comments[$j]['id'] ?></p>  
            <p class='list'>Содержание: <?= $comments[$j]['content'] ?></p>
            <a class='list' href='adminposts.php?deleteCommentById=<?= $comments[$j]['id'] ?>&byPostId=<?= $post['id'] ?>'> Удалить комментарий с ID=<?= $comments[$j]['id'] ?></a>
            </li>

                    <?php

                            }
                        echo "</ul>";
                        }
                    ?>
            
        </div>
        <div class='singleposttext'>
            
            <p class='singlepostcontent'>Ваши оценки:</p>
            
        </div>
        <div class='singleposttext'>
            
            <p class='singlepostcontent'>Ваши лайки:</p>
            
        </div>
    </div>

    <footer class='bottom'>
        <p>Website by Вячеслав Бельский &copy; <?=$year?></p>
    </footer>
</div>
</body>
</html>