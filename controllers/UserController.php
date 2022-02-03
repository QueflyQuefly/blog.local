<?php

class UserController {
    private $sessionUserId, $isSuperuser, $userService, $viewUsers;
    public function __construct(UserService $userService, ViewUsers $viewUsers){
        $this->userService = $userService;
        $this->viewUsers = $viewUsers;
    }
    public function addUser($email, $fio, $password, $rights = false) {
        return $this->userService->addUser($email, $fio, $password, $rights);
    }
    public function getUserId() {
        if (is_null($this->sessionUserId)) {
            if (!empty($_SESSION['user_id'])) {
                $this->sessionUserId = $_SESSION['user_id'];
            } elseif (!empty($_COOKIE['user_id'])) {
                $this->sessionUserId = $_COOKIE['user_id'];
            }
        }
        return $this->sessionUserId;
    }
    public function isSuperuser() {
        if (is_null($this->isSuperuser)) {
            $userId = $this->getUserId();
            if (!empty($userId) && $this->userService->getUserInfoById($userId, 'rights') === RIGHTS_SUPERUSER) {
                $this->isSuperuser = true;
            } else {
                $this->isSuperuser = false;
            }
        }
        return $this->isSuperuser;
    }
    public function isUser($login, $password) {
        return $this->userService->isUser($login, $password);
    }
    public function updateUser($sessionUserId, $email, $fio, $password) {
        return $this->userService->updateUser($sessionUserId, $email, $fio, $password);
    }
    public function getUserIdByEmail($email) {
        return $this->userService->getUserIdByEmail($email);
    }
    public function getUserInfoById($getUserInfoById, $whatNeeded = '') {
        return $this->userService->getUserInfoById($getUserInfoById, $whatNeeded);
    }
    public function showSearchUsers($searchWord, $isSuperuser) {
        $users = $this->userService->searchUsersByFioAndEmail($searchWord, $isSuperuser);
        return $this->viewUsers->renderUsers($users, 'search/?search=' . $searchWord, $isSuperuser);
    }
    public function showAdminUsers($isSuperuser, $numberOfUsers = 50, $pageOfUsers = 1) {
        $users = $this->userService->getUsersByNumber($numberOfUsers, $numberOfUsers * $pageOfUsers - $numberOfUsers);
        return $this->viewUsers->renderUsers($users, 'adminusers', $isSuperuser);
    }
    public function exitUser() {
        if ($this->getUserId()) {
            $_SESSION['user_id'] = false;
            setcookie('user_id', '0', 1, '/');
        }
    }
    public function deleteUserById($userId) {
        return $this->userService->deleteUserById($userId);
    }
}