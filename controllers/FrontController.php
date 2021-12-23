<?php
session_start();
$pathToUserService = dirname(__DIR__) . DIRECTORY_SEPARATOR . 'models' . DIRECTORY_SEPARATOR . 'UserService.php';
require $pathToUserService;

function clearInt($int) {
    return abs((int) $int);
}
function clearStr($str) {
    return trim(strip_tags($str));
}

class FrontController {
    public $sessionUserId;
    public $isSuperuser;
    private $userService;
    public function __construct(){
        $twoDaysInSeconds = 60*60*24*2;
        header("Cache-Control: max-age=$twoDaysInSeconds");
        header("Cache-Control: must-revalidate");

        $this->userService = new UserService();
        if (!empty($_COOKIE['user_id'])) {
            $this->sessionUserId = $_COOKIE['user_id'];
        } elseif (!empty($_SESSION['user_id'])) {
            $this->sessionUserId = $_SESSION['user_id'];
        }
        if (!empty($this->sessionUserId) && $this->userService->getUserInfoById($this->sessionUserId, 'rights') === RIGHTS_SUPERUSER) {
            $this->isSuperuser = true;
        }
    }
}