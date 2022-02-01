<?php

class PostController {
    private $postService, $viewPosts;
    public function __construct(PostService $postService, ViewPosts $viewPosts){
        $this->postService = $postService;
        $this->viewPosts = $viewPosts;
    }
    public function addPost($title, $userId, $content) {
        return $this->postService->addPost($title, $userId, $content);
    }
    public function showPosts($numberOfPosts, $isSuperuser, $lessThanMaxPostId = 0, $showButton = false) {
        $posts = $this->postService->getLastPosts($numberOfPosts, $lessThanMaxPostId);
        return $this->viewPosts->renderPosts($posts, $isSuperuser, $showButton);
    }
    public function showPostsByUserId($userId, $isSuperuser) {
        $posts = $this->postService->getPostsByUserId($userId);
        return $this->viewPosts->renderPosts($posts, $isSuperuser);
    }
    public function showLikedPostsByUserId($userId, $showEmailAndLinksToDelete) {
        $posts = $this->postService->getLikedPostsByUserId($userId);
        return $this->viewPosts->renderPosts($posts, $showEmailAndLinksToDelete);
    }
    public function showMoreTalkedPosts($numberOfPosts, $isSuperuser) {
        $moreTalkedPosts = $this->postService->getMoreTalkedPosts($numberOfPosts);
        return $this->viewPosts->renderMoreTalkedPosts($moreTalkedPosts, $isSuperuser);
    }
    public function showPost($postId, $isSuperuser, $isUserChangedRating = false) {
        $post = $this->postService->getPostForViewById($postId);
        if (!$post) {
            header ("Location: /404");
            exit;
        }
        $_SESSION['referrer'] = "/viewpost/$postId";
        return $this->viewPosts->renderPost($post, $isSuperuser, $isUserChangedRating);
    }
    public function showTagsByPostId($postId) {
        $tags = $this->postService->getTagsByPostId($postId);
        return $this->viewPosts->renderTags($tags);
    }
    public function deletePostById($id) {
        $deletePostId = clearInt($id);
        if ($deletePostId !== '') {
            header("Location: /");
            return $this->postService->deletePostById($deletePostId);
        }
    }
}