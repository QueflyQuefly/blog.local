<?php

class View404 {
    public function view($sessionUserId, $isSuperuser) {
        $pageTitle = 'Главная - просто Блог';
        $pageDescription = 'Прозошла ошибка 404: информация не найдена';            
        
        require "layouts/head.layout.php";
        require "layouts/menu.layout.php";
        require "layouts/description.layout.php";
        echo "<a class='link' href='/'>Вернуться на главную</a>";
        require "layouts/endbody.layout.php";
    }
}