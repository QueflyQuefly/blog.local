<?php
$file_functions = join(DIRECTORY_SEPARATOR, array(dirname(__DIR__), 'functions', 'functions.php'));
require_once $file_functions;

session_start();

$_SESSION['referrer'] = $_SERVER['REQUEST_URI'];

if (!empty($_SESSION['user_id'])) {
    $user = getUserEmailFioRightsById($_SESSION['user_id']);
    $rights = $user['rights'];
} else {
    $rights = false;
}
if (isset($_POST['view'])) {
    if ($_POST['view'] === 'viewPosts') {
        header("Location: adminposts.php");
    }
    if ($_POST['view'] === 'viewUsers') {
        header("Location: adminusers.php");
    }
    if ($_POST['view'] === 'addAdmin') {
        header("Location: reg.php");
    }
}

?>

<!DOCTYPE html>
<html>
<head>
    <meta charset='UTF-8'>
    <title>Администрирование - Просто Блог</title>
    <link rel='stylesheet' href='css/admincss.css'>
</head>
<body>
<div class='container'>
    <div class='center'>
        <div class='form'>
            <p class='logo'><a class="logo" title='На главную' href='/'>Просто Блог</a></p>
            <p class='label'>Администрирование</p>

            <?php if ($rights !== "superuser") { ?>

            <div class='msg'>
                <p class='error'>Необходимо <a class='link' href='/login.php'>войти</a> как администратор</p>
            </div>

            <?php } else { ?>

            <form action='admin.php'  method='post'>

                <div class='radio'>
                    <input type='radio' id='radio1' name='view' value='viewUsers' class='radio'>
                    <label for='radio1'>К управлению пользователями</label>

                    <br><input type='radio' id='radio2' name='view' value='viewPosts' class='radio'>
                    <label for='radio2'>К управлению постами</label>

                    <br><input type='radio' id='radio3' name='view' value='addAdmin' class='radio'>
                    <label for='radio3'>Добавить администратора</label>
                </div>

                <br><div id='right'><input type='submit' value='Перейти' class='submit'></div>
            </form>

            <?php } ?>

        </div>    
    </div>
</div>
</body>
</html>