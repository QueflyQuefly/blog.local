<?php

class ViewPosts {
    private $_pathToLayouts, $_postsView;
    public function __construct() {
        $this->_pathToLayouts = dirname(__DIR__) . DIRECTORY_SEPARATOR . 'layouts' . DIRECTORY_SEPARATOR ;
    }
    public function renderPosts($posts, $isSuperuser = false, $showButton = false) {
        if (empty($posts)) {
            echo "\n<div class='contentsinglepost'><p class='center'style='color: rgb(150, 20, 20);'>Нет постов для отображения</p></div>\n"; 
        } else {
            foreach ($posts as $key => $post) {
                $class = 'viewpost';
                if ($key == 0) {
                    $class = 'generalpost';
                }
                $post['date_time'] = date("d.m.Y в H:i", $post['date_time']);
                if ($post['count_ratings'] == 0) {
                    $post['rating'] = "Нет оценок. Будьте первым! Kомментариев: " . $post['count_comments'];
                } else {
                    $post['rating'] = "Рейтинг: " . $post['rating'] . ", оценок: " . $post['count_ratings']
                            . ", комментариев: " . $post['count_comments'];
                }
                $linkToDelete = '';
                if (!empty($isSuperuser)) {
                    $linkToDelete = "
                    <input type='submit' form='deletePostById{$post['post_id']}' value='Удалить пост с ID = {$post['post_id']}' class='link'>
                    <form id='deletePostById{$post['post_id']}' action='' method='post'>
                        <input type='hidden' value='{$post['post_id']}' name='deletePostById'>
                    </form>
                    ";
                }
                include $this->_pathToLayouts . 'post.layout.php';
            }
            if ($showButton) {
                echo "\n<p class='center'><a class='formsubmit' href='/posts'>Посмотреть посты за всё время</a></p>\n";
            }
        }
    }
    public function renderMoreTalkedPosts($posts, $isSuperuser = false) {
        if (!empty($posts)) {
            echo "\n<div class='searchdescription'><div class='singleposttext'>Самые обсуждаемые посты за неделю	
                    &darr;&darr;&darr;</div></div>\n";
            foreach ($posts as $post) {
                $class = 'viewpost';
                $post['date_time'] = date("d.m.Y в H:i", $post['date_time']);
                if ($post['count_ratings'] == 0) {
                    $post['rating'] = "Нет оценок. Будьте первым! Kомментариев: " . $post['count_comments'];
                } else {
                    $post['rating'] = "Рейтинг: " . $post['rating'] . ", оценок: " . $post['count_ratings']
                            . ", комментариев: " . $post['count_comments'];
                }
                $linkToDelete = '';
                if (!empty($isSuperuser)) {
                    $linkToDelete = "
                    <input type='submit' form='deletePostById{$post['post_id']}' value='Удалить пост с ID = {$post['post_id']}' class='link'>
                    <form id='deletePostById{$post['post_id']}' action='' method='post'>
                        <input type='hidden' value='{$post['post_id']}' name='deletePostById'>
                    </form>
                    ";
                }
                include $this->_pathToLayouts . 'post.layout.php';
            }
        }
    }
    public function renderPost($post, $tags, $isSuperuser = false, $isUserChangedRating = false) {
        $this->post = $post;
        $this->isUserChangedRating = $isUserChangedRating;
        $post['date_time'] = date("d.m.Y в H:i", $post['date_time']);
        if ($post['count_ratings'] == 0) {
            $post['rating'] = "Нет оценок. Будьте первым! Kомментариев: " . $post['count_comments'];
        } else {
            $post['rating'] = "Рейтинг: " . $post['rating'] . ", оценок: " . $post['count_ratings']
                    . ", комментариев: " . $post['count_comments'];
        }
        $post['content'] = str_replace("<br />
<br />","</p>\n<p>", nl2br($post['content']));
        $regex = '/#(\w+)/um';
        $post['content'] = preg_replace($regex, "<a class='link' href='/search/search=%23$1'>$0</a>", $post['content']);
        $post['title'] = preg_replace($regex, "<a class='link' href='/search/search=%23$1'>$0</a>", $post['title']);
        
        $ratingArea = function () {
            if (empty($this->isUserChangedRating)) {
                $post = $this->post;
                include $this->_pathToLayouts . 'ratingpost.layout.php';
            } else {
                echo "<p class='singlepostdate'>Оценка принята</p>";
            }
        };
        $linkToDelete = '';
        if (!empty($isSuperuser)) {
            $linkToDelete = "
            <input type='submit' form='deletePostById{$post['post_id']}' value='Удалить этот пост' class='link' id='right'>
            <form id='deletePostById{$post['post_id']}' action='' method='post'>
                <input type='hidden' value='{$post['post_id']}' name='deletePostById'>
            </form>
            ";
        }
        include $this->_pathToLayouts . 'viewpost.layout.php';
        $this->renderTags($tags);
        include $this->_pathToLayouts . 'addcomments.layout.php';
        if ($post['count_comments'] == 0) {
            echo "<p class='center'>Оставьте комментарий первым!</p>";
        } else {
            echo "<p class='center'>Комментарии к посту (всего {$post['count_comments']}):</p>";
        }
    }
    public function renderTags($tags) {
        echo "<p class='singleposttext'> Тэги: ";
        if ($tags) {
            foreach ($tags as $tag) {
                $tagLink = substr($tag['tag'], 1);
                echo "<a class='link' href='/search/?search=%23$tagLink'>{$tag['tag']}</a> \n";
            }
        } else {
            echo "Нет тэгов";
        }
        echo "</p>";
    }
}