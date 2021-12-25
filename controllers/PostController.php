<?php

class PostController {
    private $postService, $viewPosts;
    public function __construct(PostService $postService, ViewPosts $viewPosts){
        $this->postService = $postService;
        $this->viewPosts = $viewPosts;
    }
    public function showLastPosts($numberOfPosts, $isSuperuser) {
        $posts = $this->postService->getLastPosts($numberOfPosts);
        return $this->viewPosts->renderPosts($posts, $isSuperuser);
    }
    public function showMoreTalkedPosts($numberOfPosts, $isSuperuser) {
        $moreTalkedPosts = $this->postService->getMoreTalkedPosts($numberOfPosts);
        return $this->viewPosts->renderMoreTalkedPosts($moreTalkedPosts, $isSuperuser);
    }
    public function deletePostById($id) {
        $deletePostId = clearInt($id);
        if ($deletePostId !== '') {
            $this->postService->deletePostById($deletePostId);
            header("Location: /");
        }
    }
}