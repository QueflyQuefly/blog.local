        <?php // needed $post ?>
        <div class="rating-area">
            <form action='<?= $post['post_id'] ?>' method='post'>
                <input type='hidden' name='post_id' value="<?= $post['post_id'] ?>" >
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
