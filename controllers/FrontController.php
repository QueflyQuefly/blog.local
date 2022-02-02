<?php
session_start();

class FrontController {
    public $msg, $error, $maxSizeOfUploadImage = 4 * 1024 * 1024; //4 megabytes
    private $stabService, $view;
    private $userController, $postController, $commentController, $ratingController, $subscribeController;

    public function __construct($requestUri, $_request, $startTime, Factory $factory) {
        ob_start();
        $this->startTime = $startTime;
        $this->stabService = $factory->getStabService();
        $this->userController = $factory->getUserController();
        $this->postController = $factory->getPostController();
        $this->commentController = $factory->getCommentController();
        $this->ratingController = $factory->getRatingController();
        $this->subscribeController = $factory->getSubscribeController();
        $this->viewNested= $factory->getViewNested();
        $this->view= $factory->getView();

        $twoDaysInSeconds = 60*60*24*2;
        header("Cache-Control: max-age=$twoDaysInSeconds");
        header("Cache-Control: must-revalidate");
        $this->requestUriArray = explode('/', $requestUri);
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
        if (!empty($_request)) {
            if (isset($_request['deletePostById'])) {
                if ($this->isSuperuser()) {
                    $this->deletePostById($_request['deletePostById']);
                } else {
                    header ("Location: /login");
                }
            }
            if (isset($_request['deleteCommentById'])) {
                if ($this->isSuperuser()) {
                    $this->deleteCommentById($_request['deleteCommentById']);
                } else {
                    header ("Location: /login");
                }
            }
            if (isset($_request['post_id']) && isset($_request['addCommentContent'])) {
                if ($this->getUserId()) {
                    $this->addComment($_request['post_id'], $_request['addCommentContent']);
                } else {
                    header ("Location: /login");
                }
            }
            if (isset($_request['post_id']) && isset($_request['star'])) {
                if ($this->getUserId()) {
                    $this->changePostRating($_request['post_id'], $_request['star']);
                } else {
                    header ("Location: /login");
                }
            }
            if (isset($_request['comment_id_like']) && isset($_request['post_id'])) {
                if ($this->getUserId()) {
                    $this->changeCommentRating($_request['comment_id_like'], $_request['post_id']);
                } else {
                    header ("Location: /login");
                }
            }
            if (isset($_request['exit'])) {
                if ($this->getUserId()) {
                    $this->exitUser();
                }
            }
            if (isset($_request['email'])) {
                $variableOfCaptcha = clearInt($_POST['variable_of_captcha']);
                $email = clearStr($_POST['email']);
                $password = $_POST['password'];
                if ($variableOfCaptcha == $_SESSION['variable_of_captcha']) {
                    if ($this->userController->isUser($email, $password)) {
                        setcookie('user_id', $this->userController->getUserIdByEmail($email), strtotime('+2 days'));
                        if (!empty($_SESSION['referrer'])) {
                            header("Location: {$_SESSION['referrer']}");
                        } else {
                            header("Location: /");
                        }
                    } else {
                        $this->error = "Неверный логин или пароль";
                        header("Location: login/?msg=$this->error");
                    }
                } else {
                    $this->error = "Неверно введен код с Captcha";
                    header("Location: login/?msg=$this->error");
                }
            }
            if (isset($_request['regemail'])) {
                $variableOfCaptcha = clearInt($_POST['variable_of_captcha']);
                $regemail = clearStr($_POST['regemail']);
                $regfio = clearStr($_POST['regfio']);
                $regpassword = $_POST['regpassword'];
                $regex = '/\A[^@]+@([^@\.]+\.)+[^@\.]+\z/u';
                if (!preg_match($regex, $regemail)) {
                    $error = "Неверный формат regemail";
                    header("Location: /reg/?msg=$error");
                }   
                if ($regemail !== '' && $regfio !== '' && $regpassword !== '') {
                    $regpassword = password_hash($regpassword, PASSWORD_BCRYPT);
                    if ($variableOfCaptcha == $_SESSION['variable_of_captcha']) {
                        if (isset($_POST['add_admin']) && $this->isSuperuser()) {
                            if (!$this->userController->addUser($regemail, $regfio, $regpassword, RIGHTS_SUPERUSER)) {
                                $this->error = "Пользователь с таким email уже зарегистрирован";
                                header("Location: /reg/?msg=$this->error"); 
                            } else {
                                header("Location: /");
                            } 
                        } else {
                            if (!$this->userController->addUser($regemail, $regfio, $regpassword)) {
                                $this->error = "Пользователь с таким email уже зарегистрирован";
                                header("Location: /reg/?msg=$this->error"); 
                            } else {
                                $sessionUserId = $this->userController->getUserIdByEmail($regemail);
                                setcookie('user_id', $sessionUserId, strtotime('+2 days'));
                                header("Location: /");
                            } 
                        }
                    } else {
                        $this->error = "Неверно введен код с Captcha";
                        header("Location: /reg/?msg=$this->error");
                    }
                } else { 
                    $this->error = "Заполните все поля";
                    header("Location: /reg/?msg=$this->error");
                }
            }
            if (isset($_request['addPostZag'])) {
                $title = clearStr($_POST['addPostZag']);
                $content = clearStr($_POST['addPostContent']);
                if ($title !== '' && $content !== '') {
                    /* if ( $_FILES['addPostImg']["error"] != UPLOAD_ERR_OK ) {
                        switch($_FILES['addPostImg']["error"]){
                            case UPLOAD_ERR_INI_SIZE:
                                $error = "Превышен максимально допустимый размер";
                                header("Location: /addpost/?msg=$error");
                                break;
                            case UPLOAD_ERR_FORM_SIZE:
                                $error = "Превышено значение $maxSizeOfUploadImage байт";
                                header("Location: /addpost/?msg=$error");
                                break;
                            case UPLOAD_ERR_PARTIAL:
                                $error = "Файл загружен частично";
                                header("Location: /addpost/?msg=$error");
                                break;
                            case UPLOAD_ERR_NO_FILE:
                                $error = "Файл не был загружен";
                                header("Location: /addpost/?msg=$error");
                                break;
                            case UPLOAD_ERR_NO_TMP_DIR:
                                $error = "Отсутствует временная папка";
                                header("Location: /addpost/?msg=$error");
                                break;
                            case UPLOAD_ERR_CANT_WRITE:
                                $error = "Не удалось записать файл на диск";
                                header("Location: /addpost/?msg=$error");
                        }
                    } elseif ($_FILES['addPostImg']["type"] == 'image/jpeg') { */
                        if (!$this->postController->addPost($title, $this->getUserId(), $content)) {
                            $msg =  "Произошла ошибка при добавлении поста";
                            header("Location: /addpost/?msg=$msg");
                        } else {
                            /* move_uploaded_file($_FILES['addPostImg']["tmp_name"], "images\PostImgId" . $lastPostId . ".jpg"); */
                            $msg =  "Пост добавлен";
                            header("Location: /addpost/?msg=$msg");
                        }
                    /* } else {
                        $error = "Изображение имеет недопустимое расширение (не jpg)";
                        header("Location: /addpost/?msg=$error");
                    }  */         
                } else {
                    $error = "Заполните все поля";
                    header("Location: /addpost/?msg=$error");
                }
            }
            if (isset($_request['change_email']) && isset($_request['change_fio']) && isset($_request['change_password'])) {
                $email = clearStr($_POST['change_email']);
                $fio = clearStr($_POST['change_fio']);
                $password = $_POST['change_password'];
                $regex = '/\A[^@]+@([^@\.]+\.)+[^@\.]+\z/u';
                if (!preg_match($regex, $email)) {
                    $msg = "Неверный формат email";
                    header("Location: /cabinet/?changeinfo&msg=$msg");
                }   
                if ($email && $fio) {
                    if ($password != '') {
                        $password = password_hash($password, PASSWORD_BCRYPT);
                    } else {
                        $password = false;
                    }
                    if (!$this->userController->updateUser($this->getUserId(), $email, $fio, $password)) {
                        $msg = "Пользователь с таким email уже зарегистрирован";
                        header("Location: /cabinet/?changeinfo&msg=$msg"); 
                    } else {
                        $msg = "Изменения сохранены";
                        header("Location: /cabinet/?msg=$msg");
                    }
                } else { 
                    $msg = "Заполните все поля";
                    header("Location: /cabinet/?changeinfo&msg=$msg");
                }
            }
            if (isset($_request['view']) && $this->isSuperuser()) {
                switch($_request['view']) {
                    case 'viewPosts': header("Location: /posts"); break;
                    case 'viewUsers': header("Location: /adminusers"); break;
                    case 'addAdmin': header("Location: /reg"); break;
                    case 'viewStab': header("Location: /stab"); break;
                    default: header("Location: /");
                }
            }
        }
    }
    public function __destruct() {
        ob_end_flush();
    }
    public function showGeneral() {
        $_SESSION['referrer'] = '/';
        $this->view->viewGeneral($this->getUserId(), $this->isSuperuser(), $this->startTime);
    }
    public function showPost() {
        $postId = array_shift($this->requestUriArray);
        if ($postId < 1) {
          header ("Location: /404");
        } else {
            $postId = clearInt($postId);
            $this->view->viewPost($postId, $this->getUserId(), $this->isSuperuser(), $this->startTime, $this->ratingController->isUserChangedPostRating($this->getUserId(), $postId));
        }
    }
    public function show404() {
        $this->view->view404($this->getUserId(), $this->isSuperuser(), $this->startTime);
    }
    public function showLogin() {
        $this->view->viewLogin($this->getUserId(), $this->isSuperuser(), $this->startTime);

    }
    public function showReg() {
        $this->view->viewReg($this->getUserId(), $this->isSuperuser(), $this->startTime);
    }
    public function showAddpost() {
        $_SESSION['referrer'] = '/addpost';
        if (!$this->getUserId()) {
            header ("Location: /login");
        }
        $this->view->viewAddpost($this->getUserId(), $this->isSuperuser(), $this->startTime, $this->maxSizeOfUploadImage);
    }
    public function showStab() {
        @set_time_limit(6000);
        $_SESSION['referrer'] = '/stab';
        if (!$this->isSuperuser()) {
            header ("Location: /login");
        }
        $numberOfLoopIterations = $_GET['number'] ?? 10;
        $numberOfLoopIterations = clearInt($numberOfLoopIterations);
        $this->stabService->stabDb($numberOfLoopIterations);
        $errors = $this->stabService->getErrors();
        $this->view->viewStab($this->getUserId(), $this->isSuperuser(), $numberOfLoopIterations, $errors, $this->startTime);
    }
    public function showPosts() {
        $numberOfPosts = $_GET['number'] ?? 25;
        $numberOfPosts = clearInt($numberOfPosts);
        $pageOfPosts = $_GET['page'] ?? 1;
        $pageOfPosts = clearInt($pageOfPosts);
        $_SESSION['referrer'] = "/posts";
        $this->view->viewPosts($this->getUserId(), $this->isSuperuser(), $this->startTime, $numberOfPosts, $pageOfPosts);
    }
    public function showCabinet() {
        $userId = $_GET['user'] ?? $this->getUserId();
        $user = $this->userController->getUserInfoById($userId);
        if ($this->getUserId() == $userId || $this->isSuperuser()) {
            $showEmailAndLinksToDelete = true;
            if ($this->getUserId() == $userId) {
                $linkToChangeUserInfo = true;
            }
        }
        $showEmailAndLinksToDelete ?? false;
        $linkToChangeUserInfo ?? false;
        $_SESSION['referrer'] = "/cabinet";
        $this->view->viewCabinet($user, $showEmailAndLinksToDelete, $linkToChangeUserInfo, $this->getUserId(), $this->isSuperuser(), $this->startTime);
    }
    public function showSearch() {
        $search = $_GET['search'] ?? '';
        $_SESSION['referrer'] = "/search/?search=$search";
        $this->view->viewSearch($this->getUserId(), $this->isSuperuser(), $this->startTime, $search);
    }
    public function showAdmin() {
        $_SESSION['referrer'] = "/admin";
        if (empty($this->isSuperuser())) {
            if(!empty($this->getUserId())) {
                header("Location: /");
            } else {
                header("Location: /login");
            }
        } else {
            $this->view->viewAdmin($this->getUserId(), $this->isSuperuser(), $this->startTime);
        }
    }
    public function showAdminUsers() {
        $_SESSION['referrer'] = "/adminusers";
        if (empty($this->isSuperuser())) {
            if(!empty($this->getUserId())) {
                header("Location: /");
            } else {
                header("Location: /login");
            }
        } else {
            $numberOfUsers = $_GET['number'] ?? 50;
            $numberOfUsers = clearInt($numberOfUsers);
            $pageOfUsers = $_GET['page'] ?? 1;
            $pageOfUsers = clearInt($pageOfUsers);
            $this->view->viewAdminUsers($this->getUserId(), $this->isSuperuser(), $this->startTime, $numberOfUsers, $pageOfUsers);
        }
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