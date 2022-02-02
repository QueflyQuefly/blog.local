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

class Factory {
    private $commentService, $postService, $ratingPostService, $stabService;
    private $ratingCommentService, $sendMailService, $subscribeService, $userService;
    private $postController, $commentController, $ratingController, $userController, $subscribeController;
    private $viewComments, $viewPosts, $viewNested, $viewUsers, $view;

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
    public function getStabService() {
        if (is_null($this->stabService)) {
            $this->stabService = new StabService($this->getUserService(), $this->getCommentService(), $this->getRatingPostService(), $this->getRatingCommentService());
        }
        return $this->stabService;
    }
    public function getPostController() {
        if (is_null($this->postController)) {
            $this->postController = new PostController($this->getPostService(), $this->getViewPosts());
        }
        return $this->postController;
    }
    public function getCommentController() {
        if (is_null($this->commentController)) {
            $this->commentController = new CommentController($this->getCommentService(), $this->getViewComments());
        }
        return $this->commentController;
    }
    public function getRatingController() {
        if (is_null($this->ratingController)) {
            $this->ratingController = new RatingController($this->getRatingPostService(), $this->getRatingCommentService());
        }
        return $this->ratingController;
    }
    public function getUserController() {
        if (is_null($this->userController)) {
            $this->userController = new UserController($this->getUserService(), $this->getViewUsers());
        }
        return $this->userController;
    }
    public function getSubscribeController() {
        if (is_null($this->subscribeController)) {
            $this->subscribeController = new SubscribeController($this->getSubscribeService());
        }
        return $this->subscribeController;
    }
    public function getViewPosts() {
        if (is_null($this->viewPosts)) {
            $this->viewPosts = new ViewPosts();
        }
        return $this->viewPosts;
    }
    public function getViewComments() {
        if (is_null($this->viewComments)) {
            $this->viewComments = new ViewComments();
        }
        return $this->viewComments;
    }
    public function getViewNested() {
        if (is_null($this->viewNested)) {
            $this->viewNested= new ViewNested();
        }
        return $this->viewNested;
    }    
    public function getViewUsers() {
        if (is_null($this->viewUsers)) {
            $this->viewUsers= new ViewUsers();
        }
        return $this->viewUsers;
    }
    public function getView() {
        if (is_null($this->view)) {
            $this->view= new View($this->getPostController(), $this->getCommentController(), $this->getUserController());
        }
        return $this->view;
    }
}