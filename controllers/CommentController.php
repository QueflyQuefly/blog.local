<?php

class CommentController {
    private $commentService, $viewComments;
    public function __construct(CommentService $commentService, ViewComments $viewComments){
        $this->commentService = $commentService;
        $this->viewComments = $viewComments;
    }
    public function showCommentsByPostId($postId, $isSuperuser) {
        $comments = $this->commentService->getCommentsByPostId($postId);
        return $this->viewComments->renderComments($comments, $isSuperuser);
    }
    public function showCommentsByUserId($userId, $isSuperuser) {
        $comments = $this->commentService->getCommentsByUserId($userId);
        return $this->viewComments->renderComments($comments, $isSuperuser);
    }
    public function deleteCommentById($id) {
        $deleteCommentId = clearInt($id);
        if ($deleteCommentId !== '') {
            header("Refresh:0");
            return $this->commentService->deleteCommentById($deleteCommentId);
        }
        return false;
    }
    public function addComment($postId, $userId, $commentContent) {
        if ($commentContent !== '') {
            header("Refresh:0");
            return $this->commentService->addComment($postId, $userId, $commentContent);
        }
        return false;
    }
}