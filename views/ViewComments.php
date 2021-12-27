<?php

class ViewComments {
    private $pathToLayouts, $commentView;
    public function __construct() {
        $this->pathToLayouts = dirname(__DIR__) . DIRECTORY_SEPARATOR . 'layouts' . DIRECTORY_SEPARATOR;
    }
    public function renderComments($comments, $isSuperuser = false) {
        if (empty($comments)) {
            $this->commentView .= "\n                
            <div class='viewnotcomment'>   
                <p class='commentcontent'>Пока ещё никто не оставил комментарий. Будьте первым!</p>
                <hr>
            </div>\n"; 
        } else {
            foreach ($comments as $comment) {
                $comment['content'] = nl2br(clearStr($comment['content']));
                $comment['date_time'] = date("d.m.Y в H:i", $comment['date_time']);
                if (!empty($isSuperuser)) {
                    $linkToDelete = "
                    <object>
                        <a class='link' href='{$comment['post_id']}?deleteCommentById={$comment['comment_id']}'>
                            Удалить комментарий
                        </a>
                    </object>";
                } else {
                    $linkToDelete = '';
                }
                $this->commentView .= include $this->pathToLayouts . 'comment.layout.php';
            }
            $this->commentView .= "\n<p class='center'><a class='submit' href='posts.php'>Посмотреть ещё</a></p>\n";
        }
        return $this->commentView;
    }
}