<?php
session_start();
$functions = 'functions' . DIRECTORY_SEPARATOR . 'functions.php';
require_once $functions;

if (isset($_GET['viewPostById'])) {
    $postId = clearInt($_GET['viewPostById']);
    if (empty($postId)) {
        header("Location: /");
    }
    $_SESSION['referrer'] = "viewsinglepost.php?viewPostById=$postId";

    $post = getPostForViewById($postId);
    $postAuthorId = $post['user_id'];
    $postAuthor = getUserEmailFioRightsById($postAuthorId);
    $postAuthorFio = $postAuthor['fio'];

    $tags = getTagsToPostById($postId);

    $comments = getCommentsByPostId($postId);
    
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
    $userId = $_SESSION['user_id'];
    $user = getUserEmailFioRightsById($userId);

    if ($user['rights'] === 'superuser') {
        $isAdmin = true;
        
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
}
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (!empty($_SESSION['user_id'])) {
        if (isset($_POST['addCommentContent'])) {
            $commentAuthorId = $userId;
            $commentContent = $_POST['addCommentContent'];
            if ($commentAuthorId && $commentContent) {
                insertComments($postId, $commentAuthorId, time(), $commentContent);
                header("Location: viewsinglepost.php?viewPostById=$postId");
            } else {
                $error = 'Комментарий не может быть пустым';
            }
        }
        if (!isUserChangesPostRating($userId, $postId) && isset($_POST['star'])) {
            $star = clearInt($_POST['star']);
            changePostRating($userId, $postId, $star);
            header("Location: viewsinglepost.php?viewPostById=$postId");
        }
        
        if (isset($_POST['like'])) {
            $like = clearInt($_POST['like']);
            changeCommentRating('like', $like, $postId, $userId);
            header("Location: viewsinglepost.php?viewPostById=$postId#comment$like");
        } 
        if (isset($_POST['unlike'])) {
            $unlike = clearInt($_POST['unlike']);
            changeCommentRating('unlike', $unlike, $postId, $userId);
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
    <title>Пост - Просто блог</title>
    <link rel='stylesheet' href='css/indexcss.css'>
    <link rel="shortcut icon" href="/images/logo.jpg" type="image/x-icon">
</head>
<body>
<nav>
    <div class='top'>
        <div class="logo">
            <a class="logo" title="На главную" href='/'><img id='logo' src='images/logo.jpg' alt='Лого' width='50' height='50'>
            <div id='namelogo'>Просто Блог</div></a>
        </div>
        <div class="menu">
            <ul class='menu'>
                <?php
                    if (empty($_SESSION['user_id'])) {
                        echo "<li class='menu'><a class='menu' href='login.php'>Войти</a></li>";
                    } else {
                        echo "<li class='menu'><a class='menu' href='?exit'>Выйти</a></li>";
                        echo "<li class='menu'><a class='menu' href='cabinet.php'>Мой профиль</a></li>";
                        if ($user['rights'] === 'superuser') {
                            echo "<li class='menu'><a class='menu' href='admin/admin.php'>Админка</a></li>";
                        }
                    }
                ?>
                <li class='menu'><a class='menu' href='search.php'>Поиск</a></li>
                <li class='menu'><a class='menu' href='addpost.php'>Создать новый пост</a></li>
            </ul>
        </div>
    </div>
</nav>

<div class='allsinglepost'>
    <div class='contentsinglepost'>

        <div id='singlepostzagolovok'><p class='singlepostzagolovok'><?=$post['zag']?></p></div>

        <div id='singlepostauthor'>
            
            <?php
                if (!empty($post['countRatings'])) {
                    echo "<p class='singlepostdate'>Рейтинг поста: {$post['rating']} из 5. Оценок: {$post['countRatings']}</p>";
                } else {
                    echo "<p class='singlepostdate'>Оценок 0. Будьте первым!</p>";
                }
                
                if (!isUserChangesPostRating($userId, $postId)) {
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
            <p class='singlepostauthor'><a class='menu' title='Перейти в профиль пользвателя' href='cabinet.php?user=<?=$postAuthorId?>'><?=$postAuthorFio?></a></p>
            <p class='singlepostdate'><?=$post['date_time']?></p>
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
            <object><a class='list' href='viewsinglepost.php?viewPostById=<?=$postId?>&deletePostById=<?= $post['id'] ?>'> Удалить пост с ID=<?= $post['id'] ?></a></object><br>
            <?php
                }
            ?>
            
        </div>
        <div class='addcomments'  id='comment'>

            <?=$error?>

            <p class='center'>Добавьте комментарий:</p>

            <div class='addcomment'>

                <form action='viewsinglepost.php?viewPostById=<?=$postId?>#comment' method='post'>
                   
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
                    $content = nl2br(strip_tags($comments[$i]['content']));
                    $authorComId = $comments[$i]['user_id'];
                    $author = getUserEmailFioRightsById($authorComId);
                    $date = date("d.m.Y",$comments[$i]['date_time']) ." в ". date("H:i", $comments[$i]['date_time']);
            ?>

            <div class='viewcomment' id='comment<?= $comments[$i]['id'] ?>'>
                <p class='commentauthor'><a class='menu' href='cabinet.php?user=<?=$authorComId?>'><?=$author['fio']?></a><div class='commentdate'><?=$date?></div></p>
                <div class='commentcontent'>
                    <p class='commentcontent'><?=$content?></p> 
                    <p class='commentcontent'>
                        <?php
                            if (!empty($isAdmin)) {
                        ?> 
                            <object><a class='menu' href='viewsinglepost.php?viewPostById=<?=$postId?>&deleteCommentById=<?= $comments[$i]['id'] ?>'> Удалить комментарий</a></object>
                        <?php
                            }
                        ?>
                    </p>
                </div>
                <div class='like'>
                    <?php
                        $countLikes = $comments[$i]['rating'];
                        if (!isUserChangesCommentRating($userId, $comments[$i]['id'])) {
                            $name = 'like';
                        } else {
                            $name = 'unlike';
                        }
                    ?>
                    
                    <form action='viewsinglepost.php?viewPostById=<?=$postId?>#comment<?=$comments[$i]['id']?>' method='post'>
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