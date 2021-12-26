
<div class='<?=  $class  ?>'>
    <a class='postLink' href='viewsinglepost.php?viewPostById=<?= $post['post_id'] ?>'>
        <div class='posttext'>
            <p class='posttitle'><?= $post['title'] ?></p>
            <p class='postcontent'><?= $post['content'] ?></p>
            <p class='postdate'><?= $post['date_time']. " &copy; " . $post['author'] ?></p>
            <p class='postrating'><?= $post['rating'] ?></p>
            <?= $linkToDelete ?>
        </div>
        <div class='postimage'>
            <img src='http://blog.local/images/PostImgId<?= $post['post_id'] ?>.jpg' alt='Картинка'>
        </div>
    </a>
</div>
