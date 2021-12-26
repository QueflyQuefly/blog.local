
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
        <p class='singlepostauthor'><a class='menuLink' title='Перейти в профиль пользвателя' href='cabinet.php?user=<?= $post['user_id'] ?>'><?= $post['author']?></a></p>
        <p class='singlepostdate'><?= $post['date_time'] ?></p>
    </div>
    <div class='singlepostimage'>
        <img src='images/PostImgId<?= $post['post_id']?>.jpg' alt='Картинка' class='singlepostimg'>
    </div>
    <div class='singleposttext'>
        <p class='singlepostcontent'><?= $post['content'] ?></p>
    </div> 
