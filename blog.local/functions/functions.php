<?php
/* if not connection to  dbname=myblog, run init_db.php */
try{
    $db = new PDO("mysql:host=127.0.0.1;dbname=myblog", 'root', '');
}catch(PDOException $e){
    require_once 'init_db.php';
}


/* general functions */
function clearInt($int){
    return abs((int) $int);
}
function clearStr($str){
    return trim(strip_tags($str));
}
function getLastPostId(){
    global $db, $error;
    try{
        $sql = "SELECT Id FROM Posts;";
        $stmt = $db->query($sql);
        if(!$stmt) return false;
        while($row = $stmt->fetch()){
            $id = $row['Id'];
        }
    } catch (PDOException $e){
        $error = $e->getMessage();
    }
    return $id;
}
function addAdmin($login, $fio, $password){
    global $db, $error;
    try{

        $login = $db->quote($login);
        $fio = $db->quote($fio);
        $password = $db->quote($password);
        echo $password;

        $sql = "INSERT INTO Users(Login, Fio, Password, Rights) 
                VALUES ($login, $fio, $password, 'superuser');";
        $db->exec($sql);

    }catch(PDOException $e){
        $error = $e->getMessage();  
    }
}
/* general functions */


/* functions for index.php */
function getPostsForIndexById($id){
    global $db, $error;
    try{
        $sql = "SELECT Name, Author, Date, Content FROM Posts WHERE Id = $id;";
        $stmt = $db->query($sql);
        $post = $stmt->fetch(PDO::FETCH_ASSOC);
        $post['Name'] = mb_substr($post['Name'], 0, 140);
        if(mb_strlen($post['Name'], 'utf-8') > 100) 
            $post['Name'] = $post['Name'] . "&hellip;";

        $post['Contentsmall'] = mb_substr($post['Content'], 0, 140);
        if(mb_strlen($post['Contentsmall'], 'utf-8') > 120) 
            $post['Contentsmall'] = $post['Contentsmall'] . "&hellip;";

        $post['Content'] = mb_substr(nl2br($post['Content']), 0, 320);
        if(mb_strlen($post['Content'], 'utf-8') > 270) 
            $post['Content'] = $post['Content'] . "&hellip;";

        $post['Namesmall'] = mb_substr($post['Name'], 0, 45);
        if(mb_strlen($post['Namesmall'], 'utf-8') > 40) 
            $post['Namesmall'] = $post['Namesmall'] . "&hellip;";

        $post['Author'] = " &copy; " . $post['Author'];
        $post['Date'] = date("d.m.Y",$post['Date']) ." в ". date("H:i", $post['Date']);
    }catch(PDOException $e){
        $error = $e->getMessage();
    }
    return $post;
}
/* functions for index.php */


/* functions for reg.php and login.php */
function isUser($login, $password){
    global $db, $error;
    global $fio;

    $users = []; 
    try{
        $sql = "SELECT Login, Fio, Password FROM Users";
        $stmt = $db->query($sql);
        while($user = $stmt->fetch(PDO::FETCH_ASSOC))
            $users[] = $user;

    }catch(PDOException $e){
        $error = $e->getMessage();
    }
    foreach ($users as $user){
        if ($login == $user['Login'] && $password == $user['Password']){
            $fio = $user['Fio'];
            return true;
        }
    }
    return false;
}
function getRightsByLogin($login){
    global $db, $error;

    $users = []; 
    try{
        $login = $db->quote($login);
        $sql = "SELECT Rights FROM Users WHERE Login = $login;";

        $stmt = $db->query($sql);
        if(!$stmt) return false;
        $rights = $stmt->fetch(PDO::FETCH_ASSOC);
    }catch(PDOException $e){
        $error = $e->getMessage();
    }
    return $rights['Rights'];
}
function createUser($login, $fio, $password){
    global $db, $error;
    try{
        $db->beginTransaction();

        if(!isLoginUnique($login)) return false;

        $login = $db->quote($login);
        $fio = $db->quote($fio);
        $password = $db->quote($password);

        $sql = "INSERT INTO Users (Login, Fio, Password, Rights) 
        VALUES($login, $fio, $password, 'user');";
        $db->exec($sql);

        $db->commit();
    }catch(PDOException $e){
        $db->rollBack();
        $error = $e->getMessage();
    }
    return true;
}
function isLoginUnique($login){
    global $db, $error;
    $logins = [];
    try{
        $sql = "SELECT Login FROM Users;";
        $stmt = $db->query($sql);
        if(!$stmt) return true;
        while($data = $stmt->fetch(PDO::FETCH_ASSOC))
            $logins[] = $data;
    }catch(PDOException $e){
        $error = $e->getMessage();
    }
    foreach ($logins as $value){
        if ($login == $value['Login']){
            return false; //если есть совпадения, то логин не является уникальным
        }
    }
    return true;
}
/* functions for reg.php and login.php */


/* functions for viewsinglepost.php */
function getPostForViewById($id){
    global $db, $error;
    try{
        $sql = "SELECT Name, Author, Date, Content FROM Posts WHERE Id = $id;";
        $stmt = $db->query($sql);
        if(!$stmt) return false;
        $post = $stmt->fetch(PDO::FETCH_ASSOC);
        $post['Content'] = str_replace("<br />", "<p>", nl2br($post['Content']));
        $post['Date'] = date("d.m.Y",$post['Date']) ." в ". date("H:i", $post['Date']);
    }catch(PDOException $e){
        $error = $e->getMessage();
    }
    return $post;
}
function getCommentsByPostId($postid){
    global $db, $error;
    try{
        $sql = "SELECT Author, Date, Content FROM Comments WHERE Postid = $postid;";
        $stmt = $db->query($sql);
        if(!$stmt) return false;
        while($result = $stmt->fetch(PDO::FETCH_ASSOC)){
            $comments[] = $result;
        }
    }catch(PDOException $e){
        $error = $e->getMessage();
    }
    return $comments;
}

function insertComments($id, $commentAuthor, $commentDate, $commentContent){
    global $db, $error;
    try{
        $db->beginTransaction();

        $author = $db->quote($commentAuthor);
        $date = $commentDate;
        $content = $db->quote($commentContent);
        $content = trim(strip_tags($content));

        $sql = "INSERT INTO Comments (Postid, Author, Date, Content) 
        VALUES($id, $author, $date, $content)";
        $db->exec($sql);

        $db->commit();
    }catch(PDOException $e){
        $db->rollBack();
        $error = $e->getMessage();
    }
}
/* functions for viewsinglepost.php */


/* functions for addpost.php */
function insertToPosts($name, $author, $content){
    global $db, $error;
    $date = time();

    try{

        $name = $db->quote($name);
        $author = $db->quote($author);
        $content = $db->quote($content);

        $sql = "INSERT INTO Posts (Name, Author, Date, Content) 
        VALUES($name, $author, $date, $content);";

        $db->query($sql);
    }catch(PDOException $e){
        $db->rollBack();
        $error = $e->getMessage();
    }
}
/* functions for addpost.php */


/* functions for admin/ */
function deletePostById($id){
    global $db, $error;
    
    try{  
        $id = clearInt($id);

        $lastId = getLastPostId();

        /* Удаляю пост */
        $sql = "DELETE FROM Posts WHERE Id = $id;";
        $db->exec($sql);
        /* Удаляю его картинку */
        unlink("..\images\PostImgId$id.jpg");

        /* Удаляю все комментарии, связанные с постом */
        $sql = "DELETE FROM Comments WHERE Postid = $id;";
        $db->exec($sql);
        
        /* Переписываю все Id в Comments */
        $sql = "SET @num := 0; UPDATE Comments SET Id = @num := (@num+1); 
        ALTER TABLE Comments AUTO_INCREMENT = 1;";
        $db->exec($sql);

        if($id != $lastId){

            /* Переписываю все Id в Posts */
            $sql = "SET @num := 0; UPDATE Posts SET Id = @num := (@num+1); 
            ALTER TABLE Posts AUTO_INCREMENT = 1;";
            $db->exec($sql);

            /* Переписываю все Postid в Comments, если был удалён не последний пост */
            $sql = "UPDATE Comments SET Postid=Postid-1 WHERE Postid >= $id;";
            $db->exec($sql);
            
            /* здесь изменяю Id у картинок */
            for($i=$id+1; $i <= $lastId; $i++){
                $j = $i - 1;
                rename("..\images\PostImgId$i.jpg", "..\images\PostImgId$j.jpg");
            }
        }
    }catch(PDOException $e){
        $error = $e->getMessage();
    }
}
function connectToUsers(){
    global $db, $error;
    try{
        $sql = "SELECT Id, Login, Fio, Password, Rights FROM Users;";
        $stmt = $db->query($sql);
        while($arr = $stmt->fetch(PDO::FETCH_ASSOC)){
            $users[] = $arr;
        }
        return $users;
    }catch(PDOException $e){
        $error = $e->getMessage();
        return false;
    }
}
function deleteUserById($id){
    global $db, $error;
    $id = clearInt($id);
    try{
        $sql = "DELETE FROM Users WHERE Id = $id;";
        $db->exec($sql);
    }catch(PDOException $e){
        $error = $e->getMessage();
    }
}
