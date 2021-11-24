<?php
session_start();
$functions = join(DIRECTORY_SEPARATOR, array('functions', 'functions.php'));
require_once $functions;
$link = "<a class='menu' href='login.php'>Войти</a>";
$label = "<a class='menu' href='login.php'>Вы не авторизованы</a>";
$fio = '';
$login = '';
    
if (isset($_GET['exit'])) {
    $_SESSION['log_in'] = false;
    session_destroy();
    header("Location: {$_SERVER['REQUEST_URI']}");
} 

if (isset($_GET['viewPostById'])) {
    $id = clearInt($_GET['viewPostById']);
    if ($id  === '') {
        header("Location: /");
    }
    $post = getPostForViewById($id);
    $comments = getCommentsByPostId($id);
    $year = date("Y", time());
} else{
    header("Location: /");
}

if (isset($_SESSION['log_in']) && $_SESSION['log_in']) {
    $login = $_SESSION['login'];
    $fio = $_SESSION['fio'];

    $link = "<a class='menu' href='?exit'>Выйти</a>";
    if ($_SESSION['rights'] == 'superuser') {
        $label = "<a class='menu' href='admin/admin.php'>Вы вошли как администратор</a>";
    } else {
        $label = "<a class='menu' href='cabinet.php?user=$login'>Перейти в личный кабинет</a>";
    }
} else {
    session_destroy();
}
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (isset($_POST['addCommentAuthor']) && isset($_POST['addCommentContent'])) {
        if (isset($_SESSION['log_in']) && $_SESSION['log_in']) {
            $commentAuthor = $_POST['addCommentAuthor'];
            $commentContent = $_POST['addCommentContent'];
            if ($commentAuthor && $commentContent) {
                insertComments($id, $commentAuthor, time(), $commentContent);
                header("Location: viewsinglepost.php?viewPostById=$id");
            } else {
                $error = 'Комментарий не может быть пустым';
            }
        } else {
            header("Location: login.php");
        }
    }
    if (!isUserChangesPostRating($login, $id)) {
        if (isset($_POST['star']) && isset($_SESSION['log_in']) && $_SESSION['log_in']) {
            $star = clearInt($_POST['star']);
            changePostRating($star, $id, $login);
            header("Location: viewsinglepost.php?viewPostById=$id");
        } else {
            header("Location: login.php");
        }
    }
    
    if (isset($_POST['like'])) {
        if (isset($_SESSION['log_in']) && $_SESSION['log_in']) {
        $like = clearInt($_POST['like']);
        changeComRating('like', $like, $id, $login);
        header("Location: viewsinglepost.php?viewPostById=$id#comment$like");
        } else {
            header("Location: login.php");
        }
    } 
    if (isset($_POST['unlike'])) {
        if (isset($_SESSION['log_in']) && $_SESSION['log_in']) {
            $unlike = clearInt($_POST['unlike']);
            changeComRating('unlike', $unlike, $id, $login);
            header("Location: viewsinglepost.php?viewPostById=$id#comment$unlike");
        } else {
            header("Location: login.php");
        }
    }
}

$countRatings = countRatingsByPostId($id);

$postRating = $post['rating'];
$year = date("Y", time());
?>


<!DOCTYPE html>
<html>

<head>
    <meta charset='UTF-8'>
    <title>Пост - Просто блог</title>
    <link rel='stylesheet' href='css/indexcss.css'>
</head>
<body>
<nav>
    <div class='top'>
        <div class="logo">
            <a class="logo" title="На главную" href='/'><img id='logo' src='images/logo.jpg' alt='Лого' width='50' height='50'>
            <div id='namelogo'>Просто Блог</div></а>
        </div>
        <div class="menu">
            <ul class='menu'>
                <li class='menu'><?=$link?></li>
                <li class='menu'><a class='menu' href='addpost.php'>Создать новый пост</a></li>
                <li class='menu'><?=$label?></li>
            </ul>
        </div>
    </div>
</nav>

<div class='allsinglepost'>
    <div class='contentsinglepost'>

        <div id='singlepostzagolovok'><p class='singlepostzagolovok'><?=$post['name']?></p></div>

        <div id='singlepostauthor'>
            
            <?php
                if ($countRatings) {
                    echo "<p class='singlepostdate'>Рейтинг поста: $postRating из 5. Оценок: $countRatings</p>";
                } else {
                    echo "<p class='singlepostdate'>Оценок 0. Будьте первым!</p>";
                }
                
                if (!isUserChangesPostRating($login, $id)) {
            ?>
            <div class="rating-area">
                <form action='<?=$_SERVER['REQUEST_URI']?>' method='post'>
                    <label class='star' title="Оценка «1»" for='star-1'>&#9734;</label>
                    <input type="submit" id="star-1" name="star" value="1">

                    <label class='star' title="Оценка «2»" for='star-2'>&#9734;</label>
                    <input type="submit" id="star-2" name="star" value="2">

                    <label class='star' title="Оценка «3»" for='star-3'>&#9734;</label>
                    <input type="submit" id="star-3" name="star" value="3">

                    <label class='star' title="Оценка «4»" for='star-4'>&#9734;</label>
                    <input type="submit" id="star-4" name="star" value="4">

                    <label class='star' title="Оценка «5»" for='star-5'>&#9734;</label>
                    <input type="submit" id="star-5" name="star"  value="5">
                </form>
            </div>
            <?php 
                } else {
                    echo "<p class='singlepostdate'>Оценка принята</p>";
                }
            ?>
        </div>


        <div id='singlepostauthor'>
            <p class='singlepostauthor'><?=$post['author']?></p>
            <p class='singlepostdate'><?=$post['date']?></p>
        </div>

        <div class='singlepostimage'>
            <img src='images/PostImgId<?=$id?>.jpg' alt='Картинка' class='singlepostimg'>
        </div>

        <div class='singleposttext'>
            
            <p class='singlepostcontent'><?=$post['content']?></p>
            
        </div>
        <div class='addcomments'  id='comment'>

            <?=$error?>

            <p class='center'>Добавьте комментарий:</p>

            <div class='addcomment'>

                <form action='<?=$_SERVER['REQUEST_URI']?>#comment' method='post'>
                    <input type='hidden' name='addCommentAuthor' value='<?=$fio?>'> 
                   
                    <br><textarea name='addCommentContent' required  minlength="1" maxlength='500' wrap='hard' placeholder="Опишите ваши эмоции :-) (до 500 символов)" id='textcomment'></textarea><br>
                    
                    <input type='submit' value='Добавить комментарий' class='submit'>
                </form>
            </div>
        </div>

        <!-- viewing comments area-->
        <div class='viewcomments'>
            
            <p class='center'>Комментарии к посту:</p>
            <?php
                if (!empty($comments) && $comments != false) {
                for ($i = count($comments)-1; $i >= 0; $i--) {
                    $content = nl2br($comments[$i]['content']);
                    $date = date("d.m.Y",$comments[$i]['date']) ." в ". date("H:i", $comments[$i]['date']);
            ?>

            <div class='viewcomment' id='comment<?= $comments[$i]['id'] ?>'>
                <p class='commentauthor'><?=$comments[$i]['author']?><div class='commentdate'><?=$date?></div></p>
                <div class='commentcontent'>
                    <p class='commentcontent'><?=$content?></p> 
                </div>
                <div class='like'>
                    <?php
                        $countLikes = $comments[$i]['rating'];
                        if (isset($login) && !isUserChangesComRating($login, $comments[$i]['id'])) {
                            $name = 'like';
                        } else {
                            $name = 'unlike';
                        }
                    ?>
                    
                    <form action='<?=$_SERVER['REQUEST_URI']?>' method='post'>
                        <label class='like' title="Нравится" for='like'>&#9825;</label>
                        <input type="submit" id="like" name="<?= $name ?>" value="<?=$comments[$i]['id']?>"><?=$countLikes?>
                    </form>

                </div>
                <hr>
            </div>

            <?php
                }
            } else {
            ?>

            <div class='viewnotcomment'>   
                <p class='commentcontent'>Нет комментариев к этой записи</p>
                <hr>
            </div>

            <?php
                }
            ?>
        </div>

    </div>

    <footer class='bottom'>
        <p>Website by Вячеслав Бельский &copy; <?=$year?></p>
    </footer>
</div>
</body>
</html>