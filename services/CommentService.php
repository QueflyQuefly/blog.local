<?php

class CommentService {
    public $error;
    private $_dbService, $_db;
    public function __construct() {
        $this->_dbService = DbService::getInstance();
        $this->_db = $this->_dbService->getConnectionToDb();
    }
    public function __destruct() {
        if (!empty($this->error)) {
            throw new Exception($this->error);
        }
    }
    public function addComment($postId, $commentAuthorId, $commentContent, $commentDate = 0, $rating = 0) {
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
                throw new Exception("Запрос sql = $sql не был выполнен");
                return false;
            }
            $sql = "UPDATE additional_info_posts SET count_comments = count_comments+1 
                    WHERE post_id = $postId;";
            if (!$this->_db->exec($sql)) {
                throw new Exception("Запрос sql = $sql не был выполнен");
                return false;
            }
        } catch (PDOException $e) {
            $this->error = $e->getMessage();
        }
        return true;
    }
    public function getCommentsByPostId($postId) {
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
            } else {
                throw new Exception("Запрос sql = $sql не был выполнен");
            }
        } catch (PDOException $e) {
            $this->error = $e->getMessage();
        }
        return $comments;
    }
    public function getCommentsByUserId($userId) {
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
            } else {
                throw new Exception("Запрос sql = $sql не был выполнен");
            }
        } catch (PDOException $e) {
            $this->error = $e->getMessage();
        }
        return $comments;
    }
    public function getLikedCommentsByUserId($userId) {
        $comments = [];
        try {
            
            $sql = "SELECT r.comment_id, r.user_id, r.post_id, 
                    u.fio as author, c.content, c.date_time, c.rating
                    FROM rating_comments r
                    JOIN comments c ON r.comment_id = c.comment_id
                    JOIN users u ON r.user_id = u.user_id
                    WHERE r.user_id = $userId;";// LIMIT 30
            $stmt = $this->_db->query($sql);
            if ($stmt != false) {
                while ($result = $stmt->fetch(PDO::FETCH_ASSOC)) {
                    $comments[] = $result;
                }
            } else {
                throw new Exception("Запрос sql = $sql не был выполнен");
            }
        } catch (PDOException $e) {
            $this->error = $e->getMessage();
        }
        return $comments;
    }
    public function deleteCommentById($deleteCommentId) {
        $deleteCommentId = clearInt($deleteCommentId);
        try {
            $this->_db->beginTransaction();
            /* Decrease the number of comments on the post that the comment belongs to by 1 */
            $sql = "UPDATE additional_info_posts SET count_comments = count_comments-1 
                    WHERE post_id = (SELECT post_id FROM comments WHERE comment_id = $deleteCommentId);";
            $this->_db->exec($sql);
            /* Delete comment */
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