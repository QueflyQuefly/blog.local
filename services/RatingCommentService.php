<?php
spl_autoload_register(function ($class) {
    require "$class.php";
});

class RatingCommentService {
    public $error;
    private $_db;
    public function __construct() {
        $this->_db = DbService::getInstance();
    }
    public function changeCommentRating($rating, $comId, $postId, $userId){
        try {
            $comId = clearInt($comId);
            $postId = clearInt($postId);
            $this->_db->beginTransaction();
    
            if ($rating === 'like') {
                $sql = "INSERT INTO rating_comments (user_id, comment_id, post_id) 
                        VALUES($userId, $comId, $postId);";
                $this->_db->exec($sql);
    
                $sql = "UPDATE comments SET rating = rating+1 WHERE comment_id = $comId;";
                $this->_db->exec($sql); 
            }
            if ($rating === 'unlike') {
                $sql = "DELETE FROM rating_comments WHERE comment_id = $comId;";
                $this->_db->exec($sql);
    
                $sql = "UPDATE comments SET rating = rating-1 WHERE comment_id = $comId;";
                $this->_db->exec($sql); 
            }
            $this->_db->commit();
        } catch (PDOException $e) {
            $this->_db->rollBack();
            $this->error = $e->getMessage();
            return false;
        }
        return true;
    }
    public function isUserChangedCommentRating($userId, $comId){
        try {
            $userId = clearInt($userId);
            $comId = clearInt($comId);
            $sql = "SELECT user_id FROM rating_comments 
                    WHERE user_id = $userId AND comment_id = $comId;";
            $stmt = $this->_db->query($sql);
            if ($stmt != false) {
                $result = $stmt->fetch(PDO::FETCH_ASSOC);
                if ($result) {
                    return true;
                }
            }
        } catch (PDOException $e) {
            $this->error = $e->getMessage();
        }
        return false;
    }
}