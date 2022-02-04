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
    public function showLastPosts($numberOfPosts, $isSuperuser, $lessThanMaxPostId = 0, $showButton = false) {
        $posts = $this->postService->getLastPosts($numberOfPosts, $lessThanMaxPostId);
        return $this->viewPosts->renderPosts($posts, $isSuperuser, $showButton);
    }
    public function showPosts($posts, $isSuperuser, $showButton = false) {
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
    public function showPost($postId, $tags, $isSuperuser, $isUserChangedRating = false) {
        $post = $this->postService->getPostForViewById($postId);
        if (!$post) {
            header ("Location: /error404");
            exit;
        }
        $_SESSION['referrer'] = "/viewpost/$postId";
        return $this->viewPosts->renderPost($post, $tags, $isSuperuser, $isUserChangedRating);
    }
    public function showSearchPosts($searchWords, $isSuperuser) {
        $posts = [];
        if (!empty($searchWords)) {
            $posts += $this->postService->searchPostsByContent($searchWords);
            $posts += $this->postService->searchPostsByZagAndAuthor($searchWords);
            if (strpos($searchWords, '#') !== false) {
                $posts += $this->postService->searchPostsByTag($searchWords);
            }
        }
        krsort($posts);
        $this->showPosts($posts, $isSuperuser);
    }
    public function getTagsByPostId($postId) {
        $tags = $this->postService->getTagsByPostId($postId);
        return $tags;
    }
    public function deletePostById($postId) {
        $deletePostId = clearInt($postId);
        if ($deletePostId !== '') {
            return $this->postService->deletePostById($deletePostId);
        }
    }
}