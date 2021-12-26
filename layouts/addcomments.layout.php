
        <div class='addcomments' id='comment'>
            <p class='center'>Добавьте комментарий:</p>
            <div class='addcomment'>
                <form action='viewsinglepost.php?viewPostById=<?= $post['post_id']?>#comment' method='post'>
                    <textarea name='addCommentContent' required  minlength="1" maxlength='500' wrap='hard' placeholder="Опишите ваши эмоции :-) (до 500 символов)" id='textcomment'></textarea><br>
                    <input type='submit' value='Добавить комментарий' class='submit'>
                </form>
            </div>
        </div>