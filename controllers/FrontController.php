<?php
session_start();

class FrontController {
    public $sessionUserId = false, $isSuperuser = false;
    private $userService, $postController;

    public function __construct($request_uri, $_request, FactoryMethod $factoryMethod) {
        $this->postController = $factoryMethod->getPostController();
        $this->userService = $factoryMethod->getUserService();

        $twoDaysInSeconds = 60*60*24*2;
        header("Cache-Control: max-age=$twoDaysInSeconds");
        header("Cache-Control: must-revalidate");
        if ($request_uri === '/' || $request_uri === '/index.php') {
            $pageTitle = 'Главная - просто Блог';
            $pageDescription = 'Наилучший источник информации по теме "Путешествия"';            
            
            require "layouts/head.layout.php";
            require "layouts/menu.layout.php";
            require "layouts/startbody.layout.php";
            $this->postController->showLastPosts(10, $this->isSuperuser());
            $this->postController->showMoreTalkedPosts(3, $this->isSuperuser());
            require "layouts/endbody.layout.php";
        }
        if (!empty($_request)) {
            if (isset($_request['deletePostById'])) {
                $this->deletePostById($_request['deletePostById']);
            }
            if (isset($_request['exit'])) {
                $this->exitUser();
            }
        }
    }
    public function getUserId() {
        if (!$this->sessionUserId) {
            if (!empty($_SESSION['user_id'])) {
                $this->sessionUserId = $_COOKIE['user_id'];
            } elseif (!empty($_COOKIE['user_id'])) {
                $this->sessionUserId = $_SESSION['user_id'];
            }
        }
        return $this->sessionUserId;
    }
    public function isSuperuser() {
        if (!$this->isSuperuser) {
            $userId = $this->getUserId();
            if (!empty($userId) && $this->userService->getUserInfoById($userId, 'rights') === RIGHTS_SUPERUSER) {
                $this->isSuperuser = true;
            }
        }
        return $this->isSuperuser;
    }
    public function deletePostById ($postId) {
        if ($this->isSuperuser()) {
            $this->postController->deletePostById($postId);
        }
    }
    public function exitUser () {
        if ($this->getUserId()) {
            $_SESSION['user_id'] = false;
            setcookie('user_id', '0', 1);
            header("Location: /");
        }
    }
}