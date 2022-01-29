        <?php // needed $post ?>
        <div class='addcomments' id='comment'>
            <p class='center'>Добавьте комментарий:</p>
            <div class='addcomment'>
                <form action='<?= $post['post_id']?>#comment' method='post'>
                    <input type='hidden' name='post_id' value="<?= $post['post_id']?>" >
                    <textarea name='addCommentContent' required  minlength="1" maxlength='500' wrap='hard' placeholder="Опишите ваши эмоции :-) (до 500 символов)" id='textcomment'></textarea><br>
                    <input type='submit' value='Добавить комментарий' class='submit'>
                </form>
            </div>
        </div>
