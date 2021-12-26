
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
