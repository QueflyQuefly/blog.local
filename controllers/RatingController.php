<?php

class RatingController {
    private $ratingPostService, $ratingCommentService;
    public function __construct(RatingPostService $ratingPostService, RatingCommentService $ratingCommentService){
        $this->ratingPostService = $ratingPostService;
        $this->ratingCommentService = $ratingCommentService;
    }
    public function changePostRating($userId, $postId, $star) {
        if (!$this->isUserChangedPostRating($userId, $postId)) {
            if ($star !== '') {
                return $this->ratingPostService->changePostRating($userId, $postId, $star);
            }
        }
        return false;
    }
    public function isUserChangedPostRating($userId, $postId) {
        return $this->ratingPostService->isUserChangedPostRating($userId, $postId);
    }
    public function changeCommentRating($commentId, $postId, $userId) {
        if (!$this->ratingCommentService->isUserChangedCommentRating($userId, $commentId)) {
            $like = 'like';
            return $this->ratingCommentService->changeCommentRating($like, $commentId, $postId, $userId);
        } else {
            $unlike = 'unlike';
            return $this->ratingCommentService->changeCommentRating($unlike, $commentId, $postId, $userId);
        }
        return false;
    }
}