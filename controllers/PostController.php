<?php
$pathToPostService = dirname(__DIR__) . DIRECTORY_SEPARATOR . 'models' . DIRECTORY_SEPARATOR . 'PostService.php';
require $pathToPostService;
require 'FrontController.php';
class PostController {
    public $sessionUserId;
    private $isSuperuser, $postService, $frontController;
    public function __construct(){

        $this->postService = new PostService();
        $this->frontController = new FrontController();
        $this->isSuperuser = $this->frontController->isSuperuser;
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
        if (!empty($this->isSuperuser)) {
            if ($deletePostId !== '') {
                $this->postService->deletePostById($deletePostId);
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