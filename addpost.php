<?php
session_start(); 
$functions = 'functions' . DIRECTORY_SEPARATOR . 'functions.php';
require_once $functions;

$_SESSION['referrer'] = 'addpost.php';

$maxSizeOfUploadImage = 4096000; // 4 megabytes

if (!empty($_SESSION['user_id'])) {
    $userId = $_SESSION['user_id'];
} else {
    header("Location: login.php");
}
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $zag = clearStr($_POST['addPostZag']);
    $content = clearStr($_POST['addPostContent']);
    if ($zag !== '' && $content !== '') {
        /* if ( $_FILES['addPostImg']["error"] != UPLOAD_ERR_OK ) {
            switch($_FILES['addPostImg']["error"]){
                case UPLOAD_ERR_INI_SIZE:
                    $error = "Превышен максимально допустимый размер";
                    header("Location: addpost.php?msg=$error");
                    break;
                case UPLOAD_ERR_FORM_SIZE:
                    $error = "Превышено значение $maxSizeOfUploadImage байт";
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
            addPost($zag, $userId, $content);
            /* move_uploaded_file($_FILES['addPostImg']["tmp_name"], "images\PostImgId" . $lastPostId . ".jpg"); */
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
if (isset($_GET['msg'])) {
    $msg = clearStr($_GET['msg']);
    if ($msg == "Пост добавлен") {
        $msg = "<p class='ok'>$msg</p>";
    } else {
        $msg = "<p class='error'>$msg</p>";
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <meta charset='UTF-8'>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Добавление поста - Просто Блог</title>
    <link rel='stylesheet' href='css/addpost.css'>
</head>
<body>
<div class='content'>
    <div class='centerpost'>
        <p class='logo'><a class="logo" title='На главную' href='/'>Просто Блог</a></p>
        <div class='msg'>
            <?php
                if (!empty($msg)) {
                    echo $msg;
                }
            ?>
        </div>
        <p class='label'>Форма добавления поста:</p>
        <div class='form'>
            <form action='addpost.php' method='post' enctype="multipart/form-data" id='addpost'>
                <label id='input' for='addpostname' class='addpost'>Заголовок: </label>
                <input type='text' id='addpostname' title='Заголовок' class='addpostname' required minlength="1" maxlength='140' autofocus name='addPostZag' placeholder="Добавьте заголовок поста. Количество символов: от 20 до 140">
                
                <br> <input type="hidden" name="MAX_FILE_SIZE" value="<?= $maxSizeOfUploadImage ?>"> <br>
                <label id='img' for='file_img' class='addpost'>Пожалуйста, добавьте картинку. Допускаются jpg весом до <?= $maxSizeOfUploadImage ?> байт</label>
                <input class='addpostimg' type='file' name='addPostImg' id='file_img' > <!-- required -->
                <br>
                <br>
                <label id='input' for='content' class='addpost'>Содержание поста: </label>
                <br><textarea class='text' title='Содержание' required minlength="1" maxlength='4000' spellcheck="true"  wrap='hard' name='addPostContent' placeholder="Добавление содержания. Количество символов: от 20 до 4000 с пробелами" id='content'></textarea><br>
                
                <input type='submit' value='Добавить пост' class='addpostsubmit'>
            </form>
        </div>
    </div>
</div>
</body>
</html>