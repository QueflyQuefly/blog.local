<?php
$dbconfig = dirname(__DIR__). DIRECTORY_SEPARATOR . 'dbconfig.php';
require_once $dbconfig;

/* if not connection to  dbname=myblog, run init_db.php */
try {
    $db = new PDO("mysql:host=$host;dbname=$dbname", $username, $password);
} catch (PDOException $e) {
    $init = dirname(__DIR__) . DIRECTORY_SEPARATOR . 'init_db.php';
    require_once $init;
}


/* general functions */
function clearInt($int) {
    return abs((int) $int);
}
function clearStr($str) {
    return trim(strip_tags($str));
}
/* general functions */


/* functions for table users */
define('RIGHTS_USER', 'user');
define('RIGHTS_SUPERUSER', 'superuser');
function getUserIdAndFioByEmail($email) {
    global $db, $error;
    try {
        $email = $db->quote($email);

        $sql = "SELECT id, fio FROM users WHERE email=$email";
        $stmt = $db->query($sql);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        if (!empty($result)) {
            return $result;
        } else {
            return null;
        }
    } catch (PDOException $e) {
        $error = $e->getMessage();
    }
}
function getUsersIds() {
    global $db, $error;
    try {
        $sql = "SELECT id FROM users;";
        $stmt = $db->query($sql);
        while ($arr = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $users[] = $arr['id'];
        }
        return $users;
    } catch (PDOException $e) {
        $error = $e->getMessage();
        return false;
    }
}
function isUser($email, $password) {
    global $db, $error;
    $users = []; 
    try {
        $sql = "SELECT email, fio, pass_word FROM users";
        $stmt = $db->query($sql);
    
        while ($user = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $users[] = $user;
        }
    } catch (PDOException $e) {
        $error = $e->getMessage();
    }
    foreach ($users as $user) {
        if ($email == $user['email'] && password_verify($password, $user['pass_word'])) {
            $fio = $user['fio'];
            return true;
        }
    }
    return false;
}
function createUser($email, $fio, $password) {
    global $db, $error;
    try {
        if (!isEmailUnique($email)) {
            return false;
        }
        $email = $db->quote($email);
        $fio = $db->quote($fio);
        $date = time();
        $password = $db->quote($password);

        $sql = "INSERT INTO users (email, fio, pass_word, date_time, rights) 
        VALUES($email, $fio, $password, $date, 'user');";
        $db->exec($sql);

    } catch (PDOException $e) {
        $error = $e->getMessage();
    }
    return true;
}
function isEmailUnique($email) {
    global $db, $error;
    $emails = [];
    try {
        $sql = "SELECT email FROM users;";
        $stmt = $db->query($sql);
        while($data = $stmt->fetch(PDO::FETCH_ASSOC)){
            $emails[] = $data;
        }
    } catch (PDOException $e) {
        $error = $e->getMessage();
    }
    foreach ($emails as $value) {
        if ($email == $value['email']) {
            return false; //если есть совпадения, то логин не является уникальным
        }
    }
    return true;
}
function updateUser($id, $email, $fio, $password = false) {
    global $db, $error;
    try {
        $user = getUserEmailFioRightsById($id);
        $unchangedEmail = $user['email'];
        if ($unchangedEmail != $email) {
            if (!isEmailUnique($email)) {
                return false;
            }
        }
        $id = clearInt($id);
        $email = $db->quote($email);
        $fio = $db->quote($fio);
        $password = $db->quote($password);

        $sql = "UPDATE users SET email = $email, fio = $fio WHERE id = $id;";
        $db->exec($sql);

        if ($password !== false) {
            $sql = "UPDATE users SET pass_word = $password WHERE id = $id;";
            $db->exec($sql);
        }
    } catch (PDOException $e) {
        $error = $e->getMessage();
    }
    return true;
}
function getUserEmailFioRightsById($userId){
    global $db, $error;
    $userId = clearInt($userId);
    $login = '';
    $fio = '';
    $user = [];
    try {
        $sql = "SELECT email, fio, rights FROM users WHERE id = $userId;";
        $stmt = $db->query($sql);
        if ($stmt == false) {
            return false;
        }
        if($result = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $user = $result;
        }
    } catch (PDOException $e) {
        $error = $e->getMessage();
    }
    return $user;
}
function createAdmin($email, $fio, $password){
    global $db, $error;
    try {
        if (!isEmailUnique($email)) {
            return false;
        }
        $email = $db->quote($email);
        $fio = $db->quote($fio);
        $password = password_hash($password, PASSWORD_DEFAULT);
        $password = $db->quote($password);

        $sql = "INSERT INTO users(email, fio, pass_word, rights) 
                VALUES ($email, $fio, $password, RIGHTS_SUPERUSER);";
        if ($db->exec($sql)) {
            return true;
        } else {
            return false;
        }
    } catch (PDOException $e) {
        $error = $e->getMessage();  
    }
}
function deleteUserById($id) {
    global $db, $error;
    $id = clearInt($id);
    try {
        $sql = "DELETE FROM users WHERE id = $id;";
        if ($db->exec($sql)) {
            return true;
        } else {
            return false;
        }
    } catch (PDOException $e) {
        $error = $e->getMessage();
    }
}
/* functions for table users */


/* functions for table posts */
function getLastPostId() {
    global $db, $error;
    $postId = '';
    try {
        $sql = "SELECT id FROM posts ORDER BY id DESC LIMIT 1;";
        $stmt = $db->query($sql);
        if (!$stmt) {
            return null;
        }
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        $postId = $result['id'];
    } catch (PDOException $e) {
        $error = $e->getMessage();
    }
    return $postId;
}
function getPostIds($numberIds = false) {
    global $db, $error;
    $ids = [];
    try {
        if (empty($numberIds)) {
            $sql = "SELECT id FROM posts ORDER BY id DESC;";
        } else {
            $numberIds = clearInt($numberIds);
            $sql = "SELECT id FROM posts ORDER BY id DESC LIMIT $numberIds;";
        }
        $stmt = $db->query($sql);

        if ($stmt == false) {
            return false;
        }

        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $ids[] = $row['id'];
        }
    } catch (PDOException $e) {
        $error = $e->getMessage();
    }
    return $ids;
}
function getPostForIndexById($id) {
    global $db, $error;
    $post = [];
    try {
        $sql = "SELECT zag, user_id, date_time, content, rating FROM posts WHERE id = $id;";
        $stmt = $db->query($sql);

        if ($stmt == false) {
            return false;
        }

        $post = $stmt->fetch(PDO::FETCH_ASSOC);

        $post['id'] = $id;
        $post['zag'] = strip_tags($post['zag']);
        $post['zag'] = mb_substr($post['zag'], 0, 100);
        if (mb_strlen($post['zag'], 'utf-8') > 99) {
            $post['zag'] = $post['zag'] . "&hellip;";
        }

        $post['content'] = strip_tags($post['content']);
        $post['content'] = mb_substr($post['content'], 0, 300);
        if (mb_strlen($post['content'], 'utf-8') > 299) {
            $post['content'] = $post['content'] . "&hellip;";
        }

        $post['zag_small'] = strip_tags($post['zag']);
        $post['zag_small'] = mb_substr($post['zag_small'], 0, 45);
        if (mb_strlen($post['zag_small'], 'utf-8') > 44) {
            $post['zag_small'] = $post['zag_small'] . "&hellip;";
        }

        $post['content_small'] = strip_tags($post['content']);
        $post['content_small'] = mb_substr($post['content_small'], 0, 200);
        if (mb_strlen($post['content_small'], 'utf-8') > 199) {
            $post['content_small'] = $post['content_small'] . "&hellip;";
        }

        $post['date_time'] = date("d.m.Y",$post['date_time']) ." в ". date("H:i", $post['date_time']);
        
    } catch (PDOException $e) {
        $error = $e->getMessage();
    }
    return $post;
}
function getPostForViewById($id) {
    global $db, $error;
    try {
        $id = clearInt($id);
        $sql = "SELECT id, zag, user_id, date_time, content, rating FROM posts WHERE id = $id;";
        $stmt = $db->query($sql);
        $post = $stmt->fetch(PDO::FETCH_ASSOC);
        $post['content'] = str_replace("<br />
<br />","</p>\n<p>", nl2br($post['content']));
        $post['date_time'] = date("d.m.Y",$post['date_time']) ." в ". date("H:i", $post['date_time']);
    } catch(PDOException $e) {
        $error = $e->getMessage();
    }
    return $post;
}
function getMoreTalkedPostIds() {
    global $db, $error;
    $ids = [];
    try {
        $oneWeekInSeconds = 604800; //60 * 60 * 24 * 7
        $dateWeekAgo = time() - $oneWeekInSeconds;
        $sql = "SELECT id, post_id FROM comments WHERE date_time >= $dateWeekAgo ORDER BY id DESC LIMIT 10;";
        $stmt = $db->query($sql);

        if ($stmt == false) {
            return false;
        }
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $postId = $row['post_id'];
            $sql = "SELECT COUNT(*) as count FROM comments WHERE date_time >= $dateWeekAgo AND post_id = $postId;";
            $st = $db->query($sql);
            while ($r = $st->fetch(PDO::FETCH_ASSOC)) {
                $rows[$postId] = $r['count'];
            }
        }
        for ($i = 0; $i < 3; $i++) {
            if (!empty($rows)) {
                $maxId = array_search(max($rows), $rows);
                if (!empty($maxId)) {
                    $ids[] = $maxId;
                    unset($rows[$maxId]);
                }
            }
        }
    } catch (PDOException $e) {
        $error = $e->getMessage();
    }
    return $ids;
}
function insertToPosts($zag, $userId, $content) {
    global $db, $error;
    try {
        $date = time();
        $zag = $db->quote($zag);
        $userId = clearInt($userId);
        $user = getUserEmailFioRightsById($userId);
        $email = $user['email'];
        $fio = $user['fio'];
        $content = $db->quote($content);
        $rating = 0;
        
        $sql = "INSERT INTO posts (zag, user_id, date_time, content, rating) 
                VALUES($zag, $userId, $date, $content, $rating);";
        if (!$db->exec($sql)) {
            return false;
        }
        $sql = "SELECT email_want_subscribe FROM subscriptions WHERE email = $email";
        $stmt = $db->query($sql);
        if ($stmt) {
            $id = getLastPostId();
            while ($result = $stmt->fetch(PDO::FETCH_ASSOC)) {
                $toEmail = $result['email_want_subscribe'];
                $message = "Новый пост от $fio: http://blog.local/viewsinglepost.php?viewpostById=$id \n $zag";
                mail($toEmail, 'Новый пост', $message);
            }
        }
    } catch (PDOException $e) {
        $error = $e->getMessage();
    }
    return true;
}
function getPostsByUserId($user_id) {
    global $db, $error;
    $posts = [];
    try {
        $posts = [];
        $user_id = $db->quote($user_id);
        $sql = "SELECT id, zag, date_time, content, rating FROM posts WHERE user_id = $user_id;";
        $stmt = $db->query($sql);
        if(!$stmt) {
            return false;
        }
        while($post = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $posts[] = $post;
        }
    } catch(PDOException $e) {
        $error = $e->getMessage();
    }
    return $posts;

}
function deletePostById($id) {
    global $db, $error;
    try {  
        $id = clearInt($id);
        $db->beginTransaction();

        /* Удаляю пост */
        $sql = "DELETE FROM posts WHERE id = $id;";
        $db->exec($sql);
        
        /* Удаляю рэйтинг этого поста */
        $sql = "DELETE FROM rating_posts WHERE post_id = $id;";
        $db->exec($sql);

        /* Удаляю его картинку */
        //unlink("..\images\PostImgId$id.jpg");

        /* Удаляю все комментарии, связанные с постом */
        $sql = "DELETE FROM comments WHERE post_id = $id;";
        $db->exec($sql);

        /* Удаляю рэйтинг комментариев, связанных с постом  */
        $sql = "DELETE FROM rating_comments WHERE post_id = $id;";
        $db->exec($sql);

        /* Удаляю тэги, связанных с постом  */
        $sql = "DELETE FROM tag_posts WHERE post_id = $id;";
        $db->exec($sql);

        $db->commit();
    } catch (PDOException $e) {
        $db->rollBack();
        $error = $e->getMessage();
        return false;
    }
    return true;
}
/* functions for table posts */


/* functions for table comments */
function getCommentsByPostId($postId) {
    global $db, $error;
    $comments = [];
    try {
        $postId = clearInt($postId);
        $sql = "SELECT id, user_id, date_time, content, rating FROM comments WHERE post_id = $postId;";// LIMIT 30
        $stmt = $db->query($sql);
        if ($stmt == false) {
            return false;
        }
        while ($result = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $comments[] = $result;
        }
    } catch (PDOException $e) {
        $error = $e->getMessage();
    }
    return $comments;
}
function getCommentById($id) {
    global $db, $error;
    $comment = [];
    try {
        $id = clearInt($id);
        $sql = "SELECT post_id, user_id, date_time, content, rating FROM comments WHERE id = $id;";// LIMIT 30
        $stmt = $db->query($sql);
        if ($stmt == false) {
            return false;
        }
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        $comment = $result;
        $comment['id'] = $id;
    } catch (PDOException $e) {
        $error = $e->getMessage();
    }
    return $comment;
}
function insertComments($postId, $commentAuthorId, $commentDate, $commentContent) {
    global $db, $error;
    try {
        $authorId = clearInt($commentAuthorId);
        $postId = clearInt($postId);
        $date = $commentDate;
        $content = $db->quote($commentContent);
        $content = trim(strip_tags($content));

        $sql = "INSERT INTO comments (post_id, user_id, date_time, content, rating) 
        VALUES($postId, $authorId, $date, $content, 0)";
        if (!$db->exec($sql)) {
            return false;
        }
    } catch (PDOException $e) {
        $error = $e->getMessage();
    }
    return true;
}
function getCommentsByUserId($userId) {
    global $db, $error;
    $comments = [];
    try {
        $userId = $db->quote($userId);
        $sql = "SELECT id, post_id, date_time, content, rating FROM comments WHERE user_id = $userId;";// LIMIT 30
        $stmt = $db->query($sql);
        if ($stmt == false) {
            return false;
        }
        while ($result = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $comments[] = $result;
        }
    } catch (PDOException $e) {
        $error = $e->getMessage();
    }
    return $comments;
}
function getLikedCommentsIdsByUserId($userId) {
    global $db, $error;
    $commentsIds = [];
    try {
        $userId = clearInt($userId);
        $sql = "SELECT com_id FROM rating_comments WHERE user_id = $userId;";// LIMIT 30
        $stmt = $db->query($sql);
        if ($stmt == false) {
            return false;
        }
        while ($result = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $commentsIds[] = $result['com_id'];
        }
    } catch (PDOException $e) {
        $error = $e->getMessage();
    }
    return $commentsIds;
}
function deleteCommentById($deleteCommentId) {
    global $db, $error;
    $deleteCommentId = clearInt($deleteCommentId);
    try {
        /* Удаляю комментарий */
        $sql = "DELETE FROM comments WHERE id = $deleteCommentId;";
        $db->exec($sql);

        /* Удаляю рейтинг этого комментария*/
        $sql = "DELETE FROM rating_comments WHERE com_id = $deleteCommentId;";
        $db->exec($sql);
    } catch (PDOException $e) {
        $error = $e->getMessage();
    }
}
/* functions for table comments */


/* functions for table rating_posts */
function changePostRating($userId, $postId, $rating){
    global $db, $error;
    try {
        $userId = clearInt($userId);
        $postId = clearInt($postId);
        $rating = clearInt($rating);

        if ($rating) {
            $sql = "INSERT INTO rating_posts (user_id, post_id, rating) 
                    VALUES($userId, $postId, $rating);";
            $db->exec($sql);

            $sql = "SELECT rating FROM rating_posts WHERE post_id=$postId;";
            $stmt = $db->query($sql);
            if (!$stmt) {
                return false;
            }
            while ($postRate = $stmt->fetch(PDO::FETCH_ASSOC)) {
                $postRates[] = $postRate['rating'];
            }
            $countRatings = count($postRates);
            $summRatings = 0;
            for ($i = 0; $i < $countRatings; $i++) {
                $summRatings += $postRates[$i];
            }
            $postRating = $summRatings / $countRatings;
            $postRating = round($postRating, 1, PHP_ROUND_HALF_UP);
            
            $sql = "UPDATE posts SET rating=$postRating WHERE id=$postId;";
            if (!$db->exec($sql)) {
                return false;
            }
        }
    } catch (PDOException $e) {
        $error = $e->getMessage();
    }
    return true;
}
function isUserChangesPostRating($userId, $postId){
    global $db, $error;
    try {
        $userId = $db->quote($userId);

        $sql = "SELECT rating FROM rating_posts WHERE user_id = $userId AND post_id = $postId;";
        $stmt = $db->query($sql);
        if (!$stmt) {
            return false;
        }
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        if ($result) {
            return true;
        }
    } catch (PDOException $e) {
        $error = $e->getMessage();
    }
    return false;
}
function countRatingsByPostId($id) {
    global $db, $error;
    $countRatings = 0;
    try {
        $sql = "SELECT COUNT(*) as count FROM rating_posts WHERE post_id=$id;";
        $stmt = $db->query($sql);
        if(!$stmt) {
            return 0;
        }
        $countRatings = $stmt->fetch(PDO::FETCH_ASSOC);
        $countRatings = $countRatings['count'];
    } catch (PDOException $e) {
        $error = $e->getMessage();
    }
    return $countRatings;
}
function getLikedPostsIdsByUserId($userId) {
    global $db, $error;
    $posts = [];
    try {
        $userId = clearInt($userId);
        $sql = "SELECT post_id FROM rating_posts WHERE user_id = $userId;";// LIMIT 30
        $stmt = $db->query($sql);
        if ($stmt == false) {
            return false;
        }
        while ($result = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $posts[] = $result;
        }
    } catch (PDOException $e) {
        $error = $e->getMessage();
    }
    return $posts;   
}
/* functions for table rating_posts */


/* functions for table rating_comments */
function changeCommentRating($rating, $comId, $postId, $userId){
    global $db, $error;
    try {
        $comId = clearInt($comId);
        $postId = clearInt($postId);
        $userId = clearInt($userId);

        $db->beginTransaction();

        if ($rating === 'like') {
            $sql = "INSERT INTO rating_comments (user_id, com_id, post_id) 
                    VALUES($userId, $comId, $postId);";
            $db->exec($sql);

            $sql = "UPDATE comments SET rating=rating+1 WHERE id=$comId;";
            $db->exec($sql); 
        }
        if ($rating === 'unlike') {
            $sql = "DELETE FROM rating_comments WHERE com_id = $comId;";
            $db->exec($sql);

            $sql = "UPDATE comments SET rating = rating-1 WHERE id = $comId;";
            $db->exec($sql); 
        }
        $db->commit();
    } catch (PDOException $e) {
        $db->rollBack();
        $error = $e->getMessage();
        return false;
    }
    return true;
}
function isUserChangesCommentRating($userId, $comId){
    global $db, $error;
    try {
        $userId = clearInt($userId);

        $sql = "SELECT id FROM rating_comments WHERE user_id = $userId AND com_id = $comId;";
        $stmt = $db->query($sql);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        if ($result) {
            return true;
        }
    } catch (PDOException $e) {
        $error = $e->getMessage();
    }
    return false;
}
/* functions for table rating_comments */


/* functions for table tag_posts */
function addTagsToPost($tag, $postId) {
    global $db, $error;
    try {
        $tag = $db->quote($tag);
        $postId = clearInt($postId);
        $sql = "INSERT INTO tag_posts (tag, post_id) 
                VALUES($tag, $postId);";
        if (!$db->exec($sql)) {
            return false;
        }
    } catch (PDOException $e) {
        $error = $e->getMessage();
    }
    return true;
}
function getTagsToPostById($postId) {
    global $db, $error;
    try {
        $postId = clearInt($postId);

        $sql = "SELECT id, tag FROM tag_posts WHERE post_id=$postId";
        $stmt = $db->query($sql);
        while ($result = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $tags[] = $result;
        }
        if (!empty($tags)) {
            return $tags;
        } else {
            return null;
        }
    } catch (PDOException $e) {
        $error = $e->getMessage();
    }
}
/* functions for table tag_posts */


/* functions for table subscriptions */
function toSubscribeUser($userIdWantSubscribe, $userId) {
    global $db, $error;
    try {
        $userIdWantSubscribe = clearInt($userIdWantSubscribe);
        $userId = clearInt($userId);

        $sql = "INSERT INTO subscriptions (user_id_want_subscribe, user_id) 
        VALUES($userIdWantSubscribe, $userId);";

        if (!$db->exec($sql)) {
            return false;
        }
    } catch (PDOException $e) {
        $error = $e->getMessage();
    }
    return true;
}
function toUnsubscribeUser($userIdWantSubscribe, $userId) {
    global $db, $error;
    try {
        $userIdWantSubscribe = clearInt($userIdWantSubscribe);
        $userId = clearInt($userId);

        $sql = "DELETE FROM subscriptions WHERE user_id_want_subscribe = $userIdWantSubscribe AND user_id = $userId;";
        if (!$db->exec($sql)) {
            return false;
        }
    } catch (PDOException $e) {
        $db->rollBack();
        $error = $e->getMessage();
    }
    return true;
}
function isSubscribedUser($userIdWantSubscribe, $userId){
    global $db, $error;
    try {
        $userIdWantSubscribe = clearInt($userIdWantSubscribe);
        $userId = clearInt($userId);

        $sql = "SELECT id FROM subscriptions WHERE user_id_want_subscribe = $userIdWantSubscribe AND user_id = $userId;";
        $stmt = $db->query($sql);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        if ($result) {
            return true;
        }
    } catch (PDOException $e) {
        $error = $e->getMessage();
    }
    return false;
}
/* functions for table subscriptions */


/* functions for search.php */
function searchPostsByTag($searchword) {
    global $db, $error;
    $results = [];
    try {
        $searchword = mb_strtolower(clearStr($searchword));
        $sql = "SELECT tag, post_id FROM tag_posts;";// LIMIT 30
        $stmt = $db->query($sql);
        if ($stmt !== false) {
            while ($result = $stmt->fetch(PDO::FETCH_ASSOC)) {
                $posts[] = $result;
            }
            if (!empty($posts)) {
                foreach ($posts as $post) {
                    $post['tag'] = mb_strtolower($post['tag']);
                    if (strpos($post['tag'], $searchword) !== false) {
                        $results[] = $post['post_id'];
                    }
                } 
            }
        }
    } catch (PDOException $e) {
        $error = $e->getMessage();
    }
    return $results;
}
function searchPostsByNameAndAuthor($searchword) {
    global $db, $error;
    $results = [];
    try {
        $searchword = mb_strtolower(clearStr($searchword));
        $sql = "SELECT id, zag, user_id FROM posts;";// LIMIT 30
        $stmt = $db->query($sql);
        if ($stmt !== false) {
            while ($result = $stmt->fetch(PDO::FETCH_ASSOC)) {
                $posts[] = $result;
            }
            foreach ($posts as $post) {
                $zag = mb_strtolower($post['zag']);
                if (strpos($zag, $searchword) !== false) {
                    $results[] = $post['id'];
                }
                $user = getUserEmailFioRightsById($post['user_id']);
                $fio = mb_strtolower($user['fio']);
                if (strpos($fio, $searchword) !== false) {
                    $results[] = $post['id'];
                }
            }
        }
    } catch (PDOException $e) {
        $error = $e->getMessage();
    }
    return $results;
}
function searchPostsByContent($searchword) {
    global $db, $error;
    $results = [];
    try {
        $searchword = mb_strtolower(clearStr($searchword));
        $sql = "SELECT id, content FROM posts;";// LIMIT 30
        $stmt = $db->query($sql);
        if ($stmt !== false) {
            while ($result = $stmt->fetch(PDO::FETCH_ASSOC)) {
                $posts[] = $result;
            }
            foreach ($posts as $post) {
                $content = mb_strtolower($post['content']);
                if (strpos($content, $searchword) !== false) {
                    $results[] = $post['id'];
                }
            }
        }
    } catch (PDOException $e) {
        $error = $e->getMessage();
    }
    return $results;
}
function searchUsersByFioAndLogin($searchword, $rights = RIGHTS_USER) {
    global $db, $error;
    $results = [];
    try {
        $searchword = mb_strtolower(clearStr($searchword));
        $sql = "SELECT id, email, fio, rights FROM users;";
        $stmt = $db->query($sql);
        if ($stmt !== false) {
            while ($result = $stmt->fetch(PDO::FETCH_ASSOC)) {
                $users[] = $result;
            }
            $i = 0;
            foreach ($users as $user) {
                $fio = mb_strtolower($user['fio']);
                if (strpos($fio, $searchword) !== false) {
                    $results[$i]['id'] = $user['id'];
                    $results[$i]['fio'] = $user['fio'];
                    $results[$i]['rights'] = $user['rights'];
                    if ($rights == 'superuser') { //email отображается только для администраторов
                        $results[$i]['email'] = $user['email'];
                    }
                }
                if ($rights == 'superuser') { //поиск по email только для администраторов
                    $email = mb_strtolower($user['email']);
                    if (strpos($email, $searchword) !== false) {
                        $results[$i]['id'] = $user['id'];
                        $results[$i]['fio'] = $user['fio'];
                        $results[$i]['rights'] = $user['rights'];
                        $results[$i]['email'] = $user['email'];
                    }
                }
                $i++;
            }
        }
    } catch (PDOException $e) {
        $error = $e->getMessage();
    }
    return $results;
}
/* functions for search.php */


/* functions for stab_db.php  */
function isNounForTag($text){
    $symbols = ['а','ь'];
    $tags = [];

    $text = mb_strtolower(clearStr($text));
    $text = str_replace('.', ' ', $text);
    $words = explode(' ', $text);
    foreach ($words as $word) {
        $lastSymbol = mb_substr($word, -1);
        
        foreach ($symbols as $symbol) {
            if (mb_strlen($word) > 8) {
                if (mb_strtoupper($lastSymbol) === mb_strtoupper($symbol)) {
                    $tags[] = "#" . $word;
                    $tags = array_unique($tags);
                }
            }
        }
    }
    return $tags;
}
/* functions for stab_db.php  */