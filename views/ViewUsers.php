<?php

class ViewUsers {
    private $pathToLayouts, $userView;
    public function __construct() {
        $this->pathToLayouts = dirname(__DIR__) . DIRECTORY_SEPARATOR . 'layouts' . DIRECTORY_SEPARATOR;
    }
    public function renderUsers($users, $nameOfPath, $isSuperuser = false) {
        if (empty($users)) {
            $this->userView = "\n<div class='contentsinglepost'><p class='center' style='color: rgb(200, 50, 50);'>Нет пользователей для отображения</p></div>\n"; 
        } else {
            foreach ($users as $user) {
                $user['date_time'] = date("d.m.Y в H:i", $user['date_time']);
                $linkToDelete = '';
                if (!empty($isSuperuser)) {
                    $linkToDelete = "
                    <p class='posttitle'> Категория: {$user['rights']}</p>
                    <p class='posttitle'>ID: {$user['user_id']} </p>
                    <p class='posttitle'>E-mail: {$user['email']}</p>
                    <p class='postdate'><object><a class='list' href='/$nameOfPath/?deleteUserById={$user['user_id']}'> Удалить {$user['rights']}-а</a></object>
                    ";
                }
                include $this->pathToLayouts . 'user.layout.php';
            }
        }
        echo $this->userView;
    }
}