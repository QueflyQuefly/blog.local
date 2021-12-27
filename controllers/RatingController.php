<?php

class RatingController {
    private $ratingPostService, $ratingCommentService;
    public function __construct(RatingPostService $ratingPostService, RatingCommentService $ratingCommentService){
        $this->ratingPostService = $ratingPostService;
        $this->ratingCommentService = $ratingCommentService;
    }
    public function changePostRating($userId, $postId, $star) {
        if (!$this->ratingPostService->isUserChangedPostRating($userId, $postId)) {
            if ($star !== '') {
                header("Refresh:0");
                return $this->ratingPostService->changePostRating($userId, $postId, $star);
            }
        }
        return false;
    }
    public function changeCommentRating($commentId, $postId, $userId) {
        if (!$this->ratingCommentService->isUserChangedCommentRating($userId, $commentId)) {
            $like = 'like';
            header("Refresh:0");
            return $this->ratingCommentService->changeCommentRating($like, $commentId, $postId, $userId);
        } else {
            $unlike = 'unlike';
            header("Refresh:0");
            return $this->ratingCommentService->changeCommentRating($unlike, $commentId, $postId, $userId);
        }
        return false;
    }
}