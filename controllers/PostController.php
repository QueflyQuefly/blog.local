<?php
session_start();
$pathToPostService = dirname(__DIR__) . DIRECTORY_SEPARATOR . 'models' . DIRECTORY_SEPARATOR . 'PostService.php';
require $pathToPostService;

class PostController {
    public $sessionUserId;
    public $isSuperuser;
    public $postService;
    public function __construct(){
        $twoDaysInSeconds = 60*60*24*2;
        header("Cache-Control: max-age=$twoDaysInSeconds");
        header("Cache-Control: must-revalidate");
            
        $this->postService = new PostService();

        if (!empty($_COOKIE['user_id'])) {
            $this->sessionUserId = $_COOKIE['user_id'];
        } elseif (!empty($_SESSION['user_id'])) {
            $this->sessionUserId = $_SESSION['user_id'];
        }
        /* if (!empty($this->sessionUserId) && getUserInfoById($this->sessionUserId, 'rights') === RIGHTS_SUPERUSER) {
            $this->isSuperuser = true;
        } */
    }
    public function getIndexPosts($numberOfPosts) {
        $posts = $this->postService->getPostsByNumber($numberOfPosts);
        return $posts;
    }
    public function getMoreTalkedPosts($numberOfPosts) {
        $moreTalkedPosts = $this->postService->getMoreTalkedPosts($numberOfPosts);
        return $moreTalkedPosts;
    }
    public function deletePostById ($id) {
        $deletePostId = clearInt($id);
        if (!empty($isSuperuser)) {
            if ($deletePostId !== '') {
                deletePostById($deletePostId);
                header("Location: /");
            }
        }
    }
    public function exitUser () {
        $_SESSION['user_id'] = false;
        setcookie('user_id', '0', 1);
        header("Location: /");
    }
}

if (isset($_GET['deletePostById'])) {

}
if (isset($_GET['exit'])) {

}

?>