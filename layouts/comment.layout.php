            <?php // needed $comment, $linkToDelete ?>
            <div class='viewcomment' id='comment<?=  $comment['comment_id'] ?>'>
                <p class='commentauthor'>
                    <a class='menuLink' href='cabinet.php?user=<?=  $comment['user_id'] ?>'><?=  $comment['author'] ?></a>
                    <div class='commentdate'><?=  $comment['date_time'] ?></div>
                </p>
                <div class='commentcontent'>
                    <p class='commentcontent'><?=  $comment['content'] ?></p> 
                    <p class='commentcontent'><?=  $linkToDelete ?></p> 
                </div>
                <div class='like'>
                    <form action='<?= $comment['post_id'] ?>#comment<?= $comment['comment_id']?>' method='post'>
                        <input type='hidden' name='post_id' value="<?= $comment['post_id']?>" >
                        <label id='heartlike' title="Нравится" for='like<?= $comment['comment_id']?>'>
                            <span class='like'>&#9825; </span>
                            <?= $comment['rating'] ?>
                        </label>
                        <input type="submit" class='nodisplay' id="like<?= $comment['comment_id']?>" name="comment_id_like" value="<?= $comment['comment_id']?>">
                    </form>
                </div>
                <hr>
            </div>
