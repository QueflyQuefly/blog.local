<?php
session_start();

class FrontController {
    public $sessionUserId = false, $isSuperuser = false;
    private $userService, $postController, $commentController, $ratingController;

    public function __construct($requestUri, $_request, $startTime, FactoryMethod $factoryMethod) {
        ob_start();
        $this->startTime = $startTime;
        $this->userService = $factoryMethod->getUserService();
        $this->postController = $factoryMethod->getPostController();
        $this->commentController = $factoryMethod->getCommentController();
        $this->ratingController = $factoryMethod->getRatingController();

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
        $this->postController->showPost($postId, $this->isSuperuser());
        $this->postController->showTagsByPostId($postId);
        $this->commentController->showCommentsByPostId($postId, $this->isSuperuser());

        require "layouts/endbody.layout.php";
    }
    public function show404() {
        $pageTitle = 'Главная - просто Блог';
        $pageDescription = 'Прозошла ошибка 404: информация не найдена';            
        
        require "layouts/head.layout.php";
        require "layouts/menu.layout.php";
        require "layouts/description.layout.php";
        echo "<a class='link' href='/'>Вернуться на главную</a>";
        require "layouts/endbody.layout.php";
    }
    public function getUserId() {
        if (!$this->sessionUserId) {
            if (!empty($_SESSION['user_id'])) {
                $this->sessionUserId = $_SESSION['user_id'];
            } elseif (!empty($_COOKIE['user_id'])) {
                $this->sessionUserId = $_COOKIE['user_id'];
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
    public function deletePostById($postId) {
        if ($this->isSuperuser()) {
            $this->postController->deletePostById($postId);
            $uri = stristr($_SERVER['REQUEST_URI'], '?delete', true);
            header("Location: $uri");
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
        if ($this->getUserId()) {
            $_SESSION['user_id'] = false;
            setcookie('user_id', '0', 1);
            $uri = stristr($_SERVER['REQUEST_URI'], '?exit', true);
            header("Location: $uri");
        }
    }
}