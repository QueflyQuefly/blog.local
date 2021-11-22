<?php
$functions = join(DIRECTORY_SEPARATOR, array('functions', 'functions.php'));
require_once $functions;
$error = '';

if (isset($_POST['login']) && isset($_POST['fio']) && isset($_POST['password'])) {
    $login = clearStr($_POST['login']);
    $fio = clearStr($_POST['fio']);
    $password = clearStr($_POST['password']);    
    if ($login && $fio && $password) {
        $password = password_hash($password, PASSWORD_BCRYPT);
        if (!createUser($login, $fio, $password)) {
            $error = "Пользователь с таким логином уже зарегистрирован";
            header("Location: reg.php?msg=$error"); 
        } else {
            $ok = "Аккаунт добавлен";
            header("Location: login.php?msg=$ok");
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
            <p class='logo'><a class="logo" href='/'>Просто Блог</a></p>
            <p class='label'>Регистрация</p>
            <form action='reg.php' method='post'>
                <input type='login' name='login' required autofocus minlength="1" maxlength='20' placeholder='Введите уникальный логин' class='text'><br>
                <input type='login' name='fio' required minlength="1" maxlength='20' autocomplete="true" placeholder='ФИО или псевдоним' class='text'><br>
                <input type='password' name='password' required minlength="1" maxlength='20' placeholder='Введите пароль' class='text'><br>

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