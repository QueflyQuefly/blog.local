<?php
session_start();

class FrontController {
    public $msg, $startTime, $maxSizeOfUploadImage = 4 * 1024 * 1024; //4 megabytes
    private $_request, $_userController, $_postController, $_commentController, $_ratingController, 
            $_subscribeController, $_stabService, $_view;

    public function __construct(Factory $factory, $startTime) {
        ob_start();
        $this->startTime = $startTime;
        $this->_request = $_REQUEST;
        $this->_userController = $factory->getUserController();
        $this->_postController = $factory->getPostController();
        $this->_commentController = $factory->getCommentController();
        $this->_ratingController = $factory->getRatingController();
        $this->_subscribeController = $factory->getSubscribeController();
        $this->_stabService = $factory->getStabService();
        $this->_view = $factory->getView();

        $twoDaysInSeconds = 60*60*24*2;
        header("Cache-Control: max-age=$twoDaysInSeconds");
        header("Cache-Control: must-revalidate");
        if (!empty($this->_request)) {
            if (isset($this->_request['deletePostById'])) {
                if ($this->isSuperuser()) {
                    $this->deletePostById($this->_request['deletePostById']);
                } else {
                    header ("Location: /login");
                }
            }
            if (isset($this->_request['deleteCommentById'])) {
                if ($this->isSuperuser()) {
                    $this->deleteCommentById($this->_request['deleteCommentById']);
                } else {
                    header ("Location: /login");
                }
            }
            if (isset($this->_request['deleteUserById'])) {
                if ($this->isSuperuser()) {
                    $this->deleteUserById($this->_request['deleteUserById']);
                } else {
                    header ("Location: /login");
                }
            }
            if (isset($this->_request['post_id']) && isset($this->_request['addCommentContent'])) {
                if ($this->getUserId()) {
                    $this->addComment($this->_request['post_id'], $this->_request['addCommentContent']);
                } else {
                    header ("Location: /login");
                }
            }
            if (isset($this->_request['post_id']) && isset($this->_request['star'])) {
                if ($this->getUserId()) {
                    $this->changePostRating($this->_request['post_id'], $this->_request['star']);
                } else {
                    header ("Location: /login");
                }
            }
            if (isset($this->_request['comment_id_like'])) {
                if ($this->getUserId()) {
                    $this->changeCommentRating($this->_request['comment_id_like']);
                } else {
                    header ("Location: /login");
                }
            }
            if (isset($this->_request['exit'])) {
                if ($this->getUserId()) {
                    $this->exitUser();
                }
            }
            if (isset($this->_request['email'])) {
                $variableOfCaptcha = clearInt($this->_request['variable_of_captcha']);
                $email = clearStr($this->_request['email']);
                $password = $this->_request['password'];
                if ($variableOfCaptcha == $_SESSION['variable_of_captcha']) {
                    if ($this->_userController->isUser($email, $password)) {
                        setcookie('user_id', $this->_userController->getUserIdByEmail($email), strtotime('+2 days'));
                        header("Location: {$_SESSION['referrer']}");
                    } else {
                        $this->msg = "Неверный email или пароль";
                    }
                } else {
                    $this->msg = "Неверно введен код с Captcha";
                }
            }
            if (isset($this->_request['regemail']) && isset($this->_request['regfio']) && isset($this->_request['regpassword']) && isset($this->_request['variable_of_captcha'])) {
                $variableOfCaptcha = clearInt($this->_request['variable_of_captcha']);
                $regemail = clearStr($this->_request['regemail']);
                $regfio = clearStr($this->_request['regfio']);
                $regpassword = $this->_request['regpassword'];
                $regex = '/\A[^@]+@([^@\.]+\.)+[^@\.]+\z/u';
                if (!preg_match($regex, $regemail)) {
                    $this->msg = "Неверный формат regemail";
                }   
                if ($regemail !== '' && $regfio !== '' && $regpassword !== '') {
                    $regpassword = password_hash($regpassword, PASSWORD_BCRYPT);
                    if ($variableOfCaptcha == $_SESSION['variable_of_captcha']) {
                        $addSuperuser = false;
                        if (isset($this->_request['add_admin']) && $this->isSuperuser()) {
                            $addSuperuser = true;
                        }
                        if (!$this->_userController->addUser($regemail, $regfio, $regpassword, $addSuperuser)) {
                            $this->msg = "Пользователь с таким email уже зарегистрирован";
                        } else {
                            if (!$addSuperuser && !$this->isSuperuser()) {
                                setcookie('user_id', $this->_userController->getUserIdByEmail($regemail), strtotime('+2 days'));
                            }
                            header("Location: {$_SESSION['referrer']}");
                        } 
                    } else {
                        $this->msg = "Неверно введен код с Captcha";
                    }
                } else { 
                    $this->msg = "Заполните все поля";
                }
            }
            if (isset($this->_request['addPostZag'])) {
                $title = clearStr($this->_request['addPostZag']);
                $content = clearStr($this->_request['addPostContent']);
                if ($title !== '' && $content !== '') {
                    /* if ( $_FILES['addPostImg']["error"] != UPLOAD_ERR_OK ) {
                        switch ($_FILES['addPostImg']["error"]) {
                            case UPLOAD_ERR_INI_SIZE:
                                $this->msg = "Превышен максимально допустимый размер";
                                break;
                            case UPLOAD_ERR_FORM_SIZE:
                                $this->msg = "Превышено значение $this->maxSizeOfUploadImage байт";
                                break;
                            case UPLOAD_ERR_PARTIAL:
                                $this->msg = "Файл загружен частично";
                                break;
                            case UPLOAD_ERR_NO_FILE:
                                $this->msg = "Файл не был загружен";
                                break;
                            case UPLOAD_ERR_NO_TMP_DIR:
                                $this->msg = "Отсутствует временная папка";
                                break;
                            case UPLOAD_ERR_CANT_WRITE:
                                $this->msg = "Не удалось записать файл на диск";
                        }
                    } elseif ($_FILES['addPostImg']["type"] == 'image/jpeg') { */
                        if (!$this->_postController->addPost($title, $this->getUserId(), $content)) {
                            $this->msg =  "Произошла ошибка при добавлении поста";
                        } else {
                            /* move_uploaded_file($_FILES['addPostImg']["tmp_name"], "images\PostImgId" . $lastPostId . ".jpg"); */
                            header("Location: /");
                        }
                    /* } else {
                        $this->msg = "Изображение имеет недопустимое расширение (не jpg)";
                    }  */         
                } else {
                    $this->msg = "Заполните все поля";
                }
            }
            if (isset($this->_request['change_email']) && isset($this->_request['change_fio']) && isset($this->_request['change_password'])) {
                $email = clearStr($this->_request['change_email']);
                $fio = clearStr($this->_request['change_fio']);
                $password = $this->_request['change_password'];
                $regex = '/\A[^@]+@([^@\.]+\.)+[^@\.]+\z/u';
                if (!preg_match($regex, $email)) {
                    $this->msg = "Неверный формат email";
                }   
                if ($email && $fio) {
                    if ($password != '') {
                        $password = password_hash($password, PASSWORD_BCRYPT);
                    } else {
                        $password = false;
                    }
                    if (!$this->_userController->updateUser($this->getUserId(), $email, $fio, $password)) {
                        $this->msg = "Пользователь с таким email уже зарегистрирован";
                    } else {
                        $this->msg = "Изменения сохранены";
                        header("Refresh:1");
                    }
                } else { 
                    $this->msg = "Заполните все поля";
                }
            }
            if (isset($this->_request['user']) && (isset($this->_request['subscribe']) || isset($this->_request['unsubscribe']))) {
                header("Refresh:0");
                $this->_subscribeController->subscribeUser($this->getUserId(), $this->_request['user']);
            }
            if (isset($this->_request['view']) && $this->isSuperuser()) {
                switch($this->_request['view']) {
                    case 'viewPosts': header("Location: /posts"); break;
                    case 'viewUsers': header("Location: /adminusers"); break;
                    case 'addAdmin': header("Location: /reg"); break;
                    case 'viewStab': header("Location: /stab"); break;
                    default: header("Location: /");
                }
            }
        }
        $this->requestUriArray = explode('/', $_SERVER['REQUEST_URI']);
        array_shift($this->requestUriArray);
        switch (array_shift($this->requestUriArray)) {
            case '': $this->showGeneral(); break;
            case 'viewpost': $this->showPost(); break;
            case 'login': $this->showLogin(); break;
            case 'reg': $this->showReg(); break;
            case 'addpost': $this->showAddpost(); break;
            case 'posts': $this->showPosts(); break;
            case 'stab': $this->showStab(); break;
            case 'cabinet': $this->showCabinet(); break;
            case 'search': $this->showSearch(); break;
            case 'admin': $this->showAdmin(); break;
            case 'adminusers': $this->showAdminUsers(); break;
            default : $this->show404();
        }
    }
    public function __destruct() {
        ob_end_flush();
    }
    public function showGeneral() {
        $_SESSION['referrer'] = '/';
        $this->_view->viewGeneral($this->getUserId(), $this->isSuperuser(), $this->startTime);
    }
    public function showPost() {
        $postId = array_shift($this->requestUriArray);
        if ($postId < 1) {
          header ("Location: /error404");
        } else {
            $postId = clearInt($postId);
            $this->_view->viewPost(
                $postId, 
                $this->getUserId(), 
                $this->isSuperuser(), 
                $this->startTime, 
                $this->_ratingController->isUserChangedPostRating($this->getUserId(), $postId)
            );
        }
    }
    public function show404() {
        $this->_view->view404($this->getUserId(), $this->isSuperuser(), $this->startTime);
    }
    public function showLogin() {
        $this->_view->viewLogin($this->getUserId(), $this->isSuperuser(), $this->startTime, $this->msg);

    }
    public function showReg() {
        $this->_view->viewReg($this->getUserId(), $this->isSuperuser(), $this->startTime, $this->msg);
    }
    public function showAddpost() {
        $_SESSION['referrer'] = '/addpost';
        if (!$this->getUserId()) {
            header ("Location: /login");
        }
        $this->_view->viewAddpost($this->getUserId(), $this->isSuperuser(), $this->startTime, $this->maxSizeOfUploadImage, $this->msg);
    }
    public function showStab() {
        @set_time_limit(6000);
        if (!$this->isSuperuser()) {
            header("Location: /error404");
        } else {
            $_SESSION['referrer'] = '/stab';
            $numberOfLoopIterations = $this->_request['number'] ?? 0;
            $numberOfLoopIterations = clearInt($numberOfLoopIterations);
            $this->_stabService->stabDb($numberOfLoopIterations);
            $errors = $this->_stabService->getErrors();
            $this->_view->viewStab($this->getUserId(), $this->isSuperuser(), $numberOfLoopIterations, $errors, $this->startTime);
        }
    }
    public function showPosts() {
        $_SESSION['referrer'] = "/posts";
        $numberOfPosts = $this->_request['number'] ?? 25;
        $numberOfPosts = clearInt($numberOfPosts);
        $pageOfPosts = $this->_request['page'] ?? 1;
        $pageOfPosts = clearInt($pageOfPosts);
        $this->_view->viewPosts($this->getUserId(), $this->isSuperuser(), $this->startTime, $numberOfPosts, $pageOfPosts);
    }
    public function showCabinet() {
        $_SESSION['referrer'] = "/cabinet";
        $userId = $this->_request['user'] ?? $this->getUserId();
        if ($userId == false) {
            header("Location: /login");
        } else {
            $user = $this->_userController->getUserInfoById($userId);
            $showEmailAndLinksToDelete = false;
            $linkToChangeUserInfo = false;
            if ($this->getUserId() == $userId || $this->isSuperuser()) {
                $showEmailAndLinksToDelete = true;
                if ($this->getUserId() == $userId) {
                    $linkToChangeUserInfo = true;
                }
            }
            $this->_view->viewCabinet(
                $user, 
                $showEmailAndLinksToDelete, 
                $linkToChangeUserInfo, 
                $this->getUserId(), 
                $this->isSuperuser(), 
                $this->startTime,
                $this->msg
            );
        }
    }
    public function showSearch() {
        $search = $this->_request['search'] ?? '';
        $_SESSION['referrer'] = "/search/?search=$search";
        $this->_view->viewSearch($this->getUserId(), $this->isSuperuser(), $this->startTime, $search);
    }
    public function showAdmin() {
        if (empty($this->isSuperuser())) {
            header("Location: /error404");
        } else {
            $_SESSION['referrer'] = "/admin";
            $this->_view->viewAdmin($this->getUserId(), $this->isSuperuser(), $this->startTime);
        }
    }
    public function showAdminUsers() {
        if (empty($this->isSuperuser())) {
            header("Location: /error404");
        } else {
            $_SESSION['referrer'] = "/adminusers";
            $numberOfUsers = $this->_request['number'] ?? 50;
            $numberOfUsers = clearInt($numberOfUsers);
            $pageOfUsers = $this->_request['page'] ?? 1;
            $pageOfUsers = clearInt($pageOfUsers);
            $this->_view->viewAdminUsers($this->getUserId(), $this->isSuperuser(), $this->startTime, $numberOfUsers, $pageOfUsers);
        }
    }
    public function changePostRating($postId, $star) {
        if ($this->getUserId()) {
            header("Refresh:0");
            return $this->_ratingController->changePostRating($this->getUserId(), $postId, $star);
        }
    }
    public function deletePostById($postId) {
        if ($this->isSuperuser()) {
            header("Refresh:0");
            return $this->_postController->deletePostById($postId);
        }
    }
    public function addComment($postId, $commentContent) {
        if ($this->getUserId()) {
            header("Refresh:0");
            return $this->_commentController->addComment($postId, $this->getUserId(), $commentContent);
        }
    }
    public function changeCommentRating($commentId) {
        if ($this->getUserId()) {
            header("Refresh:0");
            return $this->_ratingController->changeCommentRating($this->getUserId(), $commentId);
        }
    }
    public function deleteCommentById($commentId) {
        if ($this->isSuperuser()) {
            header("Refresh:0");
            return $this->_commentController->deleteCommentById($commentId);
        }
    }
    public function getUserId() {
        return $this->_userController->getUserId();
    }
    public function isSuperuser() {
        return $this->_userController->isSuperuser();
    }
    public function exitUser() {
        header("Refresh:0");
        $this->_userController->exitUser();
    }
    public function deleteUserById($userId) {
        if ($this->isSuperuser()) {
            header("Refresh:0");
            return $this->_userController->deleteUserById($userId);
        }
    }
}