<?php
session_start();
$functions = 'functions' . DIRECTORY_SEPARATOR . 'functions.php';
require_once $functions;

if (!empty($_COOKIE['user_id'])) {
    $sessionUserId = $_COOKIE['user_id'];
} elseif (!empty($_SESSION['user_id'])) {
    $sessionUserId = $_SESSION['user_id'];
}
if (!empty($sessionUserId) && getUserInfoById($sessionUserId, 'rights') === RIGHTS_SUPERUSER) {
    $isSuperuser = true;
    $forAdmin = "<label><input type='checkbox' name='add_admin'>Зарегистрировать как админа</label>";
}
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $variableOfCaptcha = clearInt($_POST['variable_of_captcha']);
    $email = clearStr($_POST['email']);
    $fio = clearStr($_POST['fio']);
    $password = $_POST['password'];
    $regex = '/\A[^@]+@([^@\.]+\.)+[^@\.]+\z/u';
    if (!preg_match($regex, $email)) {
        $error = "Неверный формат email";
        header("Location: reg.php?msg=$error");
    }   
    if ($email !== '' && $fio !== '' && $password !== '') {
        $password = password_hash($password, PASSWORD_BCRYPT);
        if ($variableOfCaptcha == $_SESSION['variable_of_captcha']) {
            if (isset($_POST['add_admin']) && $isSuperuser === true) {
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
                    $sessionUserId = getUserIdByEmail($email);
                    setcookie('user_id', $sessionUserId, strtotime('+2 days'));
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
    
</div>
</body>
</html>