<?php

class RatingController {
    private $_ratingPostService, $_ratingCommentService;
    public function __construct(RatingPostService $ratingPostService, RatingCommentService $ratingCommentService){
        $this->_ratingPostService = $ratingPostService;
        $this->_ratingCommentService = $ratingCommentService;
    }
    public function changePostRating($userId, $postId, $star) {
        if (!$this->isUserChangedPostRating($userId, $postId)) {
            if ($star !== '') {
                return $this->_ratingPostService->changePostRating($userId, $postId, $star);
            }
        }
        return false;
    }
    public function isUserChangedPostRating($userId, $postId) {
        return $this->_ratingPostService->isUserChangedPostRating($userId, $postId);
    }
    public function changeCommentRating($userId, $commentId) {
        if (!$this->_ratingCommentService->isUserChangedCommentRating($userId, $commentId)) {
            $like = 'like';
            return $this->_ratingCommentService->changeCommentRating($like, $userId, $commentId);
        } else {
            $unlike = 'unlike';
            return $this->_ratingCommentService->changeCommentRating($unlike, $userId, $commentId);
        }
        return false;
    }
}