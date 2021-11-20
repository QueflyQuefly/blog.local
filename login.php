<?php
session_start();

$_SESSION['Log_in'] = false;
$error = '';

require_once "functions/functions.php";

if (isset($_POST['Login']) && isset($_POST['Password'])) {
    $login = clearStr($_POST['Login']);
    $password = clearStr($_POST['Password']);

    if (isUser($login, $password)) {
        $_SESSION['Log_in'] = true;
        $_SESSION['Fio'] = $fio;
        $_SESSION['Rights'] = getRightsByLogin($login);
    } else {
        $error = "Неверный логин или пароль";
        header("Location: login.php?msg=$error");
    }
}

if ($_SESSION['Log_in'] === true) {
    if (isset($_SESSION['Referrer'])) {
        $ref = $_SESSION['Referrer'];
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

            <p class='logo'><a class="logo" href='/'>Просто Блог</a></p>
            <p class='label'>Вход</p>

            <form action='<?=$_SERVER['PHP_SELF']?>' method='post'>
                <input type='login' name='Login' required minlength="4" maxlength='20' autofocus autocomplete="true" placeholder='Ваш логин' class='text'><br>
                <input type='password' name='Password' required minlength="5" maxlength='20' placeholder='Ваш пароль' class='text'><br>

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