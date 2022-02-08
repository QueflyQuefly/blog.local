<?php

class PostController {
    private $_postService, $_viewPosts;
    public function __construct(PostService $postService, ViewPosts $viewPosts){
        $this->_postService = $postService;
        $this->_viewPosts = $viewPosts;
    }
    public function addPost($title, $userId, $content) {
        return $this->_postService->addPost($title, $userId, $content);
    }
    public function showLastPosts($numberOfPosts, $isSuperuser, $lessThanMaxPostId = 0, $showButton = false) {
        $posts = $this->_postService->getLastPosts($numberOfPosts, $lessThanMaxPostId);
        return $this->_viewPosts->renderPosts($posts, $isSuperuser, $showButton);
    }
    public function showPosts($posts, $isSuperuser, $showButton = false) {
        return $this->_viewPosts->renderPosts($posts, $isSuperuser, $showButton);
    }
    public function showPostsByUserId($userId, $isSuperuser) {
        $posts = $this->_postService->getPostsByUserId($userId);
        return $this->_viewPosts->renderPosts($posts, $isSuperuser);
    }
    public function showLikedPostsByUserId($userId, $showEmailAndLinksToDelete) {
        $posts = $this->_postService->getLikedPostsByUserId($userId);
        return $this->_viewPosts->renderPosts($posts, $showEmailAndLinksToDelete);
    }
    public function showMoreTalkedPosts($numberOfPosts, $isSuperuser) {
        $moreTalkedPosts = $this->_postService->getMoreTalkedPosts($numberOfPosts);
        return $this->_viewPosts->renderMoreTalkedPosts($moreTalkedPosts, $isSuperuser);
    }
    public function showPost($postId, $tags, $isSuperuser, $isUserChangedRating = false) {
        $post = $this->_postService->getPostForViewById($postId);
        if (!$post) {
            header ("Location: /error404");
            exit;
        }
        $_SESSION['referrer'] = "/viewpost/$postId";
        return $this->_viewPosts->renderPost($post, $tags, $isSuperuser, $isUserChangedRating);
    }
    public function showSearchPosts($searchWords, $isSuperuser) {
        $posts = [];
        if (!empty($searchWords)) {
            $posts += $this->_postService->searchPostsByContent($searchWords);
            $posts += $this->_postService->searchPostsByZagAndAuthor($searchWords);
            if (strpos($searchWords, '#') !== false) {
                $posts += $this->_postService->searchPostsByTag($searchWords);
            }
        }
        krsort($posts);
        $this->showPosts($posts, $isSuperuser);
    }
    public function getTagsByPostId($postId) {
        $tags = $this->_postService->getTagsByPostId($postId);
        return $tags;
    }
    public function deletePostById($postId) {
        $deletePostId = clearInt($postId);
        if ($deletePostId !== '') {
            return $this->_postService->deletePostById($deletePostId);
        }
    }
}