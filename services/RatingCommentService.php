<?php

class RatingCommentService {
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
    public function changeCommentRating($rating, $userId, $commentId){
        try {
            $commentId = clearInt($commentId);
            $this->_db->beginTransaction();
    
            if ($rating === 'like') {
                $sql = "INSERT INTO rating_comments (user_id, comment_id) 
                        VALUES($userId, $commentId);";
                $this->_db->exec($sql);
    
                $sql = "UPDATE comments SET rating = rating+1 WHERE comment_id = $commentId;";
                $this->_db->exec($sql); 
            }
            if ($rating === 'unlike') {
                $sql = "DELETE FROM rating_comments WHERE comment_id = $commentId;";
                $this->_db->exec($sql);
    
                $sql = "UPDATE comments SET rating = rating-1 WHERE comment_id = $commentId;";
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
    public function isUserChangedCommentRating($userId, $commentId){
        try {
            $userId = clearInt($userId);
            $commentId = clearInt($commentId);
            $sql = "SELECT user_id FROM rating_comments 
                    WHERE user_id = $userId AND comment_id = $commentId;";
            $stmt = $this->_db->query($sql);
            if ($stmt != false) {
                $result = $stmt->fetch(PDO::FETCH_ASSOC);
                if ($result) {
                    return true;
                }
            } else {
                throw new Exception("Запрос sql = $sql не был выполнен");
            }
        } catch (PDOException $e) {
            $this->error = $e->getMessage();
        }
        return false;
    }
}