<?php

class View extends ViewNested{
    private $postController, $commentController;
    public function __construct(PostController $postController, CommentController $commentController) {
        $this->postController = $postController;
        $this->commentController = $commentController;
    }
    public function viewGeneral($sessionUserId, $isSuperuser, $startTime) {
        $pageTitle = 'Просто Блог - Главная';
        $pageDescription = 'Наилучший источник информации по теме "Путешествия"';
        $showButtonSeeAll = true;
        parent::viewHeadAndMenuWithDescLayouts($sessionUserId, $isSuperuser, $pageTitle, $pageDescription);
        $this->postController->showPosts(10, $isSuperuser, 0, $showButtonSeeAll);
        $this->postController->showMoreTalkedPosts(3, $isSuperuser);
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
        $this->postController->showPost($postId, $isSuperuser, $isUserChangedPostRating);
        $this->postController->showTagsByPostId($postId);
        $this->commentController->showCommentsByPostId($postId, $isSuperuser);
        parent::viewFooterLayout($startTime);
    }
    public function viewLogin($sessionUserId, $isSuperuser, $startTime) {
        $pageTitle = 'Вход - Просто Блог';          
        parent::viewHeadAndMenuLayouts($sessionUserId, $isSuperuser, $pageTitle);
        parent::viewLoginLayout();
        parent::viewFooterLayout($startTime);
    }
    public function viewReg($sessionUserId, $isSuperuser, $startTime) {
        $pageTitle = 'Регистрация - Просто Блог';          
        parent::viewHeadAndMenuLayouts($sessionUserId, $isSuperuser, $pageTitle);
        parent::viewRegLayout($isSuperuser);
        parent::viewFooterLayout($startTime);
    }
    public function viewAddpost($sessionUserId, $isSuperuser, $startTime, $maxSizeOfUploadImage) {
        $pageTitle = 'Добавление поста - Просто Блог';
        parent::viewHeadAndMenuLayouts($sessionUserId, $isSuperuser, $pageTitle);
        parent::viewAddpostLayout($maxSizeOfUploadImage);
        parent::viewFooterLayout($startTime);
    }
    public function viewStab($sessionUserId, $isSuperuser, $numberOfLoopIterations, $errors, $startTime) {
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
        parent::viewHeadAndMenuWithDescLayouts($sessionUserId, $isSuperuser, $pageTitle, $pageDescription);
        parent::viewStabLayout();
        parent::viewFooterLayout($startTime);
    }
    public function viewCabinet($user, $showEmailAndLinksToDelete, $linkToChangeUserInfo, $sessionUserId, $isSuperuser, $startTime) {
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
        } elseif ($sessionUserId != $user['user_id']) {
            if (!$this->subscribeController->isSubscribedUser($user['user_id'], $sessionUserId)) {
                $pageDescription .= "<p><a class='link' title='Подписаться' style='font-size:14pt' 
                        href='/cabinet/?user={$user['user_id']}&subscribe'>Подписаться</a></p>";
            } else {
                $pageDescription .= "<p><a class='link' title='Отменить подписку' style='font-size:14pt' 
                        href='/cabinet/user={$user['user_id']}&unsubscribe'>Отменить подписку</a></p>";
            }
        }
        parent::viewHeadAndMenuWithDescLayouts($sessionUserId, $isSuperuser, $pageTitle, $pageDescription);
        $this->postController->showPostsByUserId($user['user_id'], $showEmailAndLinksToDelete);
        parent::viewFooterLayout($startTime);
    }
    public function viewPosts($sessionUserId, $isSuperuser, $startTime, $numberOfPosts, $pageOfPosts) {
        $pageTitle = 'Все посты - Просто блог';
        $pageDescription = 'Наилучший источник информации по теме "Путешествия"';
        parent::viewHeadAndMenuWithDescLayouts($sessionUserId, $isSuperuser, $pageTitle, $pageDescription);
        parent::viewPaginationLayout('posts', $numberOfPosts, $pageOfPosts);
        $this->postController->showPosts($numberOfPosts,  $isSuperuser, $pageOfPosts * $numberOfPosts - $numberOfPosts);
        parent::viewFooterLayout($startTime);
    }
}