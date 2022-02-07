<?php

class ViewComments {
    private $_pathToLayouts, $_commentView;
    public function __construct() {
        $this->_pathToLayouts = dirname(__DIR__) . DIRECTORY_SEPARATOR . 'layouts' . DIRECTORY_SEPARATOR;
    }
    public function renderComments($comments, $isSuperuser = false) {
        if (empty($comments)) {
            $this->_commentView = "\n<div class='contentsinglepost'><p class='center' style='color: rgb(150, 20, 20);'>Нет комментариев для отображения</p></div>\n"; 
        } else {
            foreach ($comments as $comment) {
                $comment['content'] = nl2br(clearStr($comment['content']));
                $comment['date_time'] = date("d.m.Y в H:i", $comment['date_time']);
                $linkToDelete = '';
                if (!empty($isSuperuser)) {
                    $linkToDelete = "
                    <input type='submit' form='deleteComment{$comment['comment_id']}' value='Удалить комментарий' class='link'>
                    <form id='deleteComment{$comment['comment_id']}' class='hide' action='{$_SERVER['REQUEST_URI']}#comment' method='post'>
                        <input type='hidden' value='{$comment['comment_id']}' name='deleteCommentById'>
                    </form>
                    ";
                }
                include $this->_pathToLayouts . 'comment.layout.php';
            }
        }
        echo $this->_commentView;
    }
}