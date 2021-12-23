<?php
spl_autoload_register(function ($class) {
    require "$class.php";
});

class PostService {
    public $error;
    private $_db;
    public function __construct() {
        $this->_db = DbService::getInstance();
    }
    public function getPostsByNumber($numberOfPosts, $lessThanMaxId = 0) {
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
            if (!empty($post)) {
                $post['content'] = str_replace("<br />
    <br />","</p>\n<p>", nl2br($post['content']));
                $regex = '/#(\w+)/um';
                $post['content'] = preg_replace($regex, "<a class='link' href='search.php?search=%23$1'>$0</a>", $post['content']);
                $post['title'] = preg_replace($regex, "<a class='link' href='search.php?search=%23$1'>$0</a>", $post['title']);
                $post['date_time'] = date("d.m.Y в H:i", $post['date_time']);
            }
        } catch (PDOException $e) {
            $this->error = $e->getMessage();
        }
        return $post;
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
                    WHERE c.date_time >= $dateWeekAgo ORDER BY a.count_comments DESC LIMIT 10;";
            $stmt = $this->_db->query($sql);
            if ($stmt != false) {
                while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                    $postsNotSorted[] = $row;
                    $postId = $row['post_id'];
                    $sql = "SELECT COUNT(*) as count_comments FROM comments 
                            WHERE date_time >= $dateWeekAgo AND post_id = $postId;";
                    $st = $this->_db->query($sql);
                    while ($r = $st->fetch(PDO::FETCH_ASSOC)) {
                        $rows[$postId] = $r['count_comments'];
                    }
                }
                for ($i = 1; $i <= $numberOfPosts; $i++) {
                    if (!empty($rows)) {
                        $maxId = array_search(max($rows), $rows);
                        if (!empty($maxId)) {
                            foreach ($postsNotSorted as $post) {
                                if ($post['post_id'] == $maxId) {
                                    $posts[$maxId] = $post;
                                }
                            }
                            unset($rows[$maxId]);
                        }
                    }
                }
            }
        } catch (PDOException $e) {
            $this->error = $e->getMessage();
        }
        return $posts;
    }
    public function addPost($title, $userId, $content) {
        try {
            $date = time();
            $titleQuote = $this->_db->quote($title);
            
            $fio = getUserInfoById($userId, 'fio');
            $content = $this->_db->quote($content);
            $rating = 0;
            
            $sql = "INSERT INTO posts (title, user_id, date_time, content, rating) 
                    VALUES($titleQuote, $userId, $date, $content, $rating);";
            if (!$this->_db->exec($sql)) {
                return false;
            }
            $lastPostId = $this->_db->lastInsertId();
    
            $sql = "INSERT INTO additional_info_posts 
                    (post_id, rating, count_comments, count_ratings) 
                    VALUES($lastPostId, 0.0 , 0, 0);";
            if (!$this->_db->exec($sql)) {
                return false;
            }
    
            $regex = '/#\w+/um';
            $allText = $title . " " . $content;
            preg_match_all($regex, $allText, $tags);
    
            $tags = $tags[0];
            foreach ($tags as $tag) {
                addTagsToPost($tag, $lastPostId);
            }
    
            $sql = "SELECT s.user_id_want_subscribe, u.fio, u.email
                    FROM subscriptions s 
                    JOIN users u ON u.user_id = s.user_id_want_subscribe
                    WHERE s.user_id = $userId";
            $stmt = $this->_db->query($sql);
            if ($stmt != false) {
                while ($result = $stmt->fetch(PDO::FETCH_ASSOC)) {
                    $toEmail = $result['email'];
                    $fio = $result['fio'];
                    $title = 'Prosto Blog';
                    $message = "
                        <h2>$fio опубликовал новый пост:</h2> 
                        <p style='font-size:13pt;'>$title -
                        <a href='bloglocal.000webhostapp.com/viewsinglepost.php?viewpostById=$lastPostId'>Перейти к посту</a></p>
                        <pre>Это письмо отправлено вам, потому что вы подписаны на этого автора</pre>
                        <a href='bloglocal.000webhostapp.com/cabinet.php?user=$userId&unsubscribe'>Отписаться</a>
                        <p style='font-size:10pt;'>Необходимо прежде <a href='bloglocal.000webhostapp.com/cabinet.php?user=$userId&unsubscribe'>войти</a></p>
                    ";
    
                    sendMail($toEmail, $title, $message);
                }
            }
        } catch (PDOException $e) {
            $this->error = $e->getMessage();
        }
        return true;
    }
    public function getPostsByUserId($user_id) {
        $posts = [];
        try {
            $posts = [];
            $user_id = $this->_db->quote($user_id);
            $sql = "SELECT p.post_id, p.title, p.date_time, 
                    p.content, a.rating, a.count_comments, a.count_ratings, u.fio as author
                    FROM posts p JOIN users u ON p.user_id = u.user_id 
                    JOIN additional_info_posts a ON a.post_id = p.post_id
                    WHERE p.user_id = $user_id;";
            $stmt = $this->_db->query($sql);
            if ($stmt != false) {
                while($post = $stmt->fetch(PDO::FETCH_ASSOC)) {
                    $post['date_time'] = date("d.m.Y в H:i", $post['date_time']);
                    $posts[] = $post;
                }
            }
        } catch(PDOException $e) {
            $this->error = $e->getMessage();
        }
        return $posts;
    }
    public function searchPostsByTag($searchword) {
        $results = [];
        try {
            $searchword = clearStr($searchword);
            $searchword = '%' . $searchword . '%';
            $searchword = $this->_db->quote($searchword);
            $sql = "SELECT p.post_id, p.title, p.content, p.user_id, p.date_time, 
                    a.rating, a.count_comments, a.count_ratings, u.fio as author, t.tag 
                    FROM posts p JOIN users u
                    JOIN additional_info_posts a ON a.post_id = p.post_id
                    ON p.user_id = u.user_id JOIN tag_posts t ON p.post_id = t.post_id 
                    WHERE tag LIKE $searchword;";// LIMIT 30
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
    public function searchPostsByZagAndAuthor($searchword) {
        $results = [];
        try {
            $searchword = clearStr($searchword);
            $searchword = '%' . $searchword . '%';
            $searchword = $this->_db->quote($searchword);
            $sql = "SELECT p.post_id, p.title, p.content, p.user_id, p.date_time, 
                    a.rating, a.count_comments, a.count_ratings, u.fio as author, t.tag 
                    FROM posts p 
                    JOIN users u ON p.user_id = u.user_id 
                    JOIN tag_posts t ON p.post_id = t.post_id 
                    JOIN additional_info_posts a ON a.post_id = p.post_id 
                    WHERE fio LIKE $searchword;";// LIMIT 30
            $stmt = $this->_db->query($sql);
            if ($stmt != false) {
                while ($result = $stmt->fetch(PDO::FETCH_ASSOC)) {
                    $results[$result['post_id']] = $result;
                }
            }
            $sql = "SELECT p.post_id, p.title, p.content, p.user_id, p.date_time, 
                    a.rating, a.count_comments, a.count_ratings, u.fio as author, t.tag 
                    FROM posts p 
                    JOIN users u ON p.user_id = u.user_id 
                    JOIN tag_posts t ON p.post_id = t.post_id 
                    JOIN additional_info_posts a ON a.post_id = p.post_id
                    WHERE post_title LIKE $searchword;";// LIMIT 30
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
    public function searchPostsByContent($searchwords) {
        $results = [];
        try {
            $searchwords = clearStr($searchwords);
            $searchwords = '%' . $searchwords . '%';
            $searchwords = $this->_db->quote($searchwords);
            $sql = "SELECT p.post_id, p.title, p.content, p.user_id, p.date_time, 
                    a.rating, a.count_comments, a.count_ratings, u.fio as author, t.tag 
                    FROM posts p 
                    JOIN users u ON p.user_id = u.user_id 
                    JOIN tag_posts t ON p.post_id = t.post_id 
                    JOIN additional_info_posts a ON a.post_id = p.post_id
                    WHERE post_content LIKE $searchwords;";// LIMIT 30
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