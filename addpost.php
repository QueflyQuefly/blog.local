<?php
session_start(); 
$_SESSION['referrer'] = 'addpost.php';
$ok = '';
$error = '';

$functions = join(DIRECTORY_SEPARATOR, array('functions', 'functions.php'));
require_once $functions;

$size = 4096000; //max size of upload image

if (empty($_SESSION['log_in'])) {
    header("Location: login.php");
}

if (!isset($_SESSION['fio'])) {
    $_SESSION['fio'] = '';
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    
    if (isset($_POST['addPostName'])) {
        $name = clearStr($_POST['addPostName']);
        $author = clearStr($_POST['addPostAuthor']);
        $login = $_SESSION['login'];
        $content = clearStr($_POST['addPostContent']);

        if ($name != '' && $author != '' && $login != '' && $content != '') {

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
                $regex = "/#\w+/um";
                preg_match_all($regex, $content, $tags);
                $content = preg_replace($regex,' ', $content);
                $tags = $tags[0];
                $countTags = count($tags);
                for ($i = 0; $i < $countTags; $i++) {
                    addTagsToPosts($tags[$i]);
                }
                insertToPosts($name, $author, $login, $content);


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
        <p class='logo'><a class="logo" title='На главную' href='/'>Просто Блог</a></p>
        
        <div class='msg'>
            <p class='ok'><?=$ok?></p>
            <p class='error'><?=$error?></p>
        </div>

        <p class='label'>Форма добавления поста:</p>
        
        <div class='form'>
            <form action='addpost.php' method='post' enctype="multipart/form-data" id='addpost'>
                <label id='input' for='file_img' class='addpost'>Заголовок: </label>
                <input type='text' title='Заголовок' class='addpostname' required minlength="1" maxlength='140' autofocus name='addPostName' placeholder="Добавьте заголовок поста. Количество символов: от 20 до 140"><br>
                
                <label id='input' for='file_img' class='addpost'>Автор: </label>
                <input type='text' title='Автор' class='addpostauthor' required minlength="1" maxlength='40' name='addPostAuthor' placeholder="Имя автора или его псевдоним. Количество символов: от 3 до 40" value='<?=$_SESSION['fio']?>'> 

                <br> <input type="hidden" name="MAX_FILE_SIZE" value="<?=$size?>"> <br>
                <label id='img' for='file_img' class='addpost'>Пожалуйста, добавьте картинку. Допускаются jpg весом до <?=$size?> байт</label>
                <input class='addpostimg' type='file' name='addPostImg' id='file_img' > <!-- required -->
                
                <br><textarea class='text' title='Содержание' required minlength="1" maxlength='4000' spellcheck="true" name='addPostContent' placeholder="Добавление содержания. Количество символов: от 20 до 4000 с пробелами" id='content'></textarea><br>
                
                <input type='submit' value='Добавить пост' class='addpostsubmit'>
            </form>
        </div>

    </div>
</div>
</body>
</html>