
<?php // needed $user:array, $linkToDelete:string ?>
        <div class='viewpost'>
            <a class='postLink' href='/cabinet/?user=<?= $user['user_id']?>'>
                <div class='posttext'>
                    <p class='posttitle'> Просмотр дополнительной информации по нажатию</p>
                    <p class='posttitle'> ФИО(псевдоним): <?=  $user['fio'] ?></p>
                    <p class='posttitle'> Дата регистрации: <?=  $user['date_time'] ?></p>
                    <?= $additionalInfo ?>
                </div>
            </a>
            <?= $linkToDelete ?>
        </div>
