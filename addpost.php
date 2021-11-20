<?php
session_start(); 
$_SESSION['referrer'] = 'addpost.php';

require_once "functions/functions.php";

$size = 4096000; //max size of upload image

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    
    if (isset($_POST['addPostName'])) {
        $name = clearStr($_POST['addPostName']);
        $author = clearStr($_POST['addPostAuthor']);
        $content = clearStr($_POST['addPostContent']);

        if ($name && $author && $content) {

            /* if ( $_FILES['addPostImg']["error"] != UPLOAD_ERR_OK ) {
                switch($_FILES['addPostImg']["error"]){
                    case UPLOAD_ERR_INI_SIZE:
                        $error = "Превышен максимально допустимый размер";
                        header("Location: addpost.php?msg=$error");
                        break;
                    case UPLOAD_ERR_FORM_SIZE:
                        $error = "Превышено значение $size байт";
                        header("Location: addpost.php?msg=$error");
                        break;
                    case UPLOAD_ERR_PARTIAL:
                        $error = "Файл загружен частично";
                        header("Location: addpost.php?msg=$error");
                        break;
                    case UPLOAD_ERR_NO_FILE:
                        $error = "Файл не был загружен";
                        header("Location: addpost.php?msg=$error");
                        break;
                    case UPLOAD_ERR_NO_TMP_DIR:
                        $error = "Отсутствует временная папка";
                        header("Location: addpost.php?msg=$error");
                        break;
                    case UPLOAD_ERR_CANT_WRITE:
                        $error = "Не удалось записать файл на диск";
                        header("Location: addpost.php?msg=$error");
                }
            } elseif ($_FILES['addPostImg']["type"] == 'image/jpeg') { */
                insertToPosts($name, $author, $content);

                /* move_uploaded_file($_FILES['addPostImg']["tmp_name"], "images\PostImgId" . getLastPostId() . ".jpg"); */
                
                $msg =  "Пост добавлен";
                header("Location: addpost.php?msg=$msg");
            /* } else { 
                $error = "Изображение имеет недопустимое расширение (не jpg)";
                header("Location: addpost.php?msg=$error");
            }  */         
        } else {
            $error = "Заполните все поля";
            header("Location: addpost.php?msg=$error");
        }
    }
}

if (isset($_GET['msg'])) {
    $msg = clearStr($_GET['msg']);
    if ($msg == "Пост добавлен") {
        $ok = $msg;
    } else {
        $error = $msg;
    }
}

?>


<!DOCTYPE html>
<html>
<head>
    <meta charset='UTF-8'>
    <title>Добавление поста - Просто Блог</title>
    <link rel='stylesheet' href='css/addpostcss.css'>
</head>
<body>
<div class='content'>
    <div class='centerpost'>
        <p class='logo'><a class="logo" href='/'>Просто Блог</a></p>
        
        <div class='msg'>
            <p class='ok'><?=$ok?></p>
            <p class='error'>
                <?php
                    if (!$_SESSION['log_in']) {
                        echo "<a class='link' href='login.php'>Войдите, прежде чем продолжить</a>";
                        exit;
                    }
                    echo $error;
                ?>
            </p>
        </div>

        <p class='label'>Форма добавления поста:</p>
        
        <div class='form'>
            <form action='addpost.php' method='post' enctype="multipart/form-data" id='addpost'>
                <label id='input' for='file_img' class='addpost'>Название: </label>
                <input type='text' class='addpostname' required minlength="1" maxlength='140' autofocus name='addPostName' placeholder="Добавьте название. Количество символов: от 20 до 140"><br>
                
                <label id='input' for='file_img' class='addpost'>Автор: </label>
                <input type='text' class='addpostauthor' required minlength="1" maxlength='40' name='addPostAuthor' placeholder="Имя автора или его псевдоним. Количество символов: от 3 до 40" value='<?=$_SESSION['fio']?>'> 

                <br> <input type="hidden" name="MAX_FILE_SIZE" value="<?=$size?>"> <br>
                <label id='img' for='file_img' class='addpost'>Пожалуйста, добавьте картинку. Допускаются jpg весом до <?=$size?> байт</label>
                <input class='addpostimg' type='file' name='addPostImg' id='file_img' > <!-- required -->
                
                <br><textarea class='text' required minlength="1" maxlength='4000' spellcheck="true" name='addPostContent' placeholder="Добавление содержания. Количество символов: от 20 до 4000 с пробелами" id='content'></textarea><br>
                
                <input type='submit' value='Добавить пост' class='addpostsubmit'>
            </form>
        </div>

    </div>
</div>
</body>
</html>