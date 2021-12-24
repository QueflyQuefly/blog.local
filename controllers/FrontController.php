<?php
session_start();
$pathToUserService = dirname(__DIR__) . DIRECTORY_SEPARATOR . 'models' . DIRECTORY_SEPARATOR . 'UserService.php';
require_once $pathToUserService;

function clearInt($int) {
    return abs((int) $int);
}
function clearStr($str) {
    return trim(strip_tags($str));
}

class FrontController {
    public $sessionUserId, $isSuperuser;
    private $userService, $postController, $request;

    public function __construct($request) {
        $this->postController = new PostController();

        $twoDaysInSeconds = 60*60*24*2;
        header("Cache-Control: max-age=$twoDaysInSeconds");
        header("Cache-Control: must-revalidate");
        if (!empty($request)) {
            if (isset($request['deletePostById'])) {
                $this->postController->deletePostById($request['deletePostById']);
            }
            if (isset($request['exit'])) {
                $this->exitUser();
            }
        }
    }
    public function getUserId() {
        if (!empty($_COOKIE['user_id'])) {
            $this->sessionUserId = $_COOKIE['user_id'];
        } elseif (!empty($_SESSION['user_id'])) {
            $this->sessionUserId = $_SESSION['user_id'];
        }
        return $this->sessionUserId;
    }
    public function isSuperuser() {
        $this->userService = new UserService();
        $userId = $this->getUserId();
        if (!empty($userId) && $this->userService->getUserInfoById($userId, 'rights') === RIGHTS_SUPERUSER) {
            $this->isSuperuser = true;
            return $this->isSuperuser;
        } else {
            return false;
        }
    }
    public function exitUser () {
        $_SESSION['user_id'] = false;
        setcookie('user_id', '0', 1);
        header("Location: /");
    }
}