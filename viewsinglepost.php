<?php
session_start();
$functions = 'functions' . DIRECTORY_SEPARATOR . 'functions.php';
require_once $functions;

$twoDaysInSeconds = 60*60*24*2;
header("Cache-Control: max-age=$twoDaysInSeconds");
header("Cache-Control: must-revalidate");

if (!empty($_GET['viewPostById'])) {
    $postId = clearInt($_GET['viewPostById']);
    $_SESSION['referrer'] = "viewsinglepost.php?viewPostById=$postId";
    $post = getPostForViewById($postId);
    if (empty($post)) {
        header("Location: /");
    }
    $postAuthorId = $post['user_id'];
    $tags = getTagsToPostById($postId);
    $comments = getCommentsByPostId($postId);
} else{
    header("Location: /");
}
if (isset($_GET['exit']) && !empty($sessionUserId)) {
    $sessionUserId = false;
    setcookie('user_id', '0', 1);
    header("Location: viewsinglepost.php?viewPostById=$postId");
}
if (!empty($_COOKIE['user_id'])) {
    $sessionUserId = $_COOKIE['user_id'];
} elseif (!empty($_SESSION['user_id'])) {
    $sessionUserId = $_SESSION['user_id'];
}
if (!empty($sessionUserId) && getUserInfoById($sessionUserId, 'rights') === RIGHTS_SUPERUSER) {
    $isSuperuser = true;
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
}
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (!empty($sessionUserId)) {
        if (isset($_POST['addCommentContent'])) {
            $commentAuthorId = $sessionUserId;
            $commentContent = $_POST['addCommentContent'];
            if ($commentAuthorId && $commentContent) {
                insertComments($postId, $commentAuthorId, time(), $commentContent, 0);
                header("Location: viewsinglepost.php?viewPostById=$postId");
            } else {
                $error = 'Комментарий не может быть пустым';
            }
        }
        if (!isUserChangesPostRating($sessionUserId, $postId) && isset($_POST['star'])) {
            $star = clearInt($_POST['star']);
            changePostRating($sessionUserId, $postId, $star);
            header("Location: viewsinglepost.php?viewPostById=$postId");
        }
        
        if (isset($_POST['like'])) {
            $like = clearInt($_POST['like']);
            changeCommentRating('like', $like, $postId, $sessionUserId);
            header("Location: viewsinglepost.php?viewPostById=$postId#comment$like");
        } 
        if (isset($_POST['unlike'])) {
            $unlike = clearInt($_POST['unlike']);
            changeCommentRating('unlike', $unlike, $postId, $sessionUserId);
            header("Location: viewsinglepost.php?viewPostById=$postId#comment$unlike");
        }
    } else {
        header("Location: login.php");
    }
}
$year = date("Y", time());
?>

<!DOCTYPE html>
<html>
<head>
    <meta charset='UTF-8'>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Пост - Просто блог</title>
    <link rel='stylesheet' href='css/general.css'>
    <link rel="shortcut icon" href="/images/logo.jpg" type="image/x-icon">
</head>
<body>
<nav>
    <div class='top'>
        <div id="logo">
            <a class="logo" title="На главную" href='/'>
            <img id='imglogo' src='images/logo.jpg' alt='Лого'>
            <div id='namelogo'>Просто Блог</div>
            </а>
        </div>
        <div id="menu">
            <ul class='menuList'>
                <?php
                    if (empty($sessionUserId)) {
                        echo "<li><a class='menuLink' href='login.php'>Войти</a></li>";
                    } else {
                        echo "<li><a class='menuLink' href='viewsinglepost.php?viewPostById=$postId&exit'>Выйти</a></li>";
                        if (!empty($isSuperuser)) {
                            echo "<li><a class='menuLink' href='admin/admin.php'>Админка</a></li>";
                        }
                    }
                ?>
                <li><a class='menuLink' href='cabinet.php'>Мой профиль</a></li>
                <li><a class='menuLink' href='search.php'>Поиск</a></li>
                <li><a class='menuLink' href='addpost.php'>Создать новый пост</a></li>
            </ul>
        </div>
    </div>
</nav>
<div class='allsinglepost'>
    <div class='contentsinglepost'>
        <div id='singleposttitle'><p class='singleposttitle'><?= $post['title']?></p></div>
        <div id='singlepostauthor'>
            <?php
                if (!empty($post['count_ratings'])) {
                    echo "<p class='singlepostdate'>Рейтинг поста: {$post['rating']} из 5. Оценок: {$post['count_ratings']}</p>";
                } else {
                    echo "<p class='singlepostdate'>Оценок 0. Будьте первым!</p>";
                }
                
                if (empty($sessionUserId) || !isUserChangesPostRating($sessionUserId, $postId)) {
            ?>
            <div class="rating-area">
                <form action='<?= $_SERVER['REQUEST_URI']?>' method='post'>
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
            <p class='singlepostauthor'><a class='menuLink' title='Перейти в профиль пользвателя' href='cabinet.php?user=<?= $postAuthorId?>'><?= $post['author']?></a></p>
            <p class='singlepostdate'><?= $post['date_time']?></p>
        </div>
        <div class='singlepostimage'>
            <img src='images/PostImgId<?= $postId?>.jpg' alt='Картинка' class='singlepostimg'>
        </div>
        <div class='singleposttext'>
            <p class='singlepostcontent'><?= $post['content']?></p>
            <p class='singlepostcontent'> Тэги: 
                <?php 
                    if ($tags) {
                        foreach ($tags as $tag) {
                            $tagLink = substr($tag['tag'], 1);
                            echo "<a class='link' href='search.php?search=%23$tagLink'>{$tag['tag']}</a> ";
                        }
                    } else {
                        echo "Нет тэгов";
                    }

                ?>
            </p>
            <?php
                if (!empty($isSuperuser)) {
            ?>
            <object><a class='list' href='viewsinglepost.php?viewPostById=<?= $postId?>&deletePostById=<?=  $postId ?>'> Удалить пост с ID = <?=  $postId ?></a></object><br>
            <?php
                }
            ?>
        </div>
        <div class='addcomments'  id='comment'>

            <?= $error?>

            <p class='center'>Добавьте комментарий:</p>

            <div class='addcomment'>

                <form action='viewsinglepost.php?viewPostById=<?= $postId?>#comment' method='post'>
                   
                    <br><textarea name='addCommentContent' required  minlength="1" maxlength='500' wrap='hard' placeholder="Опишите ваши эмоции :-) (до 500 символов)" id='textcomment'></textarea><br>
                    
                    <input type='submit' value='Добавить комментарий' class='submit'>
                </form>
            </div>
        </div>
        <!-- viewing comments area-->
        <div class='viewcomments'>
            <p class='center'>Комментарии к посту (всего <?=$post['count_comments']?>):</p>
            <?php
            if (!empty($comments)) {
                foreach ($comments as $comment) {
                    $comment['content'] = nl2br(clearStr($comment['content']));
                    $comment['date_time'] = date("d.m.Y в H:i", $comment['date_time']);
            ?>

            <div class='viewcomment' id='comment<?=  $comment['comment_id'] ?>'>
                <p class='commentauthor'>
                    <a class='menuLink' href='cabinet.php?user=<?=  $comment['user_id'] ?>'><?=  $comment['author'] ?></a>
                    <div class='commentdate'><?=  $comment['date_time'] ?></div>
                </p>
                <div class='commentcontent'>
                    <p class='commentcontent'><?=  $comment['content'] ?></p> 
                    <p class='commentcontent'>
                        <?php
                            if (!empty($isSuperuser)) {
                        ?> 
                            <object>
                                <a class='menuLink' href='viewsinglepost.php?viewPostById=<?=  $postId ?>&deleteCommentById=<?=  $comment['comment_id'] ?>'>
                                    Удалить комментарий
                                </a>
                            </object>
                        <?php
                            }
                        ?>
                    </p>
                </div>
                <div class='like'>
                    <?php
                        $countLikes = $comment['rating'];
                        if (empty($sessionUserId) || !isUserChangedCommentRating($sessionUserId, $comment['comment_id'])) {
                            $name = 'like';
                        } else {
                            $name = 'unlike';
                        }
                    ?>
                    <form action='viewsinglepost.php?viewPostById=<?= $postId?>#comment<?= $comment['comment_id']?>' method='post'>
                        <label id='heartlike' title="Нравится" for='like<?= $comment['comment_id']?>'>
                            <span class='like'>&#9825; </span>
                            <?= $countLikes?>
                        </label>
                        <input type="submit" class='nodisplay' id="like<?= $comment['comment_id']?>" name="<?=  $name ?>" value="<?= $comment['comment_id']?>">
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
</div>
<footer>
    <p>Website by Вячеслав Бельский &copy; <?=  $year ?></p>
</footer>
</body>
</html>