<?php

class ViewNested{
    public function viewHeadAndMenuWithDescLayouts($sessionUserId, $isSuperuser, $pageTitle, $pageDescription) {
        require "layouts/head.layout.php";
        require "layouts/menu.layout.php";
        require "layouts/description.layout.php";
    }
    public function viewHeadAndMenuLayouts($sessionUserId, $isSuperuser, $pageTitle) {
        require "layouts/head.layout.php";
        require "layouts/menu.layout.php";
    }
    public function viewFooterLayout($startTime) {
        require "layouts/endbody.layout.php";
    }
    public function viewLoginLayout($msg) {
        require "layouts/login.layout.php";
    }
    public function viewRegLayout($isSuperuser, $msg) {
        if ($isSuperuser) {
            $forAdmin = "<label><input type='checkbox' name='add_admin'>Зарегистрировать как админа</label>";
        }
        require "layouts/reg.layout.php";
    }
    public function viewAddpostLayout($maxSizeOfUploadImage, $msg) {
        require "layouts/addpost.layout.php";
    }
    public function viewStabLayout() {
        require "layouts/stab.layout.php";
    }
    public function viewPaginationLayout($nameOfPath, $numberOfPosts, $page) {
        require "layouts/pagination.layout.php";
    }
    public function viewSearchLayout($search) {
        require "layouts/search.layout.php";
    }
    public function viewUserLayout($user, $isSuperuser, $searh) {
        require "layouts/user.layout.php";
    }
    public function viewAdminLayout() {
        require "layouts/admin.layout.php";
    }
    public function viewChangeUserInfo($user, $msg) {
        require "layouts/changeuserinfo.layout.php";
    }
    public function viewError($sessionUserId, $isSuperuser, $pageTitle, $pageDescription, $startTime) {
        $this->viewHeadAndMenuWithDescLayouts($sessionUserId, $isSuperuser, $pageTitle, $pageDescription);
        echo "<a class='link' href='{$_SESSION['referrer']}'>Вернуться назад</a><br><br>";
        echo "<a class='link' href='/'>Вернуться на главную</a>";
        $this->viewFooterLayout($startTime);
    }
}