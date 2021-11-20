<?php
require_once "functions/functions.php";

session_start();
unset($_SESSION['Referrer']);
$_SESSION['Referrer'] = $_SERVER['REQUEST_URI'];

if(isset($_GET['exit'])){
    $_SESSION['Log_in'] = false;
} 

if(isset($_GET['viewPostById'])){
    $id = clearInt($_GET['viewPostById']);
    $post = getPostForViewById($id);
}
if($_SERVER['REQUEST_METHOD'] == 'POST'){
    if(isset($_POST['addCommentAuthor']) && isset($_POST['addCommentContent']))
        $commentAuthor = $_POST['addCommentAuthor'];
        $commentContent = $_POST['addCommentContent'];
        if($commentAuthor && $commentContent){
            insertComments($id, $commentAuthor, time(), $commentContent);
            header("Location: viewsinglepost.php?viewPostById=$id");
        }else $error = 'Комментарий не может быть пустым';
}

if(is_null($id)){
    header("Location: /");
    exit;
}

if(isset($_GET['exit'])){
    $_SESSION['Log_in'] = false;
} 

if($_SESSION['Log_in']){
    $link = "<a class='menu' href='{$_SERVER['REQUEST_URI']}&exit'>Выйти</a>";
    if($_SESSION['Rights'] == 'superuser'){
        $label = 'Вы вошли как администратор';
    }else{
        $label = ucfirst($_SESSION['Fio']) . ", вы вошли как пользователь";
    }
}else{
    $link = "<a class='menu' href='login.php'>Войти</a>";
    $label = 'Вы не авторизованы';
} 

$comments = getCommentsByPostId($id);
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
                <li class='menu'><a class='menu' href='admin/admin.php'>Админка</a></li>
                <li class='menu'><?=$label?></li>
            </ul>
        </div>
    </div>
</nav>

<div class='allsinglepost'>
    <div class='contentsinglepost'>

        <div id='singlepostzagolovok'><p class='singlepostzagolovok'><?=$post['Name']?></p></div>

        <div id='singlepostauthor'>
            <p class='singlepostauthor'><?=$post['Author']?></p>
            <p class='singlepostdate'><?=$post['Date']?></p>
        </div>

        <div class='singlepostimage'>
            <img src='images/PostImgId<?=$id?>.jpg' alt='Картинка' class='singlepostimg'>
        </div>

        <div class='singleposttext'>
            
            <p class='singlepostcontent'><?=$post['Content']?></p>
            
        </div>
        <div class='addcomments'  id='comment'>

            <?php
                if(!$_SESSION['Log_in']){
                    echo "<p class='center'>Добавление комментариев доступно только для авторизованных пользователей</p>";
                }else{
                    echo $error;
            ?>

            <p class='center'>Добавьте комментарий:</p>

            <div class='addcomment'>

                <form action='<?=$_SERVER['REQUEST_URI']?>#comment' method='post'>
                    <input type='hidden' name='addCommentAuthor' value='<?=$_SESSION['Fio']?>'> 
                   
                    <br><textarea name='addCommentContent' required  minlength="1" maxlength='500' wrap='hard' placeholder="Опишите ваши эмоции :-) (до 500 символов)" id='textcomment'></textarea><br>
                    
                    <input type='submit' value='Добавить комментарий' class='submit'>
                </form>

            </div>

            <?php 
                }
            ?>
        </div>

        <!-- viewing comments area-->
        <div class='viewcomments'>
            
            <p class='center'>Комментарии к посту:</p>
            <?php
                if(!empty($comments) && $comments != false){
                for($i = count($comments)-1; $i >= 0; $i--){
                    $content = nl2br($comments[$i]['Content']);
                    $date = date("d.m.Y",$comments[$i]['Date']) ." в ". date("H:i", $comments[$i]['Date']);
            ?>

            <div class='viewcomment'>
                <p class='commentauthor'><?=$comments[$i]['Author']?><div class='commentdate'><?=$date?></div></p>
                
                <p class='commentcontent'><?=$content?></p>
                <hr>
            </div>

            <?php
                }}else {
            ?>

            <div class='viewnotcomment'>   
                <p class='commentcontent'>Нет комментариев к этой записи</p>
                <hr>
            </div>

            <?php
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