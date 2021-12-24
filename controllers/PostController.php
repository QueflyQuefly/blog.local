<?php
spl_autoload_register(function ($class) {
    if (strpos($class, 'Controller')) {
        $pathToClass = '';
    }
    if (strpos($class, 'Service')) {
        $pathToClass = 'models' . DIRECTORY_SEPARATOR;
    }
    if (strpos($class, 'View')) {
        $pathToClass = 'viewes' . DIRECTORY_SEPARATOR;
    }
    require_once $pathToClass . $class . '.php';
});


class PostController {
    private $postService, $viewPosts;
    public function __construct(){
        $this->postService = new PostService();
        $this->viewPosts = new ViewPosts();
    }
    public function showLastPosts($numberOfPosts) {
        $posts = $this->postService->getLastPosts($numberOfPosts);
        return $this->viewPosts->renderPosts($posts);
    }
    public function showMoreTalkedPosts($numberOfPosts) {
        $moreTalkedPosts = $this->postService->getMoreTalkedPosts($numberOfPosts);
        return $this->viewPosts->renderMoreTalkedPosts($moreTalkedPosts);
    }
    public function deletePostById($id) {
        $deletePostId = clearInt($id);
        if ($deletePostId !== '') {
            $this->postService->deletePostById($deletePostId);
            header("Location: /");
        }
    }
}
//
?>