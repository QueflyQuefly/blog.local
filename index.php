<?php
session_start();
require_once "functions/functions.php";


if($_SESSION['Log_in'] == false)
    session_destroy();
    
if(isset($_GET['exit'])){
    $_SESSION['Log_in'] = false;
    session_destroy();
    header("Location: /");
} 

if($_SESSION['Log_in']){
    $link = "<a class='menu' href='?exit'>Выйти</a>";
    if($_SESSION['Rights'] == 'superuser'){
        $label = 'Вы вошли как администратор';
    }else{
        $label = ucfirst($_SESSION['Fio']) . ", вы вошли как пользователь";
    }
}else{
    $link = "<a class='menu' href='login.php'>Войти</a>";
    $label = 'Вы не авторизованы';
} 

$numPosts = getLastPostId();
$year = date("Y", time());
?>



<!DOCTYPE html>
<html>

<head>
    <meta charset='UTF-8'>
    <title>Просто блог</title>
    <link rel='stylesheet' href='css/indexcss.css'>
</head>
<body>
    <nav>
    <div class='top'>
       
            <div class="logo">
                 <a class="logo" title="На главную" href='/'>
                <img id='logo' src='images/logo.jpg' alt='Лого' width='50' height='50'>
                <div id='namelogo'>Просто Блог</div>
                </а>
            </div>
        

        <div class="menu">
            <ul class='menu'>
                <li class='menu'><?=$link?></li>
                <li class='menu'><a class='menu' href='addpost.php'>Создать новый пост</a></li>
                <li class='menu'><a class='menu' href='admin/admin.php'>Админка</a></li>
                <li class='menu'><?=$label?></li>
            </ul>
        </div>
    </div>
    </nav>
<div class='allwithoutmenu'>
    <div class='content'>

        <div id='desc'><p>Наилучший источник информации по теме "Путешествия"</p></div>

        <?php 
            if(!$numPosts) die("<p>Нет постов для отображения</p>");
            else{
            $num = $numPosts;
            $post = getPostsForIndexById($num);
        ?>

        <a class='onepost' href="viewsinglepost.php?viewPostById=<?=$num?>">
        <div class='viewonepost'>
            
            <div class='oneposttext'>
                <p class='onepostzagolovok'><?=$post['Name']?></p>
                <p class='onepostcontent'><?=$post['Content']?></p>
                <p class='postdate'><?=$post['Date']. " " . $post['Author']?></p>
            </div>
            <div class='onepostimage'>
                <img src='images/PostImgId<?=$num?>.jpg' alt='Картинка' class='onepostimage'>
            </div>
        </div>
        </a>

        <?php
            $num--;
            $minId = 1;

            if($num > 9) $minId = $num - 8;  //Благодаря minId вывожу всего в общем и целом 10 постов

            for ($id = $num; $id >= $minId; $id--) { 
                $post = getPostsForIndexById($id);
        ?>

        <div class='viewsmallposts'>

            <a class='post' href='viewsinglepost.php?viewPostById=<?=$id?>'>
            <div class='smallpost'>

                 <div class='smallposttext'>
                    <p class='smallpostzagolovok'><?=$post['Namesmall']?></p>
                    <p class='smallpostcontent'><?=$post['Contentsmall']?></p>
                    <p class='postdate'><?=$post['Date']. " " . $post['Author']?></p>
                </div>

                <div class='smallpostimage'>
                    <img src='images/PostImgId<?=$id?>.jpg' alt='Картинка' class='smallpostimage'>
                </div>
               
            </div>
            </a>

        </div>

        <?php

            }}

        ?>

    </div>

    <footer class='bottom'>
        <p>Website by Вячеслав Бельский &copy; <?=$year?></p>
    </footer>
</div>
</body>
</html>