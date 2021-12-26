<?php

class CommentController {
    private $commentService, $viewComments;
    public function __construct(CommentService $commentService, ViewComments $viewComments){
        $this->commentService = $commentService;
        $this->viewComments = $viewComments;
    }
    public function showCommentsByPostId($numberOfPosts, $isSuperuser) {
        $posts = $this->commentService->getCommentsByPostId($numberOfPosts);
        return $this->viewComments->renderComments($posts, $isSuperuser);
    }
    public function showCommentsByUserId($numberOfPosts, $isSuperuser) {
        $moreTalkedPosts = $this->commentService->getCommentsByUserId($numberOfPosts);
        return $this->viewComments->renderComments($moreTalkedPosts, $isSuperuser);
    }
    public function deleteCommentById($id) {
        $deletePostId = clearInt($id);
        if ($deletePostId !== '') {
            $this->commentService->deleteCommentById($deletePostId);
            header("Location: /");
        }
    }
}