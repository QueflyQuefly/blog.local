
    <div id='singleposttitle'>
        <p class='singleposttitle'><?= $post['title'] ?></p>
    </div>
    <div id='singlepostauthor'>
        <p class='singlepostdate'><?= $post['rating'] ?></p>
        <?= $ratingArea() ?>
        <p class='singlepostauthor'><a class='menuLink' title='Перейти в профиль пользвателя' href='cabinet.php?user=<?= $post['user_id'] ?>'><?= $post['author'] ?></a></p>
        <p class='singlepostdate'><?= $post['date_time'] ?></p>
    </div>
    <div class='singlepostimage'>
        <img src='images/PostImgId<?= $post['post_id'] ?>.jpg' alt='Картинка' class='singlepostimg'>
    </div>
    <div class='singleposttext'>
        <p class='singlepostcontent'><?= $post['content'] ?></p>
    </div> 
    <?= $linkToDelete ?>