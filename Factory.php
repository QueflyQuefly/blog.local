<?php

class Factory {
    private $_commentService, $_postService, $_ratingPostService, $_stabService,
            $_ratingCommentService, $_sendMailService, $_subscribeService, $_userService,
            $_postController, $_commentController, $_ratingController, $_userController, $_subscribeController,
            $_viewComments, $_viewPosts, $_viewUsers, $_view;

    public function getCommentService() {
        if (is_null($this->_commentService)) {
            $this->_commentService = new CommentService();
        }
        return $this->_commentService;
    }
    public function getPostService() {
        if (is_null($this->_postService)) {
            $this->_postService = new PostService();
        }
        return $this->_postService;
    }
    public function getRatingPostService() {
        if (is_null($this->_ratingPostService)) {
            $this->_ratingPostService = new RatingPostService();
        }
        return $this->_ratingPostService;
    }
    public function getRatingCommentService() {
        if (is_null($this->_ratingCommentService)) {
            $this->_ratingCommentService = new RatingCommentService();
        }
        return $this->_ratingCommentService;
    }
    public function getSendMailService() {
        if (is_null($this->_sendMailService)) {
            $this->_sendMailService = SendMailService::getInstance();
        }
        return $this->_sendMailService;
    }
    public function getSubscribeService() {
        if (is_null($this->_subscribeService)) {
            $this->_subscribeService = new SubscribeService();
        }
        return $this->_subscribeService;
    }
    public function getUserService() {
        if (is_null($this->_userService)) {
            $this->_userService = new UserService();
        }
        return $this->_userService;
    }
    public function getStabService() {
        if (is_null($this->_stabService)) {
            $this->_stabService = new StabService($this->getUserService(), $this->getCommentService(), $this->getRatingPostService(), $this->getRatingCommentService());
        }
        return $this->_stabService;
    }
    public function getPostController() {
        if (is_null($this->_postController)) {
            $this->_postController = new PostController($this->getPostService(), $this->getViewPosts());
        }
        return $this->_postController;
    }
    public function getCommentController() {
        if (is_null($this->_commentController)) {
            $this->_commentController = new CommentController($this->getCommentService(), $this->getViewComments());
        }
        return $this->_commentController;
    }
    public function getRatingController() {
        if (is_null($this->_ratingController)) {
            $this->_ratingController = new RatingController($this->getRatingPostService(), $this->getRatingCommentService());
        }
        return $this->_ratingController;
    }
    public function getUserController() {
        if (is_null($this->_userController)) {
            $this->_userController = new UserController($this->getUserService(), $this->getViewUsers());
        }
        return $this->_userController;
    }
    public function getSubscribeController() {
        if (is_null($this->_subscribeController)) {
            $this->_subscribeController = new SubscribeController($this->getSubscribeService());
        }
        return $this->_subscribeController;
    }
    public function getViewPosts() {
        if (is_null($this->_viewPosts)) {
            $this->_viewPosts = new ViewPosts();
        }
        return $this->_viewPosts;
    }
    public function getViewComments() {
        if (is_null($this->_viewComments)) {
            $this->_viewComments = new ViewComments();
        }
        return $this->_viewComments;
    }
    public function getViewUsers() {
        if (is_null($this->_viewUsers)) {
            $this->_viewUsers= new ViewUsers();
        }
        return $this->_viewUsers;
    }
    public function getView() {
        if (is_null($this->_view)) {
            $this->_view= new View($this->getPostController(), $this->getCommentController(), $this->getUserController(), $this->getSubscribeController());
        }
        return $this->_view;
    }
}