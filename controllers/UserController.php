<?php

class UserController {
    private $sessionUserId, $isSuperuser, $userService;
    public function __construct(UserService $userService){
        $this->userService = $userService;
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
    public function exitUser() {
        if ($this->getUserId()) {
            $_SESSION['user_id'] = false;
            setcookie('user_id', '0', 1);
            $uri = stristr($_SERVER['REQUEST_URI'], '?exit', true);
            header("Location: $uri");
        }
    }
}