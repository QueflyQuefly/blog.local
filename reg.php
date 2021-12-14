<?php
session_start();
$functions = 'functions' . DIRECTORY_SEPARATOR . 'functions.php';
require_once $functions;

$error = '';
$forAdmin = '';
$email = '';
$fio = '';

if (!empty($_SESSION['user_id'])) {
    $user = getUserEmailFioRightsById($_SESSION['user_id']);
    $rights = $user['rights'];
    if ($rights === 'superuser') {
        $forAdmin = "<label><input type='checkbox' name='add_admin' class='center'>Зарегистрировать как админа</label>";
    }
}
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $integer = clearInt($_POST['integer']);
    $email = clearStr($_POST['email']);
    $fio = clearStr($_POST['fio']);
    $password = clearStr($_POST['password']);
    $regex = '/\A[^@]+@([^@\.]+\.)+[^@\.]+\z/u';
    if (!preg_match($regex, $email)) {
        $error = "Неверный формат email";
        header("Location: reg.php?msg=$error");
    }   
    if ($email !== '' && $fio !== '' && $password !== '') {
        $password = password_hash($password, PASSWORD_BCRYPT);
        if ($integer == $_SESSION['integer']) {
            if (isset($_POST['add_admin'])) {
                if (!createAdmin($email, $fio, $password)) {
                    $error = "Пользователь с таким email уже зарегистрирован";
                    header("Location: reg.php?msg=$error"); 
                } else {
                    header("Location: /");
                } 
            } else {
                if (!createUser($email, $fio, $password)) {
                    $error = "Пользователь с таким email уже зарегистрирован";
                    header("Location: reg.php?msg=$error"); 
                } else {
                    $_SESSION['user_id'] = getUserIdByEmail($email);
                    header("Location: /");
                } 
            }
        } else {
            $error = "Неверно введен код с Captcha";
            header("Location: reg.php?msg=$error");
        }
    } else { 
        $error = "Заполните все поля";
        header("Location: reg.php?msg=$error");
    }
}
if (isset($_GET['msg'])) {
    $msg = clearStr($_GET['msg']);
    $error = $msg;
}

?>

<!DOCTYPE html>
<html>
<head>
    <meta charset='UTF-8'>
    <title>Регистрация - Просто Блог</title>
    <link rel='stylesheet' href='css/formcss.css'>
</head>
<body>
<div class='container'>
    <div class='center'>
        <div class='form'>
            <p class='logo'><a class="logo" title='На главную' href='/'>Просто Блог</a></p>
            <p class='label'>Регистрация</p>
            <form action='reg.php' method='post'>
                <input type='email' name='email' required autofocus minlength="1" maxlength='50'  autocomplete="on" placeholder='Введите email' class='text' value="<?=$email?>"><br>
                <input type='login' name='fio' required minlength="1" maxlength='50' autocomplete="on" placeholder='ФИО или псевдоним' class='text' value="<?=$fio?>"><br>
                <input type='password' name='password' required minlength="1" maxlength='20' autocomplete="off" placeholder='Введите пароль' class='text'><br>
                <img src="noise-picture.php">
                <input type='text' name='integer' required minlength="1" maxlength='20' autocomplete="off" placeholder='Введите код с картинки' class='text'><br>
                <?=$forAdmin?>

                <div class='msg'>
                    <p class='error'><?=$error?></p>
                </div>

                <div id='right'><input type='submit' value='Создать аккаунт' class='submit'></div>
            </form>
        </div>
    </div>
</div>
</body>
</html>