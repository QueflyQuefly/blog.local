<?php

class View extends ViewNested{
    private $_postController, $_commentController, $_userController, $_subscribeController;
    public function __construct(PostController $postController, CommentController $commentController, UserController $userController, SubscribeController $subscribeController) {
        $this->_postController = $postController;
        $this->_commentController = $commentController;
        $this->_userController = $userController;
        $this->_subscribeController = $subscribeController;
    }
    public function viewGeneral($sessionUserId, $isSuperuser, $startTime) {
        $pageTitle = 'Просто Блог - Главная';
        $pageDescription = 'Наилучший источник информации по теме "Путешествия"';
        $showButtonSeeAll = true;
        parent::viewHeadAndMenuWithDescLayouts($sessionUserId, $isSuperuser, $pageTitle, $pageDescription);
        $this->_postController->showLastPosts(10, $isSuperuser, 0, $showButtonSeeAll);
        $this->_postController->showMoreTalkedPosts(3, $isSuperuser);
        parent::viewFooterLayout($startTime);
    }
    public function view404($sessionUserId, $isSuperuser, $startTime) {
        $pageTitle = 'Ошибка - Просто Блог';
        $pageDescription = 'Произошла ошибка 404: информация не найдена';            
        parent::viewHeadAndMenuWithDescLayouts($sessionUserId, $isSuperuser, $pageTitle, $pageDescription);
        echo "<a class='link' href='{$_SESSION['referrer']}'>Вернуться назад</a><br><br>";
        echo "<a class='link' href='/'>Вернуться на главную</a>";
        parent::viewFooterLayout($startTime);
    }
    public function viewPost($postId, $sessionUserId, $isSuperuser, $startTime, $isUserChangedPostRating) {
        $pageTitle = 'Просмотр поста - Просто Блог';
        parent::viewHeadAndMenuLayouts($sessionUserId, $isSuperuser, $pageTitle);
        $tags = $this->_postController->getTagsByPostId($postId);
        $this->_postController->showPost($postId, $tags, $isSuperuser, $isUserChangedPostRating);
        $this->_commentController->showCommentsByPostId($postId, $isSuperuser);
        parent::viewFooterLayout($startTime);
    }
    public function viewLogin($sessionUserId, $isSuperuser, $startTime, $msg) {
        $pageTitle = 'Вход - Просто Блог';          
        parent::viewHeadAndMenuLayouts($sessionUserId, $isSuperuser, $pageTitle);
        parent::viewLoginLayout($msg);
        parent::viewFooterLayout($startTime);
    }
    public function viewReg($sessionUserId, $isSuperuser, $startTime, $msg) {
        $pageTitle = 'Регистрация - Просто Блог';          
        parent::viewHeadAndMenuLayouts($sessionUserId, $isSuperuser, $pageTitle);
        parent::viewRegLayout($isSuperuser, $msg);
        parent::viewFooterLayout($startTime);
    }
    public function viewAddpost($sessionUserId, $isSuperuser, $startTime, $maxSizeOfUploadImage, $msg) {
        $pageTitle = 'Добавление поста - Просто Блог';
        parent::viewHeadAndMenuLayouts($sessionUserId, $isSuperuser, $pageTitle);
        parent::viewAddpostLayout($maxSizeOfUploadImage, $msg);
        parent::viewFooterLayout($startTime);
    }
    public function viewStab($sessionUserId, $isSuperuser, $numberOfLoopIterations, $errors, $startTime) {
        $pageTitle = 'Стаб - Просто Блог';
        $pageDescription = '';
        if (empty($errors)) {
            $pageDescription = "Цикл выполнился столько раз: $numberOfLoopIterations. Нет сообщений об ошибках </p> <p> 
                    В одной итерации цикла создается новый пользователь, новый пост от этого пользователя и несколько 
                    (до 14) комментариев к этому посту.<br> Создание 100 постов занимает примерно 10 секунд </p>";
        } else {
            foreach ($errors as $error) {
                $pageDescription .= $error . "<br>";
            }
        }
        parent::viewHeadAndMenuWithDescLayouts($sessionUserId, $isSuperuser, $pageTitle, $pageDescription);
        parent::viewStabLayout();
        parent::viewFooterLayout($startTime);
    }
    public function viewCabinet($user, $showEmailAndLinksToDelete, $linkToChangeUserInfo, $sessionUserId, $isSuperuser, $startTime, $msg) {
        $pageTitle = $user['fio'] . " - Просто блог";
        $pageDescription = $user['fio'];
        if ($showEmailAndLinksToDelete) {
            $pageDescription .= "<p>E-mail: {$user['email']}</p>";
        }
        if ($user['rights'] === RIGHTS_SUPERUSER) {
            $pageDescription .= "<p style='font-size: 13pt; color: green;'>Является администратором этого сайта</p>";
        }
        if (!empty($linkToChangeUserInfo)) {
            if (!isset($_GET['changeinfo'])) {
                $pageDescription .= "<a class='link' style='font-size:13pt; margin-left:30vmin' title='Изменить параметры профиля' 
                        href='/cabinet/?changeinfo'>Изменить параметры профиля</a>\n";
            } else {
                $pageDescription .= "<a class='link' style='font-size:13pt; margin-left:30vmin' title='Отмена' 
                        href='/cabinet'>Отмена</a>\n";
            }
        } elseif (!empty($sessionUserId) && $sessionUserId != $user['user_id']) {
            if (!$this->_subscribeController->isSubscribedUser($sessionUserId, $user['user_id'])) {
                $pageDescription .= "
                        <input type='submit' style='font-size:13pt;' form='subscribe' value='Подписаться' class='link'>
                        <form id='subscribe' action='' method='post'>
                            <input type='hidden' name='subscribe'>
                        </form>
                        ";
            } else {
                $pageDescription .= "
                        <input type='submit' style='font-size:13pt;' form='subscribe' value='Отписаться' class='link'>
                        <form id='subscribe' action='' method='post'>
                            <input type='hidden' name='unsubscribe'>
                        </form>
                        ";
            }
        }
        parent::viewHeadAndMenuWithDescLayouts($sessionUserId, $isSuperuser, $pageTitle, $pageDescription);
        if (!empty($linkToChangeUserInfo) && isset($_GET['changeinfo'])) {
            parent::viewChangeUserInfo($user, $msg);
        }
        if (!empty($msg)) {
            echo "<p class='ok' style='margin: -5vmin 0 0 -75vmin; font-size: 12pt;'>" . clearStr($msg) . "</p>";
        }
        echo "<br><div class='contentsinglepost'><p class='posttitle'>Посты от автора &copy; {$user['fio']}:</p></div>";
        $this->_postController->showPostsByUserId($user['user_id'], $showEmailAndLinksToDelete);

        echo "<div class='contentsinglepost'><p class='posttitle'>Комментарии автора &copy; {$user['fio']}:</p></div>";
        $this->_commentController->showCommentsByUserId($user['user_id'], $showEmailAndLinksToDelete);

        echo "<div class='contentsinglepost'><p class='posttitle'>Оценённые посты &copy; ${user['fio']}:</p></div>";
        $this->_postController->showLikedPostsByUserId($user['user_id'], $showEmailAndLinksToDelete);

        echo "<div class='contentsinglepost'><p class='posttitle'>Понравившиеся комментарии &copy; ${user['fio']}:</p></div>";
        $this->_commentController->showLikedCommentsByUserId($user['user_id'], $showEmailAndLinksToDelete);
        parent::viewFooterLayout($startTime);
    }
    public function viewPosts($sessionUserId, $isSuperuser, $startTime, $numberOfPosts, $pageOfPosts) {
        $pageTitle = 'Все посты - Просто блог';
        $pageDescription = 'Наилучший источник информации по теме "Путешествия"';
        parent::viewHeadAndMenuWithDescLayouts($sessionUserId, $isSuperuser, $pageTitle, $pageDescription);
        parent::viewPaginationLayout('posts', $numberOfPosts, $pageOfPosts);
        $this->_postController->showLastPosts($numberOfPosts,  $isSuperuser, $pageOfPosts * $numberOfPosts - $numberOfPosts);
        parent::viewFooterLayout($startTime);
    }
    public function viewSearch($sessionUserId, $isSuperuser, $startTime, $searchWords) {
        $pageTitle = 'Поиск - Просто блог';
        $pageDescription = 'Поиск поста или пользователя';
        parent::viewHeadAndMenuWithDescLayouts($sessionUserId, $isSuperuser, $pageTitle, $pageDescription);
        parent::viewSearchLayout($searchWords);
        echo "<div class='searchdescription'><div class='posttext'>Поиск поста осуществляется по заголовку, автору или по хештэгу, и по его содержимому, если ищете словосочетание</div>\n"; 
        $description = "<div class='posttext'>Поиск автора осуществляется по ФИО</div>\n</div>";
        if (!empty($isSuperuser)) {
            $description = "<div class='posttext'>Поиск автора осуществляется по ФИО и логину(email)</div>\n</div>"; 
        }
        echo $description;
        if (!empty($searchWords)) {
            echo "<div class='singleposttext'><p class='center' style='font-size: 15pt;'>Результаты поиска (посты): </p>\n</div>"; 
            $this->_postController->showSearchPosts($searchWords, $isSuperuser);
            echo "<div class='singleposttext'><p class='center' style='font-size: 15pt;'>Результаты поиска (пользователи): </p>\n</div>"; 
            $this->_userController->showSearchUsers($searchWords, $isSuperuser);
        }
        parent::viewFooterLayout($startTime);
    }
    public function viewAdmin($sessionUserId, $isSuperuser, $startTime) {
            $pageTitle = 'Администрирование - Просто Блог';          
            parent::viewHeadAndMenuLayouts($sessionUserId, $isSuperuser, $pageTitle);
            parent::viewAdminLayout();
            parent::viewFooterLayout($startTime);
    }
    public function viewAdminUsers($sessionUserId, $isSuperuser, $startTime, $numberOfUsers, $pageOfUsers) {
        $pageTitle = 'Все пользователи - Просто блог';
        $pageDescription = 'Управление пользователями';
        parent::viewHeadAndMenuWithDescLayouts($sessionUserId, $isSuperuser, $pageTitle, $pageDescription);
        parent::viewPaginationLayout('adminusers', $numberOfUsers, $pageOfUsers);
        $this->_userController->showAdminUsers($isSuperuser, $numberOfUsers, $pageOfUsers);
        parent::viewFooterLayout($startTime);
    }
}