<?php

class CommentController {
    private $_commentService, $_viewComments;
    public function __construct(CommentService $commentService, ViewComments $viewComments){
        $this->_commentService = $commentService;
        $this->_viewComments = $viewComments;
    }
    public function showCommentsByPostId($postId, $isSuperuser) {
        $comments = $this->_commentService->getCommentsByPostId($postId);
        return $this->_viewComments->renderComments($comments, $isSuperuser);
    }
    public function showCommentsByUserId($userId, $isSuperuser) {
        $comments = $this->_commentService->getCommentsByUserId($userId);
        return $this->_viewComments->renderComments($comments, $isSuperuser);
    }
    public function showLikedCommentsByUserId($userId, $isSuperuser) {
        $comments = $this->_commentService->getLikedCommentsByUserId($userId);
        return $this->_viewComments->renderComments($comments, $isSuperuser);
    }
    public function deleteCommentById($id) {
        $deleteCommentId = clearInt($id);
        if ($deleteCommentId !== '') {
            return $this->_commentService->deleteCommentById($deleteCommentId);
        }
        return false;
    }
    public function addComment($postId, $userId, $commentContent) {
        if ($commentContent !== '') {
            return $this->_commentService->addComment($postId, $userId, $commentContent);
        }
        return false;
    }
}