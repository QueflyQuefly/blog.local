<?php
require_once "functions/functions.php";

if (isset($_POST['Login']) && isset($_POST['Fio']) && isset($_POST['Password'])) {
    $login = clearStr($_POST['Login']);
    $fio = clearStr($_POST['Fio']);
    $password = clearStr($_POST['Password']);    
    if ($login && $fio && $password) {
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
            <form action='<?=$_SERVER['PHP_SELF']?>' method='post'>
                <input type='login' name='Login' required autofocus minlength="4" maxlength='20' placeholder='Введите уникальный логин' class='text'><br>
                <input type='login' name='Fio' required minlength="3" maxlength='20' autocomplete="true" placeholder='ФИО или псевдоним' class='text'><br>
                <input type='password' name='Password' required minlength="5" maxlength='20' placeholder='Введите пароль' class='text'><br>

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