<?php

class View {
    public function viewHeadWithDesc($sessionUserId, $isSuperuser, $pageTitle, $pageDescription) {
        require "layouts/head.layout.php";
        require "layouts/menu.layout.php";
        require "layouts/description.layout.php";
    }
    public function viewHead($sessionUserId, $isSuperuser, $pageTitle) {
        require "layouts/head.layout.php";
        require "layouts/menu.layout.php";
    }
    public function viewFooter($startTime) {
        require "layouts/endbody.layout.php";
    }
    public function view404($sessionUserId, $isSuperuser) {
        $pageTitle = 'Ошибка - Просто Блог';
        $pageDescription = 'Произошла ошибка 404: информация не найдена';            
        
        require "layouts/head.layout.php";
        require "layouts/menu.layout.php";
        require "layouts/description.layout.php";
        echo "<a class='link' href='{$_SESSION['referrer']}'>Вернуться назад</a><br><br>";
        echo "<a class='link' href='/'>Вернуться на главную</a>";
        require "layouts/endbody.layout.php";
    }
    public function viewLogin() {
        require "layouts/login.layout.php";
    }
    public function viewReg($isSuperuser) {
        if ($isSuperuser) {
            $forAdmin = "<label><input type='checkbox' name='add_admin'>Зарегистрировать как админа</label>";
        }
        require "layouts/reg.layout.php";
    }
    public function viewAddpost() {
        $maxSizeOfUploadImage = 4 * 1024 * 1024; // 4 megabytes
        require "layouts/addpost.layout.php";
    }
    public function viewStab() {
        require "layouts/stab.layout.php";
    }
    public function viewPagination($nameOfPath, $numberOfPosts, $page) {
        require "layouts/pagination.layout.php";
    }
}