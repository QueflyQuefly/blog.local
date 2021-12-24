<?php
spl_autoload_register(function ($class) {
    require "$class.php";
});

class RatingPostService {
    public $error;
    private $_db;
    public function __construct() {
        $this->_db = DbService::getInstance();
    }
    public function changePostRating($userId, $postId, $rating){
        try {
            $userId = clearInt($userId);
            $postId = clearInt($postId);
            $rating = clearInt($rating);
            if ($rating) {
                $sql = "INSERT INTO rating_posts (post_id, user_id, rating) 
                        VALUES($postId, $userId, $rating);";
                if (!$this->_db->exec($sql)) {
                    return false;
                }
                $sql = "SELECT avg(rating) as average_rating, COUNT(*) as count_rating 
                        FROM rating_posts WHERE post_id = $postId;";
                $stmt = $this->_db->query($sql);
                if (!$stmt) {
                    return false;
                }
                $result = $stmt->fetch(PDO::FETCH_ASSOC);
                $countRatings = $result['count_rating'];
                $postRating = $result['average_rating'];
                $postRating = round($postRating, 1, PHP_ROUND_HALF_UP);
                
                $sql = "UPDATE additional_info_posts SET rating = $postRating,
                        count_ratings = $countRatings
                        WHERE post_id = $postId;";
                if (!$this->_db->exec($sql)) {
                    return false;
                }
            }
        } catch (PDOException $e) {
            $this->error = $e->getMessage();
        }
        return true;
    }
    public function isUserChangesPostRating($userId, $postId){
        try {
            $userId = clearInt($userId);
            $postId = clearInt($postId);
            $sql = "SELECT rating FROM rating_posts 
                    WHERE user_id = $userId 
                    AND post_id = $postId;";
            $stmt = $this->_db->query($sql);
            if ($stmt != false) {
                $result = $stmt->fetch(PDO::FETCH_ASSOC);
                if (!empty($result)) {
                    return true;
                }
            }
        } catch (PDOException $e) {
            $this->error = $e->getMessage();
        }
        return false;
    }
}