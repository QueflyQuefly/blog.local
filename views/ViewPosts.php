<?php

class ViewPosts {
    private $pathToLayoutPost;
    public function __construct() {
        $this->pathToLayoutPost = dirname(__DIR__) . DIRECTORY_SEPARATOR . 'layouts' . DIRECTORY_SEPARATOR . 'post.layout.php';
    }
    public function renderPosts($posts) {
        if (empty($posts)) {
            echo "\n<p class='center'>Нет постов для отображения</p>\n"; 
        } else {
            foreach ($posts as $key => $post) {
                $class = 'viewpost';
                if ($key == 0) {
                    $class = 'generalpost';
                }
                $post['date_time'] = date("d.m.Y в H:i", $post['date_time']);
                include $this->pathToLayoutPost;
            }
            echo "\n<p class='center'><a class='submit' href='posts.php'>Посмотреть ещё</a></p>\n";
        }
    }
    public function renderMoreTalkedPosts($posts) {
        if (!empty($posts)) {
            echo "\n<div class='searchdescription'><div class='singleposttext'>Самые обсуждаемые посты за неделю	
                    &darr;&darr;&darr;</div></div>\n";
            foreach ($posts as $post) {
                $class = 'viewpost';
                $post['date_time'] = date("d.m.Y в H:i", $post['date_time']);
                include $this->pathToLayoutPost;
            }
        }
    }
}