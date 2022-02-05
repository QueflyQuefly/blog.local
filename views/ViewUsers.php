<?php

class ViewUsers {
    private $_pathToLayouts;
    public function __construct() {
        $this->_pathToLayouts = dirname(__DIR__) . DIRECTORY_SEPARATOR . 'layouts' . DIRECTORY_SEPARATOR;
    }
    public function renderUsers($users, $nameOfPath, $isSuperuser = false) {
        if (empty($users)) {
            echo "\n<div class='contentsinglepost'><p class='center'style='color: rgb(150, 20, 20);'>Нет пользователей для отображения</p></div>\n"; 
        } else {
            foreach ($users as $user) {
                $user['date_time'] = date("d.m.Y в H:i", $user['date_time']);
                $additionalInfo = '';
                $linkToDelete = '';
                if (!empty($isSuperuser)) {
                    $additionalInfo = "
                        <p class='posttitle'> Категория: {$user['rights']}</p>
                        <p class='posttitle'>ID: {$user['user_id']} </p>
                        <p class='posttitle'>E-mail: {$user['email']}</p>
                    ";
                    $linkToDelete = "
                        <input type='submit' form='deleteUserById{$user['user_id']}' value='Удалить {$user['rights']}-a' class='link' id='right'>
                        <form id='deleteUserById{$user['user_id']}' action='' method='post'>
                            <input type='hidden' value='{$user['user_id']}' name='deleteUserById'>
                        </form>
                    ";

                }
                include $this->_pathToLayouts . 'user.layout.php';
            }
        }
    }
}