<?php

class UserController {
    private $_sessionUserId, $_isSuperuser, $_userService, $_viewUsers;
    public function __construct(UserService $userService, ViewUsers $viewUsers){
        $this->_userService = $userService;
        $this->_viewUsers = $viewUsers;
    }
    public function addUser($email, $fio, $password, $rights = false) {
        return $this->_userService->addUser($email, $fio, $password, $rights);
    }
    public function getUserId() {
        if (is_null($this->_sessionUserId)) {
            if (!empty($_SESSION['user_id'])) {
                $this->_sessionUserId = $_SESSION['user_id'];
            } elseif (!empty($_COOKIE['user_id'])) {
                $this->_sessionUserId = $_COOKIE['user_id'];
            }
        }
        return $this->_sessionUserId;
    }
    public function isSuperuser() {
        if (is_null($this->_isSuperuser)) {
            $userId = $this->getUserId();
            if (!empty($userId) && $this->_userService->getUserInfoById($userId, 'rights') === RIGHTS_SUPERUSER) {
                $this->_isSuperuser = true;
            } else {
                $this->_isSuperuser = false;
            }
        }
        return $this->_isSuperuser;
    }
    public function isUser($login, $password) {
        return $this->_userService->isUser($login, $password);
    }
    public function updateUser($sessionUserId, $email, $fio, $password) {
        return $this->_userService->updateUser($sessionUserId, $email, $fio, $password);
    }
    public function getUserIdByEmail($email) {
        return $this->_userService->getUserIdByEmail($email);
    }
    public function getUserInfoById($getUserInfoById, $whatNeeded = '') {
        return $this->_userService->getUserInfoById($getUserInfoById, $whatNeeded);
    }
    public function showSearchUsers($searchWord, $isSuperuser) {
        $users = $this->_userService->searchUsersByFioAndEmail($searchWord, $isSuperuser);
        return $this->_viewUsers->renderUsers($users, 'search/?search=' . $searchWord, $isSuperuser);
    }
    public function showAdminUsers($isSuperuser, $numberOfUsers = 50, $pageOfUsers = 1) {
        $users = $this->_userService->getUsersByNumber($numberOfUsers, $numberOfUsers * $pageOfUsers - $numberOfUsers);
        return $this->_viewUsers->renderUsers($users, 'adminusers', $isSuperuser);
    }
    public function exitUser() {
        if ($this->getUserId()) {
            $_SESSION['user_id'] = false;
            setcookie('user_id', '0', 1, '/');
        }
    }
    public function deleteUserById($userId) {
        return $this->_userService->deleteUserById($userId);
    }
}