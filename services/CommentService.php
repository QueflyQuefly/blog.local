<?php
spl_autoload_register(function ($class) {
    require "$class.php";
});

class CommentService {
    public $error;
    private $_db;
    public function __construct() {
        $this->_db = DbService::getInstance();
    }
    function getCommentsByPostId($postId) {
        $comments = [];
        try {
            $postId = clearInt($postId);
            $sql = "SELECT c.comment_id, c.post_id, c.user_id, c.date_time, 
                    c.content, c.rating, u.fio as author
                    FROM comments c 
                    JOIN users u ON u.user_id = c.user_id 
                    WHERE c.post_id = $postId 
                    ORDER BY c.date_time DESC";// LIMIT 30
            $stmt = $this->_db->query($sql);
            if ($stmt != false) {
                while ($result = $stmt->fetch(PDO::FETCH_ASSOC)) {
                    $comments[] = $result;
                }
            }
        } catch (PDOException $e) {
            $this->error = $e->getMessage();
        }
        return $comments;
    }
    function addComment($postId, $commentAuthorId, $commentContent, $commentDate = 0, $rating = 0) {
        try {
            $authorId = clearInt($commentAuthorId);
            $postId = clearInt($postId);
            $commentDate = clearInt($commentDate);
            $commentDate = $commentDate ?: time();
            $content = clearStr($commentContent);
            $content = $this->_db->quote($content);
            $authorId = $this->_db->quote($authorId);
            $rating = clearInt($rating);

            $sql = "INSERT INTO comments (post_id, user_id, date_time, content, rating) 
                    VALUES($postId, $authorId, $commentDate, $content, $rating);";
            if (!$this->_db->exec($sql)) {
                return false;
            }
            $sql = "UPDATE additional_info_posts SET count_comments = count_comments+1 
                    WHERE post_id = $postId;";
            if (!$this->_db->exec($sql)) {
                return false;
            }
        } catch (PDOException $e) {
            $this->error = $e->getMessage();
        }
        return true;
    }
    function getCommentsByUserId($userId) {
        $comments = [];
        try {
            
            $sql = "SELECT c.comment_id, c.post_id, c.date_time, c.content, c.user_id,
                    c.rating, u.fio as author 
                    FROM comments c 
                    JOIN users u USING(user_id) 
                    WHERE c.user_id = $userId;";// LIMIT 30
            $stmt = $this->_db->query($sql);
            if ($stmt != false) {
                while ($result = $stmt->fetch(PDO::FETCH_ASSOC)) {
                    $comments[] = $result;
                }
            }
        } catch (PDOException $e) {
            $this->error = $e->getMessage();
        }
        return $comments;
    }
    function deleteCommentById($deleteCommentId) {
        $deleteCommentId = clearInt($deleteCommentId);
        try {
            $this->_db->beginTransaction();
            /* Уменьшаю на 1 количество комментариев у поста, которому принадлежит комментарий */
            $sql = "UPDATE additional_info_posts SET count_comments = count_comments-1 
                    WHERE post_id = (SELECT post_id FROM comments WHERE comment_id = $deleteCommentId);";
            $this->_db->exec($sql);
            /* Удаляю комментарий */
            $sql = "DELETE FROM comments WHERE comment_id = $deleteCommentId;";
            $this->_db->exec($sql);
            $this->_db->commit();
        } catch (PDOException $e) {
            $this->_db->rollBack();
            $this->error = $e->getMessage();
            return false;
        }
        return true;
    }
}