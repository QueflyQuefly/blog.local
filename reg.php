<?php
session_start();
$functions = 'functions' . DIRECTORY_SEPARATOR . 'functions.php';
require_once $functions;

if (!empty($_SESSION['user_id']) && strpos($_SESSION['user_id'], RIGHTS_SUPERUSER) !== false) {
    $forAdmin = "<label><input type='checkbox' name='add_admin'>Зарегистрировать как админа</label>";
}
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $variableOfCaptcha = clearInt($_POST['variable_of_captcha']);
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
        if ($variableOfCaptcha == $_SESSION['variable_of_captcha']) {
            if (isset($_POST['add_admin'])) {
                if (!addUser($email, $fio, $password, RIGHTS_SUPERUSER)) {
                    $error = "Пользователь с таким email уже зарегистрирован";
                    header("Location: reg.php?msg=$error"); 
                } else {
                    header("Location: /");
                } 
            } else {
                if (!addUser($email, $fio, $password)) {
                    $error = "Пользователь с таким email уже зарегистрирован";
                    header("Location: reg.php?msg=$error"); 
                } else {
                    $_SESSION['user_id'] = getUserIdByEmail($email);
                    setcookie('user_id', $_SESSION['user_id'], strtotime('+2 days'));
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
}
?>

<!DOCTYPE html>
<html>
<head>
    <meta charset='UTF-8'>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Регистрация - Просто Блог</title>
    <link rel='stylesheet' href='css/form.css'>
</head>
<body>
<div class='container'>
    <div class='center'>
        <div class='form'>
            <p class='logo'><a class="logo" title='На главную' href='/'>Просто Блог</a></p>
            <p class='label'>Регистрация</p>
            <form action='reg.php' method='post'>
                <input type='email' name='email' required autofocus minlength="1" maxlength='50'  autocomplete="on" placeholder='Введите email' class='text'><br>
                <input type='login' name='fio' required minlength="1" maxlength='50' autocomplete="on" placeholder='ФИО или псевдоним' class='text'><br>
                <input type='password' name='password' required minlength="1" maxlength='20' autocomplete="off" placeholder='Введите пароль' class='text'><br>
                <img src="noise-picture.php">
                <input type='text' name='variable_of_captcha' required minlength="1" maxlength='20' autocomplete="off" placeholder='Введите код с картинки' class='text'><br>
                <?php
                    if (!empty($forAdmin)) {
                        echo $forAdmin;
                    }
                ?>
                <div class='msg'>
                    <p class='error'>
                        <?php
                            if (!empty($msg)) {
                                echo $msg;
                            }
                        ?>
                    </p>
                </div>
                <div id='right'><input type='submit' value='Создать аккаунт' class='submit'></div>
            </form>
        </div>
    </div>
</div>
</body>
</html>