<?php
session_start();
$functions = join(DIRECTORY_SEPARATOR, array('functions', 'functions.php'));
require_once $functions;
$link = "<a class='menu' href='login.php'>Войти</a>";
$label = "<a class='menu' href='login.php'>Вы не авторизованы</a>";
$login = '';
$fio = '';
$search = '';
$_SESSION['referrer'] = 'search.php';

if (isset($_GET['exit'])) {
    $_SESSION['log_in'] = false;
    header("Location: search.php");
} 

if (isset($_SESSION['log_in']) && $_SESSION['log_in']) {
    $login = $_SESSION['login'];

    $link = "<a class='menu' href='?exit'>Выйти</a>";
    if ($_SESSION['rights'] == 'superuser') {
        $label = "<a class='menu' href='admin/admin.php'>Вы вошли как администратор</a>";
    } else {
        $label = "<a class='menu' href='cabinet.php'>Перейти в личный кабинет</a>";
    }
} else {
    session_destroy();
}

if (!empty($_GET['search'])) {
    $search = $_GET['search'];
    $search = clearStr($search);
    if ($search) {
        if (strpos($search, ' ') !== false) {
            $searchwords = explode(' ', $search);
    
            foreach ($searchwords as $searchword) {
                if (strpos($searchword, '#') !== false) {
                    $posts[] = searchPostsByTag($searchword);
                } else {
                    $posts[] = searchPostsByName($searchword);
                }
            }
        } else {
            if (strpos($search, '#') !== false) {
                $posts[] = searchPostsByTag($search);
            } else {
                $posts[] = searchPostsByName($search);
            }
        }
        if (!empty($posts[0])) {
            foreach ($posts as $post) {
                foreach ($post as $post_id) {
                    $idsnotsort[] = $post_id;
                }
            }
            $ids = array_unique($idsnotsort);
        } else {
            $error = "<p class='error'>Ничего не найдено</p>";
        }
    } else {
        $error = "<p class='error'>Введите хоть что-нибудь</p>";
    }
} 
$year = date("Y", time());
?>


<!DOCTYPE html>
<html>

<head>
    <meta charset='UTF-8'>
    <title>Поиск - Просто блог</title>
    <link rel='stylesheet' href='css/indexcss.css'>
</head>
<body>
<nav>
    <div class='top'>
        <div class="logo">
            <a class="logo" title="На главную" href='/'><img id='logo' src='images/logo.jpg' alt='Лого' width='50' height='50'>
            <div id='namelogo'>Просто Блог</div></а>
        </div>
        <div class="menu">
            <ul class='menu'>
                <li class='menu'><?=$link?></li>
                <li class='menu'><a class='menu' href='search.php'>Поиск поста</a></li>
                <li class='menu'><a class='menu' href='addpost.php'>Создать новый пост</a></li>
                <li class='menu'><?=$label?></li>
            </ul>
        </div>
    </div>
</nav>

<div class='allsinglepost'>
    
    <div class='contentsinglepost'>
        <div id='singlepostzagolovok'>
            <p class='singlepostzagolovok'>Поиск поста</p> 
        </div>

        <div class='search'>
            <form class='search' action='<?=$_SERVER['PHP_SELF']?>' method='get'>
                <input class='text' type='text' id='search' required autofocus minlength="1" maxlength="30" placeholder='Найти...' name='search' value='<?=$search?>'>
                <button type="submit">&#x2315</button>
            </form>
        </div> 
    </div>

    <div class='viewsmallposts'>
        <div class='singleposttext'>
            <?php 
                if (empty($ids)) {
                    echo "<p class='center'>Поиск поста осуществляется по заголовку, автору или по хештэгу</p>\n</div>"; 
                } else {
                    echo "<p class='center'>Результаты поиска: </p>\n</div>"; 
                    foreach ($ids as $id) {
                        $post = getPostForViewById($id);
                        $comments = getCommentsByPostId($id);
                        $tags = getTagsToPostById($id);

                        if (empty($posts)) {
                            $countComments = 0;
                        } else {
                            $countComments = count($comments);
                        }
            ?>

        <a class='post' href='viewsinglepost.php?viewPostById=<?= $post['id'] ?>'>
            <div class='smallpost'>
                <div class='smallposttext'>
                    <p class='smallpostzagolovok'><?= $post['name'] ?></p>
                    <p class='smallpostauthor'> &copy; <?= $post['author'] ?></p>
                    <p class='postdate'>Тэги: 
                        <?php
                            if ($tags) {
                                foreach ($tags as $tag) {
                                    $tagLink = substr($tag['tag'], 1);
                                    echo "<object><a class='menu' href='search.php?search=%23$tagLink'> {$tag['tag']}</a> </object>  ";
                                }
                            } else {
                                echo "Нет тэгов";
                            }
                        ?>
                        </p>
                    <p class='postdate'> Комментариев к посту: <?= $countComments ?> </p>
                </div>
                <div class='smallpostimage'>
                    <img src='images/PostImgId<?=$post['id']?>.jpg' alt='Картинка' class='smallpostimage'>
                </div>
           </div>
        </a>
        
            <?php 
                    } 
                }
                if (!empty($error)) {
                    echo $error;
                }
            ?>
            
        
    </div>

    <footer class='bottom'>
        <p>Website by Вячеслав Бельский &copy; <?=$year?></p>
    </footer>
</div>
</body>
</html>