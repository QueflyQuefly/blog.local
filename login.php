<?php
session_start();

$_SESSION['log_in'] = false;
$error = '';
$ok = '';

$functions = join(DIRECTORY_SEPARATOR, array('functions', 'functions.php'));
require_once $functions;

if (isset($_POST['login']) && isset($_POST['password'])) {
    $login = clearStr($_POST['login']);
    $password = clearStr($_POST['password']);

    if (isUser($login, $password)) {
        $_SESSION['log_in'] = true;
        $_SESSION['login'] = $login;
        $_SESSION['fio'] = $fio; //it is global var from function isUser
        $_SESSION['rights'] = getRightsByLogin($login);
    } else {
        $error = "Неверный логин или пароль";
        header("Location: login.php?msg=$error");
    }
}

if ($_SESSION['log_in'] === true) {
    if (isset($_SESSION['referrer'])) {
        $ref = $_SESSION['referrer'];
        if (strpos($ref, "&exit")) {
            $ref = explode("&", $ref);
            header("Location: {$ref[0]}");
        } else {
            header("Location: $ref");
        }
    } else {
        header("Location: /");
    }
} 
if (isset($_GET['msg'])) {
    $msg = clearStr($_GET['msg']);
    if ($msg == "Аккаунт добавлен") {
        $ok = $msg;
    }else {
        $error = $msg;
    }
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
                <input type='login' name='login' required minlength="1" maxlength='20' autofocus autocomplete="true" placeholder='Ваш логин' class='text'><br>
                <input type='password' name='password' required minlength="1" maxlength='20' placeholder='Ваш пароль' class='text'><br>

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