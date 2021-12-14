<?php
$start = microtime(true);
session_start();
$functions = 'functions' . DIRECTORY_SEPARATOR . 'functions.php';
require_once $functions;
    
if (isset($_GET['exit'])) {
    $_SESSION['user_id'] = false;
    session_destroy();
    header("Location: /");
}

$year = date("Y", time());
$ids = getPostIds(10);
?>



<!DOCTYPE html>
<html>

<head>
    <meta charset='UTF-8'>
    <title>Главная - Просто блог</title>
    <link rel='stylesheet' href='css/indexcss.css'>
    <link rel="shortcut icon" href="/images/logo.jpg" type="image/x-icon">
</head>
<body>
    <nav>
    <div class='top'>
        <div class="logo">
            <a class="logo" title="На главную" href='/'>
            <img id='logo' src='images/logo.jpg' alt='Лого' width='50' height='50'>
            <div id='namelogo'>Просто Блог</div>
            </а>
        </div>
        <div class="menu">
            <ul class='menu'>
                <?php
                    if (empty($_SESSION['user_id'])) {
                        echo "<li class='menu'><a class='menu' href='login.php'>Войти</a></li>";
                        session_destroy();
                    } else {
                        $user = getUserEmailFioRightsById($_SESSION['user_id']);
                        echo "<li class='menu'><a class='menu' href='?exit'>Выйти</a></li>";
                        echo "<li class='menu'><a class='menu' href='cabinet.php'>Мой профиль</a></li>";
                        if ($user['rights'] === 'superuser') {
                            echo "<li class='menu'><a class='menu' href='admin/admin.php'>Админка</a></li>";
                        }
                    }
                ?>
                <li class='menu'><a class='menu' href='search.php'>Поиск</a></li>
                <li class='menu'><a class='menu' href='addpost.php'>Создать новый пост</a></li>
            </ul>
        </div>
    </div>
</nav>
<div class='allwithoutmenu'>
    <div class='content'>

        <div id='desc'><p>Наилучший источник информации по теме "Путешествия"</p></div>

        <?php 
            if (empty($ids)) {
                die("<p>Нет постов для отображения</p>");    
            } else {
                foreach ($ids as $id) {
                    $posts[] = getPostForIndexById($id);
                }
                $post = $posts[0];
                $authorOfPost = getUserEmailFioRightsById($post['user_id']);
                $fioOfAuthor = $authorOfPost['fio']; 
        ?>

        <a class='onepost' href="viewsinglepost.php?viewPostById=<?=$post['id']?>">
        <div class='viewonepost'>
            
            <div class='oneposttext'>
                <p class='onepostzagolovok'><?=$post['zag']?></p>
                <p class='onepostcontent'><?=$post['content']?></p>
                <p class='postdate'><?=$post['date_time']. " &copy; " . $fioOfAuthor?></p>

                <p class='postrating'>
                    <?php
                        if (!$post['rating']) {
                            echo "Нет оценок. Будьте первым!";
                        } else {
                            echo "Рейтинг: " . $post['rating'] . ", оценок: " . $post['countRatings'];
                        }     
                    ?> 
                </p>
            </div>
            <div class='onepostimage'>
                <img src='images/PostImgId<?=$post['id']?>.jpg' alt='Картинка' class='onepostimage'>
            </div>
        </div>
        </a>

        <?php
            array_shift($posts);

            foreach ($posts as $post) {
                $authorOfPost = getUserEmailFioRightsById($post['user_id']);
                $fioOfAuthor = $authorOfPost['fio'];  
        ?>

        <div class='viewsmallposts'>

            <a class='post' href='viewsinglepost.php?viewPostById=<?=$post['id']?>'>
            <div class='smallpost'>

                 <div class='smallposttext'>
                    <p class='smallpostzagolovok'><?=$post['zag_small']?></p>
                    <p class='smallpostcontent'><?=$post['content_small']?></p>
                    <p class='postdate'><?=$post['date_time']. " &copy; " . $fioOfAuthor?></p>
                    <p class='postrating'>
                        <?php
                            if (!$post['rating']) {
                                echo "Нет оценок. Будьте первым!";
                            } else {
                                echo "Рейтинг: " . $post['rating'] . ", оценок: " . $post['countRatings'];
                            }     
                        ?>  
                    </p>
                </div>

                <div class='smallpostimage'>
                    <img src='images/PostImgId<?=$post['id']?>.jpg' alt='Картинка' class='smallpostimage'>
                </div>
               
            </div>
            </a>

        </div>

        <?php
                }
            }
            echo "<p class='center'><a class='submit' href='posts.php'>Посмотреть ещё</a></p>";
            $moreTalkedPostIds = getMoreTalkedPostIds();
            if (!empty($moreTalkedPostIds)) {
                echo "<div class='searchdescription'><div class='singleposttext'>Самые обсуждаемые посты за неделю	&darr;&darr;&darr;</div></div>";
                
                foreach ($moreTalkedPostIds as $postId) {
                    $post = getPostForIndexById($postId);
                    $authorOfPost = getUserEmailFioRightsById($post['user_id']);
                    $fioOfAuthor = $authorOfPost['fio']; 
        ?>

        <div class='viewsmallposts'>

            <a class='post' href='viewsinglepost.php?viewPostById=<?=$post['id']?>'>
            <div class='smallpost'>

                <div class='smallposttext'>
                    <p class='smallpostzagolovok'><?=$post['zag_small']?></p>
                    <p class='smallpostcontent'><?=$post['content_small']?></p>
                    <p class='postdate'><?=$post['date_time']. " &copy; " . $fioOfAuthor?></p>
                    <p class='postrating'>
                        <?php
                            if (!$post['rating']) {
                                echo "Нет оценок. Будьте первым!";
                            } else {
                                echo "Рейтинг поста: " . $post['rating'];
                            }     
                        ?>  
                    </p>
                </div>

                <div class='smallpostimage'>
                    <img src='images/PostImgId<?=$post['id']?>.jpg' alt='Картинка' class='smallpostimage'>
                </div>
                
            </div>
            </a>

        </div>

        <?php
                }
            }
        ?>

    </div>

    <footer class='bottom'>
        <p>Website by Вячеслав Бельский &copy; <?=$year?><br> Время загрузки страницы: <?=round(microtime(true) - $start, 4)?> с.</p>
    </footer>
</div>
</body>
</html>