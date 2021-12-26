<?php

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
?>
<div class='allsinglepost'>
    <div class='content'>
        
        <!-- viewing comments area-->
        <div class='viewcomments'>
            <p class='center'>Комментарии к посту (всего <?= $post['count_comments'] ?>):</p>
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
