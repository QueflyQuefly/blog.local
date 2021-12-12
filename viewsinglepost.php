<?php
session_start();
$functions = join(DIRECTORY_SEPARATOR, array('functions', 'functions.php'));
require_once $functions;
$link = "<a class='menu' href='login.php'>Войти</a>";
$label = "<a class='menu' href='login.php'>Вы не авторизованы</a>";
$fio = '';
$login = '';
$adminLink = '';


if (isset($_GET['viewPostById'])) {
    $id = clearInt($_GET['viewPostById']);
    if (empty($id)) {
        header("Location: /");
    }
    $_SESSION['referrer'] = "viewsinglepost.php?viewPostById=$id";

    $post = getPostForViewById($id);
    $postRating = $post['rating'];
    $postAuthor = getUserIdAndFioByLogin($post['login']);
    $postAuthorId = $postAuthor['id'];
    if ($post['author'] != $postAuthor['fio']) {
        $dontShowLink = true;
    } else {
        $dontShowLink = false;
    }

    $tags = getTagsToPostById($id);

    $comments = getCommentsByPostId($id);

    $countRatings = countRatingsByPostId($id);
    
    $year = date("Y", time());
} else{
    header("Location: /");
}

if (isset($_GET['exit'])) {
    $_SESSION['user_id'] = false;
    $uri = str_replace('&exit', '', $_SERVER['REQUEST_URI']);
    header("Location: $uri");
}

if (!empty($_SESSION['user_id'])) {
    $user = getLoginFioRightsById($_SESSION['user_id']);
    $login = $user['login'];
    $fio = $user['fio'];
    $rights = $user['rights'];
    $label = "<a class='menu' href='cabinet.php'>Перейти в личный кабинет</a>";

    $link = "<a class='menu' href='viewsinglepost.php?viewPostById=$id&exit'>Выйти</a>";
    if ($rights === 'superuser') {
        $adminLink = "<a class='menu' href='admin/admin.php'>Админка</a>";
        $isAdmin = true;
    }
}
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (isset($_POST['addCommentContent'])) {
        if (!empty($_SESSION['user_id'])) {
            $commentAuthor = $login;
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
    if (!isUserChangesPostRating($login, $id) && isset($_POST['star'])) {
        if (!empty($_SESSION['user_id'])) {
            $star = clearInt($_POST['star']);
            changePostRating($star, $id, $login);
            header("Location: viewsinglepost.php?viewPostById=$id");
        } else {
            header("Location: login.php");
        }
    }
    
    if (isset($_POST['like'])) {
        if (!empty($_SESSION['user_id'])) {
        $like = clearInt($_POST['like']);
        changeComRating('like', $like, $id, $login);
        header("Location: viewsinglepost.php?viewPostById=$id#comment$like");
        } else {
            header("Location: login.php");
        }
    } 
    if (isset($_POST['unlike'])) {
        if (!empty($_SESSION['user_id'])) {
            $unlike = clearInt($_POST['unlike']);
            changeComRating('unlike', $unlike, $id, $login);
            header("Location: viewsinglepost.php?viewPostById=$id#comment$unlike");
        } else {
            header("Location: login.php");
        }
    }
}

if (isset($_GET['deletePostById'])) {
    $deletePostId = clearInt($_GET['deletePostById']);
    if ($deletePostId !== '') {
        deletePostById($deletePostId);
        header("Location: /");
    } 
}
if (isset($_GET['deleteCommentById'])) {
    $deleteCommentId = clearInt($_GET['deleteCommentById']);
    if ($deleteCommentId !== '') {
        deleteCommentById($deleteCommentId);
        header("Location: {$_SESSION['referrer']}");
    } 
}

$year = date("Y", time());
?>

<!DOCTYPE html>
<html>

<head>
    <meta charset='UTF-8'>
    <title>Пост - Просто блог</title>
    <link rel='stylesheet' href='css/indexcss.css'>
    <link rel="shortcut icon" href="/images/logo.jpg" type="image/x-icon">
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
                <li class='menu'><a class='menu' href='search.php'>Поиск</a></li>
                <li class='menu'><a class='menu' href='addpost.php'>Создать новый пост</a></li>
                <li class='menu'><?=$label?></li>
                <li class='menu'><?=$adminLink?></li>
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
            <?php
                if (!$dontShowLink) {
            ?>
            <p class='singlepostauthor'><a class='menu' title='Перейти в личный кабинет пользвателя' href='cabinet.php?user=<?=$postAuthorId?>'><?=$post['author']?></a></p>
            <?php
                } else {
            ?>
            <p class='singlepostauthor'><a class='menu' title='Пользователь не захотел раскрывать своё имя' href='viewsinglepost.php?viewPostById=<?=$id?>'><?=$post['author']?></a></p>
            <?php
                }
            ?>
            <p class='singlepostdate'><?=$post['date']?></p>
        </div>

        <div class='singlepostimage'>
            <img src='images/PostImgId<?=$id?>.jpg' alt='Картинка' class='singlepostimg'>
        </div>

        <div class='singleposttext'>
            
            <p class='singlepostcontent'><?=$post['content']?></p>
            <p class='singlepostcontent'> Тэги: 
                <?php 
                    if ($tags) {
                        foreach ($tags as $tag) {
                            $tagLink = substr($tag['tag'], 1);
                            echo "<a class='menu' href='search.php?search=%23$tagLink'>{$tag['tag']}</a> ";
                        }
                    } else {
                        echo "Нет тэгов";
                    }

                ?>
            </p>
            <?php
                if (!empty($isAdmin)) {
            ?>
            <object><a class='list' href='viewsinglepost.php?viewPostById=<?=$id?>&deletePostById=<?= $post['id'] ?>'> Удалить пост с ID=<?= $post['id'] ?></a></object><br>
            <?php
                }
            ?>
            
        </div>
        <div class='addcomments'  id='comment'>

            <?=$error?>

            <p class='center'>Добавьте комментарий:</p>

            <div class='addcomment'>

                <form action='viewsinglepost.php?viewPostById=<?=$id?>#comment' method='post'>
                   
                    <br><textarea name='addCommentContent' required  minlength="1" maxlength='500' wrap='hard' placeholder="Опишите ваши эмоции :-) (до 500 символов)" id='textcomment'></textarea><br>
                    
                    <input type='submit' value='Добавить комментарий' class='submit'>
                </form>
            </div>
        </div>

        <!-- viewing comments area-->
        <div class='viewcomments'>
            
            <p class='center'>Комментарии к посту:</p>
            <?php
                if (!empty($comments)) {
                for ($i = count($comments)-1; $i >= 0; $i--) {
                    $content = nl2br($comments[$i]['content']);
                    $authorComLogin = $comments[$i]['login'];
                    $author = getUserIdAndFioByLogin($authorComLogin);
                    $date = date("d.m.Y",$comments[$i]['date']) ." в ". date("H:i", $comments[$i]['date']);
            ?>

            <div class='viewcomment' id='comment<?= $comments[$i]['id'] ?>'>
                <p class='commentauthor'><a class='menu' href='cabinet.php?user=<?=$author['id']?>'><?=$author['fio']?></a><div class='commentdate'><?=$date?></div></p>
                <div class='commentcontent'>
                    <p class='commentcontent'><?=$content?></p> 
                    <p class='commentcontent'>
                        <?php
                            if (!empty($isAdmin)) {
                        ?> 
                            <object><a class='menu' href='viewsinglepost.php?viewPostById=<?=$id?>&deleteCommentById=<?= $comments[$i]['id'] ?>'> Удалить комментарий</a></object>
                        <?php
                            }
                        ?>
                    </p>
                </div>
                <div class='like'>
                    <?php
                        $countLikes = $comments[$i]['rating'];
                        if (!isUserChangesComRating($login, $comments[$i]['id'])) {
                            $name = 'like';
                        } else {
                            $name = 'unlike';
                        }
                    ?>
                    
                    <form action='viewsinglepost.php?viewPostById=<?=$id?>#comment<?=$comments[$i]['id']?>' method='post'>
                        <label class='like' title="Нравится" for='like<?=$comments[$i]['id']?>'><span class='like'>&#9825; </span><?=$countLikes?></label>
                        <input type="submit" class='like' id="like<?=$comments[$i]['id']?>" name="<?= $name ?>" value="<?=$comments[$i]['id']?>">
                    </form>

                </div>
                <hr>
            </div>

            <?php
                }
            } else {
            ?>

            <div class='viewnotcomment'>   
                <p class='commentcontent'>Пока ещё никто не оставил комментарий. Будьте первым!</p>
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