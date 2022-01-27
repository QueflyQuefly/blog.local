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
        $pageTitle = 'Главная - просто Блог';
        $pageDescription = 'Прозошла ошибка 404: информация не найдена';            
        
        require "layouts/head.layout.php";
        require "layouts/menu.layout.php";
        require "layouts/description.layout.php";
        echo "<a class='link' href='/'>Вернуться на главную</a>";
        require "layouts/endbody.layout.php";
    }
}