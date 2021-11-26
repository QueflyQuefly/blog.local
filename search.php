<?php
session_start();
$functions = join(DIRECTORY_SEPARATOR, array('functions', 'functions.php'));
require_once $functions;
$link = "<a class='menu' href='login.php'>Войти</a>";
$label = "<a class='menu' href='login.php'>Вы не авторизованы</a>";
$login = '';
$fio = '';
$_SESSION['referrer'] = $_SERVER['REQUEST_URI'];

if (isset($_GET['exit'])) {
    $_SESSION['log_in'] = false;
    session_destroy();
    header("Location: /");
} 

if (isset($_SESSION['log_in']) && $_SESSION['log_in']) {
    $login = $_SESSION['login'];
    $fio = $_SESSION['fio'];
    $link = "<a class='menu' href='?exit'>Выйти</a>";
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
        //var_dump($posts);
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
            <p class='singlepostzagolovok'>Поиск поста по заголовку или по хештэгу</p>
           
        </div>
        <div class='search'>
            <form class='search' action='<?=$_SERVER['PHP_SELF']?>' method='get'>
                <input class='text' type='text' id='search' required autofocus minlength="1" maxlength="30" placeholder='Найти...' name='search'>
                <button type="submit">&#x2315</button>
            </form>
        </div> 

        <div class='singleposttext'>
            <?php 
                if (empty($ids)) {
                    echo "<p class='center'>Поиск поста осуществляется по заголовку или по хештэгу</p>"; 
                } else {
                    echo "<p class='center'>Результаты поиска: </p>"; 
                    echo "<ul class='list'>";
                    $num = count($ids) - 1;
                    for ($i= $num; $i>=0; $i--) {
                        $post = getPostForViewById($ids[$i]);
                        $comments = getCommentsByPostId($ids[$i]);
                        $tags = getTagsToPostById($ids[$i]);
                        if (empty($posts) or $posts == false) {
                            $countComments = 0;
                        } else {
                        $countComments = count($comments);
                        }

            ?>

            <li class='list'>
                <a class='onepost' href='viewsinglepost.php?viewPostById=<?= $post['id'] ?>'>
                    <p class='list'>Название: <?= $post['name'] ?></p>
                    <p class='list'>Тэги: 
                        <?php
                            if ($tags) {
                                foreach ($tags as $tag) {
                                    $tagLink = substr($tag['tag'], 1);
                                    echo "<object><a class='menu' href='search.php?search=%23$tagLink'> {$tag['tag']}</a></object>  ";
                                }
                            } else {
                                echo "Нет тэгов";
                            }
                        ?>
                     </p>
                    <p class='list'> Комментариев к посту: <?= $countComments ?> </p>
                    <hr>
                </a>
            </li>
        
            <?php 
                    } 
                echo "</ul>";
                }
                if (!empty($error)) {
                    echo $error;
                }
            ?>
            
        </div>
    </div>

    <footer class='bottom'>
        <p>Website by Вячеслав Бельский &copy; <?=$year?></p>
    </footer>
</div>
</body>
</html>