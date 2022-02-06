<?php

class PostService {
    public $error;
    private $_dbService, $_db;
    public function __construct() {
        $this->_dbService = DbService::getInstance();
        $this->_db = $this->_dbService->getConnectionToDb();
    }
    public function addPost($title, $userId, $content) {
        try {
            $date = time();
            $userId = clearInt($userId);
            $titleQuote = $this->_db->quote($title);
            $contentQuote = $this->_db->quote($content);
            
            $sql = "INSERT INTO posts (title, user_id, date_time, content) 
                    VALUES($titleQuote, $userId, $date, $contentQuote);";
            if (!$this->_db->exec($sql)) {
                return false;
            }
            $lastPostId = $this->_db->lastInsertId();
    
            $sql = "INSERT INTO additional_info_posts 
                    (post_id, rating, count_comments, count_ratings) 
                    VALUES($lastPostId, 0.0, 0, 0);";
            if (!$this->_db->exec($sql)) {
                return false;
            }
            $allText = $title . " " . $content;
            if (strpos($allText, '#') !== false) {
                $regex = '/#\w+/um';
                preg_match_all($regex, $allText, $tags);
        
                $tags = $tags[0];
                foreach ($tags as $tag) {
                    $this->addTagsToPost($tag, $lastPostId);
                }
            }
            $sql = "SELECT s.user_id_want_subscribe, u.email
                    FROM subscriptions s 
                    JOIN users u ON u.user_id = s.user_id_want_subscribe
                    WHERE s.user_id = $userId";
            $stmt = $this->_db->query($sql);
            if ($stmt != false) {
                while ($result = $stmt->fetch(PDO::FETCH_ASSOC)) {
                    $toEmail = $result['email'];
                    $title = 'Prosto Blog';
                    $userService = new UserService();
                    $fio = $userService->getUserInfoById($userId, 'fio');
                    $message = "
                        <h2>$fio опубликовал новый пост:</h2> 
                        <p style='font-size:13pt;'>$title -
                        <a href='bloglocal.000webhostapp.com/viewsinglepost.php?viewpostById=$lastPostId'>Перейти к посту</a></p>
                        <pre>Это письмо отправлено вам, потому что вы подписаны на этого автора</pre>
                        <a href='bloglocal.000webhostapp.com/cabinet.php?user=$userId&unsubscribe'>Отписаться</a>
                        <p style='font-size:10pt;'>Необходимо прежде <a href='bloglocal.000webhostapp.com/cabinet.php?user=$userId&unsubscribe'>войти</a></p>
                    ";
                    $mailService = SendMailService::getInstance();
                    $mailService->sendMail($toEmail, $title, $message);
                }
            }
        } catch (PDOException $e) {
            $this->error = $e->getMessage();
        }
        return true;
    }
    public function getLastPosts($numberOfPosts, $lessThanMaxId = 0) {
        $posts = [];
        try {
            $numberOfPosts = clearInt($numberOfPosts);
            $lessThanMaxId = clearInt($lessThanMaxId);
            if (!empty($lessThanMaxId)) {
                $sql = "SELECT p.post_id, p.title, p.user_id, p.date_time, p.content, 
                        a.rating, a.count_comments, a.count_ratings, u.fio as author 
                        FROM posts p 
                        JOIN additional_info_posts a ON a.post_id = p.post_id 
                        JOIN users u ON p.user_id = u.user_id 
                        ORDER BY p.post_id DESC LIMIT $lessThanMaxId, $numberOfPosts;";
            } else {
                $sql = "SELECT p.post_id, p.title, p.user_id, p.date_time, p.content, 
                        a.rating, a.count_comments, a.count_ratings, u.fio as author 
                        FROM posts p 
                        JOIN additional_info_posts a ON a.post_id = p.post_id 
                        JOIN users u ON p.user_id = u.user_id 
                        ORDER BY p.post_id DESC LIMIT $numberOfPosts;";
            }
            $stmt = $this->_db->query($sql);
            if ($stmt != false) {
                while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                    if (!$row['count_comments']) {
                        $row['count_comments'] = 0;
                    }
                    $posts[] = $row;
                }
            }
        } catch (PDOException $e) {
            $this->error = $e->getMessage();
        }
        return $posts;
    }
    public function getPostsByUserId($userId) {
        $posts = [];
        try {
            $userId = clearInt($userId);
            $sql = "SELECT p.post_id, p.title, p.date_time, 
                    p.content, a.rating, a.count_comments, a.count_ratings, u.fio as author
                    FROM posts p JOIN users u ON p.user_id = u.user_id 
                    JOIN additional_info_posts a ON a.post_id = p.post_id
                    WHERE p.user_id = $userId ORDER BY p.date_time DESC;";
            $stmt = $this->_db->query($sql);
            if ($stmt != false) {
                while($post = $stmt->fetch(PDO::FETCH_ASSOC)) {
                    $posts[] = $post;
                }
            }
        } catch(PDOException $e) {
            $this->error = $e->getMessage();
        }
        return $posts;
    }
    public function getLikedPostsByUserId($userId) {
        $posts = [];
        try {
            $userId = clearInt($userId);
            $sql = "SELECT r.post_id, p.title, r.user_id, p.date_time, p.content, 
                    a.rating, a.count_comments, a.count_ratings, u.fio as author 
                    FROM rating_posts r 
                    JOIN posts p ON p.post_id = r.post_id 
                    JOIN users u ON u.user_id = r.user_id 
                    JOIN additional_info_posts a ON a.post_id = p.post_id 
                    WHERE r.user_id = $userId ORDER BY p.date_time DESC;";// LIMIT 30
            $stmt = $this->_db->query($sql);
            if ($stmt != false) {
                while ($result = $stmt->fetch(PDO::FETCH_ASSOC)) {
                    $posts[] = $result;
                }
            }
        } catch (PDOException $e) {
            $this->error = $e->getMessage();
        }
        return $posts;
    }
    public function getMoreTalkedPosts($numberOfPosts = 3) {
        $posts = [];
        try {
            $numberOfPosts = clearInt($numberOfPosts);
            $oneWeekInSeconds = 604800; //60 * 60 * 24 * 7
            $dateWeekAgo = time() - $oneWeekInSeconds;
            $sql = "SELECT DISTINCT c.post_id, p.title, p.date_time, p.content, a.rating, 
                    a.count_comments, a.count_ratings, u.fio as author 
                    FROM comments c 
                    JOIN posts p ON c.post_id = p.post_id 
                    JOIN additional_info_posts a ON a.post_id = p.post_id 
                    JOIN users u ON p.user_id = u.user_id 
                    WHERE c.date_time >= $dateWeekAgo 
                    ORDER BY a.count_comments DESC, a.count_ratings DESC, c.post_id DESC LIMIT 3;";
            $stmt = $this->_db->query($sql);
            if ($stmt != false) {
                while ($result = $stmt->fetch(PDO::FETCH_ASSOC)) {
                    $posts[] = $result;
                }
            }
        } catch (PDOException $e) {
            $this->error = $e->getMessage();
        }
        return $posts;
    }
    public function getPostForViewById($postId) {
        $post = [];
        try {
            $postId = clearInt($postId);
            $sql = "SELECT p.post_id, p.title, p.user_id, p.date_time, p.content, 
                    a.rating, a.count_comments, a.count_ratings, u.fio as author 
                    FROM posts p 
                    JOIN users u ON p.user_id = u.user_id 
                    JOIN additional_info_posts a ON a.post_id = p.post_id
                    WHERE p.post_id = $postId;";
            $stmt = $this->_db->query($sql);
            if ($stmt != false) {
                $post = $stmt->fetch(PDO::FETCH_ASSOC);
            }
        } catch (PDOException $e) {
            $this->error = $e->getMessage();
        }
        return $post;
    }
    public function getTagsByPostId($postId) {
        $tags = [];
        try {
            $postId = clearInt($postId);
            $sql = "SELECT tag FROM tag_posts 
                    WHERE post_id = $postId;";
            $stmt = $this->_db->query($sql);
            if ($stmt != false) {
                while ($result = $stmt->fetch(PDO::FETCH_ASSOC)) {
                    $tags[] = $result;
                }
            }
        } catch (PDOException $e) {
            $this->error = $e->getMessage();
        }
        return $tags;
    }
    public function searchPostsByTag($searchWord) {
        $results = [];
        try {
            $searchWord = clearStr($searchWord);
            $searchWord = '%' . $searchWord . '%';
            $searchWord = $this->_db->quote($searchWord);
            $sql = "SELECT p.post_id, p.title, p.content, p.user_id, p.date_time, 
                    a.rating, a.count_comments, a.count_ratings, u.fio as author
                    FROM posts p
                    JOIN additional_info_posts a ON a.post_id = p.post_id
                    JOIN users u ON p.user_id = u.user_id 
                    JOIN tag_posts t ON p.post_id = t.post_id 
                    WHERE t.tag LIKE $searchWord;";// LIMIT 30
            $stmt = $this->_db->query($sql);
            if ($stmt != false) {
                while ($result = $stmt->fetch(PDO::FETCH_ASSOC)) {
                    $results[$result['post_id']] = $result;
                }
            }
        } catch (PDOException $e) {
            $this->error = $e->getMessage();
        }
        return $results;
    }
    public function searchPostsByZagAndAuthor($searchWord) {
        $results = [];
        try {
            $searchWord = clearStr($searchWord);
            $searchWord = '%' . $searchWord . '%';
            $searchWord = $this->_db->quote($searchWord);
            $sql = "SELECT p.post_id, p.title, p.content, p.user_id, p.date_time, 
                    a.rating, a.count_comments, a.count_ratings, u.fio as author
                    FROM posts p 
                    JOIN users u ON p.user_id = u.user_id 
                    JOIN additional_info_posts a ON a.post_id = p.post_id 
                    WHERE u.fio LIKE $searchWord;";// LIMIT 30
            $stmt = $this->_db->query($sql);
            if ($stmt != false) {
                while ($result = $stmt->fetch(PDO::FETCH_ASSOC)) {
                    $results[$result['post_id']] = $result;
                }
            }
            $sql = "SELECT p.post_id, p.title, p.content, p.user_id, p.date_time, 
                    a.rating, a.count_comments, a.count_ratings, u.fio as author
                    FROM posts p 
                    JOIN users u ON p.user_id = u.user_id 
                    JOIN additional_info_posts a ON a.post_id = p.post_id
                    WHERE p.title LIKE $searchWord;";// LIMIT 30
            $stmt = $this->_db->query($sql);
            if ($stmt != false) {
                while ($result = $stmt->fetch(PDO::FETCH_ASSOC)) {
                    $results[$result['post_id']] = $result;
                }
            }
        } catch (PDOException $e) {
            $this->error = $e->getMessage();
        }
        return $results;
    }
    public function searchPostsByContent($searchWords) {
        $results = [];
        try {
            $searchWords = clearStr($searchWords);
            $searchWords = '%' . $searchWords . '%';
            $searchWords = $this->_db->quote($searchWords);
            $sql = "SELECT p.post_id, p.title, p.content, p.user_id, p.date_time, 
                    a.rating, a.count_comments, a.count_ratings, u.fio as author
                    FROM posts p 
                    JOIN users u ON p.user_id = u.user_id 
                    JOIN additional_info_posts a ON a.post_id = p.post_id
                    WHERE p.content LIKE $searchWords;";// LIMIT 30
            $stmt = $this->_db->query($sql);
            if ($stmt != false) {
                while ($result = $stmt->fetch(PDO::FETCH_ASSOC)) {
                    $results[$result['post_id']] = $result;
                }
            }
        } catch (PDOException $e) {
            $this->error = $e->getMessage();
        }
        return $results;
    }
    public function addTagsToPost($tag, $postId) {
        try {
            $tag = $this->_db->quote($tag);
            $postId = clearInt($postId);
            $sql = "INSERT INTO tag_posts (tag, post_id) 
                    VALUES($tag, $postId);";
            if (!$this->_db->exec($sql)) {
                return false;
            }
        } catch (PDOException $e) {
            $this->error = $e->getMessage();
        }
        return true;
    }
    public function deletePostById($id) {
        try {  
            $id = clearInt($id);
            /* Удаляю пост */
            $sql = "DELETE FROM posts WHERE post_id = $id;";
            $this->_db->exec($sql);
    
            /* Удаляю его картинку */
            //unlink("..\images\PostImgId$id.jpg");
        } catch (PDOException $e) {
            $this->error = $e->getMessage();
            return false;
        }
        return true;
    }
}