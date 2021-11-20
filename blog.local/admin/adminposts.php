<?php
session_start();
$file_functions = dirname(__DIR__) . "/functions/functions.php";
require_once $file_functions;

$error = ''; $posts = [];

/* Удаление комментариев реализую в будущем */

if(isset($_GET['deletePostById'])){
    $deletePostId = clearInt($_GET['deletePostById']);
    if($deletePostId != ''){
        deletePostById($deleteId);
        header("Location: adminposts.php");
    } 
}
if(isset($_GET['deleteCommentById']) && isset($_GET['byPostId'])){
    $deleteCommentId = clearInt($_GET['deleteCommentById']);
    $postId = clearInt($_GET['byPostId']);
    if($deleteCommentId != '' && $postId != ''){
        deleteCommentByIdAndPostId($deleteCommentId, $postId);
        header("Location: adminposts.php");
    } 
}

?>


<!DOCTYPE html>
<html>
<head>
    <meta charset='UTF-8'>
    <title>Управление постами - Просто Блог</title>
    <link rel='stylesheet' href='css/admincss.css'>
</head>
<body>
<div class='view'>
    <div class='viewlist'>
        <p class='logo'><a class="logo" href='/'>Просто Блог</a></p>
        
        <div class='msg'>
            <p class='error'>
                <?php
                    if($_SESSION['Rights'] != "superuser"){
                        echo "<p class='error'>Необходимо <a class='link' href='/login.php'>войти</a> как администратор</p>";
                        exit;
                    }
                ?>

                <?=$php_errormsg , $error?>
            </p>
        </div>


        <p class='label'>Список всех постов <a href='adminposts.php'> &#8634</a></p>

        <div class='list'>
            <?php 
                if(!getLastPostId()) echo "<p class='error'>Нет постов для отображения</p>"; 
                else{
                    echo "<ul class='list'>";
                    for($i= getLastPostId(); $i>=1; $i--){
                        $posts = getPostsForIndexById($i);
                        $comments = getCommentsByPostId($i);
                        $countComments = count($comments);

            ?>

            

            <li class='list'>

            <p class='list'>ID:<?=$i?> ::: Название: <?=$posts['Name']?> <br> Автор: <?=$posts['Author']?> </p>
            <a class='list' href='adminposts.php?deletePostById=<?=$i?>'> Удалить <?=$i?>-й пост</a>
            <p class='list'> Комментариев к посту: <?=$countComments?> </p>
            <?php 
                if ($countComments){
                    echo "<ul class='list'>";
                    for($j = 0; $j <= $countComments -1; $j++){
            ?>

            <li class='list'>
            <p class='list'>ID:<?=$comments[$j]['Id']?> ::: Автор: <?=$comments[$j]['Author']?>  Содержание: <?=$comments[$j]['Content']?><br>  </p>
            <a class='list' href='adminposts.php?deleteCommentById=<?=$comments[$j]['Id']?>&byPostId=<?=$i?>'> Удалить <?=$comments[$j]['Id']?>-й комментарий</a>
            </li>

                    <?php

                            }
                        echo "</ul>";
                        }
                    ?>

            <hr>

            </li>
        

            <?php 
                } echo "</ul>";
            }
            ?>

        </div>
    </div>
</div>
</body>
</html>
