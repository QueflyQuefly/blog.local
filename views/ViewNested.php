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
    public function viewLoginLayout() {
        require "layouts/login.layout.php";
    }
    public function viewRegLayout($isSuperuser) {
        if ($isSuperuser) {
            $forAdmin = "<label><input type='checkbox' name='add_admin'>Зарегистрировать как админа</label>";
        }
        require "layouts/reg.layout.php";
    }
    public function viewAddpostLayout($maxSizeOfUploadImage) {
        require "layouts/addpost.layout.php";
    }
    public function viewStabLayout() {
        require "layouts/stab.layout.php";
    }
    public function viewPaginationLayout($nameOfPath, $numberOfPosts, $page) {
        require "layouts/pagination.layout.php";
    }
}