<?php
session_start();

class FrontController {
    public $sessionUserId = false, $isSuperuser = false, $isUserChangedPostRating = false;
    private $userController, $postController, $commentController, $ratingController, $view404;

    public function __construct($requestUri, $_request, $startTime, FactoryMethod $factoryMethod) {
        ob_start();
        $this->startTime = $startTime;
        $this->userController = $factoryMethod->getUserController();
        $this->postController = $factoryMethod->getPostController();
        $this->commentController = $factoryMethod->getCommentController();
        $this->ratingController = $factoryMethod->getRatingController();
        $this->view404 = $factoryMethod->getView404();

        $twoDaysInSeconds = 60*60*24*2;
        header("Cache-Control: max-age=$twoDaysInSeconds");
        header("Cache-Control: must-revalidate");
        $this->requestUriArray = explode('/', $requestUri);
        array_shift($this->requestUriArray);
        switch (array_shift($this->requestUriArray)) {
            case '': $this->showGeneral(); break;
            case 'viewpost': $this->showPost(); break;
            default : $this->show404();
        }
        if (!empty($_request)) {
            if (isset($_request['deletePostById'])) {
                $this->deletePostById($_request['deletePostById']);
            }
            if (isset($_request['deleteCommentById'])) {
                $this->deleteCommentById($_request['deleteCommentById']);
            }
            if (isset($_request['post_id']) && isset($_request['addCommentContent'])) {
                $this->addComment($_request['post_id'], $_request['addCommentContent']);
            }
            if (isset($_request['post_id']) && isset($_request['star'])) {
                $this->changePostRating($_request['post_id'], $_request['star']);
            }
            if (isset($_request['comment_id_like']) && isset($_request['post_id'])) {
                $this->changeCommentRating($_request['comment_id_like'], $_request['post_id']);
            }
            if (isset($_request['exit'])) {
                $this->exitUser();
            }
        }
        ob_end_flush();
    }
    public function showGeneral() {
        $pageTitle = 'Главная - просто Блог';
        $pageDescription = 'Наилучший источник информации по теме "Путешествия"';            
        
        require "layouts/head.layout.php";
        require "layouts/menu.layout.php";
        require "layouts/description.layout.php";
        $this->postController->showLastPosts(10, $this->isSuperuser());
        $this->postController->showMoreTalkedPosts(3, $this->isSuperuser());
        require "layouts/endbody.layout.php";
    }
    public function showPost() {
        $postId = array_shift($this->requestUriArray);
        $postId = clearInt($postId);
        if ($postId < 1) {
            header("Location: /");
        }
        $_SESSION['referrer'] = "/viewpost/$postId";

        $pageTitle = 'Просмотр поста - просто Блог';          
        
        require "layouts/head.layout.php";
        require "layouts/menu.layout.php";
        $this->postController->showPost(
            $postId, $this->isSuperuser(), $this->ratingController->isUserChangedPostRating($this->getUserId(), $postId)
        );
        $this->postController->showTagsByPostId($postId);
        $this->commentController->showCommentsByPostId($postId, $this->isSuperuser());

        require "layouts/endbody.layout.php";
    }
    public function show404() {
        $this->view404->view($this->sessionUserId, $this->isSuperuser);
    }
    public function getUserId() {
        return $this->userController->getUserId();
    }
    public function isSuperuser() {
        return $this->userController->isSuperuser();
    }
    public function deletePostById($postId) {
        if ($this->isSuperuser()) {
            $this->postController->deletePostById($postId);
            $uri = stristr($_SERVER['REQUEST_URI'], '?delete', true);
            header("Location: $uri");
        }
    }
    public function changePostRating($postId, $star) {
        if ($this->getUserId()) {
            $this->ratingController->changePostRating($this->getUserId(), $postId, $star);
        }
    }
    public function deleteCommentById($commentId) {
        if ($this->isSuperuser()) {
            $this->commentController->deleteCommentById($commentId);
            $uri = stristr($_SERVER['REQUEST_URI'], '?delete', true);
            header("Location: $uri#comment");
        }
    }
    public function addComment($postId, $commentContent) {
        if ($this->getUserId()) {
            $this->commentController->addComment($postId, $this->getUserId(), $commentContent);
        }
    }
    public function changeCommentRating($commentId, $postId) {
        if ($this->getUserId()) {
            $this->ratingController->changeCommentRating($commentId, $postId, $this->getUserId());
        }
    }
    public function exitUser() {
        $this->userController->exitUser();
    }
}