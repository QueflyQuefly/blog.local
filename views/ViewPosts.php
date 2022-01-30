<?php

class ViewPosts {
    private $pathToLayouts, $postsView;
    public function __construct() {
        $this->pathToLayouts = dirname(__DIR__) . DIRECTORY_SEPARATOR . 'layouts' . DIRECTORY_SEPARATOR ;
    }
    public function renderPosts($posts, $isSuperuser = false, $showButton = false) {
        if (empty($posts)) {
            $this->postsView = "\n<div class='contentsinglepost'><p class='center'>Нет постов для отображения</p></div>\n"; 
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
                    <object>
                    <a class='link' href='?deletePostById={$post['post_id']}'>
                        Удалить пост с ID = {$post['post_id']}
                    </a>
                    </object>\n";
                }
                include $this->pathToLayouts . 'post.layout.php';
            }
            if ($showButton) {
                echo "\n<p class='center'><a class='submit' href='posts.php'>Посмотреть посты за всё время</a></p>\n";
            }
        }
        echo $this->postsView;
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
                    <object>
                    <a class='link' href='?deletePostById={$post['post_id']}'>
                        Удалить пост с ID = {$post['post_id']}
                    </a>
                    </object>\n";
                }
                include $this->pathToLayouts . 'post.layout.php';
            }
        }
    }
    public function renderPost($post, $isSuperuser = false, $isUserChangedRating = false) {
        $this->isUserChangedRating = $isUserChangedRating;
        $this->post = $post;
        if ($post['count_ratings'] == 0) {
            $post['rating'] = "Нет оценок. Будьте первым! Kомментариев: " . $post['count_comments'];
        } else {
            $post['rating'] = "Рейтинг: " . $post['rating'] . ", оценок: " . $post['count_ratings']
                    . ", комментариев: " . $post['count_comments'];
        }
        $ratingArea = function () {
            if (empty($this->isUserChangedRating)) {
                $post = $this->post;
                include $this->pathToLayouts . 'ratingpost.layout.php';
            } else {
                echo "<p class='singlepostdate'>Оценка принята</p>";
            }
        };
        $linkToDelete = '';
        if (!empty($isSuperuser)) {
            $linkToDelete =
            "   <div class='singleposttext'>
            <object>
                <a class='list' href='{$post['post_id']}?deletePostById={$post['post_id']}'>
                    Удалить пост с ID = {$post['post_id']}
                </a>
            </object>
        </div>";
        }
        include $this->pathToLayouts . 'viewpost.layout.php';
        
        include $this->pathToLayouts . 'addcomments.layout.php';
        if ($post['count_comments'] == 0) {
            echo "<p class='center'>Пока никто не оставил комментарий. Будьте первым!</p>";
        } else {
            echo "<p class='center'>Комментарии к посту (всего {$post['count_comments']}):</p>";
        }
    }
    public function renderTags($tags) {
        $viewTags = "<p class='singlepostcontent'>";
        if ($tags) {
            $viewTags .= "Тэги: ";
            foreach ($tags as $tag) {
                $tagLink = substr($tag['tag'], 1);
                $viewTags .= "<a class='link' href='search/search=%23$tagLink'>{$tag['tag']}</a> ";
            }
        } else {
            $viewTags .= "Нет тэгов";
        }
        $viewTags .= "</p>";
        return $viewTags;
    }
}