<?php

class ViewComments {
    private $pathToLayouts, $commentView;
    public function __construct() {
        $this->pathToLayouts = dirname(__DIR__) . DIRECTORY_SEPARATOR . 'layouts' . DIRECTORY_SEPARATOR;
    }
    public function renderComments($comments, $isSuperuser = false) {
        if (empty($comments)) {
            $this->commentView = "\n<div class='contentsinglepost'><p class='center'>Нет комментариев для отображения</p></div>\n"; 
        } else {
            foreach ($comments as $comment) {
                $comment['content'] = nl2br(clearStr($comment['content']));
                $comment['date_time'] = date("d.m.Y в H:i", $comment['date_time']);
                $linkToDelete = '';
                if (!empty($isSuperuser)) {
                    $linkToDelete = "
                    <object>
                        <a class='link' href='{$comment['post_id']}?deleteCommentById={$comment['comment_id']}'>
                            Удалить комментарий
                        </a>
                    </object>";
                }
                include $this->pathToLayouts . 'comment.layout.php';
            }
        }
        echo $this->commentView;
    }
}