<?php
session_start();

class FrontController {
    public $msg, $error;
    private $stabService, $view;
    private $userController, $postController, $commentController, $ratingController;

    public function __construct($requestUri, $_request, $startTime, FactoryMethod $factoryMethod) {
        ob_start();
        $this->startTime = $startTime;
        $this->userController = $factoryMethod->getUserController();
        $this->postController = $factoryMethod->getPostController();
        $this->commentController = $factoryMethod->getCommentController();
        $this->ratingController = $factoryMethod->getRatingController();
        $this->view = $factoryMethod->getView();
        $this->stabService = $factoryMethod->getStabService();

        $twoDaysInSeconds = 60*60*24*2;
        header("Cache-Control: max-age=$twoDaysInSeconds");
        header("Cache-Control: must-revalidate");
        $this->requestUriArray = explode('/', $requestUri);
        array_shift($this->requestUriArray);
        switch (array_shift($this->requestUriArray)) {
            case '': 
                $this->showGeneral(); 
                break;
            case 'viewpost': 
                $this->showPost(); 
                break;
            case 'login': 
                $this->showLogin(); 
                break;
            case 'reg': 
                $this->showReg(); 
                break;
            case 'addpost': 
                $this->showAddpost(); 
                break;
            case 'posts':
                $numberOfPosts = $_GET['number'] ?? 25;
                $pageOfPosts = $_GET['page'] ?? 1;
                $this->showPosts($numberOfPosts, $pageOfPosts);
                break;
            case 'stab': 
                @set_time_limit(6000);
                
                $numberOfLoopIterations = $_GET['number'] ?? 10;
                $this->showStab($numberOfLoopIterations);
                break;
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
        }
    }
    public function __destruct() {
        ob_end_flush();
    }
    public function showGeneral() {
        $_SESSION['referrer'] = '/';
        $pageTitle = 'Просто Блог - Главная';
        $pageDescription = 'Наилучший источник информации по теме "Путешествия"';
                  
        $this->view->viewHeadWithDesc($this->getUserId(), $this->isSuperuser(), $pageTitle, $pageDescription);
        $this->postController->showPosts(10, $this->isSuperuser(), true);
        $this->postController->showMoreTalkedPosts(3, $this->isSuperuser());
        $this->view->viewFooter($this->startTime);
    }
    public function showPost() {
        $postId = array_shift($this->requestUriArray);
        if ($postId < 1) {
          header ("Location: /404");
        } else {
            $postId = clearInt($postId);
            $pageTitle = 'Просмотр поста - Просто Блог';

            $this->view->viewHead($this->getUserId(), $this->isSuperuser(), $pageTitle);
            $this->postController->showPost(
                $postId, $this->isSuperuser(), 
                $this->ratingController->isUserChangedPostRating($this->getUserId(), $postId)
            );
            $this->postController->showTagsByPostId($postId);
            $this->commentController->showCommentsByPostId($postId, $this->isSuperuser());
            $this->view->viewFooter($this->startTime);
        }
    }
    public function show404() {
        $this->view->view404($this->getUserId(), $this->isSuperuser());
    }
    public function showLogin() {
        $pageTitle = 'Вход - Просто Блог';          
        $this->view->viewHead($this->getUserId(), $this->isSuperuser(), $pageTitle);
        $this->view->viewLogin();
        $this->view->viewFooter($this->startTime);
    }
    public function showReg() {
        $pageTitle = 'Регистрация - Просто Блог';          
        $this->view->viewHead($this->getUserId(), $this->isSuperuser(), $pageTitle);
        $this->view->viewReg($this->isSuperuser());
        $this->view->viewFooter($this->startTime);
    }
    public function showAddpost() {
        $_SESSION['referrer'] = '/addpost';
        if (!$this->getUserId()) {
            header ("Location: /login");
        }
        $pageTitle = 'Добавление поста - Просто Блог';
        $this->view->viewHead($this->getUserId(), $this->isSuperuser(), $pageTitle);
        $this->view->viewAddpost();
        $this->view->viewFooter($this->startTime);
    }
    public function showStab($numberOfLoopIterations) {
        $_SESSION['referrer'] = '/stab';
        if (!$this->isSuperuser()) {
            header ("Location: /login");
        }
        $this->stabService->stabDb($numberOfLoopIterations);
        $errors = $this->stabService->getErrors();
        $pageTitle = 'Стаб - Просто Блог';
        $pageDescription = '';
        if (empty($errors)) {
            $pageDescription = "Подключение к БД: успешно</p><p>Создано $numberOfLoopIterations новый(-ых) пользователь(-ей, -я), 
            $numberOfLoopIterations новый(-ых) пост(-ов, -а) и несколько(до 12) комментариев к каждому.<br>
            Создание 100 постов занимает примерно 10 секунд.";
        } else {
            foreach ($errors as $error) {
                $pageDescription .= $error . "<br>";
            }
        }
        $this->view->viewHeadWithDesc($this->getUserId(), $this->isSuperuser(), $pageTitle, $pageDescription);
        $this->view->viewStab();
        $this->view->viewFooter($this->startTime);
    }
    public function showPosts($numberOfPosts, $pageOfPosts) {
        $_SESSION['referrer'] = "posts.php";
        $pageTitle = 'Все посты - Просто блог';
        $pageDescription = 'Наилучший источник информации по теме "Путешествия"';
        $this->view->viewHeadWithDesc($this->getUserId(), $this->isSuperuser(), $pageTitle, $pageDescription);
        $this->view->viewPagination('posts', $numberOfPosts, $pageOfPosts);
        $this->postController->showPosts($numberOfPosts,  $this->isSuperuser(), $pageOfPosts * $numberOfPosts - $numberOfPosts);
        $this->view->viewFooter($this->startTime);
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