<?php
spl_autoload_register(function ($class) {
    if (strpos($class, 'Controller') !== false) {
        $pathToClass = 'controllers' . DIRECTORY_SEPARATOR;
    }
    if (strpos($class, 'Service') !== false) {
        $pathToClass = 'services' . DIRECTORY_SEPARATOR;
    }
    if (strpos($class, 'View') !== false) {
        $pathToClass = 'views' . DIRECTORY_SEPARATOR;
    }
    require_once $pathToClass . $class . '.php';
});

function clearInt($int) {
    return abs((int) $int);
}
function clearStr($str) {
    return trim(strip_tags($str));
}

class FactoryMethod {
    private $postController, $dbService, $commentService, $postService, $ratingPostService;
    private $ratingCommentService, $sendMailService, $subscribeService, $userService, $viewPosts;

    public function getDbService() {
        if (is_null($this->dbService)) {
            $this->dbService = new DbService();
        }
        return $this->dbService;
    }
    public function getCommentService() {
        if (is_null($this->commentService)) {
            $this->commentService = new CommentService();
        }
        return $this->commentService;
    }
    public function getPostService() {
        if (is_null($this->postService)) {
            $this->postService = new PostService();
        }
        return $this->postService;
    }
    public function getRatingPostService() {
        if (is_null($this->ratingPostService)) {
            $this->ratingPostService = new RatingPostService();
        }
        return $this->ratingPostService;
    }
    public function getRatingCommentService() {
        if (is_null($this->ratingCommentService)) {
            $this->ratingCommentService = new RatingCommentService();
        }
        return $this->ratingCommentService;
    }
    public function getSendMailService() {
        if (is_null($this->sendMailService)) {
            $this->sendMailService = SendMailService::getInstance();
        }
        return $this->sendMailService;
    }
    public function getSubscribeService() {
        if (is_null($this->subscribeService)) {
            $this->subscribeService = new SubscribeService();
        }
        return $this->subscribeService;
    }
    public function getUserService() {
        if (is_null($this->userService)) {
            $this->userService = new UserService();
        }
        return $this->userService;
    }
    public function getPostController() {
        if (is_null($this->postController)) {
            $this->postController = new PostController($this->getPostService(), $this->getViewPosts());
        }
        return $this->postController;
    }
    public function getViewPosts() {
        if (is_null($this->viewPosts)) {
            $this->viewPosts = new ViewPosts();
        }
        return $this->viewPosts;
    }
}