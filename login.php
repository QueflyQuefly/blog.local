<?php
session_start();
$functions = 'functions' . DIRECTORY_SEPARATOR . 'functions.php';
require_once $functions;

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $variableOfCaptcha = clearInt($_POST['variable_of_captcha']);
    $email = clearStr($_POST['email']);
    $password = $_POST['password'];
    if ($variableOfCaptcha == $_SESSION['variable_of_captcha']) {
        if (isUser($email, $password)) {
            $sessionUserId = getUserIdByEmail($email);
            setcookie('user_id', $sessionUserId, strtotime('+2 days'));

            if (!empty($_SESSION['referrer'])) {
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
        header("Location: login.php?msg=$error");
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
    <title>Вход - Просто Блог</title>
    <link rel='stylesheet' href='css/form.css'>
</head>
<body>
<div class='container'>

</div>
</body>
</html>