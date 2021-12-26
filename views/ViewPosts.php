<?php

class ViewPosts {
    private $pathToLayouts, $postsView, $postsMoreTalkedView;
    public function __construct() {
        $this->pathToLayouts = dirname(__DIR__) . DIRECTORY_SEPARATOR . 'layouts' . DIRECTORY_SEPARATOR ;
    }
    public function renderPosts($posts, $isSuperuser = false) {
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
                $this->postsView .= include $this->pathToLayouts . 'post.layout.php';
            }
        }
        return $this->postsView;
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
                $this->postsMoreTalkedView .= include $this->pathToLayouts . 'post.layout.php';
            }
        }
        return $this->postsMoreTalkedView;
    }
    public function renderPost($post, $isSuperuser = false) {
        $viewPost = include $this->pathToLayouts . 'viewpost.layout.php';
        if (!empty($isSuperuser)) {
            $viewPost .=
                "<div class='singleposttext'>
                <object>
                    <a class='list' href='viewsinglepost.php?viewPostById={$post['post_id']}&deletePostById={$post['post_id']}'>
                        Удалить пост с ID = {$post['post_id']}
                    </a>
                </object><br>
                </div>";
        }
        $viewPost .= include $this->pathToLayouts . 'addcomments.layout.php';
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