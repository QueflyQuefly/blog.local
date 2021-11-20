<?php
session_start();
$file_functions = dirname(__DIR__) . "/functions/functions.php";
require_once $file_functions;

$error = ''; $posts = [];

/* Удаление комментариев реализую в будущем */

if(isset($_GET['deletePostById'])){
    $deleteId = clearInt($_GET['deletePostById']);
    if($deleteId != false){
        deletePostById($deleteId);
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
            ?>

            

            <li class='list'>

            <p class='list'>ID:<?=$i?> ::: Название: <?=$posts['Name']?> <br> Автор: <?=$posts['Author']?> </p>
            <a class='list' href='adminposts.php?deletePostById=<?=$i?>'> Удалить <?=$i?>-й пост</a>
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