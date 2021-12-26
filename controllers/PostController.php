<?php

class PostController {
    private $postService, $viewPosts;
    public function __construct(PostService $postService, ViewPosts $viewPosts){
        $this->postService = $postService;
        $this->viewPosts = $viewPosts;
    }
    public function showLastPosts($numberOfPosts, $isSuperuser, $lessThanMaxPostId = 0) {
        $posts = $this->postService->getLastPosts($numberOfPosts, $lessThanMaxPostId);
        return $this->viewPosts->renderPosts($posts, $isSuperuser);
    }
    public function showMoreTalkedPosts($numberOfPosts, $isSuperuser) {
        $moreTalkedPosts = $this->postService->getMoreTalkedPosts($numberOfPosts);
        return $this->viewPosts->renderMoreTalkedPosts($moreTalkedPosts, $isSuperuser);
    }
    public function showPost($postId, $isSuperuser) {
        $post = $this->postService->getPostForViewById($postId);
        return $this->viewPosts->renderPost($post, $isSuperuser);
    }
    public function showTagsByPostId($postId) {
        $tags = $this->postService->getTagsByPostId($postId);
        return $this->viewPosts->renderTags($tags);
    }
    public function deletePostById($id) {
        $deletePostId = clearInt($id);
        if ($deletePostId !== '') {
            $this->postService->deletePostById($deletePostId);
            header("Location: /");
        }
    }
}