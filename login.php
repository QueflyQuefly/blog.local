<?php
session_start();
$functions = 'functions' . DIRECTORY_SEPARATOR . 'functions.php';
require_once $functions;

$error = '';
$ok = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $integer = clearInt($_POST['integer']);
    $login = clearStr($_POST['login']);
    $password = clearStr($_POST['password']);
    if ($integer == $_SESSION['integer']) {
        if (isUser($login, $password)) {
            $user = getUserIdAndFioByEmail($login);
            $_SESSION['user_id'] = $user['id'];

            if (isset($_SESSION['referrer'])) {
                header("Location: {$_SESSION['referrer']}");
            } else {
                header("Location: /");
            }
        } else {
            $error = "Неверный логин или пароль";
            header("Location: login.php?msg=$error");
        }
    } else {
        $error = "Неверно введен код с Captcha";
        header("Location: reg.php?msg=$error");
    }
}
if (isset($_GET['msg'])) {
    $error = clearStr($_GET['msg']);
}

?>

<!DOCTYPE html>
<html>
<head>
    <meta charset='UTF-8'>
    <title>Вход - Просто Блог</title>
    <link rel='stylesheet' href='css/formcss.css'>
</head>
<body>
<div class='container'>
    <div class='center'>
        <div class='form'>

            <p class='logo'><a class="logo" title='На главную' href='/'>Просто Блог</a></p>
            <p class='label'>Вход</p>

            <form action='login.php' method='post'>
                <input type='login' name='login' required minlength="1" maxlength='50' autofocus autocomplete="true" placeholder='Ваш логин' class='text'><br>
                <input type='password' name='password' required minlength="1" maxlength='20' placeholder='Ваш пароль' class='text'><br>
                <img src="noise-picture.php">
                <input type='login' name='integer' required minlength="1" maxlength='20' placeholder='Введите код с картинки' class='text'><br>

                <div class='msg'>
                    <p class='error'><?=$error?></p>
                    <p class='ok'><?=$ok?></p>
                </div>

                <div id='left'><a class='button' href='reg.php'><div class='button'>Создать аккаунт</div></a></div>
                <div id='right'><input type='submit' value='Войти' class='submit'></div>
            </form>
            
        </div>
    </div> 
</div>
</body>
</html>