<?php
$functions = join(DIRECTORY_SEPARATOR, array('functions', 'functions.php'));
require_once $functions;
$error = '';
$forAdmin = '';
session_start();
if (!empty($_SESSION['user_id'])) {
    $user = getLoginFioRightsById($_SESSION['user_id']);
    $rights = $user['rights'];
    if ($rights === 'superuser') {
        $forAdmin = "<label><input type='checkbox' name='add_admin' class='center'>Зарегистрировать как админа</label>";
    }
}
if (isset($_POST['login']) && isset($_POST['fio']) && isset($_POST['password'])) {
    $login = clearStr($_POST['login']);
    $fio = clearStr($_POST['fio']);
    $password = clearStr($_POST['password']);
    $regex = '/\A[^@]+@([^@\.]+\.)+[^@\.]+\z/u';
    if (!preg_match($regex, $login)) {
        $error = "Неверный формат email";
        header("Location: reg.php?msg=$error");
        exit;
    }   
    if ($login !== '' && $fio !== '' && $password !== '') {
        $password = password_hash($password, PASSWORD_BCRYPT);
        if (isset($_POST['add_admin'])) {
            if (!addAdmin($login, $fio, $password)) {
                $error = "Пользователь с таким email уже зарегистрирован";
                header("Location: reg.php?msg=$error"); 
            } else {
                header("Location: /");
            } 
        } else {
            if (!createUser($login, $fio, $password)) {
                $error = "Пользователь с таким email уже зарегистрирован";
                header("Location: reg.php?msg=$error"); 
            } else {
                $_SESSION['log_in'] = true;
                $user = getUserIdAndFioByLogin($login);
                $userId = $user['id'];
                $_SESSION['user_id'] = $userId;
                header("Location: /");
            } 
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
                <input type='login' name='login' required autofocus minlength="1" maxlength='50' placeholder='Введите email' class='text'value='@gmail.com'><br>
                <input type='login' name='fio' required minlength="1" maxlength='50' autocomplete="true" placeholder='ФИО или псевдоним' class='text'><br>
                <input type='password' name='password' required minlength="1" maxlength='20' placeholder='Введите пароль' class='text'><br>
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