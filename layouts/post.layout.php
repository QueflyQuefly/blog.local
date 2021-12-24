<div class='<?=  $class  ?>'>
    <a class='postLink' href='viewsinglepost.php?viewPostById=<?= $post['post_id'] ?>'>
        <div class='posttext'>
        <p class='posttitle'><?= $post['title'] ?></p>
        <p class='postcontent'><?= $post['content'] ?></p>
        <p class='postdate'><?= $post['date_time']. " &copy; " . $post['author'] ?></p>
        <p class='postrating'>
        <?php
            if ($post['count_ratings'] == 0) {
                echo "Нет оценок. Будьте первым! Kомментариев: " . $post['count_comments'];
            } else {
                echo "Рейтинг: " . $post['rating'] . ", оценок: " . $post['count_ratings']
                        . ", комментариев: " . $post['count_comments'];
            }
        ?>  
        </p>
        <?php
            if (!empty($frontController->isSuperuser)) {
        ?>
            <object>
                <a class='link' href='index.php?deletePostById=<?=  $post['post_id']  ?>'>
                    Удалить пост с ID = <?=  $post['post_id']  ?>
                </a>
            </object>
        <?php
            } 
        ?>
    </div>
    <div class='postimage'>
        <img src='images/PostImgId<?= $post['post_id'] ?>.jpg' alt='Картинка'>
    </div>
    </a>
</div>