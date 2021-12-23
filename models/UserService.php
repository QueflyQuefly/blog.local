<?php
spl_autoload_register(function ($class) {
    require "$class.php";
});

class UserService {
    public $error;
    private $_db;
    public function __construct() {
        $this->_db = DbService::getInstance();
    }
    public function getUserIdByEmail($email) {
        $id = null;
        try {
            $email = $this->_db->quote($email);
    
            $sql = "SELECT user_id FROM users WHERE email = $email;";
            $stmt = $this->_db->query($sql);
            if ($stmt != false) {
                $result = $stmt->fetch(PDO::FETCH_ASSOC);
                $id = $result['user_id'];
            }
        } catch (PDOException $e) {
            $this->error = $e->getMessage();
        }
        return $id;
    }
    public function getUsersByNumber($numberOfUsers , $lessThanMaxId = 0) {
        $users = [];
        try {
            $numberOfUsers  = clearInt($numberOfUsers );
            $lessThanMaxId = clearInt($lessThanMaxId);
            if (empty($lessThanMaxId)) {
                $sql = "SELECT user_id, email, fio, pass_word, date_time, rights 
                        FROM users ORDER BY user_id 
                        DESC LIMIT $numberOfUsers;";
            } else {
                $sql = "SELECT user_id, email, fio, pass_word, date_time, rights 
                        FROM users ORDER BY user_id 
                        DESC LIMIT $lessThanMaxId, $numberOfUsers;";
            }
            $stmt = $this->_db->query($sql);
            if ($stmt != false) {
                while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                    $users[] = $row;
                }
            }
        } catch (PDOException $e) {
            $this->error = $e->getMessage();
            return false;
        }
        return $users;
    }
    public function isUser($email, $password) {
        try {
            $email = $this->_db->quote($email);
            $sql = "SELECT user_id, pass_word FROM users 
                    WHERE email = $email;";
            $stmt = $this->_db->query($sql);
            if ($stmt != false) {
                $result = $stmt->fetch(PDO::FETCH_ASSOC);
                if (password_verify($password, $result['pass_word'])) {
                    return true;
                }
            }
        } catch (PDOException $e) {
            $this->error = $e->getMessage();
        }
        return false;
    }
    public function addUser($email, $fio, $password, $rights = false) {
        try {
            if (!isEmailUnique($email)) {
                return false;
            }
            if ($rights === RIGHTS_SUPERUSER) {
                $rights = $this->_db->quote(RIGHTS_SUPERUSER);
            } else {
                $rights = $this->_db->quote(RIGHTS_USER);
            }
            
            $email = $this->_db->quote($email);
            $fio = $this->_db->quote($fio);
            $date = time();
            $password = $this->_db->quote($password);
    
            $sql = "INSERT INTO users (email, fio, pass_word, date_time, rights) 
                    VALUES ($email, $fio, $password, $date, $rights);";
            if (!$this->_db->exec($sql)) {
                return false;
            }
    
        } catch (PDOException $e) {
            $this->error = $e->getMessage();
        }
        return true;
    }
    public function isEmailUnique($email) {
        try {
            $email = $this->_db->quote($email);
            $sql = "SELECT user_id FROM users WHERE email = $email;";
            $stmt = $this->_db->query($sql);
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            if (!empty($result['user_id'])){
                return false; //если есть совпадения, то логин не является уникальным
            }
        } catch (PDOException $e) {
            $this->error = $e->getMessage();
        }
        return true;
    }
    public function updateUser($userId, $email, $fio, $password) {
        try {
            $userId = clearInt($userId);
            
            $unchangedEmail = $this->getUserInfoById($userId, 'email');
            if ($unchangedEmail != $email) {
                if (!isEmailUnique($email)) {
                    return false;
                }
            }
            $email = $this->_db->quote($email);
            $fio = $this->_db->quote($fio);
    
            $sql = "UPDATE users SET email = $email, fio = $fio 
                    WHERE user_id = $userId;";
            if (!$this->_db->exec($sql)) {
                return false;
            }
    
            if ($password !== false) {
                $password = $this->_db->quote($password);
                $sql = "UPDATE users SET pass_word = $password 
                        WHERE user_id = $userId;";
                $this->_db->exec($sql);
            }
        } catch (PDOException $e) {
            $this->error = $e->getMessage();
        }
        return true;
    }
    public function getUserInfoById($userId, $whatNeeded = ''){
        $userId = clearInt($userId);
        $result = null;
        try {
            switch ($whatNeeded) {
                case 'email': $query = 'email'; break;
                case 'fio': $query = 'fio'; break;
                case 'rights': $query = 'rights'; break;
                case 'date_time': $query = 'date_time'; break;
                default:  $query = 'email, fio, date_time, rights';
            }
            $sql = "SELECT $query FROM users WHERE user_id = $userId;";
            $stmt = $this->_db->query($sql);
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            if (!empty($whatNeeded)) {
                $result = $result[$whatNeeded];
            }
        } catch (PDOException $e) {
            $this->error = $e->getMessage();
        }
        return $result;
    }
    public function searchUsersByFioAndEmail($searchword, $rights = RIGHTS_USER) {
        $results = [];
        try {
            $searchword = clearStr($searchword);
            $searchword = '%' . $searchword . '%';
            $searchword = $this->_db->quote($searchword);
            $sql = "SELECT id, user_id, fio, email, date_time, rights 
                    FROM users WHERE fio LIKE $searchword;";
            $stmt = $this->_db->query($sql);
            if ($stmt != false) {
                while ($result = $stmt->fetch(PDO::FETCH_ASSOC)) {
                    $results[$result['id']] = $result;
                }
            }
            if ($rights === RIGHTS_SUPERUSER) {
                $sql = "SELECT id, user_id, fio, email, date_time, rights 
                        FROM users WHERE email LIKE $searchword;";
                $stmt = $this->_db->query($sql);
                if ($stmt != false) {
                    while ($result = $stmt->fetch(PDO::FETCH_ASSOC)) {
                        $results[$result['id']] = $result;
                    }
                }
            }
        } catch (PDOException $e) {
            $this->error = $e->getMessage();
        }
        return $results;
    }
    public function deleteUserById($userId) {
        $userId = clearstr($userId);
        try {
            $sql = "DELETE FROM users WHERE user_id = $userId;";
            if ($this->_db->exec($sql)) {
                return true;
            }
        } catch (PDOException $e) {
            $this->error = $e->getMessage();
        }
        return false;
    }
}
