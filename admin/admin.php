<?php
require_once $file_functions;

session_start();
unset($_SESSION['referrer']);
$_SESSION['referrer'] = $_SERVER['REQUEST_URI'];

$_SESSION['entrance'] = false;

if (isset($_POST['view'])) {
    if ($_POST['view'] == 'viewposts') {
        header("Location: adminposts.php");
    }
    if ($_POST['view'] == 'viewusers') {
        header("Location: adminusers.php");
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
            <p class='logo'><a class="logo" href='/'>Просто Блог</a></p>
            <p class='label'>Администрирование</p>

            <?php if ($_SESSION['rights'] != "superuser") { ?>

            <div class='msg'>
                <p class='error'>Необходимо <a class='link' href='/login.php'>войти</a> как администратор</p>
            </div>

            <?php } else { ?>

            <form action='admin.php'  method='post'>

                <div class='radio'>
                    <input type='radio' id='radio1' name='view' value='viewusers' class='radio'>
                    <label for='radio1'>К управлению пользователями</label>

                    <br><input type='radio' id='radio2' name='view' value='viewposts' class='radio'>
                    <label for='radio2'>К управлению постами</label>
                </div>

                <br><div id='right'><input type='submit' value='Перейти' class='submit'></div>
            </form>

            <?php } ?>

        </div>    
    </div>
</div>
</body>
</html>