<?php
$dbconfig = dirname(__DIR__). DIRECTORY_SEPARATOR . 'dbconfig.php';
require_once $dbconfig;
define('RIGHTS_USER', 'user');
define('RIGHTS_SUPERUSER', 'superuser');

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
function getUserIdByEmail($email) {
    global $db, $error;
    $id = null;
    try {
        $email = $db->quote($email);

        $sql = "SELECT user_id FROM users WHERE email = $email;";
        $stmt = $db->query($sql);
        if ($stmt != false) {
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            $id = $result['user_id'];
        }
    } catch (PDOException $e) {
        $error = $e->getMessage();
    }
    return $id;
}
function getUsersByNumber($numberOfUsers , $lessThanMaxId = 0) {
    global $db, $error;
    $users = [];
    try {
        $numberOfUsers  = clearInt($numberOfUsers );
        $lessThanMaxId = clearInt($lessThanMaxId);
        if (empty($lessThanMaxId)) {
            $sql = "SELECT id, user_id, email, fio, pass_word, date_time, 
                    rights FROM users ORDER BY id DESC 
                    LIMIT $numberOfUsers ;";
        } else {
            $sql = "SELECT id, user_id, email, fio, pass_word, date_time, rights 
                    FROM users WHERE id <= (SELECT MAX(id) FROM users) - $lessThanMaxId 
                    ORDER BY id DESC LIMIT $numberOfUsers ;";
        }
        $stmt = $db->query($sql);
        if ($stmt != false) {
            while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                $users[] = $row;
            }
        }
    } catch (PDOException $e) {
        $error = $e->getMessage();
        return false;
    }
    return $users;
}
function isUser($email, $password) {
    global $db, $error;
    try {
        $email = $db->quote($email);
        $sql = "SELECT user_id, pass_word FROM users WHERE email = $email;";
        $stmt = $db->query($sql);
        if ($stmt != false) {
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            if (password_verify($password, $result['pass_word'])) {
                return true;
            }
        }
    } catch (PDOException $e) {
        $error = $e->getMessage();
    }
    return false;
}
function addUser($email, $fio, $password, $rights = false) {
    global $db, $error;
    try {
        if (!isEmailUnique($email)) {
            return false;
        }
        if ($rights === RIGHTS_SUPERUSER) {
            $userId = uniqid(RIGHTS_SUPERUSER);
            $rights = $db->quote(RIGHTS_SUPERUSER);
        } else {
            $userId = uniqid(RIGHTS_USER);
            $rights = $db->quote(RIGHTS_USER);
        }
        $userId = $db->quote($userId);
        $email = $db->quote($email);
        $fio = $db->quote($fio);
        $date = time();
        $password = $db->quote($password);

        $sql = "INSERT INTO users (user_id, email, fio, pass_word, date_time, rights) 
                VALUES ($userId, $email, $fio, $password, $date, $rights);";
        if (!$db->exec($sql)) {
            return false;
        }

    } catch (PDOException $e) {
        $error = $e->getMessage();
    }
    return true;
}
function isEmailUnique($email) {
    global $db, $error;
    try {
        $email = $db->quote($email);
        $sql = "SELECT user_id FROM users WHERE email = $email;";
        $stmt = $db->query($sql);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        if (!empty($result['user_id'])){
            return false; //если есть совпадения, то логин не является уникальным
        }
    } catch (PDOException $e) {
        $error = $e->getMessage();
    }
    return true;
}
function updateUser($userId, $email, $fio, $password) {
    global $db, $error;
    try {
        $userId = $db->quote($userId);
        $unchangedEmail = getUserInfoById($userId, 'email');
        if ($unchangedEmail != $email) {
            if (!isEmailUnique($email)) {
                return false;
            }
        }
        $email = $db->quote($email);
        $fio = $db->quote($fio);

        $sql = "UPDATE users SET email = $email, fio = $fio WHERE user_id = $userId;";
        if (!$db->exec($sql)) {
            return false;
        }

        if ($password !== false) {
            $password = $db->quote($password);
            $sql = "UPDATE users SET pass_word = $password WHERE user_id = $userId;";
            $db->exec($sql);
        }
    } catch (PDOException $e) {
        $error = $e->getMessage();
    }
    return true;
}
function getUserInfoById($userId, $whatNeeded = ''){
    global $db, $error;
    $userId = $db->quote($userId);
    $result = null;
    try {
        $sql = "SELECT email, fio, date_time, rights 
                FROM users WHERE user_id = $userId;";
        $stmt = $db->query($sql);
        if ($stmt != false) {
            if($data = $stmt->fetch(PDO::FETCH_ASSOC)) {
                if ($whatNeeded == 'email') {
                    $result = $data['email'];
                } elseif ($whatNeeded == 'fio') {
                    $result = $data['fio'];
                } elseif ($whatNeeded == 'rights') {
                    $result = $data['rights'];
                } elseif ($whatNeeded == 'date_time') {
                    $result = $data['date_time'];
                } else {
                    $result = $data;
                }
            }
        }
    } catch (PDOException $e) {
        $error = $e->getMessage();
    }
    return $result;
}
function searchUsersByFioAndEmail($searchword, $rights = RIGHTS_USER) {
    global $db, $error;
    $results = [];
    try {
        $searchword = clearStr($searchword);
        $searchword = '%' . $searchword . '%';
        $searchword = $db->quote($searchword);
        $sql = "SELECT id, user_id, fio, email, date_time, rights 
                FROM users WHERE fio LIKE $searchword;";
        $stmt = $db->query($sql);
        if ($stmt != false) {
            while ($result = $stmt->fetch(PDO::FETCH_ASSOC)) {
                $results[$result['id']] = $result;
            }
        }
        if ($rights === RIGHTS_SUPERUSER) {
            $sql = "SELECT id, user_id, fio, email, date_time, rights 
                    FROM users WHERE email LIKE $searchword;";
            $stmt = $db->query($sql);
            if ($stmt != false) {
                while ($result = $stmt->fetch(PDO::FETCH_ASSOC)) {
                    $results[$result['id']] = $result;
                }
            }
        }
    } catch (PDOException $e) {
        $error = $e->getMessage();
    }
    return $results;
}
function deleteUserById($id) {
    global $db, $error;
    $id = clearInt($id);
    try {
        $sql = "DELETE FROM users WHERE user_id = $id;";
        if ($db->exec($sql)) {
            return true;
        }
    } catch (PDOException $e) {
        $error = $e->getMessage();
    }
    return false;
}
/* functions for table users */


/* functions for table posts */
function getPostsByNumber($numberOfPosts, $lessThanMaxId = 0) {
    global $db, $error;
    $posts = [];
    try {
        $numberOfPosts = clearInt($numberOfPosts);
        $lessThanMaxId = clearInt($lessThanMaxId);
        $sql = "SELECT p.post_id, p.zag, p.user_id, p.date_time, p.content, 
                p.rating, u.fio as author FROM posts p 
                JOIN users u ON p.user_id = u.user_id 
                WHERE p.post_id <= (SELECT MAX(post_id) FROM posts) - $lessThanMaxId 
                ORDER BY p.post_id DESC LIMIT $numberOfPosts;";
        $stmt = $db->query($sql);
        if ($stmt != false) {
            while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                $posts[] = $row;
            }
        }
    } catch (PDOException $e) {
        $error = $e->getMessage();
    }
    return $posts;
}
function getPostForViewById($postId) {
    global $db, $error;
    $post = [];
    try {
        $postId = clearInt($postId);
        $sql = "SELECT p.post_id, p.zag, p.user_id, p.date_time, p.content, 
                p.rating, u.fio as author FROM posts p 
                JOIN users u ON p.user_id = u.user_id 
                WHERE p.post_id = $postId;";
        $stmt = $db->query($sql);
        if ($stmt != false) {
            $post = $stmt->fetch(PDO::FETCH_ASSOC);
        }
        if (!empty($post)) {
            $post['content'] = str_replace("<br />
<br />","</p>\n<p>", nl2br($post['content']));
            $regex = '/#(\w+)/um';
            $post['content'] = preg_replace($regex, "<a class='link' href='search.php?search=%23$1'>$0</a>", $post['content']);
            $post['zag'] = preg_replace($regex, "<a class='link' href='search.php?search=%23$1'>$0</a>", $post['zag']);
            $post['date_time'] = date("d.m.Y в H:i", $post['date_time']);
            $post['countComments'] = countCommentsByPostId($postId);
            $post['countRatings'] = countRatingsByPostId($postId);
        }
    } catch (PDOException $e) {
        $error = $e->getMessage();
    }
    return $post;
}
function getMoreTalkedPosts($numberOfPosts = 3) {
    global $db, $error;
    $posts = [];
    try {
        $numberOfPosts = clearInt($numberOfPosts);
        $oneWeekInSeconds = 604800; //60 * 60 * 24 * 7
        $dateWeekAgo = time() - $oneWeekInSeconds;
        $sql = "SELECT DISTINCT c.post_id, p.zag, 
                p.date_time, p.content, p.rating, u.fio as author 
                FROM comments c 
                JOIN posts p ON c.post_id = p.post_id
                JOIN users u ON p.user_id = u.user_id 
                WHERE c.date_time >= $dateWeekAgo ORDER BY c.post_id DESC LIMIT 10;";
        $stmt = $db->query($sql);
        if ($stmt != false) {
            while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                $postsNotSorted[] = $row;
                $postId = $row['post_id'];
                $sql = "SELECT COUNT(*) as count_comments FROM comments 
                        WHERE date_time >= $dateWeekAgo AND post_id = $postId;";
                $st = $db->query($sql);
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
        $error = $e->getMessage();
    }
    return $posts;
}
function addPost($zag, $userId, $content) {
    global $db, $error;
    try {
        $date = time();
        $zag = $db->quote($zag);
        $userId = $db->quote($userId);
        $fio = getUserInfoById($userId, 'fio');
        $content = $db->quote($content);
        $rating = 0;
        
        $sql = "INSERT INTO posts (zag, user_id, date_time, content, rating) 
                VALUES($zag, $userId, $date, $content, $rating);";
        if (!$db->exec($sql)) {
            return false;
        }

        $lastPostId = $db->lastInsertId();

        $regex = '/#\w+/um';
        $allText = $zag . " " . $content;
        preg_match_all($regex, $allText, $tags);

        $tags = $tags[0];
        foreach ($tags as $tag) {
            addTagsToPost($tag, $lastPostId);
        }

        $sql = "SELECT user_id_want_subscribe FROM subscriptions WHERE user_id = $userId";
        $stmt = $db->query($sql);
        if ($stmt != false) {
            while ($result = $stmt->fetch(PDO::FETCH_ASSOC)) {
                $toEmail = getUserInfoById($result['user_id_want_subscribe'], 'email');
                $message = "$fio опубликовал новый пост: \n $zag \n 
                http://blog.local/viewsinglepost.php?viewpostById=$lastPostId \n
                Это письмо отправлено вам, потому что вы подписаны на этого автора \n
                Отписаться: http://blog.local/cabinet.php?user=$userId&unsubscribe - 
                необходимо прежде войти";
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
        $sql = "SELECT p.post_id, p.zag, p.date_time, 
            p.content, p.rating, u.fio as author
            FROM posts p JOIN users u ON p.user_id = u.user_id 
            WHERE p.user_id = $user_id;";
        $stmt = $db->query($sql);
        if ($stmt != false) {
            while($post = $stmt->fetch(PDO::FETCH_ASSOC)) {
                $post['date_time'] = date("d.m.Y в H:i", $post['date_time']);
                $post['countComments'] = countCommentsByPostId($post['post_id']);
                $post['countRatings'] = countRatingsByPostId($post['post_id']);
                $posts[] = $post;
            }
        }
    } catch(PDOException $e) {
        $error = $e->getMessage();
    }
    return $posts;
}
function searchPostsByTag($searchword) {
    global $db, $error;
    $results = [];
    try {
        $searchword = clearStr($searchword);
        $searchword = '%' . $searchword . '%';
        $searchword = $db->quote($searchword);
        $sql = "SELECT p.post_id, p.zag, p.user_id, p.date_time, 
                p.rating, u.fio as author, t.tag FROM posts p JOIN users u
                ON p.user_id = u.user_id JOIN tag_posts t ON p.post_id = t.post_id WHERE t.tag 
                LIKE $searchword;";// LIMIT 30
        $stmt = $db->query($sql);
        if ($stmt != false) {
            while ($result = $stmt->fetch(PDO::FETCH_ASSOC)) {
                $results[$result['post_id']] = $result;
            }
        }
    } catch (PDOException $e) {
        $error = $e->getMessage();
    }
    return $results;
}
function searchPostsByZagAndAuthor($searchword) {
    global $db, $error;
    $results = [];
    try {
        $searchword = clearStr($searchword);
        $searchword = '%' . $searchword . '%';
        $searchword = $db->quote($searchword);
        $sql = "SELECT p.post_id, p.zag, p.user_id, p.date_time, 
                p.rating, u.fio as author, t.tag FROM posts p JOIN users u 
                ON p.user_id = u.user_id JOIN tag_posts t ON p.post_id = t.post_id 
                WHERE u.fio LIKE $searchword;";// LIMIT 30
        $stmt = $db->query($sql);
        if ($stmt != false) {
            while ($result = $stmt->fetch(PDO::FETCH_ASSOC)) {
                $results[$result['post_id']] = $result;
            }
        }
        $sql = "SELECT p.post_id, p.zag, p.user_id, p.date_time, 
                p.rating, u.fio as author, t.tag FROM posts p JOIN users u 
                ON p.user_id = u.user_id JOIN tag_posts t ON p.post_id = t.post_id 
                WHERE p.zag LIKE $searchword;";// LIMIT 30
        $stmt = $db->query($sql);
        if ($stmt != false) {
            while ($result = $stmt->fetch(PDO::FETCH_ASSOC)) {
                $results[$result['post_id']] = $result;
            }
        }
    } catch (PDOException $e) {
        $error = $e->getMessage();
    }
    return $results;
}
function searchPostsByContent($searchwords) {
    global $db, $error;
    $results = [];
    try {
        $searchwords = clearStr($searchwords);
        $searchwords = '%' . $searchwords . '%';
        $searchwords = $db->quote($searchwords);
        $sql = "SELECT p.post_id, p.zag, p.user_id, p.date_time, 
                p.rating, u.fio as author, t.tag FROM posts p JOIN users u 
                ON p.user_id = u.user_id JOIN tag_posts t ON p.post_id = t.post_id 
                WHERE p.content LIKE $searchwords;";// LIMIT 30
        $stmt = $db->query($sql);
        if ($stmt != false) {
            while ($result = $stmt->fetch(PDO::FETCH_ASSOC)) {
                $results[$result['post_id']] = $result;
            }
        }
    } catch (PDOException $e) {
        $error = $e->getMessage();
    }
    return $results;
}
function deletePostById($id) {
    global $db, $error;
    try {  
        $id = clearInt($id);
        $db->beginTransaction();

        /* Удаляю пост */
        $sql = "DELETE FROM posts WHERE post_id = $id;";
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
function countCommentsByPostId($postId) {
    global $db, $error;
    $countComments = 0;
    try {
        $postId = clearInt($postId);
        $sql = "SELECT COUNT(*) as count_comments 
                FROM comments WHERE post_id = $postId;";
        $stmt = $db->query($sql);
        if ($stmt != false) {
            $result = $stmt->fetch(PDO::FETCH_ASSOC); 
            $countComments = $result['count_comments'];
        }
    } catch (PDOException $e) {
        $error = $e->getMessage();
    }
    return $countComments;
}
function getCommentsByPostId($postId) {
    global $db, $error;
    $comments = [];
    try {
        $postId = clearInt($postId);
        $sql = "SELECT c.com_id, c.post_id, c.user_id, c.date_time, 
                c.content, c.rating, u.fio as author
                FROM comments c JOIN users u 
                ON c.user_id = u.user_id WHERE post_id = $postId;";// LIMIT 30
        $stmt = $db->query($sql);
        if ($stmt != false) {
            while ($result = $stmt->fetch(PDO::FETCH_ASSOC)) {
                $comments[] = $result;
            }
        }
    } catch (PDOException $e) {
        $error = $e->getMessage();
    }
    return $comments;
}
function getCommentById($comId) {
    global $db, $error;
    $comment = [];
    try {
        $comId = clearInt($comId);
        $sql = "SELECT c.com_id, c.post_id, c.user_id, c.date_time, 
                c.content, c.rating, u.fio as author
                FROM comments c JOIN users u 
                ON c.user_id = u.user_id WHERE com_id = $comId;";
        $stmt = $db->query($sql);
        if ($stmt != false) {
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            $comment = $result;
        }
    } catch (PDOException $e) {
        $error = $e->getMessage();
    }
    return $comment;
}
function insertComments($postId, $commentAuthorId, $commentDate, $commentContent) {
    global $db, $error;
    try {
        $authorId = clearStr($commentAuthorId);
        $postId = clearInt($postId);
        $date = $commentDate;
        $content = trim(strip_tags($commentContent));
        $content = $db->quote($content);
        $authorId = $db->quote($authorId);

        $sql = "INSERT INTO comments (post_id, user_id, date_time, content, rating) 
        VALUES($postId, $authorId, $date, $content, 0);";
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
        $sql = "SELECT c.com_id, c.post_id, c.date_time, c.content, c.user_id,
                c.rating, u.fio as author FROM comments c 
                JOIN users u ON u.user_id = c.user_id 
                WHERE c.user_id = $userId;";// LIMIT 30
        $stmt = $db->query($sql);
        if ($stmt != false) {
            while ($result = $stmt->fetch(PDO::FETCH_ASSOC)) {
                $comments[] = $result;
            }
        }
    } catch (PDOException $e) {
        $error = $e->getMessage();
    }
    return $comments;
}
function deleteCommentById($deleteCommentId) {
    global $db, $error;
    $deleteCommentId = clearInt($deleteCommentId);
    try {
        $db->beginTransaction();
        /* Удаляю комментарий */
        $sql = "DELETE FROM comments WHERE com_id = $deleteCommentId;";
        $db->exec($sql);

        /* Удаляю рейтинг этого комментария*/
        $sql = "DELETE FROM rating_comments WHERE com_id = $deleteCommentId;";
        $db->exec($sql);
        $db->commit();
    } catch (PDOException $e) {
        $db->rollBack();
        $error = $e->getMessage();
    }
}
/* functions for table comments */


/* functions for table rating_posts */
function changePostRating($userId, $postId, $rating){
    global $db, $error;
    try {
        $userId = $db->quote($userId);
        $postId = clearInt($postId);
        $rating = clearInt($rating);

        if ($rating) {
            $sql = "INSERT INTO rating_posts (user_id, post_id, rating) 
                    VALUES($userId, $postId, $rating);";
            if (!$db->exec($sql)) {
                return false;
            }
            $sql = "SELECT rating FROM rating_posts WHERE post_id = $postId;";
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
            
            $sql = "UPDATE posts SET rating = $postRating 
                    WHERE post_id = $postId;";
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

        $sql = "SELECT rating FROM rating_posts 
                WHERE user_id = $userId AND post_id = $postId;";
        $stmt = $db->query($sql);
        if ($stmt != false) {
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            if (!empty($result)) {
                return true;
            }
        }
    } catch (PDOException $e) {
        $error = $e->getMessage();
    }
    return false;
}
function countRatingsByPostId($postId) {
    global $db, $error;
    $countRatings = 0;
    try {
        $postId = clearInt($postId);

        $sql = "SELECT COUNT(*) as count_ratings_of_posts 
                FROM rating_posts WHERE post_id = $postId;";
        $stmt = $db->query($sql);
        if ($stmt != false) {
            $countRatings = $stmt->fetch(PDO::FETCH_ASSOC);
            $countRatings = $countRatings['count_ratings_of_posts'];
        }
    } catch (PDOException $e) {
        $error = $e->getMessage();
    }
    return $countRatings;
}
function getLikedPostsByUserId($userId) {
    global $db, $error;
    $posts = [];
    try {
        $userId = $db->quote($userId);
        $sql = "SELECT r.post_id, p.zag, r.user_id, p.date_time, p.content, 
                p.rating, u.fio as author FROM rating_posts r 
                JOIN posts p ON p.post_id = r.post_id 
                JOIN users u ON u.user_id = r.user_id 
                WHERE r.user_id = $userId ORDER BY p.date_time DESC;";// LIMIT 30
        $stmt = $db->query($sql);
        if ($stmt != false) {
            while ($result = $stmt->fetch(PDO::FETCH_ASSOC)) {
                $posts[] = $result;
            }
        }
    } catch (PDOException $e) {
        $error = $e->getMessage();
    }
    return $posts;   
}
/* functions for table rating_posts */


/* functions for table rating_comments */
function getLikedCommentsByUserId($userId) {
    global $db, $error;
    $comments = [];
    try {
        $userId = $db->quote($userId);
        $sql = "SELECT r.com_id, r.user_id, r.post_id, 
                u.fio as author, c.content, c.date_time, c.rating
                FROM rating_comments r
                JOIN comments c ON r.com_id = c.com_id
                JOIN users u ON r.user_id = u.user_id
                WHERE r.user_id = $userId;";// LIMIT 30
        $stmt = $db->query($sql);
        if ($stmt != false) {
            while ($result = $stmt->fetch(PDO::FETCH_ASSOC)) {
                $comments[] = $result;
            }
        }
    } catch (PDOException $e) {
        $error = $e->getMessage();
    }
    return $comments;
}
function changeCommentRating($rating, $comId, $postId, $userId){
    global $db, $error;
    try {
        $comId = clearInt($comId);
        $postId = clearInt($postId);
        $userId = $db->quote($userId);

        $db->beginTransaction();

        if ($rating === 'like') {
            $sql = "INSERT INTO rating_comments (user_id, com_id, post_id) 
                    VALUES($userId, $comId, $postId);";
            $db->exec($sql);

            $sql = "UPDATE comments SET rating = rating+1 WHERE com_id = $comId;";
            $db->exec($sql); 
        }
        if ($rating === 'unlike') {
            $sql = "DELETE FROM rating_comments WHERE com_id = $comId;";
            $db->exec($sql);

            $sql = "UPDATE comments SET rating = rating-1 WHERE com_id = $comId;";
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
function isUserChangedCommentRating($userId, $comId){
    global $db, $error;
    try {
        $userId = $db->quote($userId);

        $sql = "SELECT id FROM rating_comments WHERE user_id = $userId AND com_id = $comId;";
        $stmt = $db->query($sql);
        if ($stmt != false) {
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            if ($result) {
                return true;
            }
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
    $tags = [];
    try {
        $postId = clearInt($postId);

        $sql = "SELECT tag FROM tag_posts WHERE post_id=$postId;";
        $stmt = $db->query($sql);
        if ($stmt != false) {
            while ($result = $stmt->fetch(PDO::FETCH_ASSOC)) {
                $tags[] = $result;
            }
        }
    } catch (PDOException $e) {
        $error = $e->getMessage();
    }
    return $tags;
}
/* functions for table tag_posts */


/* functions for table subscriptions */
function toSubscribeUser($userIdWantSubscribe, $userId) {
    global $db, $error;
    try {
        $userIdWantSubscribe = clearInt($userIdWantSubscribe);
        $userId = $db->quote($userId);

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
        $userId = $db->quote($userId);

        $sql = "DELETE FROM subscriptions WHERE 
                user_id_want_subscribe = $userIdWantSubscribe AND user_id = $userId;";
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
        $userId = $db->quote($userId);

        $sql = "SELECT id FROM subscriptions WHERE 
                user_id_want_subscribe = $userIdWantSubscribe AND user_id = $userId;";
        $stmt = $db->query($sql);
        if ($stmt != false) {
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            if ($result) {
                return true;
            }
        }
    } catch (PDOException $e) {
        $error = $e->getMessage();
    }
    return false;
}
/* functions for table subscriptions */


/* functions for stab_db.php  */
function isNounForTag($text){
    $symbol1 = 'а';
    $symbol2 = 'ь';
    $tags = [];

    $text = mb_strtolower(clearStr($text));
    $text = str_replace('.', ' ', $text);
    $words = explode(' ', $text);
    if (!empty($words)) {
        foreach ($words as $word) {
            $lastSymbol = mb_substr($word, -1);
            if (mb_strlen($word) > 8 && (mb_strtolower($lastSymbol) === $symbol1 
                || mb_strtolower($lastSymbol) === $symbol2)) {
                $tags[] = "#" . $word;
                $tags = array_unique($tags);
            }
        }
    }
    return $tags;
}
/* functions for stab_db.php  */