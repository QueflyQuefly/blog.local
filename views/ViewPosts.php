<?php

class ViewPosts {
    private $pathToLayoutPost, $postsView, $postsMoreTalkedView;
    public function __construct() {
        $this->pathToLayoutPost = dirname(__DIR__) . DIRECTORY_SEPARATOR . 'layouts' . DIRECTORY_SEPARATOR . 'post.layout.php';
    }
    public function renderPosts($posts, $isSuperuser = false) {
        if (is_null($this->postsView)) {
            if (empty($posts)) {
                $this->postsView .= "\n<p class='center'>Нет постов для отображения</p>\n"; 
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
                    if (!empty($isSuperuser)) {
                        $linkToDelete = "
                        <object>
                        <a class='link' href='index.php?deletePostById={$post['post_id']}'>
                            Удалить пост с ID = {$post['post_id']}
                        </a>
                        </object>\n";
                    } else {
                        $linkToDelete = '';
                    }
                    $this->postsView .= include $this->pathToLayoutPost;
                }
                $this->postsView .= "\n<p class='center'><a class='submit' href='posts.php'>Посмотреть ещё</a></p>\n";
            }
        }
        return $this->postsView;
    }
    public function renderMoreTalkedPosts($posts, $isSuperuser = false) {
        if (is_null($this->postsMoreTalkedView)) {
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
                    if (!empty($isSuperuser)) {
                        $linkToDelete = "
                        <object>
                        <a class='link' href='index.php?deletePostById={$post['post_id']}'>
                            Удалить пост с ID = {$post['post_id']}
                        </a>
                        </object>\n";
                    } else {
                        $linkToDelete = '';
                    }
                    $this->postsMoreTalkedView .= include $this->pathToLayoutPost;
                }
            }
        }
        return $this->postsMoreTalkedView;
    }
}